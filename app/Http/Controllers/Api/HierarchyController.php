<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HierarchyController extends Controller
{
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

        // If admin, start from root nodes (reporting_to_id is null)
        // If manager or employee, start from their own node
        if ($user->isAdmin()) {
            $roots = User::whereNull('reporting_to_id')
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
