<?php

namespace Lima\Core;
use Lima\Database\QueryBuilder;

class Model extends QueryBuilder {
    protected $table = '';
    protected $primaryKey = '';

    public function __construct() {
        parent::__construct();

        if (empty($this->table)) {
            die('No table set for model ' . get_class($this));
        }

        $this->table($this->table);

        if (empty($this->primaryKey)) {
            $this->primaryKey = $this->table . '_id';
        }
    }

    public function getByID($id): ?array {
        $query = $this->where($this->primaryKey, $id)->getAll();
        // var_dump($query);

        if (empty($query) || count($query) > 1) {
            return null;
        }

        return $query[0];
    }
}