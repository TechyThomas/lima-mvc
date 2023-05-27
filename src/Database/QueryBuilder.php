<?php

namespace Lima\Database;

class QueryBuilder {
    private $database;
    private $sqlParts = [
        'table' => '',
        'select' => '*',
        'update' => '',
        'insert' => '',
        'delete' => false,
        'limit' => -1,
        'order' => ''
    ];

    private $lastInsertID;

    public function __construct() {
        $this->database = Database::getInstance($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    }

    public function table($table): self {
        $this->sqlParts['table'] = $table;
        return $this;
    }

    public function select($columns = []): self {
        if (!empty($columns)) {
            $columns = is_array($columns) ? $columns : [$columns];
            $this->sqlParts['select'] = join(', ', $columns);
        }
        else {
            $this->sqlParts['select'] = '*';
        }

        return $this;
    }

    public function update($data): self {
        $this->sqlParts['update'] = $data;
        return $this;
    }

    public function insert($data): self {
        $this->sqlParts['insert'] = $data;
        return $this;
    }

    public function delete(): self {
        $this->sqlParts['delete'] = true;
        return $this;
    }

    public function where($column, $value): self {
        $this->sqlParts['where'] = [$column => $value];
        return $this;
    }

    public function wheres($data): self {
        $this->sqlParts['where'] = $data;
        return $this;
    }

    public function limit($limit): self {
        $this->sqlParts['limit'] = (int) $limit;
        return $this;
    }

    public function order($order, $direction = 'DESC'): self {
        $this->sqlParts['order'] = [$order, $direction];
        return $this;
    }

    public function get() {
        $results = $this->getAll();

        if (!empty($results) && count($results) == 1) {
            return $results[0];
        }

        return $results;
    }

    public function getSingle() {
        $results = $this->getAll();
        if (empty($results)) return $results;

        return $results[0];
    }

    public function getAll(): array {
        $queryComposer = new QueryComposer($this->sqlParts);
        $sql = $queryComposer->compose();

        $db = $this->database->getPDO()->prepare($sql);
        $values = $queryComposer->getValues();;
        $db->execute($values);

        return $db->fetchAll();
    }
}