<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all';
    public string $hierarchyFilter = 'all'; // all, assigned, unassigned

    // Modal state for Search & Add Employee by Email/Mobile
    public bool $showLookupModal = false;
    public string $lookupInput = '';
    public ?int $foundUserId = null;
    public ?string $lookupError = null;
    public bool $hasSearched = false;

    // Assignment fields for the found user
    public string $assignRole = 'employee';
    public string $assignDesignation = '';
    public string $assignDepartment = '';
    public ?int $assignReportingToId = null;

    // Modal state for Assign Hierarchy (reassign existing)
    public bool $showAssignModal = false;
    public ?int $selectedEmployeeId = null;
    public ?int $selectedManagerId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
        'hierarchyFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingHierarchyFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Open the Add Employee Modal (Search by Email or Mobile)
     */
    public function openLookupModal(): void
    {
        $this->reset([
            'lookupInput',
            'foundUserId',
            'lookupError',
            'hasSearched',
            'assignDesignation',
            'assignDepartment',
        ]);
        $this->assignRole = 'employee';
        $this->assignReportingToId = Auth::user()->isManager() ? Auth::id() : null;
        $this->showLookupModal = true;
    }

    /**
     * Search employee by Email or Mobile number
     */
    public function searchUser(): void
    {
        $this->validate([
            'lookupInput' => ['required', 'string', 'min:3'],
        ], [
            'lookupInput.required' => 'Please enter an Email address or Mobile number.',
        ]);

        $input = trim($this->lookupInput);
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);
        $digitsOnly = preg_replace('/[^0-9]/', '', $input);

        $query = User::query();

        if ($isEmail) {
            $user = $query->where('email', $input)->first();
        } elseif (strlen($digitsOnly) >= 7) {
            $user = $query->where('mobile', $digitsOnly)->orWhere('mobile', 'like', "%{$digitsOnly}%")->first();
        } else {
            $user = $query->where('email', $input)->orWhere('mobile', $input)->first();
        }

        $this->hasSearched = true;

        if (! $user) {
            $this->foundUserId = null;
            $this->lookupError = "User not found with '{$input}'. Employees must create and register their account first. Once registered, you can add them to the hierarchy.";
            return;
        }

        $this->foundUserId = $user->id;
        $this->lookupError = null;

        // Pre-fill existing or default details
        $this->assignRole = $user->role ?: 'employee';
        $this->assignDesignation = $user->designation ?: '';
        $this->assignDepartment = $user->department ?: 'Operations';
        $this->assignReportingToId = $user->reporting_to_id ?: (Auth::user()->isManager() ? Auth::id() : null);
    }

    /**
     * Confirm adding the found user to the hierarchy
     */
    public function confirmAddToHierarchy(): void
    {
        if (! $this->foundUserId) {
            return;
        }

        $user = User::findOrFail($this->foundUserId);

        $this->validate([
            'assignRole' => ['required', 'in:admin,manager,employee'],
            'assignDesignation' => ['nullable', 'string', 'max:255'],
            'assignDepartment' => ['nullable', 'string', 'max:255'],
            'assignReportingToId' => ['nullable', 'exists:users,id'],
        ]);

        if ($this->assignReportingToId) {
            if ($user->cannotReportTo((int) $this->assignReportingToId)) {
                $this->addError('assignReportingToId', 'Cannot assign this manager because it would cause a circular reporting loop.');
                return;
            }
        }

        $user->role = $this->assignRole;
        $user->designation = $this->assignDesignation ?: 'Staff Member';
        $user->department = $this->assignDepartment ?: 'Operations';
        $user->reporting_to_id = $this->assignReportingToId ? (int) $this->assignReportingToId : null;
        $user->save();

        $managerName = $user->manager ? $user->manager->name : 'Top Level (No Manager)';

        $this->showLookupModal = false;
        $this->reset(['lookupInput', 'foundUserId', 'lookupError', 'hasSearched']);

        session()->flash('message', "{$user->name} has been successfully added to the team hierarchy under {$managerName}!");
    }

    /**
     * Open modal to change hierarchy for existing employee in table
     */
    public function openAssignModal(int $employeeId): void
    {
        $employee = User::findOrFail($employeeId);
        $this->selectedEmployeeId = $employee->id;
        $this->selectedManagerId = $employee->reporting_to_id;
        $this->showAssignModal = true;
    }

    public function assignHierarchy(): void
    {
        if (! $this->selectedEmployeeId) {
            return;
        }

        $employee = User::findOrFail($this->selectedEmployeeId);

        if ($this->selectedManagerId) {
            if ($employee->cannotReportTo($this->selectedManagerId)) {
                $this->addError('selectedManagerId', 'Cannot assign this manager because it would cause a circular reporting loop.');
                return;
            }
        }

        $employee->reporting_to_id = $this->selectedManagerId ?: null;
        $employee->save();

        $this->showAssignModal = false;
        $this->reset(['selectedEmployeeId', 'selectedManagerId']);
        session()->flash('message', "Hierarchy placement for {$employee->name} updated successfully!");
    }

    public function toggleStatus(int $userId): void
    {
        $user = User::findOrFail($userId);
        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot deactivate your own account.');
            return;
        }

        $user->is_active = ! $user->is_active;
        $user->save();
        session()->flash('message', "User status updated to " . ($user->is_active ? 'Active' : 'Inactive'));
    }

    public function getEligibleManagersProperty()
    {
        $targetId = $this->selectedEmployeeId ?: $this->foundUserId;

        if (! $targetId) {
            return User::whereIn('role', ['admin', 'manager'])->where('is_active', true)->orderBy('name')->get();
        }

        $targetUser = User::find($targetId);
        if (! $targetUser) {
            return collect();
        }

        $invalidIds = array_merge([$targetUser->id], $targetUser->allSubordinateIds());

        return User::whereNotIn('id', $invalidIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $query = User::with(['manager', 'subordinates']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('mobile', 'like', "%{$this->search}%")
                    ->orWhere('designation', 'like', "%{$this->search}%");
            });
        }

        if ($this->roleFilter !== 'all') {
            $query->where('role', $this->roleFilter);
        }

        if ($this->hierarchyFilter === 'assigned') {
            $query->whereNotNull('reporting_to_id');
        } elseif ($this->hierarchyFilter === 'unassigned') {
            $query->whereNull('reporting_to_id')->where('role', '!=', 'admin');
        }

        $employees = $query->orderBy('name')->paginate(12);

        $totalEmployees = User::count();
        $unassignedCount = User::whereNull('reporting_to_id')->where('role', '!=', 'admin')->count();
        $assignedCount = User::whereNotNull('reporting_to_id')->count();

        $selectedEmployee = $this->selectedEmployeeId ? User::find($this->selectedEmployeeId) : null;
        $foundUser = $this->foundUserId ? User::with('manager')->find($this->foundUserId) : null;

        return view('livewire.employee-management', [
            'employees' => $employees,
            'totalEmployees' => $totalEmployees,
            'unassignedCount' => $unassignedCount,
            'assignedCount' => $assignedCount,
            'selectedEmployee' => $selectedEmployee,
            'foundUser' => $foundUser,
        ]);
    }
}
