<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Models\User;

class SettingsController
{
    /**
     * Ottieni impostazioni utente corrente
     */
    public function index(Request $request)
    {
        $userId = $request->session('user_id');
        
        if (!$userId) {
            return Response::error('Non autenticato', 401);
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return Response::error('Utente non trovato', 404);
        }
        
        // Rimuovi dati sensibili
        unset($user['password']);
        unset($user['remember_token']);
        
        return Response::json([
            'data' => $user,
        ]);
    }
    
    /**
     * Aggiorna profilo utente
     */
    public function updateProfile(Request $request)
    {
        $userId = $request->session('user_id');
        
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
        if ($newEmail !== $user['email']) {
            $existing = User::where('email', $newEmail)->first();
            if ($existing) {
                return Response::error('Email già in uso', 422);
            }
        }
        
        // Aggiorna dati
        $data = [
            'name' => $request->input('name'),
            'email' => $newEmail,
        ];
        
        // Campi opzionali
        if ($request->has('timezone')) {
            $data['timezone'] = $request->input('timezone');
        }
        
        if ($request->has('language')) {
            $data['language'] = $request->input('language');
        }
        
        if ($request->has('avatar_url')) {
            $data['avatar_url'] = $request->input('avatar_url');
        }
        
        User::update($userId, $data);
        
        return Response::json([
            'message' => 'Profilo aggiornato con successo',
            'data' => array_merge($user, $data),
        ]);
    }
    
    /**
     * Cambia password
     */
    public function changePassword(Request $request)
    {
        $userId = $request->session('user_id');
        
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
        if (!password_verify($request->input('current_password'), $user['password'])) {
            return Response::error('Password corrente errata', 422);
        }
        
        // Verifica match nuova password
        if ($request->input('new_password') !== $request->input('confirm_password')) {
            return Response::error('Le password non corrispondono', 422);
        }
        
        // Aggiorna password
        $newPasswordHash = password_hash($request->input('new_password'), PASSWORD_BCRYPT);
        
        User::update($userId, [
            'password' => $newPasswordHash,
        ]);
        
        return Response::json([
            'message' => 'Password modificata con successo',
        ]);
    }
    
    /**
     * Aggiorna preferenze
     */
    public function updatePreferences(Request $request)
    {
        $userId = $request->session('user_id');
        
        if (!$userId) {
            return Response::error('Non autenticato', 401);
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return Response::error('Utente non trovato', 404);
        }
        
        // Ottieni preferenze attuali
        $preferences = $user['preferences'] ?? '{}';
        if (is_string($preferences)) {
            $preferences = json_decode($preferences, true) ?? [];
        }
        
        // Aggiorna con nuove preferenze
        $newPreferences = array_merge($preferences, $request->all());
        
        User::update($userId, [
            'preferences' => json_encode($newPreferences),
        ]);
        
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
        $userId = $request->session('user_id');
        
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
        if (!password_verify($request->input('password'), $user['password'])) {
            return Response::error('Password errata', 422);
        }
        
        // Elimina utente (TODO: implementare soft delete e cleanup dati correlati)
        User::delete($userId);
        
        // Distruggi sessione
        $request->destroySession();
        
        return Response::json([
            'message' => 'Account eliminato con successo',
        ]);
    }
}
