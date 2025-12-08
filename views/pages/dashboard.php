<?php
$title = 'Dashboard - FreelanceHub';
ob_start();
?>

<!-- Page Content -->
<div x-show="currentPage === 'dashboard'">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
            <p class="text-gray-500">Benvenuto, <?= htmlspecialchars($user['name'] ?? 'Freelancer') ?></p>
        </div>
        <div class="flex gap-3">
            <button @click="showTaskModal = true; resetTaskForm()" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuovo Task
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Task Aperti -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Task Aperti</p>
                    <p class="text-3xl font-bold text-gray-800" x-text="stats.tasks?.open || 0"></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-red-500 font-medium" x-text="stats.tasks?.overdue || 0"></span>
                <span class="text-gray-500 ml-1">in ritardo</span>
                <span class="mx-2 text-gray-300">|</span>
                <span class="text-orange-500 font-medium" x-text="stats.tasks?.urgent || 0"></span>
                <span class="text-gray-500 ml-1">urgenti</span>
            </div>
        </div>
        
        <!-- Ore Oggi -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Ore Oggi</p>
                    <p class="text-3xl font-bold text-gray-800" x-text="stats.time_tracking?.today_hours || '0'"></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm text-gray-500">
                <span x-text="stats.time_tracking?.week_hours || 0"></span>
                <span class="ml-1">ore questa settimana</span>
            </div>
        </div>
        
        <!-- Clienti Attivi -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Clienti Attivi</p>
                    <p class="text-3xl font-bold text-gray-800" x-text="stats.clients?.active || 0"></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Task Completati -->
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Completati</p>
                    <p class="text-3xl font-bold text-gray-800" x-text="stats.tasks?.completed || 0"></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Task Urgenti -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Task Urgenti & In Ritardo</h3>
                    <a href="#tasks" @click.prevent="goToPage('tasks')" class="text-blue-600 hover:text-blue-700 text-sm">
                        Vedi tutti →
                    </a>
                </div>
                
                <div class="space-y-3">
                    <template x-for="task in [...(stats.overdue_tasks || []), ...(stats.urgent_tasks || [])].slice(0, 5)" :key="task.id">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-4">
                                <button @click="completeTask(task.id)" class="w-6 h-6 rounded-full border-2 border-gray-300 hover:border-green-500 hover:bg-green-50 transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-400 hover:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <div>
                                    <p class="font-medium text-gray-800" x-text="task.title"></p>
                                    <p class="text-sm text-gray-500" x-text="task.client_name || 'Nessun cliente'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="badge" :class="priorityClass(task.priority)" x-text="task.priority"></span>
                                <span class="text-sm" :class="new Date(task.due_date) < new Date() ? 'text-red-600 font-medium' : 'text-gray-500'" x-text="formatDate(task.due_date)"></span>
                                <button @click="startTimer(); newTimer.task_id = task.id" class="p-2 hover:bg-blue-100 rounded-lg transition-colors" title="Avvia timer">
                                    <svg class="w-5 h-5 text-gray-400 hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="!stats.overdue_tasks?.length && !stats.urgent_tasks?.length">
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>Nessun task urgente. Ottimo lavoro! 🎉</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- AI Suggestions -->
            <div class="card" x-show="aiSuggestions.length > 0">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Suggerimenti IA</h3>
                </div>
                
                <div class="space-y-3">
                    <template x-for="suggestion in aiSuggestions" :key="suggestion.id">
                        <div class="p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border border-purple-100">
                            <p class="font-medium text-gray-800 mb-1" x-text="suggestion.title"></p>
                            <p class="text-sm text-gray-600 mb-3" x-text="suggestion.description"></p>
                            <div class="flex gap-2">
                                <button @click="acceptSuggestion(suggestion.id)" class="text-xs px-3 py-1 bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition-colors">
                                    Accetta
                                </button>
                                <button @click="dismissSuggestion(suggestion.id)" class="text-xs px-3 py-1 bg-gray-100 text-gray-600 rounded-full hover:bg-gray-200 transition-colors">
                                    Ignora
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Ore per Cliente -->
            <div class="card">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ore questa settimana</h3>
                <canvas id="hoursChart" height="200"></canvas>
                
                <div class="mt-4 space-y-2">
                    <template x-for="client in stats.hours_by_client || []" :key="client.client_id">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" :style="`background-color: ${client.color || '#6B7280'}`"></span>
                                <span class="text-sm text-gray-600" x-text="client.name || 'Senza cliente'"></span>
                            </div>
                            <span class="text-sm font-medium" x-text="Math.round(client.minutes / 60 * 10) / 10 + 'h'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tasks Page -->
<div x-show="currentPage === 'tasks'" x-init="loadTasks()">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Task</h2>
        <button @click="showTaskModal = true; resetTaskForm()" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuovo Task
        </button>
    </div>
    
    <!-- Task List -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-500 border-b">
                        <th class="pb-3 font-medium">Task</th>
                        <th class="pb-3 font-medium">Cliente</th>
                        <th class="pb-3 font-medium">Priorità</th>
                        <th class="pb-3 font-medium">Scadenza</th>
                        <th class="pb-3 font-medium">Stato</th>
                        <th class="pb-3 font-medium text-right">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <template x-for="task in tasks" :key="task.id">
                        <tr class="hover:bg-gray-50">
                            <td class="py-4">
                                <p class="font-medium text-gray-800" x-text="task.title"></p>
                                <p class="text-sm text-gray-500" x-text="task.project_name || ''"></p>
                            </td>
                            <td class="py-4 text-sm text-gray-600" x-text="task.client_name || '-'"></td>
                            <td class="py-4">
                                <span class="badge" :class="priorityClass(task.priority)" x-text="task.priority"></span>
                            </td>
                            <td class="py-4 text-sm" :class="new Date(task.due_date) < new Date() && task.status !== 'completed' ? 'text-red-600' : 'text-gray-600'" x-text="task.due_date ? formatDate(task.due_date) : '-'"></td>
                            <td class="py-4">
                                <span class="badge" :class="statusClass(task.status)" x-text="task.status"></span>
                            </td>
                            <td class="py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="editTask(task)" class="p-2 hover:bg-gray-100 rounded-lg" title="Modifica">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="newTimer.task_id = task.id; startTimer()" class="p-2 hover:bg-blue-100 rounded-lg" title="Avvia timer">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        </svg>
                                    </button>
                                    <button x-show="task.status !== 'completed'" @click="completeTask(task.id)" class="p-2 hover:bg-green-100 rounded-lg" title="Completa">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteTask(task.id)" class="p-2 hover:bg-red-100 rounded-lg" title="Elimina">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Calendar Page -->
<div x-show="currentPage === 'calendar'" x-init="$nextTick(() => initCalendar())">
    <h2 class="text-2xl font-bold text-gray-800 mb-8">Calendario</h2>
    <div class="card">
        <div id="calendar"></div>
    </div>
</div>

<!-- Time Tracking Page -->
<div x-show="currentPage === 'time'" x-init="loadTimeEntries()">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Time Tracking</h2>
        <button @click="showTimerModal = true" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuova Entry
        </button>
    </div>
    
    <!-- Time Entries List -->
    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-500 border-b">
                        <th class="pb-3 font-medium">Data</th>
                        <th class="pb-3 font-medium">Task</th>
                        <th class="pb-3 font-medium">Cliente</th>
                        <th class="pb-3 font-medium">Descrizione</th>
                        <th class="pb-3 font-medium">Durata</th>
                        <th class="pb-3 font-medium">Fatturabile</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <template x-for="entry in timeEntries" :key="entry.id">
                        <tr class="hover:bg-gray-50">
                            <td class="py-4 text-sm text-gray-600" x-text="formatDateTime(entry.start_time)"></td>
                            <td class="py-4 text-sm font-medium" x-text="entry.task_title || '-'"></td>
                            <td class="py-4 text-sm text-gray-600" x-text="entry.client_name || '-'"></td>
                            <td class="py-4 text-sm text-gray-600" x-text="entry.description || '-'"></td>
                            <td class="py-4 text-sm font-mono font-medium" x-text="formatDuration(entry.duration_minutes)"></td>
                            <td class="py-4">
                                <span x-show="entry.is_billable" class="badge bg-green-100 text-green-700">Sì</span>
                                <span x-show="!entry.is_billable" class="badge bg-gray-100 text-gray-500">No</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Integrations Page -->
<div x-show="currentPage === 'integrations'" x-init="loadIntegrations()">
    <h2 class="text-2xl font-bold text-gray-800 mb-8">Integrazioni</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Google Calendar -->
        <div class="card">
            <div class="flex items-center gap-4 mb-4">
                <img src="https://www.gstatic.com/images/branding/product/1x/calendar_2020q4_48dp.png" alt="Google Calendar" class="w-12 h-12">
                <div>
                    <h3 class="font-semibold text-gray-800">Google Calendar</h3>
                    <p class="text-sm text-gray-500">Sincronizza eventi</p>
                </div>
            </div>
            <button @click="connectIntegration('google_calendar')" class="btn btn-secondary w-full">
                Connetti Account
            </button>
        </div>
        
        <!-- Asana -->
        <div class="card">
            <div class="flex items-center gap-4 mb-4">
                <img src="https://luna1.co/521e30.png" alt="Asana" class="w-12 h-12 rounded-lg">
                <div>
                    <h3 class="font-semibold text-gray-800">Asana</h3>
                    <p class="text-sm text-gray-500">Sincronizza task</p>
                </div>
            </div>
            <button @click="connectIntegration('asana')" class="btn btn-secondary w-full">
                Connetti Account
            </button>
        </div>
        
        <!-- ClickUp -->
        <div class="card">
            <div class="flex items-center gap-4 mb-4">
                <img src="https://clickup.com/landing/images/clickup-symbol_color.svg" alt="ClickUp" class="w-12 h-12">
                <div>
                    <h3 class="font-semibold text-gray-800">ClickUp</h3>
                    <p class="text-sm text-gray-500">Sincronizza task</p>
                </div>
            </div>
            <button @click="connectIntegration('clickup')" class="btn btn-secondary w-full">
                Connetti Account
            </button>
        </div>
    </div>
    
    <!-- Connected Accounts -->
    <template x-if="integrations.length > 0">
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Account Connessi</h3>
            <div class="card">
                <div class="divide-y">
                    <template x-for="account in integrations" :key="account.id">
                        <div class="py-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-lg" x-text="account.integration_name?.charAt(0) || '?'"></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800" x-text="account.account_name"></p>
                                    <p class="text-sm text-gray-500" x-text="account.integration_name + ' - ' + (account.account_email || 'No email')"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span x-show="account.sync_error" class="text-red-500 text-sm" title="Errore sync">⚠️</span>
                                <span class="text-sm text-gray-500" x-text="account.last_sync_at ? 'Sync: ' + formatDateTime(account.last_sync_at) : 'Mai sincronizzato'"></span>
                                <button @click="syncIntegration(account.id)" class="btn btn-secondary text-sm">Sync</button>
                                <button @click="disconnectIntegration(account.id)" class="btn btn-danger text-sm">Disconnetti</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>

<!-- Task Modal -->
<div x-show="showTaskModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showTaskModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold mb-4" x-text="editingTask ? 'Modifica Task' : 'Nuovo Task'"></h3>
            
            <form @submit.prevent="saveTask()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titolo *</label>
                        <input type="text" x-model="taskForm.title" class="input" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrizione</label>
                        <textarea x-model="taskForm.description" class="input" rows="3"></textarea>
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
                                <option value="lowest">Minima</option>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stima (minuti)</label>
                            <input type="number" x-model="taskForm.estimated_minutes" class="input" min="0">
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

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
?>
