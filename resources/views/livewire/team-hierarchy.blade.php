<div>
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
            Organizational Team Hierarchy
        </h1>
        <p class="text-sm text-slate-400 mt-1">
            Multi-level employee reporting structure and delegation channels
        </p>
    </div>

    <div class="p-6 sm:p-8 rounded-3xl bg-slate-800/80 border border-slate-700/60 shadow-xl">
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

                        <div class="text-xs text-slate-400 bg-slate-800/80 px-3.5 py-2 rounded-xl border border-slate-700">
                            Direct Reports: <strong class="text-white">{{ $root->subordinates->count() }}</strong>
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

                                        <div class="text-xs text-slate-400 bg-slate-800/60 px-3 py-1.5 rounded-lg">
                                            Team Members: <strong class="text-indigo-400">{{ $sub1->subordinates->count() }}</strong>
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

                                                <!-- Level 4 Subordinates -->
                                                @if($sub2->subordinates->isNotEmpty())
                                                    <div class="text-xs text-slate-500">
                                                        Reports: <strong class="text-slate-300">{{ $sub2->subordinates->count() }}</strong>
                                                    </div>
                                                @endif
                                            </div>

                                            @if($sub2->subordinates->isNotEmpty())
                                                <div class="ms-6 ps-4 border-l border-slate-700/60 space-y-2">
                                                    @foreach($sub2->subordinates as $sub3)
                                                        <div class="p-2.5 rounded-lg bg-slate-900/30 border border-slate-800 text-xs text-slate-300 flex items-center justify-between">
                                                            <div class="font-medium text-white">{{ $sub3->name }} ({{ $sub3->designation ?? 'Field Staff' }})</div>
                                                            <div class="text-slate-400">📱 {{ $sub3->mobile }}</div>
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
</div>
