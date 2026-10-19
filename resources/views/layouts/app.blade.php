<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Task Manager') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-900 text-slate-100 min-h-screen">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-900 flex">
            <!-- Sidebar Navigation -->
            <livewire:layout.navigation />

            <!-- Main Content Area with Left Padding on Desktop -->
            <div class="flex-1 lg:ps-64 flex flex-col min-h-screen w-full overflow-x-hidden">
                <!-- Mobile Top Header Bar -->
                <header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-slate-800/90 backdrop-blur border-b border-slate-700/60 lg:hidden">
                    <button @click="sidebarOpen = true" type="button" class="p-2 text-slate-400 hover:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center font-bold text-white shadow-md shadow-indigo-500/20">
                            TM
                        </div>
                        <span class="text-base font-bold text-white">Task Manager</span>
                    </div>
                    <div class="w-6"></div>
                </header>

                @if (isset($header))
                    <div class="px-6 py-5 bg-slate-800/50 border-b border-slate-700/50">
                        {{ $header }}
                    </div>
                @endif

                <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
