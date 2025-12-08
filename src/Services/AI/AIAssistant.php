<?php

namespace FreelanceHub\Services\AI;

use FreelanceHub\Models\Task;
use FreelanceHub\Models\Client;
use FreelanceHub\Models\TimeEntry;
use FreelanceHub\Models\AIRecommendation;
use FreelanceHub\Core\Database;

/**
 * AIAssistant - Servizio centrale per suggerimenti IA
 */
class AIAssistant
{
    private array $config;
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->config = require __DIR__ . '/../../../config/ai.php';
    }

    /**
     * Genera suggerimenti giornalieri completi
     */
    public function generateDailySuggestions(): array
    {
        $suggestions = [];

        // 1. Analisi priorità task
        $prioritySuggestions = $this->analyzePriorities();
        $suggestions = array_merge($suggestions, $prioritySuggestions);

        // 2. Suggerimenti scadenze
        $deadlineSuggestions = $this->suggestDeadlines();
        $suggestions = array_merge($suggestions, $deadlineSuggestions);

        // 3. Allocazione tempo
        $timeSuggestions = $this->suggestTimeAllocation();
        $suggestions = array_merge($suggestions, $timeSuggestions);

        // 4. Avvisi workload
        $workloadAlerts = $this->analyzeWorkload();
        $suggestions = array_merge($suggestions, $workloadAlerts);

        // Salva suggerimenti nel database
        foreach ($suggestions as $suggestion) {
            $this->saveSuggestion($suggestion);
        }

        return $suggestions;
    }

    /**
     * Analizza e calcola priorità per tutti i task aperti
     */
    public function analyzePriorities(): array
    {
        $tasks = Task::forUser($this->userId, [
            'status' => null, // tutti gli stati aperti
        ]);

        $suggestions = [];
        $weights = $this->config['analysis']['priority_weights'];

        foreach ($tasks as $task) {
            if (in_array($task->status, ['completed', 'cancelled'])) {
                continue;
            }

            $score = $this->calculatePriorityScore($task, $weights);
            
            // Aggiorna score nel task
            $task->update(['ai_priority_score' => $score]);

            // Se il punteggio è molto diverso dalla priorità manuale, suggerisci modifica
            $manualPriorityScore = $this->priorityToScore($task->priority);
            $scoreDiff = abs($score - $manualPriorityScore);

            if ($scoreDiff > 30) {
                $suggestedPriority = $this->scoreToPriority($score);
                
                $suggestions[] = [
                    'type' => 'priority',
                    'task_id' => $task->getId(),
                    'title' => "Rivaluta priorità: {$task->title}",
                    'description' => "Basandomi su scadenza, importanza cliente e carico di lavoro, suggerisco di cambiare la priorità da '{$task->priority}' a '{$suggestedPriority}'.",
                    'priority_score' => $score,
                    'suggested_action' => [
                        'action' => 'change_priority',
                        'from' => $task->priority,
                        'to' => $suggestedPriority,
                    ],
                    'reasoning' => $this->explainPriorityScore($task, $score),
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Calcola punteggio priorità per un task
     */
    private function calculatePriorityScore(Task $task, array $weights): int
    {
        $score = 0;

        // 1. Vicinanza scadenza (0-100)
        $deadlineScore = $this->calculateDeadlineScore($task);
        $score += $deadlineScore * $weights['deadline_proximity'];

        // 2. Importanza cliente (0-100)
        $clientScore = $this->calculateClientScore($task);
        $score += $clientScore * $weights['client_importance'];

        // 3. Effort stimato (task piccoli = priorità leggermente più alta per quick wins)
        $effortScore = $this->calculateEffortScore($task);
        $score += $effortScore * $weights['estimated_effort'];

        // 4. Dipendenze (se altri task dipendono da questo)
        $dependencyScore = $this->calculateDependencyScore($task);
        $score += $dependencyScore * $weights['dependencies'];

        // 5. Preferenza manuale utente
        $userPrefScore = $this->priorityToScore($task->priority);
        $score += $userPrefScore * $weights['user_preference'];

        // Bonus/penalità
        if ($task->isOverdue()) {
            $score += $this->config['analysis']['overdue_penalty'];
        }

        return min(100, max(0, (int)$score));
    }

    private function calculateDeadlineScore(Task $task): int
    {
        if (!$task->due_date) {
            return 30; // Punteggio neutro per task senza scadenza
        }

        $daysUntilDue = (strtotime($task->due_date) - time()) / 86400;

        if ($daysUntilDue < 0) {
            return 100; // Scaduto
        } elseif ($daysUntilDue <= 1) {
            return 95;
        } elseif ($daysUntilDue <= 3) {
            return 80;
        } elseif ($daysUntilDue <= 7) {
            return 60;
        } elseif ($daysUntilDue <= 14) {
            return 40;
        } else {
            return 20;
        }
    }

    private function calculateClientScore(Task $task): int
    {
        if (!$task->client_id) {
            return 50;
        }

        $client = $task->client();
        if (!$client) {
            return 50;
        }

        return match ($client->priority_level) {
            'critical' => 100,
            'high' => 80,
            'normal' => 50,
            'low' => 30,
            default => 50,
        };
    }

    private function calculateEffortScore(Task $task): int
    {
        if (!$task->estimated_minutes) {
            return 50;
        }

        // Task più piccoli ottengono score leggermente più alto (quick wins)
        $hours = $task->estimated_minutes / 60;

        if ($hours <= 1) {
            return 70;
        } elseif ($hours <= 4) {
            return 60;
        } elseif ($hours <= 8) {
            return 50;
        } else {
            return 40;
        }
    }

    private function calculateDependencyScore(Task $task): int
    {
        // Conta subtask - se questo task ha subtask, ha dipendenze
        $subtaskCount = count($task->subtasks());
        
        if ($subtaskCount > 5) {
            return 80;
        } elseif ($subtaskCount > 0) {
            return 60;
        }
        
        return 40;
    }

    private function priorityToScore(string $priority): int
    {
        return match ($priority) {
            'urgent' => 100,
            'high' => 75,
            'normal' => 50,
            'low' => 25,
            'lowest' => 10,
            default => 50,
        };
    }

    private function scoreToPriority(int $score): string
    {
        if ($score >= 85) return 'urgent';
        if ($score >= 65) return 'high';
        if ($score >= 40) return 'normal';
        if ($score >= 20) return 'low';
        return 'lowest';
    }

    private function explainPriorityScore(Task $task, int $score): string
    {
        $reasons = [];

        if ($task->isOverdue()) {
            $reasons[] = "Il task è in ritardo";
        } elseif ($task->isUrgent(3)) {
            $reasons[] = "Scadenza nei prossimi 3 giorni";
        }

        if ($task->client_id) {
            $client = $task->client();
            if ($client && $client->priority_level === 'critical') {
                $reasons[] = "Cliente prioritario";
            }
        }

        if ($task->estimated_minutes && $task->estimated_minutes <= 60) {
            $reasons[] = "Task veloce (quick win)";
        }

        return implode('. ', $reasons) ?: "Valutazione basata su criteri standard";
    }

    /**
     * Suggerisce scadenze realistiche per task senza data
     */
    public function suggestDeadlines(): array
    {
        $suggestions = [];
        
        $tasksWithoutDeadline = Task::query(
            "SELECT * FROM tasks WHERE user_id = ? AND due_date IS NULL AND status NOT IN ('completed', 'cancelled')",
            [$this->userId]
        );

        foreach ($tasksWithoutDeadline as $task) {
            $suggestedDate = $this->calculateSuggestedDeadline($task);
            
            if ($suggestedDate) {
                $task->update(['ai_suggested_deadline' => $suggestedDate]);
                
                $suggestions[] = [
                    'type' => 'deadline',
                    'task_id' => $task->getId(),
                    'title' => "Scadenza suggerita: {$task->title}",
                    'description' => "Basandomi sul carico di lavoro e task simili, suggerisco come scadenza: " . date('d/m/Y', strtotime($suggestedDate)),
                    'suggested_action' => [
                        'action' => 'set_deadline',
                        'date' => $suggestedDate,
                    ],
                    'reasoning' => $this->explainDeadlineSuggestion($task, $suggestedDate),
                ];
            }
        }

        return $suggestions;
    }

    private function calculateSuggestedDeadline(Task $task): ?string
    {
        // Stima basata su effort e workload
        $estimatedHours = ($task->estimated_minutes ?? 120) / 60; // Default 2 ore
        
        // Considera workload attuale
        $currentWorkload = $this->getCurrentWorkloadHours();
        $availableHoursPerDay = 6; // Stima ore produttive al giorno
        
        // Calcola giorni necessari
        $daysNeeded = ceil(($currentWorkload + $estimatedHours) / $availableHoursPerDay);
        $daysNeeded = max(1, min($daysNeeded, 30)); // Min 1 giorno, max 30

        // Aggiungi buffer
        $daysNeeded += ceil($daysNeeded * 0.2);

        return date('Y-m-d', strtotime("+{$daysNeeded} days"));
    }

    private function getCurrentWorkloadHours(): float
    {
        $result = Database::getInstance()->selectOne(
            "SELECT SUM(estimated_minutes) / 60 as hours 
             FROM tasks 
             WHERE user_id = ? 
             AND status NOT IN ('completed', 'cancelled')
             AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)",
            [$this->userId]
        );

        return (float)($result['hours'] ?? 0);
    }

    private function explainDeadlineSuggestion(Task $task, string $date): string
    {
        $reasons = [];
        
        if ($task->estimated_minutes) {
            $hours = $task->estimated_minutes / 60;
            $reasons[] = "Effort stimato: {$hours}h";
        }
        
        $workload = $this->getCurrentWorkloadHours();
        $reasons[] = "Carico settimanale attuale: {$workload}h";

        return implode('. ', $reasons);
    }

    /**
     * Suggerisce allocazione tempo ottimale
     */
    public function suggestTimeAllocation(): array
    {
        $suggestions = [];
        
        // Ottieni task urgenti per i prossimi 3 giorni
        $urgentTasks = Task::urgent($this->userId, 3);
        
        if (count($urgentTasks) > 5) {
            $totalHours = 0;
            foreach ($urgentTasks as $task) {
                $totalHours += ($task->estimated_minutes ?? 60) / 60;
            }

            $suggestions[] = [
                'type' => 'time_allocation',
                'task_id' => null,
                'title' => 'Sovraccarico in arrivo',
                'description' => "Hai {$totalHours}h di lavoro stimato per i prossimi 3 giorni su " . count($urgentTasks) . " task urgenti. Considera di riprioritizzare o delegare.",
                'priority_score' => 90,
                'suggested_action' => [
                    'action' => 'review_workload',
                    'urgent_tasks_count' => count($urgentTasks),
                    'total_hours' => $totalHours,
                ],
                'reasoning' => 'Workload elevato rilevato per i prossimi giorni',
            ];
        }

        // Suggerisci migliori slot temporali
        $bestSlots = $this->analyzeBestWorkingSlots();
        if (!empty($bestSlots)) {
            $suggestions[] = [
                'type' => 'time_allocation',
                'task_id' => null,
                'title' => 'Slot produttivi suggeriti',
                'description' => "Basandomi sui tuoi pattern di lavoro, i momenti più produttivi sono: " . implode(', ', $bestSlots),
                'suggested_action' => [
                    'action' => 'optimize_schedule',
                    'best_slots' => $bestSlots,
                ],
                'reasoning' => 'Analisi basata sulle tue time entry passate',
            ];
        }

        return $suggestions;
    }

    private function analyzeBestWorkingSlots(): array
    {
        // Analizza time entries per trovare pattern
        $entries = Database::getInstance()->select(
            "SELECT HOUR(start_time) as hour, COUNT(*) as count, SUM(duration_minutes) as total_minutes
             FROM time_entries
             WHERE user_id = ? AND start_time > DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY HOUR(start_time)
             ORDER BY total_minutes DESC
             LIMIT 3",
            [$this->userId]
        );

        $slots = [];
        foreach ($entries as $entry) {
            $hour = (int)$entry['hour'];
            $slots[] = sprintf('%02d:00 - %02d:00', $hour, $hour + 1);
        }

        return $slots;
    }

    /**
     * Analizza workload e genera avvisi
     */
    public function analyzeWorkload(): array
    {
        $suggestions = [];

        // Task in ritardo
        $overdueTasks = Task::overdue($this->userId);
        if (count($overdueTasks) > 0) {
            $suggestions[] = [
                'type' => 'workload',
                'task_id' => null,
                'title' => count($overdueTasks) . ' task in ritardo',
                'description' => 'Ci sono task scaduti che richiedono attenzione. Considera di aggiornarli o rimuoverli.',
                'priority_score' => 95,
                'suggested_action' => [
                    'action' => 'review_overdue',
                    'task_ids' => array_map(fn($t) => $t->getId(), $overdueTasks),
                ],
                'reasoning' => 'Task con scadenza superata',
            ];
        }

        return $suggestions;
    }

    /**
     * Salva un suggerimento nel database
     */
    private function saveSuggestion(array $suggestion): int
    {
        return Database::getInstance()->insert('ai_recommendations', [
            'user_id' => $this->userId,
            'task_id' => $suggestion['task_id'],
            'type' => $suggestion['type'],
            'title' => $suggestion['title'],
            'description' => $suggestion['description'],
            'priority_score' => $suggestion['priority_score'] ?? null,
            'suggested_action' => json_encode($suggestion['suggested_action'] ?? null),
            'reasoning' => $suggestion['reasoning'] ?? null,
            'status' => 'pending',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);
    }

    /**
     * Ottieni suggerimenti attivi per l'utente
     */
    public function getActiveSuggestions(): array
    {
        return Database::getInstance()->select(
            "SELECT ar.*, t.title as task_title
             FROM ai_recommendations ar
             LEFT JOIN tasks t ON ar.task_id = t.id
             WHERE ar.user_id = ? 
             AND ar.status = 'pending'
             AND (ar.expires_at IS NULL OR ar.expires_at > NOW())
             ORDER BY ar.priority_score DESC, ar.created_at DESC",
            [$this->userId]
        );
    }

    /**
     * Accetta un suggerimento
     */
    public function acceptSuggestion(int $suggestionId): bool
    {
        return Database::getInstance()->update(
            'ai_recommendations',
            ['status' => 'accepted', 'accepted_at' => date('Y-m-d H:i:s')],
            'id = ? AND user_id = ?',
            [$suggestionId, $this->userId]
        ) > 0;
    }

    /**
     * Rifiuta un suggerimento
     */
    public function dismissSuggestion(int $suggestionId): bool
    {
        return Database::getInstance()->update(
            'ai_recommendations',
            ['status' => 'dismissed'],
            'id = ? AND user_id = ?',
            [$suggestionId, $this->userId]
        ) > 0;
    }
}
