<?php

namespace Lima\Database;

use ArrayObject;
use Lima\Core\Model;

class Item extends ArrayObject
{
    private $model;

    public function __construct($array, Model $model)
    {
        parent::__construct($array);

        $this->model = $model;
    }

    public function getKey()
    {
        $keyField = $this->model->getKey();
        return $this[$keyField];
    }

    public function toArray()
    {
        return (array) $this;
    }

    public function toObject()
    {
        return (object) $this;
    }
}