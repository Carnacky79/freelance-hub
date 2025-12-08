# 🚀 FreelanceHub

**Task Manager centralizzato per freelance** con integrazione IA, multi-account e time tracking.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## ✨ Funzionalità

### 📋 Gestione Task
- CRUD completo task con priorità, scadenze, stati
- Organizzazione per cliente e progetto
- Subtask e dipendenze
- Tag personalizzabili

### 👥 Gestione Clienti
- Anagrafica clienti completa
- Tariffa oraria personalizzabile per cliente
- Livelli di priorità cliente
- Storico lavori

### ⏱️ Time Tracking
- Timer start/stop con un click
- Inserimento manuale ore
- Associazione a task/cliente/progetto
- Report giornalieri, settimanali, mensili
- Tracciamento ore fatturabili

### 🤖 Assistente IA
- **Analisi priorità**: Calcola automaticamente l'urgenza dei task
- **Suggerimenti scadenze**: Propone date realistiche basate sul workload
- **Allocazione tempo**: Consiglia come distribuire le ore
- **Avvisi workload**: Segnala sovraccarichi in arrivo

### 🔗 Integrazioni Multi-Account
- **Google Calendar**: Sincronizza eventi (supporta account multipli)
- **Asana**: Importa task e progetti
- **ClickUp**: Sincronizza task e spazi

Ogni integrazione supporta **account multipli** - perfetto per freelance con account personali e business separati.

### 📅 Calendario Unificato
- Vista calendario con tutti i task e eventi
- Sincronizzazione bidirezionale
- Supporto eventi ricorrenti

---

## 🛠️ Stack Tecnologico

| Componente | Tecnologia |
|------------|------------|
| **Backend** | PHP 8.2+ (vanilla, no framework) |
| **Database** | MySQL 8.0+ |
| **Frontend** | HTML5, CSS3, JavaScript |
| **CSS** | Tailwind CSS (via CDN) |
| **JS Reactivity** | Alpine.js |
| **Charts** | Chart.js |
| **Calendar** | FullCalendar |
| **AI** | Claude API / OpenAI API |

---

## 📦 Installazione

### Requisiti
- PHP 8.2+
- MySQL 8.0+
- Composer (opzionale)
- Estensioni PHP: pdo_mysql, curl, json, openssl

### 1. Clone del repository
```bash
git clone https://github.com/tuouser/freelance-hub.git
cd freelance-hub
```

### 2. Configurazione environment
```bash
cp .env.example .env
# Modifica .env con i tuoi valori
```

### 3. Creazione database
```bash
mysql -u root -p -e "CREATE DATABASE freelance_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Esecuzione migrations
```bash
mysql -u root -p freelance_hub < database/migrations/001_initial_schema.sql
```

### 5. Avvio server di sviluppo
```bash
cd public
php -S localhost:8000
```

### 6. Accedi all'applicazione
Apri http://localhost:8000 nel browser.

---

## ⚙️ Configurazione Integrazioni

### Google Calendar

1. Vai su [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Crea un nuovo progetto
3. Abilita Google Calendar API
4. Crea credenziali OAuth 2.0
5. Aggiungi `http://localhost:8000/integrations/google/callback` come redirect URI
6. Copia Client ID e Secret nel `.env`

### Asana

1. Vai su [Asana Developer Console](https://app.asana.com/0/developer-console)
2. Crea una nuova app
3. Aggiungi `http://localhost:8000/integrations/asana/callback` come redirect URI
4. Copia le credenziali nel `.env`

### ClickUp

1. Vai su [ClickUp Settings > Apps](https://app.clickup.com/settings/apps)
2. Crea una nuova app
3. Aggiungi `http://localhost:8000/integrations/clickup/callback` come redirect URI
4. Copia le credenziali nel `.env`

---

## 🤖 Configurazione IA

FreelanceHub supporta due provider IA:

### Claude (consigliato)
```env
AI_PROVIDER=claude
CLAUDE_API_KEY=sk-ant-xxxxxxxxxxxxx
```

### OpenAI
```env
AI_PROVIDER=openai
OPENAI_API_KEY=sk-xxxxxxxxxxxxx
```

L'IA analizza automaticamente i tuoi task e fornisce suggerimenti su:
- Prioritizzazione basata su scadenze e importanza cliente
- Scadenze realistiche basate sul carico di lavoro
- Allocazione ottimale del tempo
- Avvisi su potenziali sovraccarichi

---

## 📁 Struttura Progetto

```
freelance-hub/
├── config/                 # Configurazioni
│   ├── app.php
│   ├── database.php
│   ├── integrations.php
│   └── ai.php
├── src/
│   ├── Controllers/        # Controller API
│   ├── Models/             # Modelli database
│   ├── Services/
│   │   ├── Integrations/   # Connettori OAuth
│   │   └── AI/             # Servizi IA
│   ├── Core/               # Router, Request, Response
│   └── Middleware/
├── public/                 # Document root
│   ├── index.php          # Entry point
│   ├── css/
│   └── js/
├── views/                  # Template PHP
├── database/
│   └── migrations/
├── storage/
│   ├── logs/
│   └── cache/
└── tests/
```

---

## 🔌 API Endpoints

### Autenticazione
| Metodo | Endpoint | Descrizione |
|--------|----------|-------------|
| POST | `/api/v1/auth/login` | Login |
| POST | `/api/v1/auth/register` | Registrazione |
| POST | `/api/v1/auth/logout` | Logout |

### Task
| Metodo | Endpoint | Descrizione |
|--------|----------|-------------|
| GET | `/api/v1/tasks` | Lista task |
| POST | `/api/v1/tasks` | Crea task |
| GET | `/api/v1/tasks/{id}` | Dettaglio task |
| PUT | `/api/v1/tasks/{id}` | Aggiorna task |
| DELETE | `/api/v1/tasks/{id}` | Elimina task |
| POST | `/api/v1/tasks/{id}/complete` | Completa task |

### Time Tracking
| Metodo | Endpoint | Descrizione |
|--------|----------|-------------|
| GET | `/api/v1/time-entries` | Lista entries |
| POST | `/api/v1/time-entries` | Crea entry manuale |
| POST | `/api/v1/time-entries/start` | Avvia timer |
| POST | `/api/v1/time-entries/stop` | Ferma timer |
| GET | `/api/v1/time-entries/running` | Timer attivo |

### Integrazioni
| Metodo | Endpoint | Descrizione |
|--------|----------|-------------|
| GET | `/api/v1/integrations` | Lista account connessi |
| GET | `/api/v1/integrations/{service}/auth` | Avvia OAuth |
| POST | `/api/v1/integrations/{id}/sync` | Forza sync |
| DELETE | `/api/v1/integrations/{id}` | Disconnetti |

### AI
| Metodo | Endpoint | Descrizione |
|--------|----------|-------------|
| GET | `/api/v1/ai/suggestions` | Suggerimenti attivi |
| POST | `/api/v1/ai/suggestions/{id}/accept` | Accetta suggerimento |
| POST | `/api/v1/ai/suggestions/{id}/dismiss` | Ignora suggerimento |

---

## 🚀 Roadmap

- [ ] App mobile (PWA)
- [ ] Notifiche push
- [ ] Integrazione Trello
- [ ] Integrazione Notion
- [ ] Export fatture PDF
- [ ] Dashboard analytics avanzata
- [ ] Supporto multi-lingua
- [ ] API pubblica con OAuth

---

## 📄 Licenza

MIT License - vedi [LICENSE](LICENSE) per dettagli.

---

## 🤝 Contributi

Contributi benvenuti! Apri una issue o pull request.

---

**Made with ❤️ for freelancers**
