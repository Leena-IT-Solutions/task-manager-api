<div>
    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                Employee Directory
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Add staff to the company directory, then configure reporting lines in the hierarchy
            </p>
        </div>

        @if(auth()->user()->isManager())
            <button wire:click="openAddModal"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium shadow-lg shadow-indigo-600/30 transition transform active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>Add New Employee</span>
            </button>
        @endif
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Workflow Information Banner -->
    <div class="mb-8 p-5 rounded-2xl bg-gradient-to-r from-slate-800 to-indigo-950/40 border border-indigo-500/20 shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="p-2.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-white">Two-Step Employee Onboarding</h3>
                <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                    <strong>Step 1:</strong> Add an employee with their login credentials (Email, Mobile, Password).<br>
                    <strong>Step 2:</strong> Click <em>"Assign in Hierarchy"</em> to attach them under their respective manager or supervisor.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('hierarchy.index') }}" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-300 text-xs font-semibold border border-indigo-500/30 transition">
                <span>View Organizational Tree</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Quick Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700/60 shadow-lg">
            <div class="text-xs uppercase font-semibold text-slate-400">Total Directory</div>
            <div class="text-3xl font-bold text-white mt-2">{{ $totalEmployees }}</div>
            <div class="text-xs text-slate-400 mt-1">All registered users</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700/60 shadow-lg">
            <div class="text-xs uppercase font-semibold text-emerald-400">Placed in Hierarchy</div>
            <div class="text-3xl font-bold text-emerald-400 mt-2">{{ $assignedCount }}</div>
            <div class="text-xs text-slate-400 mt-1">Assigned to a manager</div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-800/80 border {{ $unassignedCount > 0 ? 'border-amber-500/40 bg-amber-500/5' : 'border-slate-700/60' }} shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs uppercase font-semibold text-amber-400">Pending Hierarchy Placement</span>
                @if($unassignedCount > 0)
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                @endif
            </div>
            <div class="text-3xl font-bold text-amber-400 mt-2">{{ $unassignedCount }}</div>
            <div class="text-xs text-slate-400 mt-1">Needs reporting manager</div>
        </div>
    </div>

    <!-- Employees List Card -->
    <div class="p-6 rounded-3xl bg-slate-800/80 border border-slate-700/60 shadow-xl">
        <!-- Search & Filter Controls -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="relative flex-1 max-w-md">
                <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Search by name, email, phone, or title..."
                       class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Hierarchy filter tabs -->
                <div class="flex rounded-xl bg-slate-900/80 p-1 border border-slate-700/80">
                    <button wire:click="$set('hierarchyFilter', 'all')"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $hierarchyFilter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                        All
                    </button>
                    <button wire:click="$set('hierarchyFilter', 'assigned')"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ $hierarchyFilter === 'assigned' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">
                        In Hierarchy
                    </button>
                    <button wire:click="$set('hierarchyFilter', 'unassigned')"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ $hierarchyFilter === 'unassigned' ? 'bg-amber-600 text-white shadow-sm' : 'text-amber-400 hover:text-white' }}">
                        <span>Unassigned</span>
                        @if($unassignedCount > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-400/20">{{ $unassignedCount }}</span>
                        @endif
                    </button>
                </div>

                <!-- Role Filter -->
                <select wire:model.live="roleFilter"
                        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="employee">Employee</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase font-semibold text-slate-400 border-b border-slate-700/60">
                    <tr>
                        <th class="py-3.5 px-4">Employee</th>
                        <th class="py-3.5 px-4">Contact</th>
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Hierarchy (Reports To)</th>
                        <th class="py-3.5 px-4">Direct Reports</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-700/20 transition">
                            <!-- Employee Info -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center font-bold text-white shadow-md shrink-0">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-white">{{ $emp->name }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $emp->designation ?? 'Staff Member' }} • <span class="text-slate-500">{{ $emp->department ?? 'General' }}</span></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="py-4 px-4 whitespace-nowrap text-xs">
                                <div class="text-slate-200">📧 {{ $emp->email }}</div>
                                <div class="text-slate-400 mt-0.5">📱 {{ $emp->mobile }}</div>
                            </td>

                            <!-- Role -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @php
                                    $roleColors = [
                                        'admin' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                                        'manager' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                                        'employee' => 'bg-slate-700/60 text-slate-300 border-slate-600',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold capitalize border {{ $roleColors[$emp->role] ?? '' }}">
                                    {{ $emp->role }}
                                </span>
                            </td>

                            <!-- Hierarchy Status (Reports To) -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($emp->isAdmin() && ! $emp->reporting_to_id)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span>👑 Top Organization Leader</span>
                                    </span>
                                @elseif($emp->manager)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-medium bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                                            <span>↳ Reports to:</span>
                                            <strong class="text-white">{{ $emp->manager->name }}</strong>
                                        </span>
                                        @if(auth()->user()->isManager())
                                            <button wire:click="openAssignModal({{ $emp->id }})" title="Change reporting manager"
                                                    class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <button wire:click="openAssignModal({{ $emp->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 border border-amber-500/40 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>Assign in Hierarchy</span>
                                    </button>
                                @endif
                            </td>

                            <!-- Direct Subordinates Count -->
                            <td class="py-4 px-4 whitespace-nowrap text-xs text-slate-400">
                                @if($emp->subordinates->count() > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-700/50 text-slate-200 font-medium">
                                        {{ $emp->subordinates->count() }} staff members
                                    </span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button wire:click="openAssignModal({{ $emp->id }})"
                                            class="px-2.5 py-1 rounded-lg bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 text-xs font-medium transition">
                                        Hierarchy
                                    </button>

                                    @if(auth()->user()->isAdmin())
                                        <button wire:click="toggleStatus({{ $emp->id }})"
                                                class="px-2.5 py-1 rounded-lg {{ $emp->is_active ? 'bg-slate-700 hover:bg-slate-600 text-slate-300' : 'bg-emerald-600/20 text-emerald-400' }} text-xs font-medium transition">
                                            {{ $emp->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <div class="text-sm font-medium">No employees found</div>
                                <p class="text-xs text-slate-500 mt-1">Try adjusting your search or add a new employee.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $employees->links() }}
        </div>
    </div>

    <!-- MODAL 1: Add New Employee to Directory -->
    @if($showAddModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" wire:click="$set('showAddModal', false)"></div>

            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-3xl bg-slate-800 border border-slate-700 p-6 sm:p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-white">Step 1: Add Employee to Directory</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Register staff member. You can link them into the hierarchy tree next.</p>
                        </div>
                        <button wire:click="$set('showAddModal', false)" class="text-slate-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="addEmployee" class="space-y-4">
                        <div>
                            <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Full Name</label>
                            <input wire:model="name" type="text" required placeholder="e.g. John Doe"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Email Address</label>
                                <input wire:model="email" type="email" required placeholder="john@company.com"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('email') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Mobile Number</label>
                                <input wire:model="mobile" type="text" required placeholder="e.g. 9876543210"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('mobile') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Role</label>
                                <select wire:model="role" required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="employee">Employee (Staff)</option>
                                    <option value="manager">Manager / Team Lead</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Temporary Password</label>
                                <input wire:model="password" type="text" required
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('password') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Designation</label>
                                <input wire:model="designation" type="text" placeholder="e.g. Junior Developer, Field Officer"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Department</label>
                                <input wire:model="department" type="text" placeholder="e.g. Operations, IT, Sales"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-700/60 text-xs text-slate-400 flex items-center gap-2.5">
                            <span class="text-indigo-400 font-bold">ℹ️ Note:</span>
                            <span>This employee will be added to the directory. You will then select who they report to in the next step.</span>
                        </div>

                        <div class="flex justify-end gap-3 pt-3">
                            <button type="button" wire:click="$set('showAddModal', false)"
                                    class="px-4 py-2 rounded-xl bg-slate-700 text-slate-300 hover:bg-slate-600 text-sm font-medium">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium shadow-md shadow-indigo-600/30">
                                Add to Directory
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: Assign Hierarchy (Reports To) -->
    @if($showAssignModal && $selectedEmployee)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" wire:click="$set('showAssignModal', false)"></div>

            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-3xl bg-slate-800 border border-slate-700 p-6 sm:p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h3 class="text-lg font-bold text-white">Step 2: Assign in Hierarchy</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Configure reporting supervisor for this employee</p>
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
                            <div class="text-xs text-slate-400">{{ $selectedEmployee->designation ?? $selectedEmployee->role }}</div>
                        </div>
                    </div>

                    <form wire:submit="assignHierarchy" class="space-y-4">
                        <div>
                            <label class="block text-xs uppercase font-semibold text-slate-400 mb-1.5">
                                Select Reporting Manager / Supervisor
                            </label>
                            <select wire:model="selectedManagerId"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">None (Unassigned / Top-level)</option>
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

                        <div class="text-xs text-slate-400 bg-indigo-500/10 border border-indigo-500/20 p-3 rounded-xl">
                            The selected manager will be able to delegate tasks to <strong>{{ $selectedEmployee->name }}</strong> and view their task progress.
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
