<div>
    <!-- Top Bar with Welcome & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                Welcome back, {{ auth()->user()->name }}
            </h1>
            <p class="text-sm text-slate-400 mt-1 flex items-center gap-2">
                <span>{{ auth()->user()->designation ?? 'Team Member' }}</span>
                <span>•</span>
                <span class="text-indigo-400 font-medium capitalize">{{ auth()->user()->role }}</span>
                @if(auth()->user()->manager)
                    <span>•</span>
                    <span>Reports to: <strong class="text-slate-300">{{ auth()->user()->manager->name }}</strong></span>
                @endif
            </p>
        </div>

        @if(auth()->user()->isManager())
            <button wire:click="$set('showCreateModal', true)"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium shadow-lg shadow-indigo-600/30 transition transform active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Delegate New Task</span>
            </button>
        @endif
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center justify-between">
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- Metrics KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Tasks -->
        <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-slate-400 mb-3">
                <span class="text-xs uppercase font-semibold tracking-wider">Total Tasks</span>
                <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ $allTasksCount }}</div>
            <div class="text-xs text-slate-400 mt-1">Active scope</div>
        </div>

        <!-- In Progress -->
        <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-slate-400 mb-3">
                <span class="text-xs uppercase font-semibold tracking-wider">In Progress</span>
                <div class="p-2 rounded-xl bg-amber-500/10 text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-amber-400">{{ $inProgressCount }}</div>
            <div class="text-xs text-slate-400 mt-1">Currently working</div>
        </div>

        <!-- Under Review -->
        <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-slate-400 mb-3">
                <span class="text-xs uppercase font-semibold tracking-wider">Under Review</span>
                <div class="p-2 rounded-xl bg-purple-500/10 text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-purple-400">{{ $underReviewCount }}</div>
            <div class="text-xs text-slate-400 mt-1">Awaiting approval</div>
        </div>

        <!-- Completed -->
        <div class="p-5 rounded-2xl bg-slate-800/80 border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-slate-400 mb-3">
                <span class="text-xs uppercase font-semibold tracking-wider">Completed</span>
                <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-emerald-400">{{ $completedCount }}</div>
            <div class="text-xs text-slate-400 mt-1">Closed tasks</div>
        </div>
    </div>

    <!-- Tasks List & Controls -->
    <div class="p-6 rounded-3xl bg-slate-800/80 border border-slate-700/60 shadow-xl mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg font-bold text-white">Delegated Tasks</h2>
                <p class="text-xs text-slate-400 mt-0.5">Tasks assigned within your organizational chain</p>
            </div>

            <!-- Filter tabs -->
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'under_review' => 'Review', 'completed' => 'Completed'] as $val => $lbl)
                    <button wire:click="$set('statusFilter', '{{ $val }}')"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $statusFilter === $val ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-700/60 text-slate-300 hover:bg-slate-700' }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        @if($tasks->isEmpty())
            <div class="py-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="text-sm font-medium">No tasks found</div>
                <p class="text-xs text-slate-500 mt-1">Create a new task to get started.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase font-semibold text-slate-400 border-b border-slate-700/60">
                        <tr>
                            <th class="py-3 px-4">Task</th>
                            <th class="py-3 px-4">Assigned To</th>
                            <th class="py-3 px-4">Created By</th>
                            <th class="py-3 px-4">Priority</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/40">
                        @foreach($tasks as $t)
                            <tr class="hover:bg-slate-700/30 transition">
                                <td class="py-4 px-4">
                                    <div class="font-semibold text-white">{{ $t->title }}</div>
                                    @if($t->description)
                                        <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $t->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-200">{{ $t->assignee->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $t->assignee->designation ?? $t->assignee->role }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-300">{{ $t->creator->name }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @php
                                        $priorityColors = [
                                            'low' => 'bg-slate-700 text-slate-300',
                                            'medium' => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
                                            'high' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
                                            'urgent' => 'bg-rose-500/20 text-rose-400 border border-rose-500/30',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold capitalize {{ $priorityColors[$t->priority] ?? 'bg-slate-700 text-slate-300' }}">
                                        {{ $t->priority }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @php
                                        $statusBadges = [
                                            'pending' => 'bg-slate-700 text-slate-300',
                                            'in_progress' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
                                            'under_review' => 'bg-purple-500/20 text-purple-400 border border-purple-500/30',
                                            'completed' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                                            'cancelled' => 'bg-rose-500/20 text-rose-400 border border-rose-500/30',
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusBadges[$t->status] ?? '' }}">
                                        {{ str_replace('_', ' ', $t->status) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if($t->status === 'pending')
                                            <button wire:click="updateTaskStatus({{ $t->id }}, 'in_progress')"
                                                    class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 text-xs font-medium transition">
                                                Start
                                            </button>
                                        @elseif($t->status === 'in_progress')
                                            <button wire:click="updateTaskStatus({{ $t->id }}, 'under_review')"
                                                    class="px-2.5 py-1 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 text-xs font-medium transition">
                                                Submit Review
                                            </button>
                                        @elseif($t->status === 'under_review' && auth()->user()->isManager())
                                            <button wire:click="updateTaskStatus({{ $t->id }}, 'completed')"
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-medium transition">
                                                Approve
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Create Task Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" wire:click="$set('showCreateModal', false)"></div>

            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-3xl bg-slate-800 border border-slate-700 p-6 shadow-2xl">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-bold text-white">Delegate New Task</h3>
                        <button wire:click="$set('showCreateModal', false)" class="text-slate-400 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form wire:submit="createTask" class="space-y-4">
                        <div>
                            <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Task Title</label>
                            <input wire:model="title" type="text" required placeholder="e.g. Audit Branch Inventory"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('title') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Description / Instructions</label>
                            <textarea wire:model="description" rows="3" placeholder="Provide step-by-step instructions..."
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                            @error('description') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Assign To Employee</label>
                                <select wire:model="assigned_to" required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($this->assignableEmployees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->designation ?? $emp->role }})</option>
                                    @endforeach
                                </select>
                                @error('assigned_to') <span class="text-xs text-rose-400">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Priority</label>
                                <select wire:model="priority" required
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs uppercase font-semibold text-slate-400 mb-1">Due Deadline (IST)</label>
                            <input wire:model="due_at" type="datetime-local"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex justify-end gap-3 pt-3">
                            <button type="button" wire:click="$set('showCreateModal', false)"
                                    class="px-4 py-2 rounded-xl bg-slate-700 text-slate-300 hover:bg-slate-600 text-sm font-medium">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium shadow-md shadow-indigo-600/30">
                                Assign Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
