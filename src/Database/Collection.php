<?php

namespace Lima\Database;

use ArrayObject;
use Lima\Core\Model;

class Collection extends ArrayObject
{
    public function __construct($array, Model $model)
    {
        $itemArray = [];

        foreach ($array as $key => $value) {
            $itemArray[$key] = new Item($value, $model);
        }

        parent::__construct($itemArray);
    }
}