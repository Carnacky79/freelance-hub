<?php

namespace FreelanceHub\Middleware;

use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;

/**
 * RateLimitMiddleware - Protezione brute-force e DoS
 * 
 * Limita numero di richieste per IP in un dato intervallo
 */
class RateLimitMiddleware
{
    private int $maxAttempts;
    private int $decayMinutes;
    private string $storageFile;
    
    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        $this->storageFile = __DIR__ . '/../../storage/cache/rate_limits.json';
        
        // Assicura che la directory esista
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    public function __invoke(Request $request): ?Response
    {
        $key = $this->resolveRequestKey($request);
        
        if ($this->tooManyAttempts($key)) {
            $retryAfter = $this->availableIn($key);
            
            return Response::json([
                'error' => 'Too many requests',
                'retry_after' => $retryAfter
            ], 429)
            ->setHeader('Retry-After', (string)$retryAfter)
            ->setHeader('X-RateLimit-Limit', (string)$this->maxAttempts)
            ->setHeader('X-RateLimit-Remaining', '0');
        }
        
        $this->hit($key);
        
        return null;
    }
    
    /**
     * Genera chiave univoca per IP + endpoint
     */
    private function resolveRequestKey(Request $request): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $path = $request->getPath();
        
        return md5($ip . '|' . $path);
    }
    
    /**
     * Verifica se limite è superato
     */
    private function tooManyAttempts(string $key): bool
    {
        return $this->attempts($key) >= $this->maxAttempts;
    }
    
    /**
     * Conta tentativi per chiave
     */
    private function attempts(string $key): int
    {
        $data = $this->load();
        
        if (!isset($data[$key])) {
            return 0;
        }
        
        // Filtra tentativi scaduti
        $validAttempts = array_filter(
            $data[$key]['attempts'],
            fn($timestamp) => $timestamp > time() - ($this->decayMinutes * 60)
        );
        
        return count($validAttempts);
    }
    
    /**
     * Registra tentativo
     */
    private function hit(string $key): void
    {
        $data = $this->load();
        
        if (!isset($data[$key])) {
            $data[$key] = ['attempts' => []];
        }
        
        // Aggiungi timestamp
        $data[$key]['attempts'][] = time();
        
        // Pulisci vecchi tentativi
        $data[$key]['attempts'] = array_filter(
            $data[$key]['attempts'],
            fn($timestamp) => $timestamp > time() - ($this->decayMinutes * 60)
        );
        
        $this->save($data);
    }
    
    /**
     * Secondi rimanenti prima di poter ritentare
     */
    private function availableIn(string $key): int
    {
        $data = $this->load();
        
        if (!isset($data[$key]['attempts']) || empty($data[$key]['attempts'])) {
            return 0;
        }
        
        $oldestAttempt = min($data[$key]['attempts']);
        $expiresAt = $oldestAttempt + ($this->decayMinutes * 60);
        
        return max(0, $expiresAt - time());
    }
    
    /**
     * Carica dati da file
     */
    private function load(): array
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }
        
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?? [];
    }
    
    /**
     * Salva dati su file
     */
    private function save(array $data): void
    {
        // Pulisci vecchie chiavi (più vecchie di 1 ora)
        $cutoff = time() - 3600;
        $data = array_filter($data, function($item) use ($cutoff) {
            return !empty($item['attempts']) && max($item['attempts']) > $cutoff;
        });
        
        file_put_contents($this->storageFile, json_encode($data));
    }
    
    /**
     * Reset limiti per una chiave (utile dopo login riuscito)
     */
    public function clear(string $key): void
    {
        $data = $this->load();
        unset($data[$key]);
        $this->save($data);
    }
}
