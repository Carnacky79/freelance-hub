<?php

namespace FreelanceHub\Models;

/**
 * Client Model
 */
class Client extends Model
{
    protected static string $table = 'clients';
    
    protected static array $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'notes',
        'color',
        'priority_level',
        'hourly_rate',
        'is_active',
    ];

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    /**
     * Clienti per utente
     */
    public static function forUser(int $userId, bool $activeOnly = true): array
    {
        $sql = "SELECT c.*, 
                    (SELECT COUNT(*) FROM projects WHERE client_id = c.id) as projects_count,
                    (SELECT COUNT(*) FROM tasks WHERE client_id = c.id AND status NOT IN ('completed', 'cancelled')) as open_tasks_count
                FROM clients c 
                WHERE c.user_id = ?";
        
        if ($activeOnly) {
            $sql .= " AND c.is_active = 1";
        }
        
        $sql .= " ORDER BY c.priority_level DESC, c.name ASC";
        
        return static::query($sql, [$userId]);
    }

    /**
     * Progetti del cliente
     */
    public function projects(): array
    {
        return Project::where('client_id', $this->getId());
    }

    /**
     * Task del cliente
     */
    public function tasks(array $filters = []): array
    {
        $filters['client_id'] = $this->getId();
        return Task::forUser($this->user_id, $filters);
    }

    /**
     * Task aperti
     */
    public function openTasks(): array
    {
        $sql = "SELECT * FROM tasks 
                WHERE client_id = ? 
                AND status NOT IN ('completed', 'cancelled')
                ORDER BY due_date ASC";
        return Task::query($sql, [$this->getId()]);
    }

    /**
     * Time entries del cliente
     */
    public function timeEntries(string $from = null, string $to = null): array
    {
        $sql = "SELECT te.*, t.title as task_title 
                FROM time_entries te
                LEFT JOIN tasks t ON te.task_id = t.id
                WHERE te.client_id = ?";
        $params = [$this->getId()];

        if ($from) {
            $sql .= " AND te.start_time >= ?";
            $params[] = $from;
        }
        if ($to) {
            $sql .= " AND te.start_time <= ?";
            $params[] = $to;
        }

        $sql .= " ORDER BY te.start_time DESC";

        return TimeEntry::query($sql, $params);
    }

    /**
     * Totale ore tracciate
     */
    public function totalTrackedHours(string $from = null, string $to = null): float
    {
        $sql = "SELECT SUM(duration_minutes) as total FROM time_entries WHERE client_id = ?";
        $params = [$this->getId()];

        if ($from) {
            $sql .= " AND start_time >= ?";
            $params[] = $from;
        }
        if ($to) {
            $sql .= " AND start_time <= ?";
            $params[] = $to;
        }

        $result = static::db()->selectOne($sql, $params);
        return round(($result['total'] ?? 0) / 60, 2);
    }

    /**
     * Totale fatturabile
     */
    public function totalBillable(string $from = null, string $to = null): float
    {
        $sql = "SELECT SUM(duration_minutes * COALESCE(te.hourly_rate, 0) / 60) as total 
                FROM time_entries te
                WHERE te.client_id = ? AND te.is_billable = 1";
        $params = [$this->getId()];

        if ($from) {
            $sql .= " AND te.start_time >= ?";
            $params[] = $from;
        }
        if ($to) {
            $sql .= " AND te.start_time <= ?";
            $params[] = $to;
        }

        $result = static::db()->selectOne($sql, $params);
        return round($result['total'] ?? 0, 2);
    }

    /**
     * Tariffa oraria effettiva (client override o user default)
     */
    public function getEffectiveHourlyRate(): float
    {
        if ($this->hourly_rate) {
            return (float)$this->hourly_rate;
        }
        
        $user = User::find($this->user_id);
        return $user ? (float)$user->default_hourly_rate : 0;
    }

    /**
     * Disattiva cliente
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => 0]);
    }

    /**
     * Riattiva cliente
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => 1]);
    }
}
