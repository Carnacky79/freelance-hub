<?php

namespace FreelanceHub\Models;

/**
 * TimeEntry Model - Gestione time tracking
 */
class TimeEntry extends Model
{
    protected static string $table = 'time_entries';
    
    protected static array $fillable = [
        'user_id',
        'task_id',
        'project_id',
        'client_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'description',
        'is_billable',
        'hourly_rate',
        'is_running',
    ];

    /**
     * Entries per utente con filtri
     */
    public static function forUser(int $userId, array $filters = []): array
    {
        $sql = "SELECT te.*, 
                    t.title as task_title,
                    p.name as project_name,
                    c.name as client_name,
                    c.color as client_color
                FROM time_entries te
                LEFT JOIN tasks t ON te.task_id = t.id
                LEFT JOIN projects p ON te.project_id = p.id
                LEFT JOIN clients c ON te.client_id = c.id
                WHERE te.user_id = ?";
        $params = [$userId];

        if (!empty($filters['client_id'])) {
            $sql .= " AND te.client_id = ?";
            $params[] = $filters['client_id'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND te.project_id = ?";
            $params[] = $filters['project_id'];
        }

        if (!empty($filters['task_id'])) {
            $sql .= " AND te.task_id = ?";
            $params[] = $filters['task_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(te.start_time) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(te.start_time) <= ?";
            $params[] = $filters['date_to'];
        }

        if (isset($filters['is_billable'])) {
            $sql .= " AND te.is_billable = ?";
            $params[] = $filters['is_billable'] ? 1 : 0;
        }

        $sql .= " ORDER BY te.start_time DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return static::query($sql, $params);
    }

    /**
     * Timer attualmente in esecuzione per utente
     */
    public static function getRunning(int $userId): ?static
    {
        return static::whereFirst('user_id', $userId, '=') 
            ? static::query(
                "SELECT * FROM time_entries WHERE user_id = ? AND is_running = 1 LIMIT 1",
                [$userId]
            )[0] ?? null
            : null;
    }

    /**
     * Avvia un nuovo timer
     */
    public static function startTimer(int $userId, array $data = []): static
    {
        // Ferma eventuali timer attivi
        static::stopAllRunning($userId);

        return static::create([
            'user_id' => $userId,
            'task_id' => $data['task_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'start_time' => date('Y-m-d H:i:s'),
            'description' => $data['description'] ?? null,
            'is_billable' => $data['is_billable'] ?? true,
            'hourly_rate' => $data['hourly_rate'] ?? null,
            'is_running' => 1,
        ]);
    }

    /**
     * Ferma tutti i timer attivi
     */
    public static function stopAllRunning(int $userId): int
    {
        $now = date('Y-m-d H:i:s');
        
        return static::db()->query(
            "UPDATE time_entries 
             SET is_running = 0, 
                 end_time = ?,
                 duration_minutes = TIMESTAMPDIFF(MINUTE, start_time, ?)
             WHERE user_id = ? AND is_running = 1",
            [$now, $now, $userId]
        )->rowCount();
    }

    /**
     * Ferma questo timer
     */
    public function stop(): bool
    {
        if (!$this->is_running) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $duration = (strtotime($now) - strtotime($this->start_time)) / 60;

        return $this->update([
            'is_running' => 0,
            'end_time' => $now,
            'duration_minutes' => max(1, (int)$duration),
        ]);
    }

    /**
     * Report giornaliero
     */
    public static function dailyReport(int $userId, string $date): array
    {
        $sql = "SELECT 
                    te.client_id,
                    c.name as client_name,
                    c.color as client_color,
                    SUM(te.duration_minutes) as total_minutes,
                    SUM(CASE WHEN te.is_billable = 1 THEN te.duration_minutes * COALESCE(te.hourly_rate, 0) / 60 ELSE 0 END) as total_billable
                FROM time_entries te
                LEFT JOIN clients c ON te.client_id = c.id
                WHERE te.user_id = ? AND DATE(te.start_time) = ?
                GROUP BY te.client_id, c.name, c.color
                ORDER BY total_minutes DESC";

        return static::db()->select($sql, [$userId, $date]);
    }

    /**
     * Report settimanale
     */
    public static function weeklyReport(int $userId, string $weekStart): array
    {
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
        
        $sql = "SELECT 
                    DATE(te.start_time) as date,
                    te.client_id,
                    c.name as client_name,
                    SUM(te.duration_minutes) as total_minutes
                FROM time_entries te
                LEFT JOIN clients c ON te.client_id = c.id
                WHERE te.user_id = ? 
                AND DATE(te.start_time) BETWEEN ? AND ?
                GROUP BY DATE(te.start_time), te.client_id, c.name
                ORDER BY date ASC, total_minutes DESC";

        return static::db()->select($sql, [$userId, $weekStart, $weekEnd]);
    }

    /**
     * Report mensile per cliente
     */
    public static function monthlyClientReport(int $userId, int $year, int $month): array
    {
        $sql = "SELECT 
                    c.id as client_id,
                    c.name as client_name,
                    c.color as client_color,
                    COUNT(DISTINCT DATE(te.start_time)) as days_worked,
                    SUM(te.duration_minutes) as total_minutes,
                    SUM(CASE WHEN te.is_billable = 1 THEN te.duration_minutes ELSE 0 END) as billable_minutes,
                    SUM(CASE WHEN te.is_billable = 1 THEN te.duration_minutes * COALESCE(te.hourly_rate, 0) / 60 ELSE 0 END) as total_amount
                FROM time_entries te
                LEFT JOIN clients c ON te.client_id = c.id
                WHERE te.user_id = ? 
                AND YEAR(te.start_time) = ? 
                AND MONTH(te.start_time) = ?
                GROUP BY c.id, c.name, c.color
                ORDER BY total_minutes DESC";

        return static::db()->select($sql, [$userId, $year, $month]);
    }

    /**
     * Totale ore oggi
     */
    public static function todayTotal(int $userId): int
    {
        $result = static::db()->selectOne(
            "SELECT SUM(duration_minutes) as total 
             FROM time_entries 
             WHERE user_id = ? AND DATE(start_time) = CURDATE()",
            [$userId]
        );
        return (int)($result['total'] ?? 0);
    }

    /**
     * Totale ore questa settimana
     */
    public static function weekTotal(int $userId): int
    {
        $result = static::db()->selectOne(
            "SELECT SUM(duration_minutes) as total 
             FROM time_entries 
             WHERE user_id = ? AND YEARWEEK(start_time, 1) = YEARWEEK(CURDATE(), 1)",
            [$userId]
        );
        return (int)($result['total'] ?? 0);
    }

    /**
     * Formatta durata in ore:minuti
     */
    public function getFormattedDuration(): string
    {
        $minutes = $this->duration_minutes ?? 0;
        
        if ($this->is_running) {
            $minutes = (time() - strtotime($this->start_time)) / 60;
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%d:%02d', $hours, $mins);
    }

    /**
     * Calcola importo fatturabile
     */
    public function getBillableAmount(): float
    {
        if (!$this->is_billable || !$this->hourly_rate) {
            return 0;
        }
        
        return round(($this->duration_minutes / 60) * $this->hourly_rate, 2);
    }
}
