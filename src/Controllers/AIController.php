<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Core\Database;

/**
 * AIController - Gestione suggerimenti IA
 */
class AIController
{
    /**
     * Lista suggerimenti IA attivi
     */
    public function suggestions(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $db = Database::getInstance();
        
        $suggestions = $db->select(
            "SELECT * FROM ai_recommendations 
             WHERE user_id = ? 
             AND status = 'pending'
             AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY priority_score DESC
             LIMIT 10",
            [$userId]
        );

        return Response::success($suggestions);
    }

    /**
     * Accetta suggerimento
     */
    public function accept(Request $request, int $id): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $db = Database::getInstance();
        
        $suggestion = $db->selectOne(
            "SELECT * FROM ai_recommendations WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );

        if (!$suggestion) {
            return Response::notFound('Suggerimento non trovato');
        }

        $db->query(
            "UPDATE ai_recommendations SET status = 'accepted', accepted_at = NOW() WHERE id = ?",
            [$id]
        );

        // TODO: Applica l'azione suggerita (es. aggiorna priorità task)

        return Response::success(null, 'Suggerimento accettato');
    }

    /**
     * Ignora suggerimento
     */
    public function dismiss(Request $request, int $id): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $db = Database::getInstance();
        
        $db->query(
            "UPDATE ai_recommendations SET status = 'dismissed' WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );

        return Response::success(null, 'Suggerimento ignorato');
    }

    /**
     * Genera nuova analisi IA
     */
    public function analyze(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        // TODO: Implementa chiamata API Claude/OpenAI
        // Per ora ritorna suggerimenti mock

        $suggestions = [
            [
                'type' => 'priority',
                'title' => 'Task urgente rilevato',
                'description' => 'Hai 3 task in scadenza nei prossimi 2 giorni. Considera di riprioritizzare.',
                'priority_score' => 85,
            ],
            [
                'type' => 'workload',
                'title' => 'Carico di lavoro elevato',
                'description' => 'Questa settimana hai più di 40 ore pianificate. Vuoi spostare qualcosa?',
                'priority_score' => 70,
            ],
        ];

        return Response::success($suggestions);
    }
}