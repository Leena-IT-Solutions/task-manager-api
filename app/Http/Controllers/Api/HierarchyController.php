<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HierarchyController extends Controller
{
    /**
     * List all employees in directory with hierarchy status.
     */
    public function listEmployees(Request $request): JsonResponse
    {
        $employees = User::with(['manager:id,name,designation,email', 'subordinates:id,name,designation'])
            ->latest()
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'status' => true,
            'employees' => $employees,
            'unassigned_count' => User::whereNull('reporting_to_id')->where('role', '!=', 'admin')->count(),
        ]);
    }

    /**
     * Step 1: Add new employee to directory (without hierarchy).
     */
    public function storeEmployee(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'role' => ['required', 'in:admin,manager,employee'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'role' => $request->role,
            'designation' => $request->designation ?: 'Staff Member',
            'department' => $request->department ?: 'General',
            'reporting_to_id' => null, // Added to directory first
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Employee added to directory. Ready to be assigned in hierarchy.',
            'employee' => $employee,
        ], 201);
    }

    /**
     * Step 2: Assign or change reporting manager in hierarchy.
     */
    public function assignManager(Request $request, User $employee): JsonResponse
    {
        $request->validate([
            'manager_id' => ['nullable', 'exists:users,id'],
        ]);

        $managerId = $request->manager_id ? (int) $request->manager_id : null;

        if ($managerId) {
            if ($employee->cannotReportTo($managerId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot assign this manager because it would cause a circular reporting loop.',
                ], 422);
            }
        }

        $employee->reporting_to_id = $managerId;
        $employee->save();

        return response()->json([
            'status' => true,
            'message' => 'Employee hierarchy placement updated successfully.',
            'employee' => $employee->fresh(['manager', 'subordinates']),
        ]);
    }

    /**
     * Get list of employees the current user can assign tasks to.
     */
    public function assignableEmployees(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $employees = User::where('is_active', true)
                ->select(['id', 'name', 'email', 'mobile', 'role', 'designation', 'department', 'reporting_to_id'])
                ->get();
        } else {
            $subordinateIds = $user->allSubordinateIds();
            $allowedIds = array_merge([$user->id], $subordinateIds);

            $employees = User::whereIn('id', $allowedIds)
                ->where('is_active', true)
                ->select(['id', 'name', 'email', 'mobile', 'role', 'designation', 'department', 'reporting_to_id'])
                ->get();
        }

        return response()->json([
            'status' => true,
            'employees' => $employees,
        ]);
    }

    /**
     * Get hierarchy organizational chart tree.
     */
    public function teamTree(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $roots = User::whereNull('reporting_to_id')
                ->where('role', 'admin')
                ->where('is_active', true)
                ->with('subordinates')
                ->get();
            $tree = $roots->map(fn ($root) => $this->buildNode($root));
        } else {
            $tree = [$this->buildNode($user)];
        }

        return response()->json([
            'status' => true,
            'tree' => $tree,
            'unassigned' => User::whereNull('reporting_to_id')->where('role', '!=', 'admin')->get(['id', 'name', 'email', 'mobile', 'designation']),
        ]);
    }

    private function buildNode(User $user): array
    {
        $subordinates = $user->subordinates()->where('is_active', true)->get();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'role' => $user->role,
            'designation' => $user->designation,
            'department' => $user->department,
            'subordinates' => $subordinates->map(fn ($child) => $this->buildNode($child))->values(),
        ];
    }
}
