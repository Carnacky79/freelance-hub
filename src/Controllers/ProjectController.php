<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Models\Project;

/**
 * ProjectController - Gestione progetti
 */
class ProjectController
{
    /**
     * Lista progetti
     */
    public function index(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $projects = Project::where('user_id', $userId);
        
        return Response::success($projects);
    }

    /**
     * Crea progetto
     */
    public function store(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $errors = $request->validate([
            'name' => 'required|min:2|max:200',
        ]);

        if (!empty($errors)) {
            return Response::validationError($errors);
        }

        $project = new Project();
        $project->user_id = $userId;
        $project->client_id = $request->getBody('client_id');
        $project->name = $request->getBody('name');
        $project->description = $request->getBody('description');
        $project->color = $request->getBody('color', '#10B981');
        $project->status = $request->getBody('status', 'planning');
        $project->start_date = $request->getBody('start_date');
        $project->due_date = $request->getBody('due_date');
        $project->estimated_hours = $request->getBody('estimated_hours');
        $project->budget = $request->getBody('budget');
        $project->is_billable = $request->getBody('is_billable', 1);
        $project->save();

        return Response::success($project->toArray(), 'Progetto creato');
    }

    /**
     * Dettaglio progetto
     */
    public function show(Request $request, int $id): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        $project = Project::find($id);
        
        if (!$project || $project->user_id != $userId) {
            return Response::notFound('Progetto non trovato');
        }

        return Response::success($project->toArray());
    }

    /**
     * Aggiorna progetto
     */
    public function update(Request $request, int $id): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        $project = Project::find($id);
        
        if (!$project || $project->user_id != $userId) {
            return Response::notFound('Progetto non trovato');
        }

        $project->client_id = $request->getBody('client_id', $project->client_id);
        $project->name = $request->getBody('name', $project->name);
        $project->description = $request->getBody('description', $project->description);
        $project->color = $request->getBody('color', $project->color);
        $project->status = $request->getBody('status', $project->status);
        $project->start_date = $request->getBody('start_date', $project->start_date);
        $project->due_date = $request->getBody('due_date', $project->due_date);
        $project->estimated_hours = $request->getBody('estimated_hours', $project->estimated_hours);
        $project->budget = $request->getBody('budget', $project->budget);
        $project->is_billable = $request->getBody('is_billable', $project->is_billable);
        $project->save();

        return Response::success($project->toArray(), 'Progetto aggiornato');
    }

    /**
     * Elimina progetto
     */
    public function destroy(Request $request, int $id): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        $project = Project::find($id);
        
        if (!$project || $project->user_id != $userId) {
            return Response::notFound('Progetto non trovato');
        }

        $project->delete();

        return Response::success(null, 'Progetto eliminato');
    }
}
