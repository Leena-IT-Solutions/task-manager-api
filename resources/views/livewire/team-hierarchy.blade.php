<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                Organizational Team Hierarchy
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Multi-level employee reporting structure and delegation channels
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-300 font-medium text-xs border border-indigo-500/30 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Manage Employees Directory</span>
            </a>
        </div>
    </div>

    <!-- Flash message -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- UNASSIGNED EMPLOYEES POOL (Staff added to directory but not yet in hierarchy) -->
    @if($unassignedEmployees->isNotEmpty())
        <div class="mb-8 p-6 rounded-3xl bg-amber-500/10 border border-amber-500/30 shadow-xl">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-white">Unassigned Employees Pool ({{ $unassignedEmployees->count() }})</h2>
                        <p class="text-xs text-amber-300/80">These employees are registered in the directory but have not yet been placed under a manager in the hierarchy tree.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($unassignedEmployees as $unassigned)
                    <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-700/80 flex items-center justify-between gap-3 shadow-md">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-300 font-bold flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($unassigned->name, 0, 1)) }}
                            </div>
                            <div class="truncate">
                                <div class="text-sm font-bold text-white truncate">{{ $unassigned->name }}</div>
                                <div class="text-xs text-slate-400 truncate">{{ $unassigned->designation ?? $unassigned->role }} • {{ $unassigned->department }}</div>
                            </div>
                        </div>

                        <button wire:click="openAssignModal({{ $unassigned->id }})"
                                class="shrink-0 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 transition">
                            Place in Tree
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ORGANIZATIONAL TREE -->
    <div class="p-6 sm:p-8 rounded-3xl bg-slate-800/80 border border-slate-700/60 shadow-xl">
        <h2 class="text-base font-bold text-white mb-6 flex items-center gap-2">
            <span>Organizational Tree Structure</span>
        </h2>

        @foreach($roots as $root)
            <div class="space-y-6">
                <!-- Root Leader Card -->
                <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-950/60 to-slate-900 border border-indigo-500/30 shadow-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg text-lg">
                                {{ strtoupper(substr($root->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-white">{{ $root->name }}</h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        {{ $root->role }}
                                    </span>
                                </div>
                                <div class="text-xs text-indigo-400 font-medium">{{ $root->designation ?? 'Organization Leader' }}</div>
                                <div class="text-xs text-slate-400 mt-1 flex flex-wrap gap-x-4 gap-y-1">
                                    <span>📧 {{ $root->email }}</span>
                                    <span>📱 {{ $root->mobile }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-xs text-slate-400 bg-slate-800/80 px-3.5 py-2 rounded-xl border border-slate-700">
                                Direct Reports: <strong class="text-white">{{ $root->subordinates->count() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Level 2 Subordinates -->
                @if($root->subordinates->isNotEmpty())
                    <div class="ms-4 sm:ms-8 ps-4 sm:ps-6 border-l-2 border-indigo-500/40 space-y-6">
                        @foreach($root->subordinates as $sub1)
                            <div class="space-y-4">
                                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-700 shadow-md">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-600/40 border border-indigo-500/30 flex items-center justify-center font-bold text-indigo-300">
                                                {{ strtoupper(substr($sub1->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="text-sm font-bold text-white">{{ $sub1->name }}</h4>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-400">
                                                        {{ $sub1->role }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-slate-400">{{ $sub1->designation ?? 'Manager' }} • 📧 {{ $sub1->email }} • 📱 {{ $sub1->mobile }}</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <div class="text-xs text-slate-400 bg-slate-800/60 px-3 py-1.5 rounded-lg">
                                                Team: <strong class="text-indigo-400">{{ $sub1->subordinates->count() }}</strong>
                                            </div>
                                            @if(auth()->user()->isManager())
                                                <button wire:click="openAssignModal({{ $sub1->id }})" title="Change reporting manager"
                                                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Level 3 Subordinates -->
                                @if($sub1->subordinates->isNotEmpty())
                                    <div class="ms-4 sm:ms-8 ps-4 sm:ps-6 border-l-2 border-slate-700 space-y-3">
                                        @foreach($sub1->subordinates as $sub2)
                                            <div class="p-3.5 rounded-xl bg-slate-900/50 border border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">
                                                        {{ strtoupper(substr($sub2->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <div class="text-sm font-semibold text-slate-200">{{ $sub2->name }}</div>
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] uppercase font-semibold bg-emerald-500/10 text-emerald-400">
                                                                {{ $sub2->role }}
                                                            </span>
                                                        </div>
                                                        <div class="text-xs text-slate-400">{{ $sub2->designation ?? 'Associate' }} • 📧 {{ $sub2->email }} • 📱 {{ $sub2->mobile }}</div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    @if($sub2->subordinates->isNotEmpty())
                                                        <div class="text-xs text-slate-500">
                                                            Reports: <strong class="text-slate-300">{{ $sub2->subordinates->count() }}</strong>
                                                        </div>
                                                    @endif
                                                    @if(auth()->user()->isManager())
                                                        <button wire:click="openAssignModal({{ $sub2->id }})" title="Change reporting manager"
                                                                class="p-1 rounded text-slate-400 hover:text-white hover:bg-slate-800">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Level 4 Subordinates -->
                                            @if($sub2->subordinates->isNotEmpty())
                                                <div class="ms-6 ps-4 border-l border-slate-700/60 space-y-2">
                                                    @foreach($sub2->subordinates as $sub3)
                                                        <div class="p-2.5 rounded-lg bg-slate-900/30 border border-slate-800 text-xs text-slate-300 flex items-center justify-between">
                                                            <div>
                                                                <span class="font-medium text-white">{{ $sub3->name }}</span>
                                                                <span class="text-slate-500">({{ $sub3->designation ?? 'Field Staff' }})</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-slate-400">📱 {{ $sub3->mobile }}</span>
                                                                @if(auth()->user()->isManager())
                                                                    <button wire:click="openAssignModal({{ $sub3->id }})" title="Change reporting manager"
                                                                            class="p-1 text-slate-400 hover:text-white">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                                        </svg>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Quick Assign Modal inside Hierarchy -->
    @if($showAssignModal && $selectedEmployee)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" wire:click="$set('showAssignModal', false)"></div>

            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-3xl bg-slate-800 border border-slate-700 p-6 sm:p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-white">Place in Hierarchy Tree</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Select who this employee reports to</p>
                        </div>
                        <button wire:click="$set('showAssignModal', false)" class="text-slate-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Target Employee Card -->
                    <div class="mb-5 p-4 rounded-2xl bg-slate-900/80 border border-slate-700/80 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/30 text-indigo-400 border border-indigo-500/20 flex items-center justify-center font-bold">
                            {{ strtoupper(substr($selectedEmployee->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white">{{ $selectedEmployee->name }}</div>
                            <div class="text-xs text-slate-400">{{ $selectedEmployee->designation ?? $selectedEmployee->role }} • {{ $selectedEmployee->department }}</div>
                        </div>
                    </div>

                    <form wire:submit="assignHierarchy" class="space-y-4">
                        <div>
                            <label class="block text-xs uppercase font-semibold text-slate-400 mb-1.5">
                                Select Reporting Supervisor
                            </label>
                            <select wire:model="selectedManagerId"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">None (Unassigned / Independent)</option>
                                @foreach($this->eligibleManagers as $mgr)
                                    <option value="{{ $mgr->id }}">
                                        {{ $mgr->name }} ({{ $mgr->designation ?? $mgr->role }}) - {{ $mgr->department }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedManagerId')
                                <span class="text-xs text-rose-400 block mt-1.5">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-3">
                            <button type="button" wire:click="$set('showAssignModal', false)"
                                    class="px-4 py-2 rounded-xl bg-slate-700 text-slate-300 hover:bg-slate-600 text-sm font-medium">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium shadow-md shadow-indigo-600/30">
                                Save Hierarchy Placement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
