# FreelanceHub - Architettura del Sistema

## 📋 Overview

**FreelanceHub** è un sistema centralizzato di gestione task per freelance che integra:
- Gestione clienti e progetti
- Integrazione multi-account con Asana, Google Calendar, ClickUp
- Assistente IA per priorità, scadenze e allocazione tempo
- Time tracking per task e clienti

---

## 🏗️ Stack Tecnologico

| Componente | Tecnologia |
|------------|------------|
| Backend | PHP 8.2+ |
| Database | MySQL 8.0+ |
| Frontend | HTML5/CSS3/JavaScript |
| Librerie JS | Alpine.js (reattività), Chart.js (grafici), FullCalendar (calendario) |
| CSS Framework | Tailwind CSS |
| IA | OpenAI API / Claude API |
| Autenticazione | OAuth 2.0 (per integrazioni) |

---

## 📁 Struttura del Progetto

```
freelance-hub/
├── config/
│   ├── app.php              # Configurazione generale
│   ├── database.php         # Configurazione DB
│   ├── integrations.php     # Credenziali API esterne
│   └── ai.php               # Configurazione IA
├── src/
│   ├── Controllers/         # Controller MVC
│   │   ├── DashboardController.php
│   │   ├── ClientController.php
│   │   ├── ProjectController.php
│   │   ├── TaskController.php
│   │   ├── TimeTrackingController.php
│   │   ├── CalendarController.php
│   │   └── IntegrationController.php
│   ├── Models/              # Modelli database
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Project.php
│   │   ├── Task.php
│   │   ├── TimeEntry.php
│   │   ├── Integration.php
│   │   └── AIRecommendation.php
│   ├── Services/
│   │   ├── Integrations/    # Connettori API esterni
│   │   │   ├── AsanaService.php
│   │   │   ├── GoogleCalendarService.php
│   │   │   ├── ClickUpService.php
│   │   │   └── IntegrationManager.php
│   │   └── AI/              # Servizi IA
│   │       ├── AIAssistant.php
│   │       ├── PriorityAnalyzer.php
│   │       ├── TimeAllocator.php
│   │       └── DeadlineOptimizer.php
│   ├── Core/                # Classi core
│   │   ├── Database.php
│   │   ├── Router.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Session.php
│   └── Middleware/
│       ├── AuthMiddleware.php
│       └── CorsMiddleware.php
├── public/                  # Document root
│   ├── index.php           # Entry point
│   ├── css/
│   ├── js/
│   └── assets/
├── views/
│   ├── layouts/
│   ├── components/
│   └── pages/
├── database/
│   ├── migrations/
│   └── seeds/
├── api/                     # Endpoint API REST
│   └── v1/
├── storage/
│   ├── logs/
│   └── cache/
└── tests/
```

---

## 🗄️ Schema Database

### Diagramma ER

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   users     │────<│  clients    │────<│  projects   │
└─────────────┘     └─────────────┘     └─────────────┘
       │                                       │
       │            ┌─────────────┐            │
       └───────────>│   tasks     │<───────────┘
                    └─────────────┘
                           │
       ┌───────────────────┼───────────────────┐
       ▼                   ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ time_entries│     │external_tasks│    │ai_recommend │
└─────────────┘     └─────────────┘     └─────────────┘

┌─────────────┐     ┌─────────────┐
│integrations │────<│int_accounts │
└─────────────┘     └─────────────┘
```

### Tabelle Principali

| Tabella | Descrizione |
|---------|-------------|
| `users` | Utenti freelance del sistema |
| `clients` | Clienti del freelance |
| `projects` | Progetti per cliente |
| `tasks` | Task interni del sistema |
| `time_entries` | Registrazioni tempo lavoro |
| `integrations` | Tipi di integrazione (Asana, GCal, ClickUp) |
| `integration_accounts` | Account multipli per integrazione |
| `external_tasks` | Task sincronizzati da fonti esterne |
| `ai_recommendations` | Suggerimenti generati dall'IA |
| `calendar_events` | Eventi calendario sincronizzati |

---

## 🔌 Sistema di Integrazioni Multi-Account

### Flusso OAuth 2.0

```
┌──────────┐     ┌──────────────┐     ┌─────────────┐
│  Utente  │────>│ FreelanceHub │────>│ Servizio    │
│          │     │              │     │ (Asana/GCal)│
└──────────┘     └──────────────┘     └─────────────┘
     │                  │                    │
     │   1. Click       │                    │
     │   "Connetti"     │                    │
     │─────────────────>│                    │
     │                  │  2. Redirect OAuth │
     │                  │───────────────────>│
     │                  │                    │
     │                  │  3. Auth Code      │
     │                  │<───────────────────│
     │                  │                    │
     │                  │  4. Exchange Token │
     │                  │───────────────────>│
     │                  │                    │
     │                  │  5. Access Token   │
     │                  │<───────────────────│
     │                  │                    │
     │  6. Conferma     │  7. Store Token    │
     │<─────────────────│   (encrypted)      │
```

### Gestione Account Multipli

Ogni integrazione supporta N account. Esempio:
- **Google Calendar**: Account personale + Account business
- **Asana**: Workspace Cliente A + Workspace Cliente B
- **ClickUp**: Space Progetto X + Space Progetto Y

---

## 🤖 Sistema IA

### Componenti

1. **PriorityAnalyzer**: Analizza urgenza/importanza dei task
2. **TimeAllocator**: Suggerisce allocazione ottimale del tempo
3. **DeadlineOptimizer**: Propone scadenze realistiche

### Dati Utilizzati per l'Analisi

```php
$taskContext = [
    'task' => [...],           // Dettagli task
    'client_history' => [...], // Storico cliente
    'workload' => [...],       // Carico attuale
    'deadlines' => [...],      // Scadenze esistenti
    'time_patterns' => [...],  // Pattern di lavoro utente
    'external_events' => [...] // Eventi calendario
];
```

### Output IA

```json
{
  "priority_score": 85,
  "suggested_deadline": "2025-01-15",
  "time_allocation": {
    "estimated_hours": 4,
    "best_time_slots": ["2025-01-10 09:00", "2025-01-12 14:00"]
  },
  "reasoning": "Task critico per cliente prioritario con deadline ravvicinata..."
}
```

---

## ⏱️ Time Tracking

### Funzionalità

- Timer start/stop per task
- Inserimento manuale ore
- Categorizzazione per cliente/progetto
- Report settimanali/mensili
- Export per fatturazione

### Struttura Time Entry

```php
[
    'id' => 1,
    'task_id' => 123,
    'user_id' => 1,
    'start_time' => '2025-01-10 09:00:00',
    'end_time' => '2025-01-10 11:30:00',
    'duration_minutes' => 150,
    'notes' => 'Sviluppo feature login',
    'billable' => true,
    'hourly_rate' => 50.00
]
```

---

## 🔄 Sincronizzazione

### Strategia

| Tipo | Frequenza | Metodo |
|------|-----------|--------|
| Real-time | Immediata | Webhooks (dove supportato) |
| Periodica | 5-15 min | Cron job polling |
| Manuale | On-demand | Bottone sync utente |

### Conflict Resolution

1. **Last-write-wins**: Per modifiche semplici
2. **User-prompt**: Per conflitti complessi
3. **Source-of-truth**: Configurabile per integrazione

---

## 🛡️ Sicurezza

- Token OAuth criptati (AES-256)
- CSRF protection
- Rate limiting API
- Input sanitization
- Prepared statements SQL
- HTTPS obbligatorio

---

## 📱 API REST

Base URL: `/api/v1/`

| Endpoint | Metodo | Descrizione |
|----------|--------|-------------|
| `/tasks` | GET/POST | Lista/crea task |
| `/tasks/{id}` | GET/PUT/DELETE | Gestione singolo task |
| `/clients` | GET/POST | Lista/crea clienti |
| `/time-entries` | GET/POST | Time tracking |
| `/calendar/events` | GET | Eventi calendario |
| `/ai/suggestions` | GET | Suggerimenti IA |
| `/sync/{service}` | POST | Forza sincronizzazione |

---

## 🚀 Prossimi Step

1. [ ] Setup database e migrations
2. [ ] Implementazione Core (Router, Database, Auth)
3. [ ] CRUD Clienti e Progetti
4. [ ] CRUD Task con time tracking
5. [ ] Integrazione Google Calendar OAuth
6. [ ] Integrazione Asana OAuth
7. [ ] Integrazione ClickUp OAuth
8. [ ] Implementazione IA
9. [ ] Dashboard e UI
10. [ ] Testing e deploy
