<?php

namespace FreelanceHub\Core;

/**
 * Session - Gestione sicura delle sessioni
 */
class Session
{
    /**
     * Inizializza sessione con configurazione sicura
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Configurazione sicura
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $_SERVER['HTTPS'] ?? false ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        
        session_start();
        
        // Rigenera ID dopo login per prevenire session fixation
        if (self::get('user_id') && !self::get('id_regenerated')) {
            session_regenerate_id(true);
            self::put('id_regenerated', true);
        }
    }
    
    /**
     * Genera token CSRF
     */
    public static function generateCSRFToken(): string
    {
        if (!self::has('csrf_token')) {
            self::put('csrf_token', bin2hex(random_bytes(32)));
        }
        
        return self::get('csrf_token');
    }
    
    /**
     * Verifica token CSRF
     */
    public static function verifyCSRFToken(?string $token): bool
    {
        if (!self::has('csrf_token') || !$token) {
            return false;
        }
        
        return hash_equals(self::get('csrf_token'), $token);
    }
    
    /**
     * Ottieni valore dalla sessione
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Imposta valore in sessione
     */
    public static function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Verifica esistenza chiave
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Rimuovi chiave
     */
    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    /**
     * Distruggi sessione
     */
    public static function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }
    
    /**
     * Flash message (disponibile solo per la prossima richiesta)
     */
    public static function flash(string $key, $value): void
    {
        self::put('_flash', array_merge(
            self::get('_flash', []),
            [$key => $value]
        ));
    }
    
    /**
     * Ottieni e rimuovi flash message
     */
    public static function getFlash(string $key, $default = null)
    {
        $flash = self::get('_flash', []);
        $value = $flash[$key] ?? $default;
        
        unset($flash[$key]);
        self::put('_flash', $flash);
        
        return $value;
    }
}
