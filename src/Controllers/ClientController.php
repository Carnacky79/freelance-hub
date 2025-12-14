<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Models\Client;

/**
 * ClientController - Gestione clienti
 */
class ClientController
{
    /**
     * Lista clienti con statistiche
     */
//    public function index(Request $request): Response
//    {
//        $userId = $_SESSION['user_id'] ?? null;
//
//        if (!$userId) {
//            return Response::unauthorized();
//        }
//
//        $clients = Client::where('user_id', $userId);
//
//        // Aggiungi statistiche per ogni cliente
//        $db = \FreelanceHub\Core\Database::getInstance();
//
//        $clientsWithStats = [];
//        foreach ($clients as $client) {
//            $clientData = $client->toArray();
//
//            // Conta task attivi (non completati/cancellati)
//            $activeTasks = $db->query(
//                "SELECT COUNT(*) as count FROM tasks
//             WHERE client_id = ? AND status NOT IN ('completed', 'cancelled')",
//                [$client->id]
//            );
//            $clientData['active_tasks'] = (int)($activeTasks[0]['count'] ?? 0);
//
//            // Somma ore lavorate totali
//            $totalHours = $db->query(
//                "SELECT SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 3600) as hours
//             FROM time_entries te
//             JOIN tasks t ON te.task_id = t.id
//             WHERE t.client_id = ? AND te.end_time IS NOT NULL",
//                [$client->id]
//            );
//            $clientData['total_hours'] = round((float)($totalHours[0]['hours'] ?? 0), 1);
//
//            $clientsWithStats[] = $clientData;
//        }
//
//        return Response::success($clientsWithStats);
//    }

    /**
     * Lista clienti con statistiche
     */
    public function index(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return Response::unauthorized();
        }

        // Query diretta con PDO
        $db = \FreelanceHub\Core\Database::getInstance();

        // Ottieni connessione PDO
        $pdo = $db->getConnection();

        // Query clienti
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE user_id = ? ORDER BY name ASC");
        $stmt->execute([$userId]);
        $clients = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Aggiungi statistiche per ogni cliente
        $clientsWithStats = [];
        foreach ($clients as $client) {
            // Conta task attivi
            $stmtTasks = $pdo->prepare(
                "SELECT COUNT(*) as count FROM tasks 
             WHERE client_id = ? AND status NOT IN ('completed', 'cancelled')"
            );
            $stmtTasks->execute([$client['id']]);
            $activeTasks = $stmtTasks->fetch(\PDO::FETCH_ASSOC);
            $client['active_tasks'] = (int)($activeTasks['count'] ?? 0);

            // Somma ore lavorate
            $stmtHours = $pdo->prepare(
                "SELECT SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 3600) as hours 
             FROM time_entries te
             JOIN tasks t ON te.task_id = t.id
             WHERE t.client_id = ? AND te.end_time IS NOT NULL"
            );
            $stmtHours->execute([$client['id']]);
            $totalHours = $stmtHours->fetch(\PDO::FETCH_ASSOC);
            $client['total_hours'] = round((float)($totalHours['hours'] ?? 0), 1);

            $clientsWithStats[] = $client;
        }

        return Response::success($clientsWithStats);
    }

    /**
     * Crea cliente
     */
    public function store(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $errors = $request->validate([
            'name' => 'required|min:2|max:150',
        ]);

        if (!empty($errors)) {
            return Response::validationError($errors);
        }

        $client = new Client();
        $client->user_id = $userId;
        $client->name = $request->getBody('name');
        $client->email = $request->getBody('email');
        $client->phone = $request->getBody('phone');
        $client->company = $request->getBody('company');
        $client->address = $request->getBody('address');
        $client->notes = $request->getBody('notes');
        $client->color = $request->getBody('color', '#3B82F6');
        $client->priority_level = $request->getBody('priority_level', 'normal');
        $client->hourly_rate = $request->getBody('hourly_rate');
        $client->save();

        return Response::success($client->toArray(), 'Cliente creato');
    }

    /**
     * Dettaglio cliente
     */
    public function show(Request $request, int $id): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        $client = Client::find($id);
        
        if (!$client || $client->user_id != $userId) {
            return Response::notFound('Cliente non trovato');
        }

        return Response::success($client->toArray());
    }

    /**
     * Aggiorna cliente
     */
    public function update(Request $request, int $id): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        $client = Client::find($id);
        
        if (!$client || $client->user_id != $userId) {
            return Response::notFound('Cliente non trovato');
        }

        $client->name = $request->getBody('name', $client->name);
        $client->email = $request->getBody('email', $client->email);
        $client->phone = $request->getBody('phone', $client->phone);
        $client->company = $request->getBody('company', $client->company);
        $client->address = $request->getBody('address', $client->address);
        $client->notes = $request->getBody('notes', $client->notes);
        $client->color = $request->getBody('color', $client->color);
        $client->priority_level = $request->getBody('priority_level', $client->priority_level);
        $client->hourly_rate = $request->getBody('hourly_rate', $client->hourly_rate);
        $client->is_active = $request->getBody('is_active', $client->is_active);
        $client->save();

        return Response::success($client->toArray(), 'Cliente aggiornato');
    }

    /**
     * Elimina cliente
     */
    public function destroy(Request $request, int $id): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        $client = Client::find($id);
        
        if (!$client || $client->user_id != $userId) {
            return Response::notFound('Cliente non trovato');
        }

        $client->delete();

        return Response::success(null, 'Cliente eliminato');
    }
}
