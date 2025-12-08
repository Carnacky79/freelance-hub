<?php

namespace FreelanceHub\Models;

/**
 * IntegrationAccount Model - Gestisce account multipli per integrazione
 */
class IntegrationAccount extends Model
{
    protected static string $table = 'integration_accounts';
    
    protected static array $fillable = [
        'user_id',
        'integration_id',
        'account_name',
        'account_email',
        'external_user_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'extra_data',
        'sync_enabled',
        'last_sync_at',
        'sync_error',
        'is_active',
    ];

    protected static array $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Account per utente e tipo integrazione
     */
    public static function forUserByIntegration(int $userId, string $integrationSlug): array
    {
        $sql = "SELECT ia.*, i.slug as integration_slug, i.name as integration_name
                FROM integration_accounts ia
                JOIN integrations i ON ia.integration_id = i.id
                WHERE ia.user_id = ? AND i.slug = ? AND ia.is_active = 1
                ORDER BY ia.account_name ASC";
        
        return static::query($sql, [$userId, $integrationSlug]);
    }

    /**
     * Tutti gli account attivi per utente
     */
    public static function allForUser(int $userId): array
    {
        $sql = "SELECT ia.*, i.slug as integration_slug, i.name as integration_name, i.icon_url
                FROM integration_accounts ia
                JOIN integrations i ON ia.integration_id = i.id
                WHERE ia.user_id = ? AND ia.is_active = 1
                ORDER BY i.name ASC, ia.account_name ASC";
        
        return static::query($sql, [$userId]);
    }

    /**
     * Account che necessitano sincronizzazione
     */
    public static function needsSync(int $intervalMinutes = 15): array
    {
        $sql = "SELECT ia.*, i.slug as integration_slug
                FROM integration_accounts ia
                JOIN integrations i ON ia.integration_id = i.id
                WHERE ia.is_active = 1 
                AND ia.sync_enabled = 1
                AND (ia.last_sync_at IS NULL 
                     OR ia.last_sync_at < DATE_SUB(NOW(), INTERVAL ? MINUTE))
                ORDER BY ia.last_sync_at ASC";
        
        return static::query($sql, [$intervalMinutes]);
    }

    /**
     * Verifica se token è scaduto
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }
        return strtotime($this->token_expires_at) < time();
    }

    /**
     * Verifica se token sta per scadere (entro 5 minuti)
     */
    public function isTokenExpiringSoon(int $minutes = 5): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }
        return strtotime($this->token_expires_at) < (time() + ($minutes * 60));
    }

    /**
     * Aggiorna i token
     */
    public function updateTokens(string $accessToken, ?string $refreshToken = null, ?int $expiresIn = null): bool
    {
        $data = [
            'access_token' => $this->encryptToken($accessToken),
        ];

        if ($refreshToken) {
            $data['refresh_token'] = $this->encryptToken($refreshToken);
        }

        if ($expiresIn) {
            $data['token_expires_at'] = date('Y-m-d H:i:s', time() + $expiresIn);
        }

        return $this->update($data);
    }

    /**
     * Ottieni access token decriptato
     */
    public function getDecryptedAccessToken(): ?string
    {
        if (!$this->access_token) return null;
        return $this->decryptToken($this->access_token);
    }

    /**
     * Ottieni refresh token decriptato
     */
    public function getDecryptedRefreshToken(): ?string
    {
        if (!$this->refresh_token) return null;
        return $this->decryptToken($this->refresh_token);
    }

    /**
     * Cripta un token
     */
    private function encryptToken(string $token): string
    {
        $config = require __DIR__ . '/../../config/app.php';
        $key = $config['encryption_key'];
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($token, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decripta un token
     */
    private function decryptToken(string $encrypted): string
    {
        $config = require __DIR__ . '/../../config/app.php';
        $key = $config['encryption_key'];
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Registra errore di sincronizzazione
     */
    public function recordSyncError(string $error): bool
    {
        return $this->update([
            'sync_error' => $error,
            'last_sync_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Registra sincronizzazione riuscita
     */
    public function recordSyncSuccess(): bool
    {
        return $this->update([
            'sync_error' => null,
            'last_sync_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Extra data come array
     */
    public function getExtraData(): array
    {
        if (!$this->extra_data) return [];
        return json_decode($this->extra_data, true) ?? [];
    }

    /**
     * Imposta extra data
     */
    public function setExtraData(array $data): bool
    {
        return $this->update([
            'extra_data' => json_encode($data),
        ]);
    }

    /**
     * Ottieni un valore specifico da extra_data
     */
    public function getExtraValue(string $key, $default = null)
    {
        $data = $this->getExtraData();
        return $data[$key] ?? $default;
    }

    /**
     * Disattiva account
     */
    public function disconnect(): bool
    {
        return $this->update([
            'is_active' => 0,
            'access_token' => null,
            'refresh_token' => null,
        ]);
    }

    /**
     * Task esterni sincronizzati da questo account
     */
    public function externalTasks(): array
    {
        return ExternalTask::where('integration_account_id', $this->getId());
    }

    /**
     * Eventi calendario sincronizzati da questo account
     */
    public function calendarEvents(): array
    {
        return CalendarEvent::where('integration_account_id', $this->getId());
    }
}
