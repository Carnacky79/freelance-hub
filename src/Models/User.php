<?php

namespace FreelanceHub\Models;

/**
 * User Model
 */
class User extends Model
{
    protected static string $table = 'users';
    
    protected static array $fillable = [
        'email',
        'password_hash',
        'name',
        'avatar_url',
        'timezone',
        'default_hourly_rate',
        'working_hours_start',
        'working_hours_end',
        'working_days',
        'ai_enabled',
    ];

    protected static array $hidden = [
        'password_hash',
    ];

    /**
     * Trova utente per email
     */
    public static function findByEmail(string $email): ?static
    {
        return static::whereFirst('email', $email);
    }

    /**
     * Crea nuovo utente con password hashata
     */
    public static function register(array $data): static
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        return static::create($data);
    }

    /**
     * Verifica password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    /**
     * Clienti dell'utente
     */
    public function clients(bool $activeOnly = true): array
    {
        return Client::forUser($this->getId(), $activeOnly);
    }

    /**
     * Progetti dell'utente
     */
    public function projects(): array
    {
        return Project::where('user_id', $this->getId());
    }

    /**
     * Task dell'utente
     */
    public function tasks(array $filters = []): array
    {
        return Task::forUser($this->getId(), $filters);
    }

    /**
     * Giorni lavorativi come array
     */
    public function getWorkingDays(): array
    {
        if (!$this->working_days) {
            return ['mon', 'tue', 'wed', 'thu', 'fri'];
        }
        return json_decode($this->working_days, true) ?? [];
    }

    /**
     * Ore lavorative giornaliere
     */
    public function getWorkingHoursPerDay(): float
    {
        $start = strtotime($this->working_hours_start ?? '09:00:00');
        $end = strtotime($this->working_hours_end ?? '18:00:00');
        return ($end - $start) / 3600;
    }

    /**
     * Account integrazioni
     */
    public function integrationAccounts(): array
    {
        return IntegrationAccount::allForUser($this->getId());
    }
}
