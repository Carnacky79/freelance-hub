<?php

namespace FreelanceHub\Middleware;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;

/**
 * CSRFMiddleware - Protezione Cross-Site Request Forgery
 * 
 * UTILIZZO:
 * 1. Applica a tutte le route POST/PUT/DELETE
 * 2. Nei form HTML aggiungi: <input type="hidden" name="csrf_token" value="<?= \FreelanceHub\Core\Session::generateCSRFToken() ?>">
 * 3. In JS API, aggiungi header: 'X-CSRF-TOKEN': localStorage.getItem('csrf_token')
 */
class CSRFMiddleware
{
    /**
     * Verifica token CSRF per richieste che modificano dati
     */
    public function __invoke(Request $request): ?Response
    {
        // Skip per richieste GET/HEAD/OPTIONS
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return null;
        }
        
        // Token da form o header
        $token = $request->getBody('csrf_token') 
              ?? $request->getHeader('X-CSRF-TOKEN');
        
        // Verifica token
        if (!$this->verifyToken($token)) {
            return Response::error('CSRF token validation failed', 403);
        }
        
        return null; // Continua elaborazione
    }
    
    /**
     * Verifica validità token CSRF
     */
    private function verifyToken(?string $token): bool
    {
        if (!isset($_SESSION['csrf_token']) || !$token) {
            return false;
        }
        
        // Usa hash_equals per timing-attack protection
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
