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
     * Lista clienti
     */
    public function index(Request $request): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::unauthorized();
        }

        $clients = Client::where('user_id', $userId);
        
        return Response::success($clients);
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
