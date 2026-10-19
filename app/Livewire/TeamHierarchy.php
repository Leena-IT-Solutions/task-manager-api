<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeamHierarchy extends Component
{
    public bool $showAssignModal = false;
    public ?int $selectedEmployeeId = null;
    public ?int $selectedManagerId = null;

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
        session()->flash('message', 'Hierarchy updated successfully!');
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
        $user = Auth::user();

        // 1. Get Top Leaders / Root Nodes
        if ($user->isAdmin()) {
            $roots = User::whereNull('reporting_to_id')
                ->where('role', 'admin')
                ->where('is_active', true)
                ->with(['subordinates.subordinates.subordinates', 'assignedTasks'])
                ->get();

            // Unassigned Employees (not admin and have no reporting_to_id)
            $unassignedEmployees = User::whereNull('reporting_to_id')
                ->where('role', '!=', 'admin')
                ->where('is_active', true)
                ->get();
        } else {
            $roots = User::where('id', $user->id)
                ->with(['subordinates.subordinates', 'assignedTasks'])
                ->get();

            $unassignedEmployees = collect();
        }

        $selectedEmployee = $this->selectedEmployeeId ? User::find($this->selectedEmployeeId) : null;

        return view('livewire.team-hierarchy', [
            'roots' => $roots,
            'unassignedEmployees' => $unassignedEmployees,
            'selectedEmployee' => $selectedEmployee,
        ]);
    }
}
