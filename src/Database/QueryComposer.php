<?php

namespace Lima\Database;

class QueryComposer {
    private $sqlParts;
    private $values = [];

    public function __construct($sqlParts) {
        $this->sqlParts = $sqlParts;
    }

    public function compose(): string {
        $sql = '';

        if (!empty($this->sqlParts['select'])) {
            $sql = "SELECT {$this->sqlParts['select']} FROM {$this->sqlParts['table']}";
        }

        if (!empty($this->sqlParts['delete'])) {
            $sql = "DELETE FROM {$this->sqlParts['table']}";
        }

        if (!empty($this->sqlParts['where'])) {
            $sql .= ' WHERE ' . $this->composeWhere();
        }

        if (!empty($this->sqlParts['order'])) {
            $orderColumn = $this->sqlParts['order'];
            $orderDirection = 'DESC';

            if (is_array($this->sqlParts['order'])) {
                $orderColumn = $this->sqlParts['order'][0];
                $orderDirection = $this->sqlParts['order'][1];
            }

            $sql .= " ORDER BY {$orderColumn} {$orderDirection}";
        }

        if ($this->sqlParts['limit'] > 0) {
            $sql .= ' LIMIT ' . $this->sqlParts['limit'];
        }

        // echo $sql. '<br/>';

        return $sql;
    }

    public function composeWhere(): string {
        if (empty($this->sqlParts['where'])) {
            return '';
        }

        $sqlWhere = [];

        foreach ($this->sqlParts['where'] as $column => $value) {
            $sqlWhere[] = $column . ' = ?';
            $this->values[] = $value;
        }

        return join(' AND ', $sqlWhere);
    }

    public function getValues(): array {
        return $this->values;
    }
}