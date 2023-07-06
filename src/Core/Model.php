<?php

namespace Lima\Core;

use Lima\Database\QueryBuilder;

class Model extends QueryBuilder
{
    protected $table = '';
    protected $primaryKey = '';
    protected $fields = [];
    protected $timestamps = ['created', 'updated'];

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

    public function getByID($id): ?array
    {
        $query = $this->where($this->primaryKey, $id)->getAll();
        // var_dump($query);

        if (empty($query) || count($query) > 1) {
            return null;
        }

        return $query[0];
    }

    public function create($data): bool|array
    {
        if (!empty($this->timestamps) && in_array('created', $this->timestamps)) {
            $dt = new \DateTime();
            $data['date_created'] = $dt->format('Y-m-d H:i:s');
        }

        $insertRow = $this->insert($data);
        if (!$insertRow)
            return false;

        return $this->getByID($insertRow);
    }

    public function update($data): bool
    {
        if (!empty($this->timestamps) && in_array('updated', $this->timestamps)) {
            $dt = new \DateTime();
            $data['date_updated'] = $dt->format('Y-m-d H:i:s');
        }

        return $this->update($data);
    }
}