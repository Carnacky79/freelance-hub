<?php

namespace FreelanceHub\Services\Integrations;

use FreelanceHub\Models\IntegrationAccount;

/**
 * ClickUpService - Integrazione con ClickUp
 */
class ClickUpService extends BaseIntegrationService
{
    protected string $slug = 'clickup';

    public function getAuthUrl(string $state): string
    {
        $params = [
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'state' => $state,
        ];

        return 'https://app.clickup.com/api?' . http_build_query($params);
    }

    public function exchangeCodeForTokens(string $code): array
    {
        $params = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'code' => $code,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.clickup.com/api/v2/oauth/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    public function refreshAccessToken(): bool
    {
        // ClickUp tokens non scadono, quindi non c'è refresh
        // Se il token è invalido, l'utente deve riconnettere
        return true;
    }

    public function getUserInfo(): array
    {
        $this->ensureValidToken();
        
        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/user'
        );

        return $response['data']['user'] ?? [];
    }

    /**
     * Ottieni lista team/workspace
     */
    public function getTeams(): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/team'
        );

        return $response['data']['teams'] ?? [];
    }

    /**
     * Ottieni spaces di un team
     */
    public function getSpaces(string $teamId): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/team/' . $teamId . '/space'
        );

        return $response['data']['spaces'] ?? [];
    }

    /**
     * Ottieni folders di uno space
     */
    public function getFolders(string $spaceId): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/space/' . $spaceId . '/folder'
        );

        return $response['data']['folders'] ?? [];
    }

    /**
     * Ottieni liste di un folder
     */
    public function getLists(string $folderId): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/folder/' . $folderId . '/list'
        );

        return $response['data']['lists'] ?? [];
    }

    /**
     * Ottieni liste folderless di uno space
     */
    public function getFolderlessLists(string $spaceId): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/space/' . $spaceId . '/list'
        );

        return $response['data']['lists'] ?? [];
    }

    public function syncTasks(): array
    {
        $this->ensureValidToken();
        $syncedTasks = [];

        // Ottieni team
        $teamId = $this->account->getExtraValue('team_id');
        
        if (!$teamId) {
            $teams = $this->getTeams();
            if (empty($teams)) {
                return [];
            }
            $teamId = $teams[0]['id'];
        }

        // Ottieni task assegnati all'utente
        $userInfo = $this->getUserInfo();
        $userId = $userInfo['id'] ?? null;

        if (!$userId) {
            return [];
        }

        // Usa l'endpoint filtered tasks
        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/team/' . $teamId . '/task?' . http_build_query([
                'assignees[]' => $userId,
                'subtasks' => true,
                'include_closed' => false,
            ])
        );

        $tasks = $response['data']['tasks'] ?? [];

        foreach ($tasks as $task) {
            $syncedTasks[] = $this->mapTaskToLocal($task);
        }

        return $syncedTasks;
    }

    private function mapTaskToLocal(array $task): array
    {
        $status = 'todo';
        if (isset($task['status']['type'])) {
            $status = match ($task['status']['type']) {
                'done', 'closed' => 'completed',
                'in progress', 'review' => 'in_progress',
                default => 'todo',
            };
        }

        $dueDate = null;
        if (!empty($task['due_date'])) {
            $dueDate = date('Y-m-d', $task['due_date'] / 1000); // ClickUp usa millisecondi
        }

        $priority = 'normal';
        if (isset($task['priority']['id'])) {
            $priority = match ((int)$task['priority']['id']) {
                1 => 'urgent',
                2 => 'high',
                3 => 'normal',
                4 => 'low',
                default => 'normal',
            };
        }

        return [
            'external_id' => $task['id'],
            'external_url' => $task['url'] ?? null,
            'title' => $task['name'],
            'description' => $task['description'] ?? null,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate,
            'project_name' => $task['list']['name'] ?? null,
            'workspace_name' => $task['space']['name'] ?? $this->account->getExtraValue('team_name'),
            'raw_data' => $task,
        ];
    }

    public function syncCalendarEvents(): array
    {
        // ClickUp task con date come eventi
        $tasks = $this->syncTasks();
        $events = [];

        foreach ($tasks as $task) {
            if ($task['due_date']) {
                $events[] = [
                    'external_id' => 'clickup_' . $task['external_id'],
                    'title' => '[ClickUp] ' . $task['title'],
                    'description' => $task['description'],
                    'start_datetime' => $task['due_date'] . ' 09:00:00',
                    'end_datetime' => $task['due_date'] . ' 10:00:00',
                    'is_all_day' => true,
                    'source' => 'clickup',
                ];
            }
        }

        return $events;
    }

    public function createExternalTask(array $taskData): ?string
    {
        $this->ensureValidToken();

        $listId = $taskData['list_id'] ?? $this->account->getExtraValue('default_list_id');
        
        if (!$listId) {
            return null;
        }

        $clickupTask = [
            'name' => $taskData['title'],
            'description' => $taskData['description'] ?? '',
        ];

        if (!empty($taskData['due_date'])) {
            $clickupTask['due_date'] = strtotime($taskData['due_date']) * 1000; // Millisecondi
        }

        if (!empty($taskData['priority'])) {
            $clickupTask['priority'] = match ($taskData['priority']) {
                'urgent' => 1,
                'high' => 2,
                'normal' => 3,
                'low' => 4,
                default => 3,
            };
        }

        $response = $this->httpRequest(
            'POST',
            $this->config['api_base'] . '/list/' . $listId . '/task',
            $clickupTask
        );

        return $response['data']['id'] ?? null;
    }

    public function updateExternalTask(string $externalId, array $taskData): bool
    {
        $this->ensureValidToken();

        $clickupTask = [];
        
        if (isset($taskData['title'])) {
            $clickupTask['name'] = $taskData['title'];
        }
        if (isset($taskData['description'])) {
            $clickupTask['description'] = $taskData['description'];
        }
        if (isset($taskData['due_date'])) {
            $clickupTask['due_date'] = strtotime($taskData['due_date']) * 1000;
        }
        if (isset($taskData['status'])) {
            $clickupTask['status'] = $taskData['status'];
        }

        $response = $this->httpRequest(
            'PUT',
            $this->config['api_base'] . '/task/' . $externalId,
            $clickupTask
        );

        return $response['status'] === 200;
    }

    /**
     * Ottieni dettagli task
     */
    public function getTask(string $taskId): ?array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/task/' . $taskId
        );

        return $response['data'] ?? null;
    }

    /**
     * Aggiungi commento a un task
     */
    public function addComment(string $taskId, string $text): ?string
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'POST',
            $this->config['api_base'] . '/task/' . $taskId . '/comment',
            ['comment_text' => $text]
        );

        return $response['data']['id'] ?? null;
    }

    /**
     * Traccia tempo su un task
     */
    public function trackTime(string $taskId, int $durationMs, string $description = ''): ?string
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'POST',
            $this->config['api_base'] . '/task/' . $taskId . '/time',
            [
                'duration' => $durationMs,
                'description' => $description,
            ]
        );

        return $response['data']['id'] ?? null;
    }
}
