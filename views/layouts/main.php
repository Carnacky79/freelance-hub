<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'FreelanceHub' ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom App -->
    <script src="./js/app.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .sidebar { background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%); }
        
        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; color: #a5b4fc; border-radius: 10px;
            transition: all 0.2s ease; margin: 4px 12px;
        }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar-link.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 500; }
        .sidebar-link svg { width: 20px; height: 20px; flex-shrink: 0; }
        
        .stat-card {
            background: white; border-radius: 16px; padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
            border: 1px solid #f1f5f9; transition: all 0.2s ease;
        }
        .stat-card:hover { box-shadow: 0 10px 40px rgba(0,0,0,0.08); transform: translateY(-2px); }
        
        .card {
            background: white; border-radius: 16px; padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;
        }
        
        .btn {
            padding: 10px 20px; border-radius: 10px; font-weight: 500; font-size: 14px;
            transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5); }
        .btn-secondary { background: #f1f5f9; color: #475569; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .btn-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; }
        
        .input {
            width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0;
            border-radius: 10px; font-size: 14px; transition: all 0.2s ease; background: #f8fafc;
        }
        .input:focus { outline: none; border-color: #6366f1; background: white; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 500; }
        .badge-urgent { background: #fef2f2; color: #dc2626; }
        .badge-high { background: #fff7ed; color: #ea580c; }
        .badge-normal { background: #eff6ff; color: #2563eb; }
        .badge-low { background: #f0fdf4; color: #16a34a; }
        
        .timer-widget { background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; padding: 16px; color: white; }
        .timer-stopped { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
        
        .task-item {
            padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9;
            background: #fafafa; transition: all 0.2s ease;
        }
        .task-item:hover { background: white; border-color: #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        .integration-card {
            background: white; border: 2px solid #f1f5f9; border-radius: 16px;
            padding: 24px; text-align: center; transition: all 0.2s ease;
        }
        .integration-card:hover { border-color: #6366f1; box-shadow: 0 8px 30px rgba(99, 102, 241, 0.15); }
        .integration-card.connected { border-color: #10b981; background: linear-gradient(180deg, #f0fdf4 0%, white 100%); }
        
        .modal-backdrop { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); }
        .modal-content { background: white; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.3s ease forwards; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <div x-data="app()" x-init="init()" class="flex min-h-screen">
        
        <!-- Sidebar -->
        <aside class="sidebar w-64 min-h-screen flex flex-col fixed left-0 top-0 z-40">
            <div class="p-6 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white">FreelanceHub</span>
                </div>
            </div>
            
            <nav class="flex-1 py-4">
                <a href="#" @click.prevent="currentPage = 'dashboard'" :class="{'active': currentPage === 'dashboard'}" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="#" @click.prevent="currentPage = 'tasks'" :class="{'active': currentPage === 'tasks'}" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Task</span>
                    <span x-show="stats.tasks?.open > 0" class="ml-auto bg-white/20 text-white text-xs px-2 py-0.5 rounded-full" x-text="stats.tasks?.open"></span>
                </a>
                <a href="#" @click.prevent="currentPage = 'clients'" :class="{'active': currentPage === 'clients'}" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Clienti</span>
                </a>
                <a href="#" @click.prevent="currentPage = 'projects'" :class="{'active': currentPage === 'projects'}" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span>Progetti</span>
                </a>
                <a href="#" @click.prevent="currentPage = 'calendar'" :class="{'active': currentPage === 'calendar'}" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Calendario</span>
                </a>
                <a href="#" @click.prevent="currentPage = 'time'" :class="{'active': currentPage === 'time'}" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Time Tracking</span>
                </a>
                <div class="my-4 mx-4 border-t border-white/10"></div>
                <a href="#" @click.prevent="currentPage = 'integrations'" :class="{'active': currentPage === 'integrations'}" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                    <span>Integrazioni</span>
                </a>
            </nav>
            
            <!-- Timer Widget -->
            <div class="p-4">
                <div :class="runningTimer ? 'timer-widget' : 'timer-widget timer-stopped'">
                    <template x-if="runningTimer">
                        <div>
                            <div class="text-white/70 text-xs uppercase tracking-wide mb-1">Timer attivo</div>
                            <div class="text-2xl font-bold" x-text="formatDuration(runningTimer.elapsed)"></div>
                            <div class="text-white/80 text-sm mt-1 truncate" x-text="runningTimer.description || 'Senza descrizione'"></div>
                            <button @click="stopTimer()" class="mt-3 w-full bg-white/20 hover:bg-white/30 text-white py-2 rounded-lg text-sm font-medium transition">⏹ Ferma</button>
                        </div>
                    </template>
                    <template x-if="!runningTimer">
                        <div>
                            <div class="text-white/70 text-xs uppercase tracking-wide mb-1">Timer</div>
                            <div class="text-2xl font-bold">00:00:00</div>
                            <button @click="showTimerModal = true" class="mt-3 w-full bg-white/20 hover:bg-white/30 text-white py-2 rounded-lg text-sm font-medium transition">▶ Avvia Timer</button>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- User -->
            <div class="p-4 border-t border-white/10">
                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="w-full flex items-center gap-3 hover:bg-white/10 rounded-lg p-2 transition">
                        <div class="w-10 h-10 bg-primary-400 rounded-full flex items-center justify-center text-white font-semibold">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <div class="text-white font-medium truncate"><?= htmlspecialchars($user['name'] ?? 'Utente') ?></div>
                            <div class="text-primary-300 text-sm truncate"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                        </div>
                        <svg class="w-5 h-5 text-primary-300 transition-transform" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="userMenuOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         @click.away="userMenuOpen = false"
                         class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                        
                        <a href="#" @click.prevent="currentPage = 'settings'; userMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Impostazioni</span>
                        </a>
                        
                        <div class="border-t border-gray-100"></div>
                        
                        <a href="#" @click.prevent="logout()" class="flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Esci</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 ml-64">
            <header class="bg-white border-b border-gray-100 sticky top-0 z-30">
                <div class="flex items-center justify-between px-8 py-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800" x-text="pageTitle"></h1>
                        <p class="text-gray-500 text-sm mt-0.5">Benvenuto, <?= htmlspecialchars($user['name'] ?? 'Utente') ?></p>
                    </div>
                    <button @click="showTaskModal = true; editingTask = null; resetTaskForm()" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nuovo Task
                    </button>
                </div>
            </header>
            
            <div class="p-8">
                <!-- Dashboard -->
                <div x-show="currentPage === 'dashboard'" class="animate-fade-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">Task Aperti</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-1" x-text="stats.tasks?.open || 0"></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-red-500 text-sm font-medium" x-text="(stats.tasks?.overdue || 0) + ' in ritardo'"></span>
                                    </div>
                                </div>
                                <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">Ore Oggi</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-1" x-text="stats.time_tracking?.today_hours || '0'"></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-gray-500 text-sm" x-text="(stats.time_tracking?.week_hours || 0) + 'h questa settimana'"></span>
                                    </div>
                                </div>
                                <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">Clienti Attivi</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-1" x-text="stats.clients?.active || 0"></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-gray-500 text-sm" x-text="(stats.clients?.total || 0) + ' totali'"></span>
                                    </div>
                                </div>
                                <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">Completati</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-1" x-text="stats.tasks?.completed || 0"></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-green-500 text-sm">questo mese</span>
                                    </div>
                                </div>
                                <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 card">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-semibold text-gray-800">Task Urgenti & In Ritardo</h2>
                                <a href="#" @click.prevent="currentPage = 'tasks'" class="text-primary-600 text-sm font-medium hover:text-primary-700">Vedi tutti →</a>
                            </div>
                            <div class="space-y-3">
                                <template x-for="task in [...(stats.overdue_tasks || []), ...(stats.urgent_tasks || [])].slice(0, 5)" :key="task.id">
                                    <div class="task-item flex items-center justify-between">
                                        <div class="flex items-center gap-4 flex-1 min-w-0">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="task.priority === 'urgent' ? 'bg-red-100' : 'bg-amber-100'">
                                                <svg class="w-5 h-5" :class="task.priority === 'urgent' ? 'text-red-600' : 'text-amber-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-800 truncate" x-text="task.title"></p>
                                                <p class="text-sm text-gray-500" x-text="task.due_date ? 'Scadenza: ' + formatDate(task.due_date) : 'Nessuna scadenza'"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="badge" :class="'badge-' + task.priority" x-text="task.priority"></span>
                                            <button @click="completeTask(task.id)" class="p-2 hover:bg-green-100 rounded-lg transition" title="Completa">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                            <button @click="startTimerForTask(task)" class="p-2 hover:bg-primary-100 rounded-lg transition" title="Avvia timer">
                                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!stats.overdue_tasks?.length && !stats.urgent_tasks?.length">
                                    <div class="text-center py-12">
                                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <p class="text-gray-500">Nessun task urgente. Ottimo lavoro! 🎉</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="card" x-show="aiSuggestions.length > 0">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-800">Suggerimenti AI</h3>
                                </div>
                                <div class="space-y-3">
                                    <template x-for="suggestion in aiSuggestions" :key="suggestion.id">
                                        <div class="p-3 bg-purple-50 rounded-xl border border-purple-100">
                                            <p class="text-sm text-gray-700" x-text="suggestion.description"></p>
                                            <div class="flex gap-2 mt-2">
                                                <button @click="acceptSuggestion(suggestion.id)" class="text-xs text-purple-600 font-medium hover:text-purple-700">Accetta</button>
                                                <button @click="dismissSuggestion(suggestion.id)" class="text-xs text-gray-500 hover:text-gray-700">Ignora</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="card">
                                <h3 class="font-semibold text-gray-800 mb-4">Ore questa settimana</h3>
                                <div class="space-y-3">
                                    <template x-for="client in stats.hours_by_client || []" :key="client.client_id">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-3 h-3 rounded-full" :style="'background:' + (client.color || '#6366f1')"></div>
                                                <span class="text-sm text-gray-700" x-text="client.client_name"></span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-800" x-text="client.total_hours + 'h'"></span>
                                        </div>
                                    </template>
                                    <template x-if="!stats.hours_by_client?.length">
                                        <p class="text-sm text-gray-500 text-center py-4">Nessun tempo tracciato</p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tasks -->
                <div x-show="currentPage === 'tasks'" x-init="loadTasks()" class="animate-fade-in">
                    <div class="card">
                        <div class="space-y-3">
                            <template x-for="task in tasks" :key="task.id">
                                <div class="task-item flex items-center justify-between">
                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        <input type="checkbox" :checked="task.status === 'completed'" @change="toggleTaskComplete(task)" class="w-5 h-5 rounded-md border-2 border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate" :class="task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-800'" x-text="task.title"></p>
                                            <div class="flex items-center gap-3 mt-1">
                                                <span class="text-xs text-gray-500" x-text="task.client_name || 'Nessun cliente'"></span>
                                                <span class="text-xs text-gray-400">•</span>
                                                <span class="text-xs text-gray-500" x-text="task.due_date ? formatDate(task.due_date) : 'Nessuna scadenza'"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="badge" :class="'badge-' + task.priority" x-text="task.priority"></span>
                                        <button @click="editTask(task)" class="p-2 hover:bg-gray-100 rounded-lg transition">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button @click="deleteTask(task.id)" class="p-2 hover:bg-red-100 rounded-lg transition">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <template x-if="tasks.length === 0">
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-gray-500 mb-4">Nessun task</p>
                                    <button @click="showTaskModal = true; editingTask = null; resetTaskForm()" class="btn btn-primary">Crea il primo task</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- Calendar -->
                <template x-if="currentPage === 'calendar'">
                    <div x-data="{ calendarReady: false }" x-init="$nextTick(() => { initCalendar(); calendarReady = true; })" class="animate-fade-in">
                        <div class="card"><div id="calendar" class="min-h-[600px]"></div></div>
                    </div>
                </template>
                
                <!-- Time -->
                <div x-show="currentPage === 'time'" x-init="loadTimeEntries()" class="animate-fade-in">
                    <div class="card">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-semibold text-gray-800">Registrazioni Tempo</h2>
                            <button @click="showTimerModal = true" class="btn btn-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Nuova
                            </button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="entry in timeEntries" :key="entry.id">
                                <div class="task-item flex items-center justify-between">
                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-800 truncate" x-text="entry.description || entry.task_title || 'Senza descrizione'"></p>
                                            <p class="text-sm text-gray-500" x-text="formatDate(entry.start_time)"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="font-mono font-semibold text-gray-800" x-text="formatDuration(entry.duration_minutes * 60)"></span>
                                        <span x-show="entry.is_billable" class="badge badge-low">Fatturabile</span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="timeEntries.length === 0">
                                <div class="text-center py-12"><p class="text-gray-500">Nessuna registrazione</p></div>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- Integrations -->
                <div x-show="currentPage === 'integrations'" x-init="loadIntegrations()" class="animate-fade-in">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="integration in integrations" :key="integration.slug">
                            <div class="integration-card" :class="integration.connected ? 'connected' : ''">
                                <div class="text-4xl mb-4" x-text="integration.icon"></div>
                                <h3 class="text-lg font-semibold text-gray-800" x-text="integration.name"></h3>
                                <p class="text-sm text-gray-500 mt-1" x-text="integration.description"></p>
                                <template x-if="integration.connected">
                                    <div class="mt-4">
                                        <div class="flex items-center justify-center gap-2 text-green-600 mb-3">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span class="font-medium">Connesso</span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!integration.connected">
                                    <button @click="connectIntegration(integration.slug)" class="btn btn-secondary mt-4 w-full justify-center">Connetti</button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Clients -->
                <div x-show="currentPage === 'clients'" class="animate-fade-in">
                    <div class="card text-center py-16">
                        <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Gestione Clienti</h3>
                        <p class="text-gray-500 mb-6">Pagina in costruzione</p>
                    </div>
                </div>
                
                <!-- Projects -->
                <div x-show="currentPage === 'projects'" class="animate-fade-in">
                    <div class="card text-center py-16">
                        <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Gestione Progetti</h3>
                        <p class="text-gray-500 mb-6">Pagina in costruzione</p>
                    </div>
                </div>
                
                <!-- Settings -->
                <div x-show="currentPage === 'settings'" class="animate-fade-in">
                    <div class="grid gap-6">
                        <!-- Profilo -->
                        <div class="card">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profilo
                            </h3>
                            <div class="grid gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($user['name'] ?? 'Utente') ?></h4>
                                        <p class="text-gray-500"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                    </div>
                                </div>
                                <div class="border-t pt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                        <input type="text" class="input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" disabled>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <input type="email" class="input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-400">La modifica del profilo sarà disponibile prossimamente.</p>
                            </div>
                        </div>
                        
                        <!-- Preferenze -->
                        <div class="card">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                Preferenze
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between py-3 border-b">
                                    <div>
                                        <h4 class="font-medium text-gray-800">Notifiche Email</h4>
                                        <p class="text-sm text-gray-500">Ricevi email per task in scadenza</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked disabled>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between py-3 border-b">
                                    <div>
                                        <h4 class="font-medium text-gray-800">Suggerimenti AI</h4>
                                        <p class="text-sm text-gray-500">Mostra suggerimenti intelligenti</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked disabled>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                    </label>
                                </div>
                                <div class="flex items-center justify-between py-3">
                                    <div>
                                        <h4 class="font-medium text-gray-800">Tema Scuro</h4>
                                        <p class="text-sm text-gray-500">Modalità scura per l'interfaccia</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" disabled>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                                    </label>
                                </div>
                            </div>
                            <p class="text-sm text-gray-400 mt-4">Le preferenze saranno salvabili prossimamente.</p>
                        </div>
                        
                        <!-- Danger Zone -->
                        <div class="card border-red-200 bg-red-50/50">
                            <h3 class="text-lg font-semibold text-red-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Zona Pericolosa
                            </h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-800">Elimina Account</h4>
                                    <p class="text-sm text-gray-500">Elimina permanentemente il tuo account e tutti i dati</p>
                                </div>
                                <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium" disabled>
                                    Elimina Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Task Modal -->
        <div x-show="showTaskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="modal-backdrop fixed inset-0" @click="showTaskModal = false"></div>
                <div class="modal-content relative w-full max-w-lg p-6">
                    <h3 class="text-xl font-semibold mb-6" x-text="editingTask ? 'Modifica Task' : 'Nuovo Task'"></h3>
                    <form @submit.prevent="saveTask()">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Titolo *</label>
                                <input type="text" x-model="taskForm.title" class="input" required placeholder="Cosa devi fare?">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrizione</label>
                                <textarea x-model="taskForm.description" class="input" rows="3" placeholder="Dettagli..."></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                                    <select x-model="taskForm.client_id" class="input">
                                        <option value="">Nessuno</option>
                                        <template x-for="client in clients" :key="client.id">
                                            <option :value="client.id" x-text="client.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Priorità</label>
                                    <select x-model="taskForm.priority" class="input">
                                        <option value="low">Bassa</option>
                                        <option value="normal">Normale</option>
                                        <option value="high">Alta</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Scadenza</label>
                                    <input type="date" x-model="taskForm.due_date" class="input">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempo stimato (min)</label>
                                    <input type="number" x-model="taskForm.estimated_minutes" class="input" min="0" placeholder="60">
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button type="button" @click="showTaskModal = false" class="btn btn-secondary flex-1">Annulla</button>
                            <button type="submit" class="btn btn-primary flex-1">Salva</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Timer Modal -->
        <div x-show="showTimerModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="modal-backdrop fixed inset-0" @click="showTimerModal = false"></div>
                <div class="modal-content relative w-full max-w-md p-6">
                    <h3 class="text-xl font-semibold mb-6">Avvia Timer</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Task</label>
                            <select x-model="newTimer.task_id" class="input">
                                <option value="">Seleziona...</option>
                                <template x-for="task in tasks.filter(t => t.status !== 'completed')" :key="task.id">
                                    <option :value="task.id" x-text="task.title"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrizione</label>
                            <input type="text" x-model="newTimer.description" class="input" placeholder="Cosa stai facendo?">
                        </div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" x-model="newTimer.is_billable" class="w-4 h-4 rounded border-gray-300 text-primary-600">
                            <span class="text-sm text-gray-700">Fatturabile</span>
                        </label>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button @click="showTimerModal = false" class="btn btn-secondary flex-1">Annulla</button>
                        <button @click="startTimer()" class="btn btn-success flex-1">▶ Avvia</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>