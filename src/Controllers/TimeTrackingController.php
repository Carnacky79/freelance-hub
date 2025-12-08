<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Models\TimeEntry;
use FreelanceHub\Models\Task;

/**
 * TimeTrackingController - Gestione Time Tracking API
 */
class TimeTrackingController
{
    /**
     * Lista time entries
     */
    public function index(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $filters = [
            'client_id' => $request->getQuery('client_id'),
            'project_id' => $request->getQuery('project_id'),
            'task_id' => $request->getQuery('task_id'),
            'date_from' => $request->getQuery('date_from'),
            'date_to' => $request->getQuery('date_to'),
            'is_billable' => $request->getQuery('billable'),
            'limit' => $request->getQuery('limit', 50),
        ];

        $entries = TimeEntry::forUser($userId, array_filter($filters, fn($v) => $v !== null));

        return Response::success(array_map(fn($e) => $e->toArray(), $entries));
    }

    /**
     * Crea time entry manuale
     */
    public function store(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $errors = $request->validate([
            'start_time' => 'required|date',
            'duration_minutes' => 'required|numeric',
        ]);

        if (!empty($errors)) {
            return Response::validationError($errors);
        }

        $data = $request->getBody();
        $data['user_id'] = $userId;
        $data['is_running'] = 0;

        // Calcola end_time se non fornito
        if (empty($data['end_time']) && !empty($data['duration_minutes'])) {
            $data['end_time'] = date('Y-m-d H:i:s', 
                strtotime($data['start_time']) + ($data['duration_minutes'] * 60)
            );
        }

        // Eredita client_id dal task se non specificato
        if (empty($data['client_id']) && !empty($data['task_id'])) {
            $task = Task::find($data['task_id']);
            if ($task) {
                $data['client_id'] = $task->client_id;
                $data['project_id'] = $data['project_id'] ?? $task->project_id;
            }
        }

        $entry = TimeEntry::create($data);

        // Aggiorna minuti effettivi sul task
        if ($entry->task_id) {
            $this->updateTaskActualMinutes($entry->task_id);
        }

        return Response::success($entry->toArray(), 'Time entry creato');
    }

    /**
     * Avvia timer
     */
    public function start(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $data = $request->getBody();

        // Eredita client_id dal task se non specificato
        if (empty($data['client_id']) && !empty($data['task_id'])) {
            $task = Task::find($data['task_id']);
            if ($task && $task->user_id == $userId) {
                $data['client_id'] = $task->client_id;
                $data['project_id'] = $data['project_id'] ?? $task->project_id;
            }
        }

        $entry = TimeEntry::startTimer($userId, $data);

        return Response::success($entry->toArray(), 'Timer avviato');
    }

    /**
     * Ferma timer attivo
     */
    public function stop(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        // Trova timer attivo
        $entries = TimeEntry::query(
            "SELECT * FROM time_entries WHERE user_id = ? AND is_running = 1 LIMIT 1",
            [$userId]
        );

        if (empty($entries)) {
            return Response::error('Nessun timer attivo', 404);
        }

        $entry = $entries[0];
        $entry->stop();

        // Aggiorna minuti effettivi sul task
        if ($entry->task_id) {
            $this->updateTaskActualMinutes($entry->task_id);
        }

        return Response::success($entry->toArray(), 'Timer fermato');
    }

    /**
     * Timer attualmente in esecuzione
     */
    public function running(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $entries = TimeEntry::query(
            "SELECT te.*, t.title as task_title, c.name as client_name
             FROM time_entries te
             LEFT JOIN tasks t ON te.task_id = t.id
             LEFT JOIN clients c ON te.client_id = c.id
             WHERE te.user_id = ? AND te.is_running = 1 
             LIMIT 1",
            [$userId]
        );

        if (empty($entries)) {
            return Response::success(null);
        }

        $entry = $entries[0];
        $data = $entry->toArray();
        $data['current_duration'] = (time() - strtotime($entry->start_time)) / 60;
        $data['formatted_duration'] = $entry->getFormattedDuration();

        return Response::success($data);
    }

    /**
     * Report giornaliero
     */
    public function dailyReport(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $date = $request->getQuery('date', date('Y-m-d'));
        $report = TimeEntry::dailyReport($userId, $date);
        $totalMinutes = TimeEntry::todayTotal($userId);

        return Response::success([
            'date' => $date,
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 2),
            'by_client' => $report,
        ]);
    }

    /**
     * Report settimanale
     */
    public function weeklyReport(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        // Inizio settimana (lunedì)
        $weekStart = $request->getQuery('week_start', date('Y-m-d', strtotime('monday this week')));
        $report = TimeEntry::weeklyReport($userId, $weekStart);
        $totalMinutes = TimeEntry::weekTotal($userId);

        return Response::success([
            'week_start' => $weekStart,
            'week_end' => date('Y-m-d', strtotime($weekStart . ' +6 days')),
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 2),
            'daily_breakdown' => $report,
        ]);
    }

    /**
     * Report mensile per cliente
     */
    public function monthlyReport(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $year = (int)$request->getQuery('year', date('Y'));
        $month = (int)$request->getQuery('month', date('m'));
        
        $report = TimeEntry::monthlyClientReport($userId, $year, $month);

        $totalMinutes = 0;
        $totalBillable = 0;
        foreach ($report as $row) {
            $totalMinutes += $row['total_minutes'];
            $totalBillable += $row['total_amount'];
        }

        return Response::success([
            'year' => $year,
            'month' => $month,
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 2),
            'total_billable' => round($totalBillable, 2),
            'by_client' => $report,
        ]);
    }

    /**
     * Aggiorna minuti effettivi sul task
     */
    private function updateTaskActualMinutes(int $taskId): void
    {
        $task = Task::find($taskId);
        if ($task) {
            $totalMinutes = $task->totalTrackedMinutes();
            $task->update(['actual_minutes' => $totalMinutes]);
        }
    }

    /**
     * Modifica time entry esistente
     */
    public function update(Request $request): Response
    {
        $userId = $request->getUserId();
        $entryId = (int)$request->getParam('id');

        $entry = TimeEntry::find($entryId);
        
        if (!$entry || $entry->user_id != $userId) {
            return Response::notFound('Time entry non trovato');
        }

        $data = $request->getBody();
        unset($data['user_id'], $data['id']);

        // Ricalcola durata se cambiano start/end
        if (isset($data['start_time']) || isset($data['end_time'])) {
            $start = $data['start_time'] ?? $entry->start_time;
            $end = $data['end_time'] ?? $entry->end_time;
            if ($start && $end) {
                $data['duration_minutes'] = (strtotime($end) - strtotime($start)) / 60;
            }
        }

        $entry->update($data);

        return Response::success($entry->toArray(), 'Time entry aggiornato');
    }

    /**
     * Elimina time entry
     */
    public function destroy(Request $request): Response
    {
        $userId = $request->getUserId();
        $entryId = (int)$request->getParam('id');

        $entry = TimeEntry::find($entryId);
        
        if (!$entry || $entry->user_id != $userId) {
            return Response::notFound('Time entry non trovato');
        }

        $taskId = $entry->task_id;
        $entry->delete();

        // Aggiorna minuti sul task
        if ($taskId) {
            $this->updateTaskActualMinutes($taskId);
        }

        return Response::success(null, 'Time entry eliminato');
    }
}
