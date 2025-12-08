<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Core\Database;
use FreelanceHub\Models\Task;
use FreelanceHub\Models\Client;
use FreelanceHub\Models\TimeEntry;
use FreelanceHub\Services\AI\AIAssistant;

/**
 * DashboardController - Statistiche e overview
 */
class DashboardController
{
    /**
     * Statistiche dashboard
     */
    public function stats(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $db = Database::getInstance();

        // Task stats
        $taskStats = $db->selectOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status NOT IN ('completed', 'cancelled') AND due_date < CURDATE() THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN status NOT IN ('completed', 'cancelled') AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 1 ELSE 0 END) as urgent
             FROM tasks WHERE user_id = ?",
            [$userId]
        );

        // Client stats
        $clientStats = $db->selectOne(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
             FROM clients WHERE user_id = ?",
            [$userId]
        );

        // Time tracking oggi
        $todayMinutes = TimeEntry::todayTotal($userId);
        $weekMinutes = TimeEntry::weekTotal($userId);

        // Timer attivo
        $runningTimer = $db->selectOne(
            "SELECT te.*, t.title as task_title 
             FROM time_entries te
             LEFT JOIN tasks t ON te.task_id = t.id
             WHERE te.user_id = ? AND te.is_running = 1",
            [$userId]
        );

        // Task urgenti (prossimi 3 giorni)
        $urgentTasks = Task::urgent($userId, 3);

        // Task in ritardo
        $overdueTasks = Task::overdue($userId);

        // Ore per cliente questa settimana
        $hoursByClient = $db->select(
            "SELECT c.name, c.color, SUM(te.duration_minutes) as minutes
             FROM time_entries te
             LEFT JOIN clients c ON te.client_id = c.id
             WHERE te.user_id = ? AND YEARWEEK(te.start_time, 1) = YEARWEEK(CURDATE(), 1)
             GROUP BY c.id, c.name, c.color
             ORDER BY minutes DESC
             LIMIT 5",
            [$userId]
        );

        // Suggerimenti IA
        $aiSuggestions = [];
        $user = \FreelanceHub\Models\User::find($userId);
        if ($user && $user->ai_enabled) {
            $aiAssistant = new AIAssistant($userId);
            $aiSuggestions = $aiAssistant->getActiveSuggestions();
        }

        // Attività recente
        $recentActivity = $db->select(
            "SELECT 
                'task' as type,
                t.title as title,
                t.status,
                t.updated_at
             FROM tasks t
             WHERE t.user_id = ?
             ORDER BY t.updated_at DESC
             LIMIT 5",
            [$userId]
        );

        return Response::success([
            'tasks' => [
                'total' => (int)$taskStats['total'],
                'open' => (int)$taskStats['open'],
                'completed' => (int)$taskStats['completed'],
                'overdue' => (int)$taskStats['overdue'],
                'urgent' => (int)$taskStats['urgent'],
            ],
            'clients' => [
                'total' => (int)$clientStats['total'],
                'active' => (int)$clientStats['active'],
            ],
            'time_tracking' => [
                'today_minutes' => $todayMinutes,
                'today_hours' => round($todayMinutes / 60, 2),
                'week_minutes' => $weekMinutes,
                'week_hours' => round($weekMinutes / 60, 2),
                'running_timer' => $runningTimer,
            ],
            'urgent_tasks' => array_slice(array_map(fn($t) => $t->toArray(), $urgentTasks), 0, 5),
            'overdue_tasks' => array_slice(array_map(fn($t) => $t->toArray(), $overdueTasks), 0, 5),
            'hours_by_client' => $hoursByClient,
            'ai_suggestions' => array_slice($aiSuggestions, 0, 3),
            'recent_activity' => $recentActivity,
        ]);
    }

    /**
     * Calendario - eventi e task
     */
    public function calendar(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $start = $request->getQuery('start', date('Y-m-01'));
        $end = $request->getQuery('end', date('Y-m-t'));

        $db = Database::getInstance();

        // Task con scadenza
        $tasks = $db->select(
            "SELECT t.id, t.title, t.due_date, t.due_time, t.status, t.priority,
                    c.name as client_name, c.color
             FROM tasks t
             LEFT JOIN clients c ON t.client_id = c.id
             WHERE t.user_id = ? AND t.due_date BETWEEN ? AND ?
             ORDER BY t.due_date, t.due_time",
            [$userId, $start, $end]
        );

        // Eventi calendario sincronizzati
        $events = $db->select(
            "SELECT * FROM calendar_events 
             WHERE user_id = ? AND start_datetime BETWEEN ? AND ?
             ORDER BY start_datetime",
            [$userId, $start . ' 00:00:00', $end . ' 23:59:59']
        );

        // Combina in formato FullCalendar
        $calendarEvents = [];

        foreach ($tasks as $task) {
            $calendarEvents[] = [
                'id' => 'task_' . $task['id'],
                'title' => $task['title'],
                'start' => $task['due_date'] . ($task['due_time'] ? 'T' . $task['due_time'] : ''),
                'allDay' => !$task['due_time'],
                'color' => $task['color'] ?? $this->priorityColor($task['priority']),
                'extendedProps' => [
                    'type' => 'task',
                    'task_id' => $task['id'],
                    'status' => $task['status'],
                    'client' => $task['client_name'],
                ],
            ];
        }

        foreach ($events as $event) {
            $calendarEvents[] = [
                'id' => 'event_' . $event['id'],
                'title' => $event['title'],
                'start' => $event['start_datetime'],
                'end' => $event['end_datetime'],
                'allDay' => (bool)$event['is_all_day'],
                'color' => $event['color'] ?? '#6B7280',
                'extendedProps' => [
                    'type' => 'event',
                    'event_id' => $event['id'],
                    'source' => $event['source'],
                    'location' => $event['location'],
                ],
            ];
        }

        return Response::success($calendarEvents);
    }

    private function priorityColor(string $priority): string
    {
        return match ($priority) {
            'urgent' => '#EF4444',
            'high' => '#F97316',
            'normal' => '#3B82F6',
            'low' => '#6B7280',
            'lowest' => '#9CA3AF',
            default => '#3B82F6',
        };
    }
}
