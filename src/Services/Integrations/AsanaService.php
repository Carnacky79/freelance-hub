<?php

namespace FreelanceHub\Services\Integrations;

use FreelanceHub\Models\IntegrationAccount;

/**
 * AsanaService - Integrazione con Asana
 */
class AsanaService extends BaseIntegrationService
{
    protected string $slug = 'asana';

    public function getAuthUrl(string $state): string
    {
        $params = [
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'state' => $state,
        ];

        return 'https://app.asana.com/-/oauth_authorize?' . http_build_query($params);
    }

    public function exchangeCodeForTokens(string $code): array
    {
        return $this->exchangeToken($code, 'https://app.asana.com/-/oauth_token');
    }

    public function refreshAccessToken(): bool
    {
        $tokens = $this->refreshToken('https://app.asana.com/-/oauth_token');

        if (isset($tokens['access_token'])) {
            $this->account->updateTokens(
                $tokens['access_token'],
                $tokens['refresh_token'] ?? null,
                $tokens['expires_in'] ?? 3600
            );
            return true;
        }

        return false;
    }

    public function getUserInfo(): array
    {
        $this->ensureValidToken();
        
        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/users/me'
        );

        return $response['data']['data'] ?? [];
    }

    /**
     * Ottieni lista workspace
     */
    public function getWorkspaces(): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/workspaces'
        );

        return $response['data']['data'] ?? [];
    }

    /**
     * Ottieni progetti di un workspace
     */
    public function getProjects(string $workspaceGid): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/workspaces/' . $workspaceGid . '/projects?' . http_build_query([
                'opt_fields' => 'name,color,due_date,notes,completed',
            ])
        );

        return $response['data']['data'] ?? [];
    }

    public function syncTasks(): array
    {
        $this->ensureValidToken();
        $syncedTasks = [];

        // Ottieni workspace (potrebbe essere salvato in extra_data)
        $workspaceGid = $this->account->getExtraValue('workspace_gid');
        
        if (!$workspaceGid) {
            $workspaces = $this->getWorkspaces();
            if (empty($workspaces)) {
                return [];
            }
            $workspaceGid = $workspaces[0]['gid'];
        }

        // Ottieni task assegnati all'utente
        $userInfo = $this->getUserInfo();
        $userGid = $userInfo['gid'] ?? null;

        if (!$userGid) {
            return [];
        }

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/tasks?' . http_build_query([
                'workspace' => $workspaceGid,
                'assignee' => $userGid,
                'completed_since' => 'now', // Solo task non completati
                'opt_fields' => 'name,notes,due_on,due_at,completed,completed_at,assignee,projects,tags,created_at,modified_at,permalink_url',
            ])
        );

        $tasks = $response['data']['data'] ?? [];

        foreach ($tasks as $task) {
            $syncedTasks[] = $this->mapTaskToLocal($task);
        }

        return $syncedTasks;
    }

    private function mapTaskToLocal(array $task): array
    {
        $projectName = null;
        if (!empty($task['projects'])) {
            $projectName = $task['projects'][0]['name'] ?? null;
        }

        return [
            'external_id' => $task['gid'],
            'external_url' => $task['permalink_url'] ?? null,
            'title' => $task['name'],
            'description' => $task['notes'] ?? null,
            'status' => $task['completed'] ? 'completed' : 'todo',
            'due_date' => $task['due_on'] ?? ($task['due_at'] ? date('Y-m-d', strtotime($task['due_at'])) : null),
            'project_name' => $projectName,
            'workspace_name' => $this->account->getExtraValue('workspace_name'),
            'raw_data' => $task,
        ];
    }

    public function syncCalendarEvents(): array
    {
        // Asana non ha calendario nativo, ma i task con date possono essere mostrati come eventi
        $tasks = $this->syncTasks();
        $events = [];

        foreach ($tasks as $task) {
            if ($task['due_date']) {
                $events[] = [
                    'external_id' => 'asana_' . $task['external_id'],
                    'title' => '[Asana] ' . $task['title'],
                    'description' => $task['description'],
                    'start_datetime' => $task['due_date'] . ' 09:00:00',
                    'end_datetime' => $task['due_date'] . ' 10:00:00',
                    'is_all_day' => true,
                    'source' => 'asana',
                ];
            }
        }

        return $events;
    }

    public function createExternalTask(array $taskData): ?string
    {
        $this->ensureValidToken();

        $workspaceGid = $this->account->getExtraValue('workspace_gid');
        if (!$workspaceGid) {
            $workspaces = $this->getWorkspaces();
            $workspaceGid = $workspaces[0]['gid'] ?? null;
        }

        if (!$workspaceGid) {
            return null;
        }

        $asanaTask = [
            'name' => $taskData['title'],
            'notes' => $taskData['description'] ?? '',
            'workspace' => $workspaceGid,
        ];

        if (!empty($taskData['due_date'])) {
            $asanaTask['due_on'] = $taskData['due_date'];
        }

        if (!empty($taskData['project_gid'])) {
            $asanaTask['projects'] = [$taskData['project_gid']];
        }

        $response = $this->httpRequest(
            'POST',
            $this->config['api_base'] . '/tasks',
            ['data' => $asanaTask]
        );

        return $response['data']['data']['gid'] ?? null;
    }

    public function updateExternalTask(string $externalId, array $taskData): bool
    {
        $this->ensureValidToken();

        $asanaTask = [];
        
        if (isset($taskData['title'])) {
            $asanaTask['name'] = $taskData['title'];
        }
        if (isset($taskData['description'])) {
            $asanaTask['notes'] = $taskData['description'];
        }
        if (isset($taskData['due_date'])) {
            $asanaTask['due_on'] = $taskData['due_date'];
        }
        if (isset($taskData['completed'])) {
            $asanaTask['completed'] = $taskData['completed'];
        }

        $response = $this->httpRequest(
            'PUT',
            $this->config['api_base'] . '/tasks/' . $externalId,
            ['data' => $asanaTask]
        );

        return $response['status'] === 200;
    }

    /**
     * Completa task in Asana
     */
    public function completeTask(string $taskGid): bool
    {
        return $this->updateExternalTask($taskGid, ['completed' => true]);
    }

    /**
     * Aggiungi commento a un task
     */
    public function addComment(string $taskGid, string $text): ?string
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'POST',
            $this->config['api_base'] . '/tasks/' . $taskGid . '/stories',
            ['data' => ['text' => $text]]
        );

        return $response['data']['data']['gid'] ?? null;
    }

    /**
     * Ottieni dettagli task
     */
    public function getTask(string $taskGid): ?array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/tasks/' . $taskGid . '?' . http_build_query([
                'opt_fields' => 'name,notes,due_on,due_at,completed,completed_at,assignee,projects,tags,subtasks,stories',
            ])
        );

        return $response['data']['data'] ?? null;
    }
}
