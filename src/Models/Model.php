<?php

namespace FreelanceHub\Models;

use FreelanceHub\Core\Database;

/**
 * Model Base - Classe base per tutti i modelli
 */
abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $hidden = [];
    
    protected array $attributes = [];
    protected array $original = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    protected static function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Trova per ID
     */
    public static function find(int $id): ?static
    {
        $row = static::db()->selectOne(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
        
        return $row ? new static($row) : null;
    }

    /**
     * Trova o fallisce
     */
    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        if (!$model) {
            throw new \RuntimeException(static::class . " with ID {$id} not found");
        }
        return $model;
    }

    /**
     * Ritorna tutti i record
     */
    public static function all(): array
    {
        $rows = static::db()->select("SELECT * FROM " . static::$table);
        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Query builder semplice - where
     */
    public static function where(string $column, $value, string $operator = '='): array
    {
        $rows = static::db()->select(
            "SELECT * FROM " . static::$table . " WHERE {$column} {$operator} ?",
            [$value]
        );
        return array_map(fn($row) => new static($row), $rows);
    }

    /**
     * Primo risultato di una query where
     */
    public static function whereFirst(string $column, $value, string $operator = '='): ?static
    {
        $row = static::db()->selectOne(
            "SELECT * FROM " . static::$table . " WHERE {$column} {$operator} ? LIMIT 1",
            [$value]
        );
        return $row ? new static($row) : null;
    }

    /**
     * Conta record
     */
    public static function count(string $where = '1=1', array $params = []): int
    {
        $result = static::db()->selectOne(
            "SELECT COUNT(*) as count FROM " . static::$table . " WHERE {$where}",
            $params
        );
        return (int)($result['count'] ?? 0);
    }

    /**
     * Crea nuovo record
     */
    public static function create(array $data): static
    {
        $filtered = array_intersect_key($data, array_flip(static::$fillable));
        $id = static::db()->insert(static::$table, $filtered);
        return static::find($id);
    }

    /**
     * Aggiorna il record corrente
     */
    public function update(array $data): bool
    {
        $filtered = array_intersect_key($data, array_flip(static::$fillable));
        
        $affected = static::db()->update(
            static::$table,
            $filtered,
            static::$primaryKey . ' = ?',
            [$this->getId()]
        );
        
        if ($affected) {
            $this->fill($filtered);
        }
        
        return $affected > 0;
    }

    /**
     * Elimina il record corrente
     */
    public function delete(): bool
    {
        return static::db()->delete(
            static::$table,
            static::$primaryKey . ' = ?',
            [$this->getId()]
        ) > 0;
    }

    /**
     * Salva il modello (insert o update)
     */
    public function save(): bool
    {
        if ($this->getId()) {
            return $this->update($this->getDirty());
        }
        
        $filtered = array_intersect_key($this->attributes, array_flip(static::$fillable));
        $id = static::db()->insert(static::$table, $filtered);
        $this->attributes[static::$primaryKey] = $id;
        
        return true;
    }

    /**
     * Riempie gli attributi
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * Ottiene attributi modificati
     */
    public function getDirty(): array
    {
        return array_diff_assoc($this->attributes, $this->original);
    }

    /**
     * Getter per ID
     */
    public function getId(): ?int
    {
        return isset($this->attributes[static::$primaryKey]) 
            ? (int)$this->attributes[static::$primaryKey] 
            : null;
    }

    /**
     * Magic getter
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Magic setter
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Magic isset
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Converte in array (nasconde campi hidden)
     */
    public function toArray(): array
    {
        return array_diff_key($this->attributes, array_flip(static::$hidden));
    }

    /**
     * Converte in JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Query raw con mapping a modelli
     */
    public static function query(string $sql, array $params = []): array
    {
        $rows = static::db()->select($sql, $params);
        return array_map(fn($row) => new static($row), $rows);
    }
}
