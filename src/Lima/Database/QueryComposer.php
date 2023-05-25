<?php

namespace Lima\Database;

class QueryComposer {
    private $sqlParts;

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

        return $sql;
    }
}