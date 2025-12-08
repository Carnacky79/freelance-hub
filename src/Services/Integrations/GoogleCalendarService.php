<?php

namespace FreelanceHub\Services\Integrations;

use FreelanceHub\Models\IntegrationAccount;
use FreelanceHub\Models\CalendarEvent;

/**
 * GoogleCalendarService - Integrazione con Google Calendar
 */
class GoogleCalendarService extends BaseIntegrationService
{
    protected string $slug = 'google_calendar';

    public function getAuthUrl(string $state): string
    {
        $params = [
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(' ', $this->config['scopes']),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    public function exchangeCodeForTokens(string $code): array
    {
        return $this->exchangeToken($code, 'https://oauth2.googleapis.com/token');
    }

    public function refreshAccessToken(): bool
    {
        $tokens = $this->refreshToken('https://oauth2.googleapis.com/token');

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
            'https://www.googleapis.com/oauth2/v2/userinfo'
        );

        return $response['data'] ?? [];
    }

    public function syncTasks(): array
    {
        // Google Calendar non ha task nativi, ritorna vuoto
        // I task di Google Tasks potrebbero essere integrati separatamente
        return [];
    }

    public function syncCalendarEvents(): array
    {
        $this->ensureValidToken();
        $syncedEvents = [];

        // Ottieni lista calendari
        $calendarsResponse = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/users/me/calendarList'
        );

        $calendars = $calendarsResponse['data']['items'] ?? [];

        foreach ($calendars as $calendar) {
            // Sincronizza eventi degli ultimi 30 giorni e prossimi 90
            $timeMin = date('c', strtotime('-30 days'));
            $timeMax = date('c', strtotime('+90 days'));

            $eventsResponse = $this->httpRequest(
                'GET',
                $this->config['api_base'] . '/calendars/' . urlencode($calendar['id']) . '/events?' . http_build_query([
                    'timeMin' => $timeMin,
                    'timeMax' => $timeMax,
                    'singleEvents' => 'true',
                    'orderBy' => 'startTime',
                    'maxResults' => 250,
                ])
            );

            $events = $eventsResponse['data']['items'] ?? [];

            foreach ($events as $event) {
                $syncedEvents[] = $this->mapEventToLocal($event, $calendar);
            }
        }

        return $syncedEvents;
    }

    private function mapEventToLocal(array $event, array $calendar): array
    {
        $isAllDay = isset($event['start']['date']);
        
        if ($isAllDay) {
            $startDateTime = $event['start']['date'] . ' 00:00:00';
            $endDateTime = $event['end']['date'] . ' 23:59:59';
        } else {
            $startDateTime = date('Y-m-d H:i:s', strtotime($event['start']['dateTime']));
            $endDateTime = date('Y-m-d H:i:s', strtotime($event['end']['dateTime']));
        }

        return [
            'external_id' => $event['id'],
            'title' => $event['summary'] ?? 'Senza titolo',
            'description' => $event['description'] ?? null,
            'location' => $event['location'] ?? null,
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'is_all_day' => $isAllDay,
            'is_recurring' => isset($event['recurringEventId']),
            'recurrence_rule' => isset($event['recurrence']) ? implode(';', $event['recurrence']) : null,
            'color' => $calendar['backgroundColor'] ?? null,
            'source' => 'google',
            'raw_data' => $event,
        ];
    }

    public function createExternalTask(array $taskData): ?string
    {
        // Google Calendar crea eventi, non task
        return $this->createCalendarEvent($taskData);
    }

    public function updateExternalTask(string $externalId, array $taskData): bool
    {
        return $this->updateCalendarEvent($externalId, $taskData);
    }

    /**
     * Crea evento nel calendario
     */
    public function createCalendarEvent(array $eventData, string $calendarId = 'primary'): ?string
    {
        $this->ensureValidToken();

        $event = [
            'summary' => $eventData['title'],
            'description' => $eventData['description'] ?? '',
            'location' => $eventData['location'] ?? '',
        ];

        if ($eventData['is_all_day'] ?? false) {
            $event['start'] = ['date' => $eventData['start_date']];
            $event['end'] = ['date' => $eventData['end_date']];
        } else {
            $event['start'] = [
                'dateTime' => date('c', strtotime($eventData['start_datetime'])),
                'timeZone' => $eventData['timezone'] ?? 'Europe/Rome',
            ];
            $event['end'] = [
                'dateTime' => date('c', strtotime($eventData['end_datetime'])),
                'timeZone' => $eventData['timezone'] ?? 'Europe/Rome',
            ];
        }

        if (!empty($eventData['reminder_minutes'])) {
            $event['reminders'] = [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'popup', 'minutes' => $eventData['reminder_minutes']],
                ],
            ];
        }

        $response = $this->httpRequest(
            'POST',
            $this->config['api_base'] . '/calendars/' . urlencode($calendarId) . '/events',
            $event
        );

        return $response['data']['id'] ?? null;
    }

    /**
     * Aggiorna evento nel calendario
     */
    public function updateCalendarEvent(string $eventId, array $eventData, string $calendarId = 'primary'): bool
    {
        $this->ensureValidToken();

        $event = [];
        
        if (isset($eventData['title'])) {
            $event['summary'] = $eventData['title'];
        }
        if (isset($eventData['description'])) {
            $event['description'] = $eventData['description'];
        }
        if (isset($eventData['start_datetime'])) {
            $event['start'] = [
                'dateTime' => date('c', strtotime($eventData['start_datetime'])),
                'timeZone' => $eventData['timezone'] ?? 'Europe/Rome',
            ];
        }
        if (isset($eventData['end_datetime'])) {
            $event['end'] = [
                'dateTime' => date('c', strtotime($eventData['end_datetime'])),
                'timeZone' => $eventData['timezone'] ?? 'Europe/Rome',
            ];
        }

        $response = $this->httpRequest(
            'PATCH',
            $this->config['api_base'] . '/calendars/' . urlencode($calendarId) . '/events/' . $eventId,
            $event
        );

        return $response['status'] === 200;
    }

    /**
     * Elimina evento dal calendario
     */
    public function deleteCalendarEvent(string $eventId, string $calendarId = 'primary'): bool
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'DELETE',
            $this->config['api_base'] . '/calendars/' . urlencode($calendarId) . '/events/' . $eventId
        );

        return $response['status'] === 204;
    }

    /**
     * Ottieni lista calendari
     */
    public function getCalendarList(): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'GET',
            $this->config['api_base'] . '/users/me/calendarList'
        );

        return $response['data']['items'] ?? [];
    }

    /**
     * Trova slot liberi nel calendario
     */
    public function findFreeSlots(string $startDate, string $endDate, int $durationMinutes = 60): array
    {
        $this->ensureValidToken();

        $response = $this->httpRequest(
            'POST',
            $this->config['api_base'] . '/freeBusy',
            [
                'timeMin' => date('c', strtotime($startDate)),
                'timeMax' => date('c', strtotime($endDate)),
                'items' => [['id' => 'primary']],
            ]
        );

        $busyPeriods = $response['data']['calendars']['primary']['busy'] ?? [];
        
        // Calcola slot liberi (logica semplificata)
        $freeSlots = [];
        $currentTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        foreach ($busyPeriods as $busy) {
            $busyStart = strtotime($busy['start']);
            $busyEnd = strtotime($busy['end']);

            if ($busyStart - $currentTime >= $durationMinutes * 60) {
                $freeSlots[] = [
                    'start' => date('c', $currentTime),
                    'end' => date('c', $busyStart),
                ];
            }

            $currentTime = max($currentTime, $busyEnd);
        }

        if ($endTime - $currentTime >= $durationMinutes * 60) {
            $freeSlots[] = [
                'start' => date('c', $currentTime),
                'end' => date('c', $endTime),
            ];
        }

        return $freeSlots;
    }
}
