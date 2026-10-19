<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\TaskUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardOverview extends Component
{
    public string $title = '';
    public string $description = '';
    public ?int $assigned_to = null;
    public string $priority = 'medium';
    public ?string $due_at = null;

    public string $statusFilter = 'all';
    public bool $showCreateModal = false;

    public function mount(): void
    {
        $firstAssignable = $this->assignableEmployees->first();
        if ($firstAssignable) {
            $this->assigned_to = $firstAssignable->id;
        }
    }

    public function getAssignableEmployeesProperty()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return User::where('is_active', true)->orderBy('name')->get();
        }

        $subordinateIds = $user->allSubordinateIds();
        $allowedIds = array_merge([$user->id], $subordinateIds);

        return User::whereIn('id', $allowedIds)->where('is_active', true)->orderBy('name')->get();
    }

    public function createTask(): void
    {
        $user = Auth::user();

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['required', 'exists:users,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'due_at' => ['nullable', 'date'],
        ]);

        $task = Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'created_by' => $user->id,
            'assigned_to' => $this->assigned_to,
            'priority' => $this->priority,
            'status' => 'pending',
            'due_at' => $this->due_at,
        ]);

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'type' => 'comment',
            'comment' => 'Task created and delegated.',
        ]);

        $this->reset(['title', 'description', 'due_at']);
        $this->showCreateModal = false;
        session()->flash('message', 'Task assigned successfully!');
    }

    public function updateTaskStatus(int $taskId, string $newStatus): void
    {
        $user = Auth::user();
        $task = Task::findOrFail($taskId);

        $oldStatus = $task->status;
        $task->status = $newStatus;
        if ($newStatus === 'completed') {
            $task->completed_at = now();
        }
        $task->save();

        TaskUpdate::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'type' => 'status_change',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'comment' => "Status changed from {$oldStatus} to {$newStatus}.",
        ]);

        session()->flash('message', "Task status updated to {$newStatus}.");
    }

    public function render()
    {
        $user = Auth::user();

        $query = Task::with(['creator', 'assignee'])->whereNull('parent_id');

        if ($user->isAdmin()) {
            // All tasks
        } elseif ($user->isManager()) {
            $subordinateIds = $user->allSubordinateIds();
            $allowedIds = array_merge([$user->id], $subordinateIds);
            $query->where(function ($q) use ($user, $allowedIds) {
                $q->whereIn('assigned_to', $allowedIds)->orWhere('created_by', $user->id);
            });
        } else {
            $query->where('assigned_to', $user->id);
        }

        $allTasksCount = (clone $query)->count();
        $inProgressCount = (clone $query)->where('status', 'in_progress')->count();
        $underReviewCount = (clone $query)->where('status', 'under_review')->count();
        $completedCount = (clone $query)->where('status', 'completed')->count();

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $tasks = $query->latest()->take(10)->get();

        return view('livewire.dashboard-overview', [
            'tasks' => $tasks,
            'allTasksCount' => $allTasksCount,
            'inProgressCount' => $inProgressCount,
            'underReviewCount' => $underReviewCount,
            'completedCount' => $completedCount,
        ]);
    }
}
