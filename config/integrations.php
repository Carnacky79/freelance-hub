<?php
/**
 * FreelanceHub - Configurazione Integrazioni
 * 
 * Per ottenere le credenziali:
 * - Asana: https://app.asana.com/0/developer-console
 * - Google: https://console.cloud.google.com/apis/credentials
 * - ClickUp: https://app.clickup.com/settings/apps
 */

return [
    'asana' => [
        'client_id' => $_ENV['ASANA_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['ASANA_CLIENT_SECRET'] ?? '',
        'redirect_uri' => $_ENV['APP_URL'] . '/integrations/asana/callback',
        'scopes' => ['default'],
        'api_base' => 'https://app.asana.com/api/1.0',
    ],
    
    'google_calendar' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
        'redirect_uri' => $_ENV['APP_URL'] . '/integrations/google/callback',
        'scopes' => [
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/userinfo.email',
        ],
        'api_base' => 'https://www.googleapis.com/calendar/v3',
    ],
    
    'clickup' => [
        'client_id' => $_ENV['CLICKUP_CLIENT_ID'] ?? '',
        'client_secret' => $_ENV['CLICKUP_CLIENT_SECRET'] ?? '',
        'redirect_uri' => $_ENV['APP_URL'] . '/integrations/clickup/callback',
        'scopes' => [],
        'api_base' => 'https://api.clickup.com/api/v2',
    ],
    
    // Impostazioni sincronizzazione
    'sync' => [
        'interval_minutes' => 15,
        'max_retries' => 3,
        'timeout_seconds' => 30,
    ],
];
