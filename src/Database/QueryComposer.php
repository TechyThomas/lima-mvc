<?php

namespace Lima\Database;

class QueryComposer
{
    private $sqlParts;
    private $values = [];

    public function __construct($sqlParts)
    {
        $this->sqlParts = $sqlParts;
    }

    public function compose(): string
    {
        $sql = '';

        if (!empty($this->sqlParts['select'])) {
            $sql = "SELECT {$this->sqlParts['select']} FROM {$this->sqlParts['table']}";
        }

        if (!empty($this->sqlParts['insert'])) {
            $insertColumns = [];
            $insertValues = [];

            foreach ($this->sqlParts['insert'] as $column => $value) {
                $insertColumns[] = '`' . $column . '`';
                $this->values[] = $value;
                $insertValues[] = '?';
            }

            $columnString = join(', ', $insertColumns);
            $valuesString = join(', ', $insertValues);

            $sql = "INSERT INTO {$this->sqlParts['table']} ({$columnString}) VALUES ({$valuesString})";
        }

        if (!empty($this->sqlParts['update'])) {
            $updateSet = [];

            foreach ($this->sqlParts['update'] as $column => $value) {
                $updateSet[] = "`{$column}` = ?";
                $this->values[] = $value;
            }

            $sql = "UPDATE {$this->sqlParts['table']} SET " . join(', ', $updateSet);
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

        return $sql;
    }

    public function composeWhere(): string
    {
        if (empty($this->sqlParts['where'])) {
            return '';
        }

        $sqlWhere = [];

        foreach ($this->sqlParts['where'] as $column => $value) {

            if (is_array($value)) {
                $whereValue = $value[0];
                $whereOperator = $value[1];

                switch ($whereOperator) {
                    case '=':
                    default:
                        $sqlWhere[] = $column . ' ' . $whereOperator . ' ?';
                        break;
                    case 'IN':
                        $sqlWhere[] = $column . ' ' . $whereOperator . ' (?)';
                        break;
                }
            } else {
                $whereValue = $value;
                $sqlWhere[] = $column . ' = ?';
            }

            $this->values[] = $whereValue;
        }

        return join(' AND ', $sqlWhere);
    }

    public function getValues(): array
    {
        return $this->values;
    }
}