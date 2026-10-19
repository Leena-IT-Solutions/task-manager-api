<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    /**
     * Dynamic user initials (e.g. 'SR', 'LA')
     */
    public function getInitialsProperty(): string
    {
        $name = auth()->user()?->name ?? 'U';
        $words = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach ($words as $w) {
            $initials .= mb_substr($w, 0, 1);
            if (mb_strlen($initials) >= 2) break;
        }
        return strtoupper($initials ?: 'U');
    }
}; ?>

<div>
    <!-- Desktop Fixed Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-slate-800/95 backdrop-blur-xl border-r border-slate-700/60 hidden lg:flex flex-col z-40">
        <!-- Brand Header -->
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-700/60 bg-slate-800/50">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/30">
                TM
            </div>
            <div>
                <div class="text-base font-bold text-white tracking-tight">TaskManager</div>
                <div class="text-xs text-indigo-400 font-medium tracking-wide">Enterprise Hierarchy</div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}" wire:navigate
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('tasks.index') }}" wire:navigate
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('tasks.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Tasks Management</span>
            </a>

            <a href="{{ route('hierarchy.index') }}" wire:navigate
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('hierarchy.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Team Hierarchy</span>
            </a>

            <a href="{{ route('profile') }}" wire:navigate
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('profile') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Profile Settings</span>
            </a>
        </nav>

        <!-- Bottom User Card -->
        <div class="p-4 border-t border-slate-700/60 bg-slate-800/70" x-data="{ userMenuOpen: false }">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white shadow">
                        {{ $this->initials }}
                    </div>
                    <div class="truncate">
                        <div class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ auth()->user()->isAdmin() ? 'bg-amber-500/20 text-amber-400' : (auth()->user()->isManager() ? 'bg-indigo-500/20 text-indigo-400' : 'bg-emerald-500/20 text-emerald-400') }}">
                                {{ auth()->user()->role }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-700 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>

                    <!-- Dropdown popup -->
                    <div x-show="userMenuOpen" @click.away="userMenuOpen = false" x-cloak
                         class="absolute bottom-full left-0 mb-2 w-48 rounded-xl bg-slate-800 border border-slate-700 shadow-xl py-1.5 text-sm z-50">
                        <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white">
                            Profile Settings
                        </a>
                        <button wire:click="logout" class="w-full text-left px-4 py-2 text-rose-400 hover:bg-rose-500/10 transition">
                            Log Out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Mobile Off-Canvas Drawer -->
    <div x-show="sidebarOpen" x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"></div>

        <div class="fixed inset-0 flex">
            <div x-show="sidebarOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-slate-800 border-r border-slate-700">

                <!-- Close button -->
                <div class="absolute top-0 right-0 -mr-12 pt-4">
                    <button @click="sidebarOpen = false" type="button" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full text-white hover:bg-white/10 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Brand Header -->
                <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-700 bg-slate-800/50">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center font-bold text-white shadow-lg">
                        TM
                    </div>
                    <div>
                        <div class="text-base font-bold text-white">TaskManager</div>
                        <div class="text-xs text-indigo-400 font-medium">Enterprise Hierarchy</div>
                    </div>
                </div>

                <!-- Nav Links Mobile -->
                <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" wire:navigate
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('tasks.index') }}" @click="sidebarOpen = false" wire:navigate
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('tasks.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                        <span>Tasks Management</span>
                    </a>
                    <a href="{{ route('hierarchy.index') }}" @click="sidebarOpen = false" wire:navigate
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('hierarchy.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                        <span>Team Hierarchy</span>
                    </a>
                    <a href="{{ route('profile') }}" @click="sidebarOpen = false" wire:navigate
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('profile') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-700' }}">
                        <span>Profile Settings</span>
                    </a>
                </nav>

                <!-- User Card Mobile -->
                <div class="p-4 border-t border-slate-700 bg-slate-800/80">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white">
                                {{ $this->initials }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-indigo-400 capitalize">{{ auth()->user()->role }}</div>
                            </div>
                        </div>
                        <button wire:click="logout" class="text-xs text-rose-400 hover:underline">
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
