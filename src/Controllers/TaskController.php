<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Models\Task;

/**
 * TaskController - Gestione Task API
 */
class TaskController
{
    public function index(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $filters = [
            'status' => $request->getQuery('status'),
            'client_id' => $request->getQuery('client_id'),
            'project_id' => $request->getQuery('project_id'),
            'due_date_from' => $request->getQuery('due_from'),
            'due_date_to' => $request->getQuery('due_to'),
            'order_by' => $request->getQuery('order_by', 'due_date'),
            'order_dir' => $request->getQuery('order_dir', 'ASC'),
            'limit' => $request->getQuery('limit'),
        ];

        $tasks = Task::forUser($userId, array_filter($filters));

        return Response::success(array_map(fn($t) => $t->toArray(), $tasks));
    }

    public function show(Request $request): Response
    {
        $userId = $request->getUserId();
        $taskId = (int)$request->getParam('id');

        $task = Task::find($taskId);
        
        if (!$task || $task->user_id != $userId) {
            return Response::notFound('Task non trovato');
        }

        $data = $task->toArray();
        $data['subtasks'] = array_map(fn($t) => $t->toArray(), $task->subtasks());
        $data['total_tracked_minutes'] = $task->totalTrackedMinutes();
        $data['is_overdue'] = $task->isOverdue();
        $data['is_urgent'] = $task->isUrgent();

        return Response::success($data);
    }

    public function store(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $errors = $request->validate([
            'title' => 'required|min:1|max:300',
        ]);

        if (!empty($errors)) {
            return Response::validationError($errors);
        }

        $data = $request->getBody();
        $data['user_id'] = $userId;

        $task = Task::create($data);

        return Response::success($task->toArray(), 'Task creato');
    }

    public function update(Request $request): Response
    {
        $userId = $request->getUserId();
        $taskId = (int)$request->getParam('id');

        $task = Task::find($taskId);
        
        if (!$task || $task->user_id != $userId) {
            return Response::notFound('Task non trovato');
        }

        $data = $request->getBody();
        unset($data['user_id'], $data['id']); // Proteggi campi sensibili

        $task->update($data);

        return Response::success($task->toArray(), 'Task aggiornato');
    }

    public function destroy(Request $request): Response
    {
        $userId = $request->getUserId();
        $taskId = (int)$request->getParam('id');

        $task = Task::find($taskId);
        
        if (!$task || $task->user_id != $userId) {
            return Response::notFound('Task non trovato');
        }

        $task->delete();

        return Response::success(null, 'Task eliminato');
    }

    public function complete(Request $request): Response
    {
        $userId = $request->getUserId();
        $taskId = (int)$request->getParam('id');

        $task = Task::find($taskId);
        
        if (!$task || $task->user_id != $userId) {
            return Response::notFound('Task non trovato');
        }

        $task->complete();

        return Response::success($task->toArray(), 'Task completato');
    }

    /**
     * Task urgenti
     */
    public function urgent(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $days = (int)$request->getQuery('days', 3);
        $tasks = Task::urgent($userId, $days);

        return Response::success(array_map(fn($t) => $t->toArray(), $tasks));
    }

    /**
     * Task in ritardo
     */
    public function overdue(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $tasks = Task::overdue($userId);

        return Response::success(array_map(fn($t) => $t->toArray(), $tasks));
    }

    /**
     * Task ordinati per priorità IA
     */
    public function aiPriority(Request $request): Response
    {
        $userId = $request->getUserId();
        if (!$userId) {
            return Response::unauthorized();
        }

        $limit = (int)$request->getQuery('limit', 10);
        $tasks = Task::byAIPriority($userId, $limit);

        return Response::success(array_map(fn($t) => $t->toArray(), $tasks));
    }
}
