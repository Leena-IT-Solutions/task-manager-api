<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskUpdate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * List tasks scoped by user hierarchy and role.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Task::with(['creator:id,name,email,designation', 'assignee:id,name,email,designation', 'subtasks'])
            ->whereNull('parent_id'); // top-level tasks by default

        // Hierarchy Scoping
        if ($user->isAdmin()) {
            // Admin can see everything
        } elseif ($user->isManager()) {
            $subordinateIds = $user->allSubordinateIds();
            $allowedIds = array_merge([$user->id], $subordinateIds);

            $query->where(function ($q) use ($user, $allowedIds) {
                $q->whereIn('assigned_to', $allowedIds)
                    ->orWhere('created_by', $user->id);
            });
        } else {
            // Standard Employee: can only see assigned tasks
            $query->where('assigned_to', $user->id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $tasks = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => true,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Create a new task or delegated subtask.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['required', 'exists:users,id'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'due_at' => ['nullable', 'date'],
            'parent_id' => ['nullable', 'exists:tasks,id'],
        ]);

        // Hierarchy validation: check if assignee is allowed
        if (! $user->isAdmin()) {
            $allowedIds = array_merge([$user->id], $user->allSubordinateIds());
            if (! in_array((int) $request->assigned_to, $allowedIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can only assign tasks to yourself or your reporting team members.',
                ], 403);
            }
        }

        $task = Task::create([
            'parent_id' => $request->parent_id,
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $user->id,
            'assigned_to' => $request->assigned_to,
            'priority' => $request->priority ?? 'medium',
            'status' => 'pending',
            'due_at' => $request->due_at,
        ]);

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'type' => 'comment',
            'comment' => 'Task created and assigned.',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Task created successfully',
            'task' => $task->load(['creator', 'assignee']),
        ], 201);
    }

    /**
     * Get single task with details, subtasks, and updates.
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        // Check view permission
        if (! $this->canAccessTask($user, $task)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized to view this task.',
            ], 403);
        }

        $task->load([
            'creator:id,name,email,designation',
            'assignee:id,name,email,designation',
            'subtasks.assignee:id,name,email,designation',
            'updates.user:id,name,designation',
        ]);

        return response()->json([
            'status' => true,
            'task' => $task,
        ]);
    }

    /**
     * Update task status (e.g. In Progress, Under Review, Completed).
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (! $this->canAccessTask($user, $task)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized to modify this task.',
            ], 403);
        }

        $request->validate([
            'status' => ['required', 'in:pending,in_progress,under_review,completed,cancelled'],
            'comment' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'], // 10MB
        ]);

        $oldStatus = $task->status;
        $newStatus = $request->status;

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('task_attachments', 'public');
        }

        $task->status = $newStatus;
        if ($newStatus === 'completed') {
            $task->completed_at = now();
        } elseif ($oldStatus === 'completed' && $newStatus !== 'completed') {
            $task->completed_at = null;
        }
        $task->save();

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'type' => 'status_change',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'comment' => $request->comment ?? "Status changed from {$oldStatus} to {$newStatus}.",
            'attachment' => $attachmentPath,
        ]);

        return response()->json([
            'status' => true,
            'message' => "Task status updated to {$newStatus}",
            'task' => $task->fresh(['creator', 'assignee', 'updates.user']),
        ]);
    }

    /**
     * Add a comment or submission note to a task.
     */
    public function addComment(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (! $this->canAccessTask($user, $task)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'comment' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('task_attachments', 'public');
        }

        $update = TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'type' => 'comment',
            'comment' => $request->comment,
            'attachment' => $attachmentPath,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Comment added',
            'update' => $update->load('user:id,name,designation'),
        ]);
    }

    private function canAccessTask(User $user, Task $task): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if ($task->created_by === $user->id || $task->assigned_to === $user->id) {
            return true;
        }
        $subordinateIds = $user->allSubordinateIds();

        return in_array($task->assigned_to, $subordinateIds) || in_array($task->created_by, $subordinateIds);
    }
}
