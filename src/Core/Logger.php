<?php

namespace FreelanceHub\Core;

/**
 * Logger - Sistema di logging semplice ma efficace
 */
class Logger
{
    private const LOG_DIR = __DIR__ . '/../../storage/logs';
    
    /**
     * Log livello ERROR
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * Log livello WARNING
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Log livello INFO
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log livello DEBUG
     */
    public static function debug(string $message, array $context = []): void
    {
        if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
            self::log('DEBUG', $message, $context);
        }
    }
    
    /**
     * Log evento di sicurezza
     */
    public static function security(string $message, array $context = []): void
    {
        self::log('SECURITY', $message, $context, 'security.log');
    }
    
    /**
     * Log accesso utente
     */
    public static function access(string $action, int $userId, array $context = []): void
    {
        self::log('ACCESS', $action, array_merge(['user_id' => $userId], $context), 'access.log');
    }
    
    /**
     * Scrivi log su file
     */
    private static function log(string $level, string $message, array $context = [], string $filename = null): void
    {
        // Assicura che la directory esista
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }
        
        // Nome file basato su data
        $filename = $filename ?? date('Y-m-d') . '.log';
        $filepath = self::LOG_DIR . '/' . $filename;
        
        // Formatta messaggio
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        
        $logLine = sprintf(
            "[%s] [%s] [IP:%s] %s %s\n",
            $timestamp,
            $level,
            $ip,
            $message,
            $contextStr
        );
        
        // Scrivi su file (append)
        file_put_contents($filepath, $logLine, FILE_APPEND | LOCK_EX);
        
        // Rotazione log (max 10MB)
        self::rotateIfNeeded($filepath);
    }
    
    /**
     * Ruota log se troppo grande
     */
    private static function rotateIfNeeded(string $filepath): void
    {
        if (!file_exists($filepath)) {
            return;
        }
        
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (filesize($filepath) > $maxSize) {
            $rotated = $filepath . '.' . time();
            rename($filepath, $rotated);
            
            // Comprimi vecchi log
            if (function_exists('gzopen')) {
                self::compressLog($rotated);
            }
        }
    }
    
    /**
     * Comprimi file log
     */
    private static function compressLog(string $filepath): void
    {
        $compressed = $filepath . '.gz';
        
        $fp = fopen($filepath, 'rb');
        $gz = gzopen($compressed, 'wb9');
        
        while (!feof($fp)) {
            gzwrite($gz, fread($fp, 1024 * 512));
        }
        
        fclose($fp);
        gzclose($gz);
        
        // Rimuovi file non compresso
        unlink($filepath);
    }
    
    /**
     * Pulisci log vecchi (oltre 30 giorni)
     */
    public static function cleanup(int $days = 30): int
    {
        if (!is_dir(self::LOG_DIR)) {
            return 0;
        }
        
        $cutoff = time() - ($days * 86400);
        $deleted = 0;
        
        foreach (glob(self::LOG_DIR . '/*.log*') as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Log exception
     */
    public static function exception(\Throwable $e, array $context = []): void
    {
        self::error(
            $e->getMessage(),
            array_merge([
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], $context)
        );
    }
}
