<?php

namespace FreelanceHub\Models;

/**
 * Project Model
 */
class Project extends Model
{
    protected static string $table = 'projects';
    
    protected static array $fillable = [
        'user_id',
        'client_id',
        'name',
        'description',
        'color',
        'status',
        'start_date',
        'due_date',
        'estimated_hours',
        'budget',
        'is_billable',
    ];

    const STATUS_PLANNING = 'planning';
    const STATUS_ACTIVE = 'active';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Progetti per utente
     */
    public static function forUser(int $userId, array $filters = []): array
    {
        $sql = "SELECT p.*, 
                    c.name as client_name,
                    c.color as client_color,
                    (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status NOT IN ('completed', 'cancelled')) as open_tasks,
                    (SELECT SUM(duration_minutes) FROM time_entries WHERE project_id = p.id) as tracked_minutes
                FROM projects p
                LEFT JOIN clients c ON p.client_id = c.id
                WHERE p.user_id = ?";
        $params = [$userId];

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['client_id'])) {
            $sql .= " AND p.client_id = ?";
            $params[] = $filters['client_id'];
        }

        $sql .= " ORDER BY p.status ASC, p.due_date ASC";

        return static::query($sql, $params);
    }

    /**
     * Client del progetto
     */
    public function client(): ?Client
    {
        if (!$this->client_id) return null;
        return Client::find($this->client_id);
    }

    /**
     * Task del progetto
     */
    public function tasks(): array
    {
        return Task::where('project_id', $this->getId());
    }

    /**
     * Progresso completamento (%)
     */
    public function getProgress(): int
    {
        $result = static::db()->selectOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
             FROM tasks WHERE project_id = ?",
            [$this->getId()]
        );

        if (!$result || $result['total'] == 0) {
            return 0;
        }

        return (int)(($result['completed'] / $result['total']) * 100);
    }

    /**
     * Ore tracciate
     */
    public function getTrackedHours(): float
    {
        $result = static::db()->selectOne(
            "SELECT SUM(duration_minutes) as total FROM time_entries WHERE project_id = ?",
            [$this->getId()]
        );
        return round(($result['total'] ?? 0) / 60, 2);
    }

    /**
     * Budget rimanente
     */
    public function getRemainingBudget(): ?float
    {
        if (!$this->budget) return null;
        
        $spent = static::db()->selectOne(
            "SELECT SUM(duration_minutes * COALESCE(hourly_rate, 0) / 60) as total 
             FROM time_entries 
             WHERE project_id = ? AND is_billable = 1",
            [$this->getId()]
        );

        return round($this->budget - ($spent['total'] ?? 0), 2);
    }
}
