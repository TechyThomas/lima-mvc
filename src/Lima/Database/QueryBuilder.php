<?php

namespace Lima\Database;

class QueryBuilder {
    private $database;
    private $sqlParts = [
        'table' => '',
        'select' => '',
        'update' => '',
        'insert' => '',
        'delete' => false
    ];

    private $lastInsertID;

    public function __construct() {
        $this->database = Database::getInstance('', '', '', '');
    }

    public function table($table): self {
        $this->sqlParts['table'] = $table;
        return $this;
    }

    public function select($columns): self {
        $columns = is_array($columns) ? $columns : [$columns];
        $this->sqlParts['select'] = join(', ', $columns);
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
}