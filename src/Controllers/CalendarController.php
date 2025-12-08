<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Core\Database;

/**
 * CalendarController - Gestione eventi calendario
 */
class CalendarController
{
    /**
     * Lista eventi per FullCalendar
     */
    public function events(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $start = $request->getQuery('start', date('Y-m-01'));
        $end = $request->getQuery('end', date('Y-m-t'));

        $db = Database::getInstance();
        
        // Eventi dal calendario
        $events = $db->select(
            "SELECT * FROM calendar_events 
             WHERE user_id = ? 
             AND start_datetime >= ? 
             AND start_datetime <= ?",
            [$userId, $start, $end]
        );

        // Task con scadenza come eventi
        $tasks = $db->select(
            "SELECT id, title, due_date, priority, status, client_id 
             FROM tasks 
             WHERE user_id = ? 
             AND due_date IS NOT NULL 
             AND due_date >= ? 
             AND due_date <= ?
             AND status != 'completed'",
            [$userId, $start, $end]
        );

        // Formatta per FullCalendar
        $calendarEvents = [];

        foreach ($events as $event) {
            $calendarEvents[] = [
                'id' => 'event_' . $event['id'],
                'title' => $event['title'],
                'start' => $event['start_datetime'],
                'end' => $event['end_datetime'],
                'allDay' => (bool)$event['is_all_day'],
                'color' => $event['color'] ?? '#3B82F6',
                'extendedProps' => [
                    'type' => 'event',
                    'description' => $event['description'],
                    'location' => $event['location'],
                ]
            ];
        }

        foreach ($tasks as $task) {
            $color = match($task['priority']) {
                'urgent' => '#EF4444',
                'high' => '#F97316',
                'normal' => '#3B82F6',
                'low' => '#10B981',
                default => '#6B7280'
            };

            $calendarEvents[] = [
                'id' => 'task_' . $task['id'],
                'title' => '📋 ' . $task['title'],
                'start' => $task['due_date'],
                'allDay' => true,
                'color' => $color,
                'extendedProps' => [
                    'type' => 'task',
                    'task_id' => $task['id'],
                    'priority' => $task['priority'],
                    'status' => $task['status'],
                ]
            ];
        }

        return Response::json($calendarEvents);
    }

    /**
     * Crea evento
     */
    public function createEvent(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $data = [
            'user_id' => $userId,
            'title' => $request->getBody('title'),
            'description' => $request->getBody('description'),
            'start_datetime' => $request->getBody('start'),
            'end_datetime' => $request->getBody('end'),
            'is_all_day' => $request->getBody('allDay', false) ? 1 : 0,
            'color' => $request->getBody('color', '#3B82F6'),
            'location' => $request->getBody('location'),
        ];

        $db = Database::getInstance();
        $id = $db->insert('calendar_events', $data);

        return Response::success(['id' => $id], 'Evento creato');
    }
}