<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeamHierarchy extends Component
{
    public function render()
    {
        $user = Auth::user();

        // If admin, load from root level
        if ($user->isAdmin()) {
            $roots = User::whereNull('reporting_to_id')
                ->where('is_active', true)
                ->with(['subordinates.subordinates.subordinates', 'assignedTasks'])
                ->get();
        } else {
            $roots = User::where('id', $user->id)
                ->with(['subordinates.subordinates', 'assignedTasks'])
                ->get();
        }

        return view('livewire.team-hierarchy', [
            'roots' => $roots,
        ]);
    }
}
