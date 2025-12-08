<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Models\User;
use FreelanceHub\Core\Database;

class SettingsController
{
    /**
     * Ottieni impostazioni utente corrente
     */
    public function index(Request $request)
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return Response::error('Non autenticato', 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return Response::error('Utente non trovato', 404);
        }

        // Converti oggetto in array per risposta
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'timezone' => $user->timezone ?? 'Europe/Rome',
            'language' => $user->language ?? 'it',
            'avatar_url' => $user->avatar_url ?? '',
            'preferences' => $user->preferences ?? '{}',
            'created_at' => $user->created_at,
        ];

        return Response::json([
            'data' => $userData,
        ]);
    }

    /**
     * Aggiorna profilo utente
     */
    public function updateProfile(Request $request)
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return Response::error('Non autenticato', 401);
        }

        // Validazione
        $errors = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if (!empty($errors)) {
            return Response::error('Validazione fallita', 422, $errors);
        }

        $user = User::find($userId);

        if (!$user) {
            return Response::error('Utente non trovato', 404);
        }

        // Verifica email univoca (se cambiata)
        $newEmail = $request->input('email');
        if ($newEmail !== $user->email) {
            $existing = User::where('email', $newEmail)->first();
            if ($existing && $existing->id !== $userId) {
                return Response::error('Email già in uso', 422);
            }
        }

        // Aggiorna dati con query diretta
        $db = Database::getInstance();

        $updateData = [
            'name' => $request->input('name'),
            'email' => $newEmail,
        ];

        // Campi opzionali
        if ($request->has('timezone')) {
            $updateData['timezone'] = $request->input('timezone');
        }

        if ($request->has('language')) {
            $updateData['language'] = $request->input('language');
        }

        if ($request->has('avatar_url')) {
            $updateData['avatar_url'] = $request->input('avatar_url');
        }

        // Build UPDATE query
        $sets = [];
        $params = [];
        foreach ($updateData as $key => $value) {
            $sets[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $userId;

        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?";
        $db->query($sql, $params);

        return Response::json([
            'message' => 'Profilo aggiornato con successo',
        ]);
    }

    /**
     * Cambia password
     */
    public function changePassword(Request $request)
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return Response::error('Non autenticato', 401);
        }

        // Validazione
        $errors = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required',
        ]);

        if (!empty($errors)) {
            return Response::error('Validazione fallita', 422, $errors);
        }

        $user = User::find($userId);

        if (!$user) {
            return Response::error('Utente non trovato', 404);
        }

        // Verifica password corrente
        if (!password_verify($request->input('current_password'), $user->password)) {
            return Response::error('Password corrente errata', 422);
        }

        // Verifica match nuova password
        if ($request->input('new_password') !== $request->input('confirm_password')) {
            return Response::error('Le password non corrispondono', 422);
        }

        // Aggiorna password
        $newPasswordHash = password_hash($request->input('new_password'), PASSWORD_BCRYPT);

        $db = Database::getInstance();
        $db->query("UPDATE users SET password = ? WHERE id = ?", [$newPasswordHash, $userId]);

        return Response::json([
            'message' => 'Password modificata con successo',
        ]);
    }

    /**
     * Aggiorna preferenze
     */
    public function updatePreferences(Request $request)
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return Response::error('Non autenticato', 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return Response::error('Utente non trovato', 404);
        }

        // Ottieni preferenze attuali
        $preferences = $user->preferences ?? '{}';
        if (is_string($preferences)) {
            $preferences = json_decode($preferences, true) ?? [];
        }

        // Aggiorna con nuove preferenze
        $body = $request->getBody();
        $newPreferences = array_merge($preferences, $body);

        // Salva con query diretta
        $db = Database::getInstance();
        $db->query(
            "UPDATE users SET preferences = ? WHERE id = ?",
            [json_encode($newPreferences), $userId]
        );

        return Response::json([
            'message' => 'Preferenze aggiornate',
            'data' => $newPreferences,
        ]);
    }

    /**
     * Elimina account
     */
    public function deleteAccount(Request $request)
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return Response::error('Non autenticato', 401);
        }

        // Validazione password per conferma
        $errors = $request->validate([
            'password' => 'required',
        ]);

        if (!empty($errors)) {
            return Response::error('Validazione fallita', 422, $errors);
        }

        $user = User::find($userId);

        if (!$user) {
            return Response::error('Utente non trovato', 404);
        }

        // Verifica password
        if (!password_verify($request->input('password'), $user->password)) {
            return Response::error('Password errata', 422);
        }

        // Elimina utente con query diretta
        $db = Database::getInstance();
        $db->query("DELETE FROM users WHERE id = ?", [$userId]);

        // Distruggi sessione
        session_destroy();
        $_SESSION = [];

        return Response::json([
            'message' => 'Account eliminato con successo',
        ]);
    }
}
