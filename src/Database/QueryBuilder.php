<?php

namespace Lima\Database;

use FTP\Connection;

class QueryBuilder
{
    private $database;
    private $pdo;
    private $values;

    private $sqlParts = [
        'table' => '',
        'select' => '*',
        'update' => '',
        'insert' => '',
        'delete' => false,
        'limit' => -1,
        'order' => ''
    ];

    protected $fields = [];

    private $lastInsertID;

    public function __construct()
    {
        $this->database = Database::getInstance($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);

        if (!empty($this->fields)) {
            $this->select($this->fields);
        }

        if (!empty($_ENV['DB_DEFAULT_LIMIT']) && (int) $_ENV['DB_DEFAULT_LIMIT'] > 0) {
            $this->limit((int) $_ENV['DB_DEFAULT_LIMIT']);
        }
    }

    public function table($table): self
    {
        $this->sqlParts['table'] = $table;
        return $this;
    }

    public function select($columns = []): self
    {
        if (!empty($columns)) {
            $columns = is_array($columns) ? $columns : [$columns];
            $this->sqlParts['select'] = join(', ', $columns);
        } else {
            $this->sqlParts['select'] = '*';
        }

        return $this;
    }

    public function update($data): bool
    {
        $this->sqlParts['update'] = $data;
        $this->prepareQuery();

        $result = $this->pdo->execute($this->values);

        $this->resetSql();

        return $result;
    }

    public function insert($data): bool|int|string
    {
        $this->sqlParts['insert'] = $data;

        $prepare = $this->prepareQuery();
        $doInsert = $prepare->execute($this->values);

        $this->resetSql();

        if (!$doInsert)
            return false;


        return $this->database->getPDO()->lastInsertId();
    }

    public function delete(): bool
    {
        $this->sqlParts['delete'] = true;
        $this->prepareQuery();

        $result = $this->pdo->execute($this->values);

        $this->resetSql();

        return $result;
    }

    public function where($column, $value, $operator = '='): self
    {
        $this->sqlParts['where'] = [$column => [$value, $operator]];
        return $this;
    }

    public function wheres($data): self
    {
        $this->sqlParts['where'] = $data;
        return $this;
    }

    public function limit($limit): self
    {
        $this->sqlParts['limit'] = (int) $limit;
        return $this;
    }

    public function order($order, $direction = 'DESC'): self
    {
        $this->sqlParts['order'] = [$order, $direction];
        return $this;
    }

    private function prepareQuery(): \PDOStatement
    {
        $queryComposer = new QueryComposer($this->sqlParts);
        $sql = $queryComposer->compose();

        $db = $this->database->getPDO()->prepare($sql);
        $this->values = $queryComposer->getValues();
        $this->pdo = $db;

        return $db;
    }

    public function get(): Collection|Item|null
    {
        $results = $this->getAll();

        if (!empty($results) && count($results) == 1) {
            return new Item($results[0], $this);
        }

        return $results;
    }

    public function getSingle(): Item|null
    {
        $results = $this->getAll();
        if (empty($results))
            return null;

        return new Item($results[0], $this);
    }

    public function getAll(): Collection|null
    {
        $db = $this->prepareQuery();
        $db->execute($this->values);

        $results = $db->fetchAll(\PDO::FETCH_ASSOC);

        $this->resetSql();

        if (empty($results)) {
            return null;
        }

        $rows = [];

        foreach ($results as $index => $result) {
            $rows[$index] = new Item($result, $this);
        }

        return new Collection($rows, $this);
    }

    public function resetSql()
    {
        $this->sqlParts = [
            'table' => $this->sqlParts['table'],
            'select' => '*',
            'update' => '',
            'insert' => '',
            'delete' => false,
            'limit' => -1,
            'order' => ''
        ];
    }
}