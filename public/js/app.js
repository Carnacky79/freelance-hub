/**
 * FreelanceHub - Main Application JavaScript
 * Utilizza Alpine.js per la reattività
 */

// API Helper
const api = {
    baseUrl: './api/v1',
    
    async request(method, endpoint, data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        };
        
        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(this.baseUrl + endpoint, options);
        const json = await response.json();
        
        if (!response.ok) {
            throw new Error(json.message || 'Errore API');
        }
        
        return json;
    },
    
    get: (endpoint) => api.request('GET', endpoint),
    post: (endpoint, data) => api.request('POST', endpoint, data),
    put: (endpoint, data) => api.request('PUT', endpoint, data),
    delete: (endpoint) => api.request('DELETE', endpoint),
};

// Main App Component
function app() {
    return {
        // State
        currentPage: 'dashboard',
        loading: false,
        stats: {},
        tasks: [],
        clients: [],
        projects: [],
        timeEntries: [],
        integrations: [],
        aiSuggestions: [],
        
        // Page title
        get pageTitle() {
            const titles = {
                'dashboard': 'Dashboard',
                'tasks': 'Task',
                'clients': 'Clienti',
                'projects': 'Progetti',
                'calendar': 'Calendario',
                'time': 'Time Tracking',
                'integrations': 'Integrazioni',
                'settings': 'Impostazioni'
            };
            return titles[this.currentPage] || 'Dashboard';
        },
        
        // Logout
        async logout() {
            try {
                await api.post('/auth/logout');
                window.location.href = './login';
            } catch (error) {
                // Anche se fallisce, redirect a login
                window.location.href = './login';
            }
        },
        
        // Timer
        runningTimer: null,
        timerInterval: null,
        showTimerModal: false,
        newTimer: {
            task_id: '',
            description: '',
            is_billable: true,
        },
        
        // Modals
        showTaskModal: false,
        showClientModal: false,
        editingTask: null,
        editingClient: null,
        
        // Forms
        taskForm: {
            title: '',
            description: '',
            client_id: '',
            project_id: '',
            priority: 'normal',
            due_date: '',
            estimated_minutes: '',
        },
        
        clientForm: {
            name: '',
            email: '',
            phone: '',
            company: '',
            priority_level: 'normal',
            hourly_rate: '',
            color: '#3B82F6',
        },
        
        // Calendar
        calendar: null,
        
        // Init
        async init() {
            await this.loadDashboard();
            await this.checkRunningTimer();
            
            // Aggiorna timer ogni secondo
            this.timerInterval = setInterval(() => {
                if (this.runningTimer) {
                    this.runningTimer.current_duration += 1/60;
                }
            }, 1000);
            
            // Polling timer ogni 30 secondi
            setInterval(() => this.checkRunningTimer(), 30000);
        },
        
        // Dashboard
        async loadDashboard() {
            try {
                this.loading = true;
                const response = await api.get('/dashboard/stats');
                this.stats = response.data;
                this.aiSuggestions = response.data.ai_suggestions || [];
            } catch (error) {
                console.error('Errore caricamento dashboard:', error);
            } finally {
                this.loading = false;
            }
        },
        
        // Tasks
        async loadTasks() {
            try {
                this.loading = true;
                const response = await api.get('/tasks');
                this.tasks = response.data;
            } catch (error) {
                console.error('Errore caricamento task:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async saveTask() {
            try {
                if (this.editingTask) {
                    await api.put(`/tasks/${this.editingTask.id}`, this.taskForm);
                } else {
                    await api.post('/tasks', this.taskForm);
                }
                this.showTaskModal = false;
                this.resetTaskForm();
                await this.loadTasks();
                await this.loadDashboard();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        async deleteTask(id) {
            if (!confirm('Eliminare questo task?')) return;
            try {
                await api.delete(`/tasks/${id}`);
                await this.loadTasks();
                await this.loadDashboard();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        async completeTask(id) {
            try {
                await api.post(`/tasks/${id}/complete`);
                await this.loadTasks();
                await this.loadDashboard();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        editTask(task) {
            this.editingTask = task;
            this.taskForm = { ...task };
            this.showTaskModal = true;
        },
        
        resetTaskForm() {
            this.editingTask = null;
            this.taskForm = {
                title: '',
                description: '',
                client_id: '',
                project_id: '',
                priority: 'normal',
                due_date: '',
                estimated_minutes: '',
            };
        },
        
        // Clients
        async loadClients() {
            try {
                this.loading = true;
                const response = await api.get('/clients');
                this.clients = response.data;
            } catch (error) {
                console.error('Errore caricamento clienti:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async saveClient() {
            try {
                if (this.editingClient) {
                    await api.put(`/clients/${this.editingClient.id}`, this.clientForm);
                } else {
                    await api.post('/clients', this.clientForm);
                }
                this.showClientModal = false;
                this.resetClientForm();
                await this.loadClients();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        resetClientForm() {
            this.editingClient = null;
            this.clientForm = {
                name: '',
                email: '',
                phone: '',
                company: '',
                priority_level: 'normal',
                hourly_rate: '',
                color: '#3B82F6',
            };
        },
        
        // Time Tracking
        async checkRunningTimer() {
            try {
                const response = await api.get('/time-entries/running');
                this.runningTimer = response.data;
            } catch (error) {
                this.runningTimer = null;
            }
        },
        
        async startTimer() {
            try {
                await api.post('/time-entries/start', this.newTimer);
                await this.checkRunningTimer();
                this.showTimerModal = false;
                this.newTimer = { task_id: '', description: '', is_billable: true };
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        async stopTimer() {
            try {
                await api.post('/time-entries/stop');
                this.runningTimer = null;
                await this.loadDashboard();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        startTimerForTask(task) {
            this.newTimer.task_id = task.id;
            this.newTimer.description = task.title;
            this.newTimer.is_billable = true;
            this.startTimer();
        },
        
        async toggleTaskComplete(task) {
            try {
                if (task.status === 'completed') {
                    await api.put(`/tasks/${task.id}`, { status: 'todo' });
                } else {
                    await api.post(`/tasks/${task.id}/complete`);
                }
                await this.loadTasks();
                await this.loadDashboard();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        async loadTimeEntries(filters = {}) {
            try {
                const params = new URLSearchParams(filters).toString();
                const response = await api.get('/time-entries?' + params);
                this.timeEntries = response.data;
            } catch (error) {
                console.error('Errore caricamento time entries:', error);
            }
        },
        
        // AI Suggestions
        async acceptSuggestion(id) {
            try {
                await api.post(`/ai/suggestions/${id}/accept`);
                await this.loadDashboard();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        async dismissSuggestion(id) {
            try {
                await api.post(`/ai/suggestions/${id}/dismiss`);
                await this.loadDashboard();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        // Integrations
        async loadIntegrations() {
            try {
                const response = await api.get('/integrations');
                this.integrations = response.data;
            } catch (error) {
                console.error('Errore caricamento integrazioni:', error);
            }
        },
        
        connectIntegration(service) {
            window.location.href = `./api/v1/integrations/${service}/auth`;
        },
        
        async syncIntegration(accountId) {
            try {
                await api.post(`/integrations/${accountId}/sync`);
                alert('Sincronizzazione avviata');
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        async disconnectIntegration(accountId) {
            if (!confirm('Disconnettere questo account?')) return;
            try {
                await api.delete(`/integrations/${accountId}`);
                await this.loadIntegrations();
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },
        
        // Calendar
        initCalendar() {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            
            // Distruggi il calendario precedente se esiste
            if (this.calendar) {
                this.calendar.destroy();
                this.calendar = null;
            }
            
            this.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'it',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                buttonText: {
                    today: 'Oggi',
                    month: 'Mese',
                    week: 'Settimana',
                    list: 'Lista'
                },
                events: async (info, successCallback, failureCallback) => {
                    try {
                        const response = await api.get(`/dashboard/calendar?start=${info.startStr}&end=${info.endStr}`);
                        if (Array.isArray(response)) {
                            successCallback(response);
                        } else if (response.data) {
                            successCallback(response.data);
                        } else {
                            successCallback([]);
                        }
                    } catch (error) {
                        console.error('Errore caricamento eventi:', error);
                        successCallback([]);
                    }
                },
                eventClick: (info) => {
                    const props = info.event.extendedProps;
                    if (props.type === 'task') {
                        console.log('Task clicked:', props.task_id);
                    }
                },
            });
            
            this.calendar.render();
        },
        
        // Helpers
        formatDuration(minutes) {
            if (!minutes) return '0:00';
            const h = Math.floor(minutes / 60);
            const m = Math.floor(minutes % 60);
            const s = Math.floor((minutes * 60) % 60);
            return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        },
        
        formatDate(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('it-IT');
        },
        
        formatDateTime(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleString('it-IT');
        },
        
        priorityClass(priority) {
            const classes = {
                urgent: 'bg-red-100 text-red-700',
                high: 'bg-orange-100 text-orange-700',
                normal: 'bg-blue-100 text-blue-700',
                low: 'bg-gray-100 text-gray-700',
                lowest: 'bg-gray-50 text-gray-500',
            };
            return classes[priority] || classes.normal;
        },
        
        statusClass(status) {
            const classes = {
                backlog: 'bg-gray-100 text-gray-700',
                todo: 'bg-yellow-100 text-yellow-700',
                in_progress: 'bg-blue-100 text-blue-700',
                review: 'bg-purple-100 text-purple-700',
                completed: 'bg-green-100 text-green-700',
                cancelled: 'bg-red-100 text-red-700',
            };
            return classes[status] || classes.todo;
        },
        
        // Page Navigation
        async goToPage(page) {
            this.currentPage = page;
            
            switch (page) {
                case 'dashboard':
                    await this.loadDashboard();
                    break;
                case 'tasks':
                    await this.loadTasks();
                    break;
                case 'clients':
                    await this.loadClients();
                    break;
                case 'projects':
                    await this.loadProjects();
                    break;
                case 'calendar':
                    this.$nextTick(() => this.initCalendar());
                    break;
                case 'time':
                    await this.loadTimeEntries();
                    break;
                case 'integrations':
                    await this.loadIntegrations();
                    break;
            }
        },
        
        async loadProjects() {
            try {
                const response = await api.get('/projects');
                this.projects = response.data;
            } catch (error) {
                console.error('Errore caricamento progetti:', error);
            }
        },
    };
}

// Charts Helper
function initCharts(data) {
    // Ore per cliente (pie chart)
    const hoursCtx = document.getElementById('hoursChart');
    if (hoursCtx && data.hours_by_client) {
        new Chart(hoursCtx, {
            type: 'doughnut',
            data: {
                labels: data.hours_by_client.map(c => c.name || 'Senza cliente'),
                datasets: [{
                    data: data.hours_by_client.map(c => Math.round(c.minutes / 60 * 10) / 10),
                    backgroundColor: data.hours_by_client.map(c => c.color || '#6B7280'),
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
}