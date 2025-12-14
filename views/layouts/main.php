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

    <link rel="stylesheet" href="./css/dark-mode.css">

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

<!--        <nav class="flex-1 py-4">-->
<!--            <a href="#" @click.prevent="currentPage = 'dashboard'" :class="{'active': currentPage === 'dashboard'}" class="sidebar-link">-->
<!--                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>-->
<!--                <span>Dashboard</span>-->
<!--            </a>-->
<!--            <a href="#" @click.prevent="currentPage = 'tasks'" :class="{'active': currentPage === 'tasks'}" class="sidebar-link">-->
<!--                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>-->
<!--                <span>Task</span>-->
<!--                <span x-show="stats.tasks?.open > 0" class="ml-auto bg-white/20 text-white text-xs px-2 py-0.5 rounded-full" x-text="stats.tasks?.open"></span>-->
<!--            </a>-->
<!--            <a href="#" @click.prevent="currentPage = 'clients'" :class="{'active': currentPage === 'clients'}" class="sidebar-link">-->
<!--                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>-->
<!--                <span>Clienti</span>-->
<!--            </a>-->
<!--            <a href="#" @click.prevent="currentPage = 'projects'" :class="{'active': currentPage === 'projects'}" class="sidebar-link">-->
<!--                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>-->
<!--                <span>Progetti</span>-->
<!--            </a>-->
<!--            <a href="#" @click.prevent="currentPage = 'calendar'" :class="{'active': currentPage === 'calendar'}" class="sidebar-link">-->
<!--                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>-->
<!--                <span>Calendario</span>-->
<!--            </a>-->
<!--            <a href="#" @click.prevent="currentPage = 'time'" :class="{'active': currentPage === 'time'}" class="sidebar-link">-->
<!--                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>-->
<!--                <span>Time Tracking</span>-->
<!--            </a>-->
<!--            <div class="my-4 mx-4 border-t border-white/10"></div>-->
<!--            <a href="#" @click.prevent="currentPage = 'integrations'" :class="{'active': currentPage === 'integrations'}" class="sidebar-link">-->
<!--                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>-->
<!--                <span>Integrazioni</span>-->
<!--            </a>-->
<!--        </nav>-->

        <nav class="flex-1 py-4">
            <a href="#" @click.prevent="goToPage('dashboard')" :class="{'active': currentPage === 'dashboard'}" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="#" @click.prevent="goToPage('tasks')" :class="{'active': currentPage === 'tasks'}" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Task</span>
                <span x-show="stats.tasks?.open > 0" class="ml-auto bg-white/20 text-white text-xs px-2 py-0.5 rounded-full" x-text="stats.tasks?.open"></span>
            </a>
            <a href="#" @click.prevent="goToPage('clients')" :class="{'active': currentPage === 'clients'}" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Clienti</span>
            </a>
            <a href="#" @click.prevent="goToPage('projects')" :class="{'active': currentPage === 'projects'}" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                <span>Progetti</span>
            </a>
            <a href="#" @click.prevent="goToPage('calendar')" :class="{'active': currentPage === 'calendar'}" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Calendario</span>
            </a>
            <a href="#" @click.prevent="goToPage('time')" :class="{'active': currentPage === 'time'}" class="sidebar-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Time Tracking</span>
            </a>
            <div class="my-4 mx-4 border-t border-white/10"></div>
            <a href="#" @click.prevent="goToPage('integrations')" :class="{'active': currentPage === 'integrations'}" class="sidebar-link">
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
            <div x-show="currentPage === 'clients'" class="space-y-6">
                <!-- Header con ricerca e filtri -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-600">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Gestione Clienti</h1>
                            <p class="text-sm text-gray-600 mt-1">
                                <span x-text="clients.length"></span> clienti totali
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <!-- Ricerca -->
                            <div class="relative">
                                <input
                                        type="text"
                                        x-model="clientSearch"
                                        @input="filterClients()"
                                        placeholder="Cerca cliente..."
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent w-64"
                                >
                                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <!-- Filtro Priorità -->
                            <select
                                    x-model="clientPriorityFilter"
                                    @change="filterClients()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                                <option value="">Tutte le priorità</option>
                                <option value="urgent">Urgente</option>
                                <option value="high">Alta</option>
                                <option value="normal">Normale</option>
                                <option value="low">Bassa</option>
                                <option value="lowest">Molto bassa</option>
                            </select>

                            <!-- Nuovo Cliente -->
                            <button
                                    @click="showClientModal = true; resetClientForm();"
                                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Nuovo Cliente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                    <p class="mt-4 text-gray-600">Caricamento clienti...</p>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && filteredClients.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nessun cliente trovato</h3>
                    <p class="text-gray-600 mb-6">
                        <span x-show="clientSearch || clientPriorityFilter">Prova a modificare i filtri di ricerca</span>
                        <span x-show="!clientSearch && !clientPriorityFilter">Inizia aggiungendo il tuo primo cliente</span>
                    </p>
                    <button
                            @click="showClientModal = true; resetClientForm();"
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium"
                    >
                        Aggiungi Primo Cliente
                    </button>
                </div>

                <!-- Grid Clienti -->
                <div x-show="!loading && filteredClients.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="client in filteredClients" :key="client.id">
                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border-l-4 overflow-hidden"
                             :style="`border-left-color: ${client.color || '#8B5CF6'}`">

                            <!-- Card Header -->
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <!-- Avatar/Initial -->
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
                                         :style="`background: linear-gradient(135deg, ${client.color || '#8B5CF6'} 0%, ${client.color || '#7C3AED'} 100%)`">
                                        <span x-text="client.name ? client.name.charAt(0).toUpperCase() : '?'"></span>
                                    </div>

                                    <!-- Badge Priorità -->
                                    <span class="px-2 py-1 text-xs font-medium rounded-full"
                                          :class="priorityClass(client.priority_level || 'normal')"
                                          x-text="priorityLabel(client.priority_level || 'normal')">
                        </span>
                                </div>

                                <!-- Nome Cliente -->
                                <h3 class="text-lg font-semibold text-gray-800 mb-1" x-text="client.name"></h3>

                                <!-- Azienda -->
                                <p x-show="client.company" class="text-sm text-gray-600 mb-3">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span x-text="client.company"></span>
                                </p>

                                <!-- Contatti -->
                                <div class="space-y-2 mb-4">
                                    <p x-show="client.email" class="text-sm text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span x-text="client.email" class="truncate"></span>
                                    </p>
                                    <p x-show="client.phone" class="text-sm text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span x-text="client.phone"></span>
                                    </p>
                                </div>

                                <!-- Tariffa Oraria -->
                                <div x-show="client.hourly_rate" class="bg-purple-50 rounded-lg p-3 mb-4">
                                    <p class="text-xs text-purple-600 font-medium mb-1">Tariffa Oraria</p>
                                    <p class="text-lg font-bold text-purple-700">
                                        €<span x-text="client.hourly_rate"></span>/h
                                    </p>
                                </div>

                                <!-- Statistiche rapide -->
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-gray-600 mb-1">Task Attivi</p>
                                        <p class="text-lg font-bold text-gray-800" x-text="client.active_tasks || 0"></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-gray-600 mb-1">Ore Totali</p>
                                        <p class="text-lg font-bold text-gray-800" x-text="(client.total_hours || 0) + 'h'"></p>
                                    </div>
                                </div>

                                <!-- Azioni -->
                                <div class="flex gap-2">
                                    <button
                                            @click="viewClientDetails(client)"
                                            class="flex-1 px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors text-sm font-medium"
                                    >
                                        Dettagli
                                    </button>
                                    <button
                                            @click="editClient(client)"
                                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                                            title="Modifica"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button
                                            @click="deleteClient(client.id)"
                                            class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                                            title="Elimina"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Modal Nuovo/Modifica Cliente -->
                <div x-show="showClientModal"
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Overlay -->
                        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                             @click="showClientModal = false"></div>

                        <!-- Modal -->
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-semibold text-white">
                                        <span x-text="editingClient ? 'Modifica Cliente' : 'Nuovo Cliente'"></span>
                                    </h3>
                                    <button @click="showClientModal = false" class="text-white hover:text-gray-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Form -->
                            <form @submit.prevent="saveClient()" class="px-6 py-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Nome -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Nome Cliente <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                                type="text"
                                                x-model="clientForm.name"
                                                required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                placeholder="Mario Rossi"
                                        >
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input
                                                type="email"
                                                x-model="clientForm.email"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                placeholder="mario@example.com"
                                        >
                                    </div>

                                    <!-- Telefono -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Telefono</label>
                                        <input
                                                type="tel"
                                                x-model="clientForm.phone"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                placeholder="+39 333 1234567"
                                        >
                                    </div>

                                    <!-- Azienda -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Azienda</label>
                                        <input
                                                type="text"
                                                x-model="clientForm.company"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                placeholder="Nome Azienda S.r.l."
                                        >
                                    </div>

                                    <!-- Tariffa Oraria -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tariffa Oraria (€/h)</label>
                                        <input
                                                type="number"
                                                x-model="clientForm.hourly_rate"
                                                step="0.01"
                                                min="0"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                placeholder="50.00"
                                        >
                                    </div>

                                    <!-- Priorità -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Livello di Priorità</label>
                                        <select
                                                x-model="clientForm.priority_level"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        >
                                            <option value="lowest">Molto Bassa</option>
                                            <option value="low">Bassa</option>
                                            <option value="normal">Normale</option>
                                            <option value="high">Alta</option>
                                            <option value="urgent">Urgente</option>
                                        </select>
                                    </div>

                                    <!-- Colore -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Colore Identificativo</label>
                                        <div class="flex gap-2">
                                            <input
                                                    type="color"
                                                    x-model="clientForm.color"
                                                    class="w-16 h-10 border border-gray-300 rounded cursor-pointer"
                                            >
                                            <input
                                                    type="text"
                                                    x-model="clientForm.color"
                                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"
                                                    placeholder="#8B5CF6"
                                            >
                                        </div>
                                    </div>

                                    <!-- Note -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                                        <textarea
                                                x-model="clientForm.notes"
                                                rows="3"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                placeholder="Note aggiuntive sul cliente..."
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                                    <button
                                            type="button"
                                            @click="showClientModal = false"
                                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Annulla
                                    </button>
                                    <button
                                            type="submit"
                                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium"
                                    >
                                        <span x-text="editingClient ? 'Salva Modifiche' : 'Crea Cliente'"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projects -->
            <!-- Projects -->
            <div x-show="currentPage === 'projects'" class="space-y-6">
                <!-- Header con ricerca e filtri -->
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-600">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Gestione Progetti</h1>
                            <p class="text-sm text-gray-600 mt-1">
                                <span x-text="projects.length"></span> progetti totali
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <!-- Ricerca -->
                            <div class="relative">
                                <input
                                        type="text"
                                        x-model="projectSearch"
                                        @input="filterProjects()"
                                        placeholder="Cerca progetto..."
                                        class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent w-64"
                                >
                                <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>

                            <!-- Filtro Cliente -->
                            <select
                                    x-model="projectClientFilter"
                                    @change="filterProjects()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option value="">Tutti i clienti</option>
                                <template x-for="client in clients" :key="client.id">
                                    <option :value="client.id" x-text="client.name"></option>
                                </template>
                            </select>

                            <!-- Filtro Stato -->
                            <select
                                    x-model="projectStatusFilter"
                                    @change="filterProjects()"
                                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option value="">Tutti gli stati</option>
                                <option value="planning">In Pianificazione</option>
                                <option value="active">Attivo</option>
                                <option value="on_hold">In Pausa</option>
                                <option value="completed">Completato</option>
                                <option value="cancelled">Cancellato</option>
                            </select>

                            <!-- Nuovo Progetto -->
                            <button
                                    @click="showProjectModal = true; resetProjectForm();"
                                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center gap-2 whitespace-nowrap"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Nuovo Progetto
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
                    <p class="mt-4 text-gray-600">Caricamento progetti...</p>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && filteredProjects.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Nessun progetto trovato</h3>
                    <p class="text-gray-600 mb-6">
                        <span x-show="projectSearch || projectStatusFilter || projectClientFilter">Prova a modificare i filtri di ricerca</span>
                        <span x-show="!projectSearch && !projectStatusFilter && !projectClientFilter">Inizia creando il tuo primo progetto</span>
                    </p>
                    <button
                            @click="showProjectModal = true; resetProjectForm();"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                    >
                        Crea Primo Progetto
                    </button>
                </div>

                <!-- Grid Progetti -->
                <div x-show="!loading && filteredProjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="project in filteredProjects" :key="project.id">
                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border-l-4 overflow-hidden"
                             :style="`border-left-color: ${project.color || '#10B981'}`">

                            <!-- Card Header -->
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <!-- Icon & Nome -->
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white font-bold text-lg"
                                             :style="`background: linear-gradient(135deg, ${project.color || '#10B981'} 0%, ${project.color || '#059669'} 100%)`">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-semibold text-gray-800 truncate" x-text="project.name"></h3>
                                            <p x-show="project.client_name" class="text-sm text-gray-600 truncate" x-text="project.client_name"></p>
                                        </div>
                                    </div>

                                    <!-- Badge Stato -->
                                    <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap ml-2"
                                          :class="projectStatusClass(project.status)"
                                          x-text="projectStatusLabel(project.status)">
                        </span>
                                </div>

                                <!-- Descrizione -->
                                <p x-show="project.description" class="text-sm text-gray-600 mb-4 line-clamp-2" x-text="project.description"></p>

                                <!-- Date -->
                                <div class="flex items-center gap-4 text-sm text-gray-600 mb-4">
                                    <div x-show="project.start_date" class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span x-text="formatDate(project.start_date)"></span>
                                    </div>
                                    <div x-show="project.due_date" class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span x-text="formatDate(project.due_date)"></span>
                                    </div>
                                </div>

                                <!-- Progresso -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-600">Progresso</span>
                                        <span class="font-medium text-gray-800" x-text="(project.progress || 0) + '%'"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full transition-all"
                                             :style="`width: ${project.progress || 0}%`"></div>
                                    </div>
                                </div>

                                <!-- Statistiche -->
                                <div class="grid grid-cols-3 gap-3 mb-4">
                                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-gray-600 mb-1">Task</p>
                                        <p class="text-lg font-bold text-gray-800" x-text="project.open_tasks || 0"></p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-gray-600 mb-1">Ore</p>
                                        <p class="text-lg font-bold text-gray-800" x-text="(project.tracked_minutes ? Math.round(project.tracked_minutes / 60) : 0) + 'h'"></p>
                                    </div>
                                    <div x-show="project.budget" class="bg-gray-50 rounded-lg p-3 text-center">
                                        <p class="text-xs text-gray-600 mb-1">Budget</p>
                                        <p class="text-lg font-bold text-gray-800">€<span x-text="project.budget"></span></p>
                                    </div>
                                </div>

                                <!-- Azioni -->
                                <div class="flex gap-2">
                                    <button
                                            @click="editProject(project)"
                                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                                            title="Modifica"
                                    >
                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button
                                            @click="deleteProject(project.id)"
                                            class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                                            title="Elimina"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Modal Nuovo/Modifica Progetto -->
                <div x-show="showProjectModal"
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Overlay -->
                        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                             @click="showProjectModal = false"></div>

                        <!-- Modal -->
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-semibold text-white">
                                        <span x-text="editingProject ? 'Modifica Progetto' : 'Nuovo Progetto'"></span>
                                    </h3>
                                    <button @click="showProjectModal = false" class="text-white hover:text-gray-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Form -->
                            <form @submit.prevent="saveProject()" class="px-6 py-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Nome Progetto -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Nome Progetto <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                                type="text"
                                                x-model="projectForm.name"
                                                required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="Sito web aziendale"
                                        >
                                    </div>

                                    <!-- Cliente -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                                        <select
                                                x-model="projectForm.client_id"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        >
                                            <option value="">Nessuno</option>
                                            <template x-for="client in clients" :key="client.id">
                                                <option :value="client.id" x-text="client.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Stato -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                                        <select
                                                x-model="projectForm.status"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        >
                                            <option value="planning">In Pianificazione</option>
                                            <option value="active">Attivo</option>
                                            <option value="on_hold">In Pausa</option>
                                            <option value="completed">Completato</option>
                                            <option value="cancelled">Cancellato</option>
                                        </select>
                                    </div>

                                    <!-- Data Inizio -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Inizio</label>
                                        <input
                                                type="date"
                                                x-model="projectForm.start_date"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Scadenza -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Scadenza</label>
                                        <input
                                                type="date"
                                                x-model="projectForm.due_date"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        >
                                    </div>

                                    <!-- Ore Stimate -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Ore Stimate</label>
                                        <input
                                                type="number"
                                                x-model="projectForm.estimated_hours"
                                                step="0.5"
                                                min="0"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="40"
                                        >
                                    </div>

                                    <!-- Budget -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Budget (€)</label>
                                        <input
                                                type="number"
                                                x-model="projectForm.budget"
                                                step="0.01"
                                                min="0"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="5000.00"
                                        >
                                    </div>

                                    <!-- Colore -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Colore Identificativo</label>
                                        <div class="flex gap-2">
                                            <input
                                                    type="color"
                                                    x-model="projectForm.color"
                                                    class="w-16 h-10 border border-gray-300 rounded cursor-pointer"
                                            >
                                            <input
                                                    type="text"
                                                    x-model="projectForm.color"
                                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono text-sm"
                                                    placeholder="#10B981"
                                            >
                                        </div>
                                    </div>

                                    <!-- Fatturabile -->
                                    <div class="flex items-center">
                                        <input
                                                type="checkbox"
                                                x-model="projectForm.is_billable"
                                                class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                        >
                                        <label class="ml-2 text-sm font-medium text-gray-700">Fatturabile</label>
                                    </div>

                                    <!-- Descrizione -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione</label>
                                        <textarea
                                                x-model="projectForm.description"
                                                rows="3"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="Descrizione del progetto..."
                                        ></textarea>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                                    <button
                                            type="button"
                                            @click="showProjectModal = false"
                                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Annulla
                                    </button>
                                    <button
                                            type="submit"
                                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                                    >
                                        <span x-text="editingProject ? 'Salva Modifiche' : 'Crea Progetto'"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings -->
            <div x-show="currentPage === 'settings'" class="max-w-4xl mx-auto space-y-6">

                <!-- Loading State -->
                <div x-show="settingsLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-600">Caricamento...</p>
                </div>

                <!-- Success Message -->
                <div x-show="settingsSaved"
                     x-transition
                     class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center"
                >
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-green-800 font-medium">Modifiche salvate con successo!</span>
                </div>

                <div x-show="!settingsLoading">
                    <!-- Header -->
                    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-600">
                        <h1 class="text-2xl font-bold text-gray-800">Impostazioni</h1>
                        <p class="text-sm text-gray-600 mt-1">Benvenuto, <span x-text="userProfile.name"></span></p>
                    </div>

                    <!-- Profilo Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center mb-6">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <h2 class="text-lg font-semibold text-gray-800">Profilo</h2>
                        </div>

                        <!-- Avatar -->
                        <div class="mb-6 flex items-center">
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                                <span x-text="userProfile.name ? userProfile.name.charAt(0).toUpperCase() : 'U'"></span>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-gray-800" x-text="userProfile.name"></h3>
                                <p class="text-sm text-gray-600" x-text="userProfile.email"></p>
                            </div>
                        </div>

                        <!-- Form Profilo -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                                <input
                                        type="text"
                                        x-model="userProfile.name"
                                        @keyup.enter="saveProfile()"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                        placeholder="Il tuo nome"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input
                                        type="email"
                                        x-model="userProfile.email"
                                        @keyup.enter="saveProfile()"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                        placeholder="email@example.com"
                                >
                            </div>
                        </div>

                        <!-- Pulsante Salva Profilo -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-500">
                                Premi Invio o clicca Salva per confermare le modifiche
                            </p>
                            <button
                                    @click="saveProfile()"
                                    :disabled="settingsLoading"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                <span x-show="!settingsLoading">Salva Profilo</span>
                                <span x-show="settingsLoading">Salvataggio...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Preferenze Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center mb-6">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            <h2 class="text-lg font-semibold text-gray-800">Preferenze</h2>
                        </div>

                        <!-- Toggle Preferences -->
                        <div class="space-y-4">
                            <!-- Notifiche Email -->
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-800">Notifiche Email</h3>
                                    <p class="text-sm text-gray-600">Ricevi email per task in scadenza</p>
                                </div>
                                <button
                                        @click="togglePreference('email_notifications')"
                                        type="button"
                                        :class="userPreferences.email_notifications ? 'bg-blue-600' : 'bg-gray-300'"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        role="switch"
                                >
                        <span
                                :class="userPreferences.email_notifications ? 'translate-x-5' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        ></span>
                                </button>
                            </div>

                            <!-- Suggerimenti AI -->
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-800">Suggerimenti AI</h3>
                                    <p class="text-sm text-gray-600">Mostra suggerimenti intelligenti</p>
                                </div>
                                <button
                                        @click="togglePreference('ai_suggestions')"
                                        type="button"
                                        :class="userPreferences.ai_suggestions ? 'bg-blue-600' : 'bg-gray-300'"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        role="switch"
                                >
                        <span
                                :class="userPreferences.ai_suggestions ? 'translate-x-5' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        ></span>
                                </button>
                            </div>

                            <!-- Tema Scuro -->
                            <div class="flex items-center justify-between py-3">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-800">Tema Scuro</h3>
                                    <p class="text-sm text-gray-600">Modalità scura per l'interfaccia</p>
                                </div>
                                <button
                                        @click="togglePreference('dark_mode')"
                                        type="button"
                                        :class="userPreferences.dark_mode ? 'bg-blue-600' : 'bg-gray-300'"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        role="switch"
                                >
                        <span
                                :class="userPreferences.dark_mode ? 'translate-x-5' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        ></span>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-500">
                                Le preferenze vengono salvate automaticamente
                            </p>
                        </div>
                    </div>

                    <!-- Sicurezza Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center mb-6">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <h2 class="text-lg font-semibold text-gray-800">Sicurezza</h2>
                        </div>

                        <!-- Form Cambio Password -->
                        <form @submit.prevent="changePassword()" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password Attuale</label>
                                <input
                                        type="password"
                                        x-model="passwordForm.current_password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="••••••••"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nuova Password</label>
                                <input
                                        type="password"
                                        x-model="passwordForm.new_password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="••••••••"
                                >
                                <p class="text-xs text-gray-500 mt-1">Minimo 8 caratteri</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Conferma Nuova Password</label>
                                <input
                                        type="password"
                                        x-model="passwordForm.confirm_password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="••••••••"
                                >
                            </div>

                            <button
                                    type="submit"
                                    :disabled="settingsLoading"
                                    class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                            >
                                Cambia Password
                            </button>
                        </form>
                    </div>

                    <!-- Danger Zone -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <h2 class="text-lg font-semibold text-red-800">Zona Pericolosa</h2>
                        </div>

                        <p class="text-sm text-red-600 mb-4">
                            L'eliminazione dell'account è <strong>permanente</strong> e <strong>non può essere annullata</strong>.
                            Tutti i tuoi dati verranno cancellati definitivamente.
                        </p>

                        <button
                                @click="deleteAccount()"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium"
                        >
                            Elimina Account
                        </button>
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
