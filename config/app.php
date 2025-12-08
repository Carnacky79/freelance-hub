<?php
/**
 * FreelanceHub - Configurazione Applicazione
 */

return [
    // Informazioni App
    'name' => 'FreelanceHub',
    'version' => '1.0.0',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    
    // Timezone
    'timezone' => 'Europe/Rome',
    
    // Sicurezza
    'encryption_key' => $_ENV['ENCRYPTION_KEY'] ?? 'your-32-char-secret-key-here!!!',
    'session_lifetime' => 120, // minuti
    
    // Upload
    'max_upload_size' => 10 * 1024 * 1024, // 10MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    
    // Paginazione
    'items_per_page' => 25,
    
    // Cache
    'cache_enabled' => true,
    'cache_ttl' => 3600, // secondi
];
