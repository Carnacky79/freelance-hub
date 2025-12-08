<?php

namespace FreelanceHub\Controllers;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Models\User;

/**
 * AuthController - Gestione autenticazione
 */
class AuthController
{
    /**
     * Login
     */
    public function login(Request $request): Response
    {
        $email = $request->getBody('email');
        $password = $request->getBody('password');

        if (!$email || !$password) {
            return Response::error('Email e password sono obbligatori', 400);
        }

        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            return Response::error('Credenziali non valide', 401);
        }

        // Salva in sessione
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;

        return Response::success([
            'user' => $user->toArray()
        ], 'Login effettuato');
    }

    /**
     * Registrazione
     */
    public function register(Request $request): Response
    {
        $errors = $request->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (!empty($errors)) {
            return Response::validationError($errors);
        }

        $email = $request->getBody('email');
        
        // Verifica email già esistente
        if (User::findByEmail($email)) {
            return Response::error('Email già registrata', 409);
        }

        $user = User::register([
            'name' => $request->getBody('name'),
            'email' => $email,
            'password' => $request->getBody('password'),
            'timezone' => $request->getBody('timezone', 'Europe/Rome'),
            'default_hourly_rate' => $request->getBody('hourly_rate', 0),
        ]);

        // Login automatico dopo registrazione
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;

        return Response::success([
            'user' => $user->toArray()
        ], 'Registrazione completata');
    }

    /**
     * Logout
     */
    public function logout(Request $request): Response
    {
        session_destroy();
        return Response::success(null, 'Logout effettuato');
    }

    /**
     * Utente corrente
     */
    public function me(Request $request): Response
    {
        if (!$request->isAuthenticated()) {
            return Response::unauthorized();
        }

        return Response::success($request->getUser());
    }
}
