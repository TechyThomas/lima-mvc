<?php

namespace Lima\Core;

use Lima\Database\Item;
use Lima\Database\QueryBuilder;

class Model extends QueryBuilder
{
    protected $table = '';
    protected $primaryKey = '';
    protected $fields = [];
    protected $timestamps = ['created', 'updated'];
    protected $foreignKeys = [];
    protected $casts = [];

    public function __construct()
    {
        parent::__construct();

        if (empty($this->table)) {
            die('No table set for model ' . get_class($this));
        }

        $this->table($this->table);

        if (empty($this->primaryKey)) {
            $this->primaryKey = $this->table . '_id';
        }
    }

    public function getByID($id): ?Item
    {
        $query = $this->where($this->primaryKey, $id)->getAll();

        if (empty($query) || count($query) > 1) {
            return null;
        }

        return new Item($query[0], $this);
    }

    public function create($data): bool|Item
    {
        if (!empty($this->timestamps)) {
            $dt = new \DateTime();

            if (in_array('created', $this->timestamps)) {
                $data['date_created'] = $dt->format('Y-m-d H:i:s');
            }

            if (in_array('updated', $this->timestamps)) {
                $data['date_updated'] = $dt->format('Y-m-d H:i:s');
            }
        }

        if (!empty($this->fields)) {
            foreach ($this->fields as $field) {
                if (empty($data[$field]))
                    continue;

                unset($data[$field]);
            }
        }

        foreach ($this->casts as $field => $type) {
            if (empty($data[$field]))
                continue;

            $data[$field] = $this->castField($data[$field], $type);
        }

        $insertRowID = $this->insert($data);

        if (!$insertRowID)
            return false;

        return $this->getByID($insertRowID);
    }

    public function update($data): bool
    {
        if (!empty($this->timestamps) && in_array('updated', $this->timestamps)) {
            $dt = new \DateTime();
            $data['date_updated'] = $dt->format('Y-m-d H:i:s');
        }

        if (!empty($this->fields)) {
            foreach ($this->fields as $field) {
                if (empty($data[$field]))
                    continue;

                unset($data[$field]);
            }
        }

        foreach ($this->casts as $field => $type) {
            if (empty($data[$field]))
                continue;

            $data[$field] = $this->castField($data[$field], $type);
        }

        return parent::update($data);
    }

    public function delete(): bool
    {
        if (!empty($this->foreignKeys)) {
            $currentData = $this->select(array_keys($this->foreignKeys))->getAll();
            $columnsToDelete = [];

            foreach ($currentData as $row) {
                foreach ($row as $column => $value) {
                    if (empty($columnsToDelete[$column])) {
                        $columnsToDelete[$column] = [];
                    }

                    if (is_int($value)) {
                        $columnsToDelete[$column][] = $value;
                    } else {
                        $columnsToDelete[$column][] = '"' . $value . '"';
                    }
                }
            }

            foreach ($this->foreignKeys as $column => $models) {
                foreach ($models as $model) {
                    $modelInstance = new $model[0]();
                    $modelColumn = $model[1];

                    $modelInstance->where($modelColumn, join(',', $columnsToDelete[$column]), 'IN')->delete();
                }
            }
        }

        return parent::delete();
    }

    protected function castField($value, $type)
    {
        $type = strtolower($type);
        $class = get_class($value);

        if ($parentClass = get_parent_class($value)) {
            $class = $parentClass;
        }

        switch ($type) {
            case 'datetime':
                $dt = $class === \DateTime::class ? $value : new \DateTime($value);
                $value = $dt->format('Y-m-d H:i:s');

                break;
        }

        return $value;
    }

    public function getKey()
    {
        return $this->primaryKey;
    }
}