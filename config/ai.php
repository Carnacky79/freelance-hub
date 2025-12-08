<?php
/**
 * FreelanceHub - Configurazione AI
 */

return [
    // Provider IA (claude, openai)
    'provider' => $_ENV['AI_PROVIDER'] ?? 'claude',
    
    // Anthropic Claude
    'claude' => [
        'api_key' => $_ENV['CLAUDE_API_KEY'] ?? '',
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'api_base' => 'https://api.anthropic.com/v1',
    ],
    
    // OpenAI (alternativa)
    'openai' => [
        'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
        'model' => 'gpt-4o',
        'max_tokens' => 1024,
        'api_base' => 'https://api.openai.com/v1',
    ],
    
    // Impostazioni analisi
    'analysis' => [
        // Pesi per il calcolo priorità
        'priority_weights' => [
            'deadline_proximity' => 0.35,    // Vicinanza scadenza
            'client_importance' => 0.25,     // Importanza cliente
            'estimated_effort' => 0.15,      // Effort stimato
            'dependencies' => 0.15,          // Dipendenze da altri task
            'user_preference' => 0.10,       // Preferenza manuale utente
        ],
        
        // Soglie
        'urgent_days_threshold' => 3,        // Giorni per considerare urgente
        'overdue_penalty' => 50,             // Bonus priorità se scaduto
        
        // Aggiornamento suggerimenti
        'refresh_interval_hours' => 6,
        'max_suggestions_per_day' => 10,
    ],
    
    // Prompt templates
    'prompts' => [
        'priority_analysis' => "Analizza i seguenti task e suggerisci priorità basandoti su urgenza, importanza cliente, carico di lavoro attuale e scadenze. Rispondi in JSON.",
        'time_allocation' => "Dato il carico di lavoro e le ore disponibili, suggerisci come allocare il tempo per massimizzare produttività e rispettare le scadenze.",
        'deadline_suggestion' => "Basandoti sullo storico di task simili e sul carico attuale, suggerisci una scadenza realistica per questo task.",
    ],
];
