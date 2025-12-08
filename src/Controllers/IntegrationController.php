<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Core\Database;

/**
 * IntegrationController - Gestione integrazioni
 */
class IntegrationController
{
    /**
     * Lista integrazioni disponibili e account connessi
     */
    public function index(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        // Integrazioni disponibili
        $available = [
            [
                'slug' => 'google_calendar',
                'name' => 'Google Calendar',
                'description' => 'Sincronizza eventi e scadenze',
                'icon' => '📅',
                'connected' => false,
                'accounts' => []
            ],
            [
                'slug' => 'asana',
                'name' => 'Asana',
                'description' => 'Importa task e progetti',
                'icon' => '📋',
                'connected' => false,
                'accounts' => []
            ],
            [
                'slug' => 'clickup',
                'name' => 'ClickUp',
                'description' => 'Sincronizza task e tempo',
                'icon' => '✅',
                'connected' => false,
                'accounts' => []
            ],
        ];

        // Carica account connessi
        $db = Database::getInstance();
        $accounts = $db->select(
            "SELECT * FROM integration_accounts WHERE user_id = ?",
            [$userId]
        );
        
        foreach ($accounts as $account) {
            foreach ($available as &$integration) {
                if ($account['integration_id'] == $this->getIntegrationId($integration['slug'])) {
                    $integration['connected'] = true;
                    $integration['accounts'][] = [
                        'id' => $account['id'],
                        'name' => $account['account_name'],
                        'email' => $account['account_email'],
                        'last_sync' => $account['last_sync_at'],
                        'sync_enabled' => (bool)$account['sync_enabled'],
                    ];
                }
            }
        }

        return Response::success($available);
    }

    /**
     * Ottieni URL di autorizzazione OAuth
     */
    public function auth(Request $request): Response
    {
        $service = $request->getParam('service');
        
        // Configurazione OAuth per ogni servizio
        $configs = [
            'google_calendar' => [
                'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
                'client_id' => getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID',
                'redirect_uri' => $this->getBaseUrl() . '/api/v1/integrations/google_calendar/callback',
                'scopes' => 'https://www.googleapis.com/auth/calendar.readonly https://www.googleapis.com/auth/calendar.events',
            ],
            'asana' => [
                'auth_url' => 'https://app.asana.com/-/oauth_authorize',
                'client_id' => getenv('ASANA_CLIENT_ID') ?: 'YOUR_ASANA_CLIENT_ID',
                'redirect_uri' => $this->getBaseUrl() . '/api/v1/integrations/asana/callback',
                'scopes' => '',
            ],
            'clickup' => [
                'auth_url' => 'https://app.clickup.com/api',
                'client_id' => getenv('CLICKUP_CLIENT_ID') ?: 'YOUR_CLICKUP_CLIENT_ID',
                'redirect_uri' => $this->getBaseUrl() . '/api/v1/integrations/clickup/callback',
                'scopes' => '',
            ],
        ];
        
        if (!isset($configs[$service])) {
            return Response::error('Servizio non supportato: ' . $service, 400);
        }

        $config = $configs[$service];
        
        // Genera state per sicurezza
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_service'] = $service;
        
        // Costruisci URL OAuth
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];
        
        if (!empty($config['scopes'])) {
            $params['scope'] = $config['scopes'];
        }

        $authUrl = $config['auth_url'] . '?' . http_build_query($params);

        // Redirect diretto all'URL OAuth
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Callback OAuth
     */
    public function callback(Request $request): Response
    {
        $service = $request->getParam('service');
        $code = $request->getQuery('code');
        $state = $request->getQuery('state');
        $error = $request->getQuery('error');
        
        // Gestisci errori OAuth
        if ($error) {
            header('Location: ' . $this->getBaseUrl() . '/?error=' . urlencode($error));
            exit;
        }
        
        // Verifica state
        if ($state !== ($_SESSION['oauth_state'] ?? '')) {
            header('Location: ' . $this->getBaseUrl() . '/?error=invalid_state');
            exit;
        }

        // TODO: Scambia code per access token
        // Per ora salva un account demo
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId && $code) {
            $db = Database::getInstance();
            
            // Inserisci account (demo)
            $db->query(
                "INSERT INTO integration_accounts (user_id, integration_id, account_name, account_email, access_token, sync_enabled, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())",
                [
                    $userId,
                    $this->getIntegrationId($service),
                    ucfirst(str_replace('_', ' ', $service)) . ' Account',
                    $_SESSION['user_email'] ?? 'user@example.com',
                    $code
                ]
            );
        }

        // Pulisci sessione OAuth
        unset($_SESSION['oauth_state'], $_SESSION['oauth_service']);

        // Redirect alla pagina integrazioni
        header('Location: ' . $this->getBaseUrl() . '/?page=integrations&connected=' . $service);
        exit;
    }

    /**
     * Sincronizza account
     */
    public function sync(Request $request): Response
    {
        $accountId = $request->getParam('accountId');
        $userId = $_SESSION['user_id'] ?? null;
        
        $db = Database::getInstance();
        $account = $db->selectOne(
            "SELECT * FROM integration_accounts WHERE id = ? AND user_id = ?",
            [$accountId, $userId]
        );
        
        if (!$account) {
            return Response::notFound('Account non trovato');
        }

        $db->query(
            "UPDATE integration_accounts SET last_sync_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$accountId]
        );

        return Response::success(['message' => 'Sincronizzazione completata']);
    }

    /**
     * Disconnetti account
     */
    public function disconnect(Request $request): Response
    {
        $accountId = $request->getParam('accountId');
        $userId = $_SESSION['user_id'] ?? null;
        
        $db = Database::getInstance();
        $account = $db->selectOne(
            "SELECT * FROM integration_accounts WHERE id = ? AND user_id = ?",
            [$accountId, $userId]
        );
        
        if (!$account) {
            return Response::notFound('Account non trovato');
        }

        $db->query("DELETE FROM integration_accounts WHERE id = ?", [$accountId]);

        return Response::success(['message' => 'Account disconnesso']);
    }

    /**
     * Helper per ottenere ID integrazione da slug
     */
    private function getIntegrationId(string $slug): int
    {
        $map = [
            'asana' => 1,
            'google_calendar' => 2,
            'clickup' => 3,
        ];
        return $map[$slug] ?? 0;
    }
    
    /**
     * Helper per ottenere base URL
     */
    private function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);
        
        return $protocol . '://' . $host . $basePath;
    }
}