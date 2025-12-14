/**
 * FreelanceHub - Main Application JavaScript
 * Utilizza Alpine.js per la reattività
 */

// API Helper
const api = {
    baseUrl: './api/v1',
    _csrfToken: null,
    _csrfPromise: null,

    /**
     * Inizializza CSRF token (con promise per evitare chiamate multiple)
     */
    async initCsrf() {
        // Se già in caricamento, aspetta quella promise
        if (this._csrfPromise) {
            return this._csrfPromise;
        }

        // Se già caricato, restituiscilo
        if (this._csrfToken) {
            return this._csrfToken;
        }

        // Carica il token
        this._csrfPromise = fetch('./api/test')
            .then(r => r.json())
            .then(data => {
                this._csrfToken = data.csrf_token || '';
                console.log('✅ CSRF token caricato:', this._csrfToken ? 'OK' : 'VUOTO');
                this._csrfPromise = null;
                return this._csrfToken;
            })
            .catch(e => {
                console.error('❌ Errore caricamento CSRF token:', e);
                this._csrfPromise = null;
                return '';
            });

        return this._csrfPromise;
    },

    async request(method, endpoint, data = null) {
        // Assicura che abbiamo il CSRF token per modifiche
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
            await this.initCsrf();
        }

        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        };

        // Aggiungi CSRF token per modifiche
        if (this._csrfToken && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
            options.headers['X-CSRF-TOKEN'] = this._csrfToken;
        }

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(this.baseUrl + endpoint, options);
        const json = await response.json();

        if (!response.ok) {
            // Se errore CSRF, prova a ricaricare il token (MAX 1 volta)
            if (response.status === 403 && json.error && json.error.includes('CSRF') && !data?._csrfRetry) {
                console.warn('🔄 CSRF token scaduto, ricarico...');
                this._csrfToken = null;
                this._csrfPromise = null;
                // Marca il retry per evitare loop
                const retryData = data ? { ...data, _csrfRetry: true } : { _csrfRetry: true };
                return this.request(method, endpoint, retryData);
            }

            throw new Error(json.message || json.error || 'Errore API');
        }

        return json;
    },

    get: (endpoint) => api.request('GET', endpoint),
    post: (endpoint, data) => api.request('POST', endpoint, data),
    put: (endpoint, data) => api.request('PUT', endpoint, data),
    delete: (endpoint) => api.request('DELETE', endpoint),
};

// Inizializza CSRF all'avvio (eager loading)
document.addEventListener('DOMContentLoaded', () => {
    api.initCsrf();
});

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
        clientSearch: '',
        clientPriorityFilter: '',
        filteredClients: [],

        settingsLoading: false,
        settingsSaved: false,
        userProfile: {
            name: '',
            email: '',
        },
        userPreferences: {
            email_notifications: true,
            ai_suggestions: true,
            dark_mode: false,
        },
        passwordForm: {
            current_password: '',
            new_password: '',
            confirm_password: '',
        },
        // Projects
        showProjectModal: false,
        editingProject: null,
        projectSearch: '',
        projectStatusFilter: '',
        projectClientFilter: '',
        filteredProjects: [],

        projectForm: {
            name: '',
            description: '',
            client_id: '',
            color: '#10B981',
            status: 'planning',
            start_date: '',
            due_date: '',
            estimated_hours: '',
            budget: '',
            is_billable: true,
        },

        // filterClients() {
        //     let filtered = this.clients;
        //
        //     // Filtro per ricerca testuale
        //     if (this.clientSearch) {
        //         const search = this.clientSearch.toLowerCase();
        //         filtered = filtered.filter(client =>
        //             (client.name && client.name.toLowerCase().includes(search)) ||
        //             (client.company && client.company.toLowerCase().includes(search)) ||
        //             (client.email && client.email.toLowerCase().includes(search)) ||
        //             (client.phone && client.phone.toLowerCase().includes(search))
        //         );
        //     }
        //
        //     // Filtro per priorità
        //     if (this.clientPriorityFilter) {
        //         filtered = filtered.filter(client =>
        //             client.priority_level === this.clientPriorityFilter
        //         );
        //     }
        //
        //     this.filteredClients = filtered;
        // },

        filterClients() {
            console.log('🔍 filterClients() chiamato');
            console.log('  clients:', this.clients);
            console.log('  clientSearch:', this.clientSearch);
            console.log('  clientPriorityFilter:', this.clientPriorityFilter);

            let filtered = this.clients;

            // Filtro per ricerca testuale
            if (this.clientSearch && this.clientSearch.trim() !== '') {
                const search = this.clientSearch.toLowerCase();
                filtered = filtered.filter(client =>
                    (client.name && client.name.toLowerCase().includes(search)) ||
                    (client.company && client.company.toLowerCase().includes(search)) ||
                    (client.email && client.email.toLowerCase().includes(search)) ||
                    (client.phone && client.phone.toLowerCase().includes(search))
                );
                console.log('  dopo ricerca:', filtered.length);
            }

            // Filtro per priorità
            if (this.clientPriorityFilter && this.clientPriorityFilter !== '') {
                filtered = filtered.filter(client =>
                    client.priority_level === this.clientPriorityFilter
                );
                console.log('  dopo priorità:', filtered.length);
            }

            this.filteredClients = filtered;
            console.log('✅ filteredClients impostato:', this.filteredClients.length);
        },

        /**
         * Label priorità cliente
         */
        priorityLabel(priority) {
            const labels = {
                urgent: 'Urgente',
                high: 'Alta',
                normal: 'Normale',
                low: 'Bassa',
                lowest: 'Molto Bassa',
            };
            return labels[priority] || 'Normale';
        },

        /**
         * Modifica cliente esistente
         */
        editClient(client) {
            this.editingClient = client;
            this.clientForm = {
                name: client.name,
                email: client.email || '',
                phone: client.phone || '',
                company: client.company || '',
                priority_level: client.priority_level || 'normal',
                hourly_rate: client.hourly_rate || '',
                color: client.color || '#8B5CF6',
                notes: client.notes || '',
            };
            this.showClientModal = true;
        },

        /**
         * Elimina cliente
         */
        async deleteClient(id) {
            if (!confirm('Sei sicuro di voler eliminare questo cliente? Verranno eliminate anche le associazioni con task e progetti.')) {
                return;
            }

            try {
                await api.delete(`/clients/${id}`);
                await this.loadClients();
                this.filterClients();
                alert('✅ Cliente eliminato con successo');
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },

        /**
         * Vista dettaglio cliente
         */
        async viewClientDetails(client) {
            // TODO: Implementare modal dettaglio con statistiche
            alert(`Dettaglio cliente: ${client.name}\n\nFunzionalità in arrivo:\n- Task associati\n- Ore lavorate\n- Progetti attivi\n- Timeline attività`);
            console.log('Client details:', client);
        },

        /**
         * Carica progetti
         */
        async loadProjects() {
            try {
                this.loading = true;
                const response = await api.get('/projects');
                this.projects = response.data;
                this.filterProjects();
            } catch (error) {
                console.error('❌ Errore caricamento progetti:', error);
                this.projects = [];
                this.filteredProjects = [];
            } finally {
                this.loading = false;
            }
        },

        /**
         * Filtra progetti
         */
        filterProjects() {
            let filtered = Array.isArray(this.projects) ? [...this.projects] : [];

            // Filtro ricerca testuale
            if (this.projectSearch && this.projectSearch.trim() !== '') {
                const search = this.projectSearch.toLowerCase();
                filtered = filtered.filter(project =>
                    (project.name && project.name.toLowerCase().includes(search)) ||
                    (project.description && project.description.toLowerCase().includes(search)) ||
                    (project.client_name && project.client_name.toLowerCase().includes(search))
                );
            }

            // Filtro stato
            if (this.projectStatusFilter && this.projectStatusFilter !== '') {
                filtered = filtered.filter(project =>
                    project.status === this.projectStatusFilter
                );
            }

            // Filtro cliente
            if (this.projectClientFilter && this.projectClientFilter !== '') {
                filtered = filtered.filter(project =>
                    project.client_id == this.projectClientFilter
                );
            }

            this.filteredProjects = filtered;
        },

        /**
         * Salva progetto
         */
        async saveProject() {
            try {
                if (this.editingProject) {
                    await api.put(`/projects/${this.editingProject.id}`, this.projectForm);
                } else {
                    await api.post('/projects', this.projectForm);
                }
                this.showProjectModal = false;
                this.resetProjectForm();
                await this.loadProjects();
                this.filterProjects();
                alert('✅ Progetto salvato con successo');
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },

        /**
         * Modifica progetto
         */
        editProject(project) {
            this.editingProject = project;
            this.projectForm = {
                name: project.name,
                description: project.description || '',
                client_id: project.client_id || '',
                color: project.color || '#10B981',
                status: project.status || 'planning',
                start_date: project.start_date || '',
                due_date: project.due_date || '',
                estimated_hours: project.estimated_hours || '',
                budget: project.budget || '',
                is_billable: project.is_billable !== undefined ? project.is_billable : true,
            };
            this.showProjectModal = true;
        },

        /**
         * Elimina progetto
         */
        async deleteProject(id) {
            if (!confirm('Sei sicuro di voler eliminare questo progetto? Verranno mantenuti i task associati.')) {
                return;
            }

            try {
                await api.delete(`/projects/${id}`);
                await this.loadProjects();
                this.filterProjects();
                alert('✅ Progetto eliminato');
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },

        /**
         * Reset form progetto
         */
        resetProjectForm() {
            this.editingProject = null;
            this.projectForm = {
                name: '',
                description: '',
                client_id: '',
                color: '#10B981',
                status: 'planning',
                start_date: '',
                due_date: '',
                estimated_hours: '',
                budget: '',
                is_billable: true,
            };
        },

        /**
         * Label stato progetto
         */
        projectStatusLabel(status) {
            const labels = {
                planning: 'In Pianificazione',
                active: 'Attivo',
                on_hold: 'In Pausa',
                completed: 'Completato',
                cancelled: 'Cancellato',
            };
            return labels[status] || 'Sconosciuto';
        },

        /**
         * Classe CSS stato progetto
         */
        projectStatusClass(status) {
            const classes = {
                planning: 'bg-blue-100 text-blue-700',
                active: 'bg-green-100 text-green-700',
                on_hold: 'bg-yellow-100 text-yellow-700',
                completed: 'bg-gray-100 text-gray-700',
                cancelled: 'bg-red-100 text-red-700',
            };
            return classes[status] || 'bg-gray-100 text-gray-700';
        },

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

            this.timerInterval = setInterval(() => {
                if (this.runningTimer && this.runningTimer.start_time) {
                    const elapsed = (Date.now() - new Date(this.runningTimer.start_time).getTime()) / 1000;
                    this.runningTimer.elapsed = elapsed;
                }
            }, 1000);

            setInterval(() => this.checkRunningTimer(), 30000);
        },

        destroy() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
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
        // async loadClients() {
        //     try {
        //         this.loading = true;
        //         const response = await api.get('/clients');
        //         this.clients = response.data;
        //         this.filterClients(); // <-- AGGIUNGI QUESTA RIGA
        //     } catch (error) {
        //         console.error('Errore caricamento clienti:', error);
        //     } finally {
        //         this.loading = false;
        //     }
        // },

        async loadClients() {
            try {
                this.loading = true;
                const response = await api.get('/clients');

                console.log('📦 Response:', response);
                console.log('📦 Response.data:', response.data);

                // FIX: response.data contiene l'array
                this.clients = response.data;

                console.log('✅ this.clients popolato con:', this.clients.length, 'elementi');

                this.filterClients();

                console.log('✅ filteredClients dopo filtro:', this.filteredClients.length);
            } catch (error) {
                console.error('❌ Errore caricamento clienti:', error);
                this.clients = [];
                this.filteredClients = [];
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
                this.filterClients(); // <-- ASSICURATI CHE CI SIA
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
                color: '#8B5CF6',
                notes: '',
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
                    await this.loadClients();
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
                case 'settings':
                    await this.loadUserSettings(); // <-- AGGIUNTO
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



// Carica dati utente
        async loadUserSettings() {
            try {
                this.settingsLoading = true;
                const response = await api.get('/settings');

                console.log('📥 Dati ricevuti da server:', response.data);

                // Carica profilo
                this.userProfile = {
                    name: response.data.name || '',
                    email: response.data.email || '',
                    timezone: response.data.timezone || 'Europe/Rome',
                };

                // Carica preferenze - FIX: reset prima dei default
                let loadedPreferences = {
                    email_notifications: true,
                    ai_suggestions: true,
                    dark_mode: false,
                };

                if (response.data.preferences) {
                    try {
                        // Parse se è stringa JSON
                        const prefs = typeof response.data.preferences === 'string'
                            ? JSON.parse(response.data.preferences)
                            : response.data.preferences;

                        console.log('📝 Preferenze dal DB:', prefs);

                        // Merge con valori caricati (sovrascrive i default)
                        loadedPreferences = {
                            email_notifications: prefs.email_notifications ?? true,
                            ai_suggestions: prefs.ai_suggestions ?? true,
                            dark_mode: prefs.dark_mode ?? false,
                        };
                    } catch (e) {
                        console.error('Errore parsing preferenze:', e);
                    }
                }

                // Imposta le preferenze caricate
                this.userPreferences = loadedPreferences;

                console.log('✅ Preferenze caricate:', this.userPreferences);

                // Applica tema scuro se attivo
                if (this.userPreferences.dark_mode) {
                    document.documentElement.classList.add('dark');
                }

            } catch (error) {
                console.error('❌ Errore caricamento settings:', error);
            } finally {
                this.settingsLoading = false;
            }
        },

// Salva profilo
        async saveProfile() {
            try {
                await api.put('/settings/profile', this.userProfile);
                alert('Profilo aggiornato con successo!');
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },

// Cambia password
        async changePassword() {
            if (this.passwordForm.new_password !== this.passwordForm.confirm_password) {
                alert('Le password non corrispondono!');
                return;
            }

            if (this.passwordForm.new_password.length < 8) {
                alert('La password deve essere di almeno 8 caratteri!');
                return;
            }

            try {
                await api.post('/settings/password', this.passwordForm);
                alert('Password modificata con successo!');

                // Reset form
                this.passwordForm = {
                    current_password: '',
                    new_password: '',
                    confirm_password: '',
                };
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },

// Salva preferenze
        async savePreferences() {
            try {
                await api.put('/settings/preferences', this.userPreferences);
                this.settingsSaved = true;
                setTimeout(() => this.settingsSaved = false, 3000);
                alert('Preferenze salvate!');
            } catch (error) {
                alert('Errore: ' + error.message);
            }
        },

// Toggle singola preferenza (per i switch)
        async togglePreference(key) {
            try {
                // Aggiorna immediatamente l'UI
                this.userPreferences[key] = !this.userPreferences[key];

                // Salva automaticamente su server
                await api.put('/settings/preferences', this.userPreferences);

                console.log(`✅ Preferenza ${key} salvata:`, this.userPreferences[key]);

                // Mostra feedback
                this.settingsSaved = true;
                setTimeout(() => this.settingsSaved = false, 2000);

                // Applica tema scuro se necessario
                if (key === 'dark_mode') {
                    document.documentElement.classList.toggle('dark', this.userPreferences.dark_mode);
                }
            } catch (error) {
                // Rollback se errore
                this.userPreferences[key] = !this.userPreferences[key];
                alert('Errore nel salvataggio: ' + error.message);
                console.error('Errore toggle:', error);
            }
        },

// Elimina account
        async confirmDeleteAccount() {
            const password = prompt('Per confermare l\'eliminazione, inserisci la tua password:');

            if (!password) return;

            if (!confirm('Sei SICURO di voler eliminare il tuo account? Questa azione è IRREVERSIBILE!')) {
                return;
            }

            try {
                await api.delete('/settings/account', { password });
                alert('Account eliminato');
                window.location.href = './login';
            } catch (error) {
                alert('Errore: ' + error.message);
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
