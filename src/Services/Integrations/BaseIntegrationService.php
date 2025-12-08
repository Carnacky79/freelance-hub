<?php

namespace FreelanceHub\Services\Integrations;

use FreelanceHub\Models\IntegrationAccount;

/**
 * BaseIntegrationService - Classe base per tutti i servizi di integrazione
 */
abstract class BaseIntegrationService
{
    protected IntegrationAccount $account;
    protected array $config;
    protected string $slug;

    public function __construct(IntegrationAccount $account)
    {
        $this->account = $account;
        $allConfig = require __DIR__ . '/../../../config/integrations.php';
        $this->config = $allConfig[$this->slug] ?? [];
    }

    /**
     * Genera URL di autorizzazione OAuth
     */
    abstract public function getAuthUrl(string $state): string;

    /**
     * Scambia codice autorizzazione per token
     */
    abstract public function exchangeCodeForTokens(string $code): array;

    /**
     * Refresh del token
     */
    abstract public function refreshAccessToken(): bool;

    /**
     * Sincronizza task dall'integrazione
     */
    abstract public function syncTasks(): array;

    /**
     * Sincronizza eventi calendario
     */
    abstract public function syncCalendarEvents(): array;

    /**
     * Crea task nell'integrazione esterna
     */
    abstract public function createExternalTask(array $taskData): ?string;

    /**
     * Aggiorna task nell'integrazione esterna
     */
    abstract public function updateExternalTask(string $externalId, array $taskData): bool;

    /**
     * HTTP request helper
     */
    protected function httpRequest(
        string $method,
        string $url,
        array $data = [],
        array $headers = []
    ): array {
        $ch = curl_init();

        $defaultHeaders = [
            'Authorization: Bearer ' . $this->account->getDecryptedAccessToken(),
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT' || $method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("HTTP request failed: {$error}");
        }

        return [
            'status' => $httpCode,
            'data' => json_decode($response, true) ?? [],
            'raw' => $response,
        ];
    }

    /**
     * Verifica e refresh token se necessario
     */
    protected function ensureValidToken(): void
    {
        if ($this->account->isTokenExpiringSoon()) {
            $this->refreshAccessToken();
        }
    }

    /**
     * Ottieni info utente dal servizio esterno
     */
    abstract public function getUserInfo(): array;

    /**
     * Testa la connessione
     */
    public function testConnection(): bool
    {
        try {
            $this->ensureValidToken();
            $userInfo = $this->getUserInfo();
            return !empty($userInfo);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * OAuth token exchange helper
     */
    protected function exchangeToken(string $code, string $tokenUrl, array $extraParams = []): array
    {
        $params = array_merge([
            'grant_type' => 'authorization_code',
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'redirect_uri' => $this->config['redirect_uri'],
            'code' => $code,
        ], $extraParams);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    /**
     * Refresh token helper
     */
    protected function refreshToken(string $tokenUrl): array
    {
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'refresh_token' => $this->account->getDecryptedRefreshToken(),
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }
}
