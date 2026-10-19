<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all';
    public string $hierarchyFilter = 'all'; // all, assigned, unassigned

    // Modal state for Add Employee
    public bool $showAddModal = false;
    public string $name = '';
    public string $email = '';
    public string $mobile = '';
    public string $role = 'employee';
    public string $designation = '';
    public string $department = '';
    public string $password = 'password';

    // Modal state for Assign Hierarchy
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

    public function openAddModal(): void
    {
        $this->reset(['name', 'email', 'mobile', 'designation', 'department']);
        $this->role = 'employee';
        $this->password = 'password';
        $this->showAddModal = true;
    }

    public function addEmployee(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'role' => ['required', 'in:admin,manager,employee'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'role' => $this->role,
            'designation' => $this->designation ?: 'Staff Member',
            'department' => $this->department ?: 'General',
            'reporting_to_id' => null, // Added to directory first without hierarchy
            'password' => Hash::make($this->password),
            'is_active' => true,
        ]);

        $this->showAddModal = false;
        $this->reset(['name', 'email', 'mobile', 'designation', 'department']);
        session()->flash('message', 'Employee successfully added to the directory! You can now assign them in the hierarchy.');
    }

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
        session()->flash('message', 'Hierarchy placement updated successfully!');
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
        if (! $this->selectedEmployeeId) {
            return User::whereIn('role', ['admin', 'manager'])->where('is_active', true)->orderBy('name')->get();
        }

        $employee = User::find($this->selectedEmployeeId);
        if (! $employee) {
            return collect();
        }

        $invalidIds = array_merge([$employee->id], $employee->allSubordinateIds());

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

        return view('livewire.employee-management', [
            'employees' => $employees,
            'totalEmployees' => $totalEmployees,
            'unassignedCount' => $unassignedCount,
            'assignedCount' => $assignedCount,
            'selectedEmployee' => $selectedEmployee,
        ]);
    }
}
