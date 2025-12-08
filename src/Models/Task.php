<?php

namespace FreelanceHub\Models;

/**
 * Task Model
 */
class Task extends Model
{
    protected static string $table = 'tasks';
    
    protected static array $fillable = [
        'user_id',
        'project_id',
        'client_id',
        'parent_task_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'due_time',
        'start_date',
        'estimated_minutes',
        'actual_minutes',
        'is_recurring',
        'recurrence_rule',
        'tags',
        'ai_priority_score',
        'ai_suggested_deadline',
        'completed_at',
    ];

    const STATUS_BACKLOG = 'backlog';
    const STATUS_TODO = 'todo';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_REVIEW = 'review';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const PRIORITY_LOWEST = 'lowest';
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Task per utente
     */
    public static function forUser(int $userId, array $filters = []): array
    {
        $sql = "SELECT t.*, c.name as client_name, p.name as project_name 
                FROM tasks t 
                LEFT JOIN clients c ON t.client_id = c.id 
                LEFT JOIN projects p ON t.project_id = p.id 
                WHERE t.user_id = ?";
        $params = [$userId];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['client_id'])) {
            $sql .= " AND t.client_id = ?";
            $params[] = $filters['client_id'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND t.project_id = ?";
            $params[] = $filters['project_id'];
        }

        if (!empty($filters['due_date_from'])) {
            $sql .= " AND t.due_date >= ?";
            $params[] = $filters['due_date_from'];
        }

        if (!empty($filters['due_date_to'])) {
            $sql .= " AND t.due_date <= ?";
            $params[] = $filters['due_date_to'];
        }

        $orderBy = $filters['order_by'] ?? 'due_date';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        $sql .= " ORDER BY {$orderBy} {$orderDir}";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return static::query($sql, $params);
    }

    /**
     * Task urgenti (prossimi 3 giorni)
     */
    public static function urgent(int $userId, int $days = 3): array
    {
        $sql = "SELECT t.*, c.name as client_name 
                FROM tasks t 
                LEFT JOIN clients c ON t.client_id = c.id 
                WHERE t.user_id = ? 
                AND t.status NOT IN ('completed', 'cancelled')
                AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY t.due_date ASC, t.priority DESC";
        
        return static::query($sql, [$userId, $days]);
    }

    /**
     * Task in ritardo
     */
    public static function overdue(int $userId): array
    {
        $sql = "SELECT t.*, c.name as client_name 
                FROM tasks t 
                LEFT JOIN clients c ON t.client_id = c.id 
                WHERE t.user_id = ? 
                AND t.status NOT IN ('completed', 'cancelled')
                AND t.due_date < CURDATE()
                ORDER BY t.due_date ASC";
        
        return static::query($sql, [$userId]);
    }

    /**
     * Task ordinati per priorità IA
     */
    public static function byAIPriority(int $userId, int $limit = 10): array
    {
        $sql = "SELECT t.*, c.name as client_name 
                FROM tasks t 
                LEFT JOIN clients c ON t.client_id = c.id 
                WHERE t.user_id = ? 
                AND t.status NOT IN ('completed', 'cancelled')
                AND t.ai_priority_score IS NOT NULL
                ORDER BY t.ai_priority_score DESC
                LIMIT ?";
        
        return static::query($sql, [$userId, $limit]);
    }

    /**
     * Subtask di questo task
     */
    public function subtasks(): array
    {
        return static::where('parent_task_id', $this->getId());
    }

    /**
     * Client associato
     */
    public function client(): ?Client
    {
        if (!$this->client_id) return null;
        return Client::find($this->client_id);
    }

    /**
     * Progetto associato
     */
    public function project(): ?Project
    {
        if (!$this->project_id) return null;
        return Project::find($this->project_id);
    }

    /**
     * Time entries per questo task
     */
    public function timeEntries(): array
    {
        return TimeEntry::where('task_id', $this->getId());
    }

    /**
     * Tempo totale tracciato (minuti)
     */
    public function totalTrackedMinutes(): int
    {
        $result = static::db()->selectOne(
            "SELECT SUM(duration_minutes) as total FROM time_entries WHERE task_id = ?",
            [$this->getId()]
        );
        return (int)($result['total'] ?? 0);
    }

    /**
     * Completa il task
     */
    public function complete(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Verifica se è scaduto
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) return false;
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) return false;
        return strtotime($this->due_date) < strtotime('today');
    }

    /**
     * Verifica se è urgente
     */
    public function isUrgent(int $daysThreshold = 3): bool
    {
        if (!$this->due_date) return false;
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) return false;
        return strtotime($this->due_date) <= strtotime("+{$daysThreshold} days");
    }

    /**
     * Tags come array
     */
    public function getTagsArray(): array
    {
        if (!$this->tags) return [];
        return json_decode($this->tags, true) ?? [];
    }

    /**
     * Imposta tags da array
     */
    public function setTagsFromArray(array $tags): void
    {
        $this->tags = json_encode(array_values(array_unique($tags)));
    }
}
