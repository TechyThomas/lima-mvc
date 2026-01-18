<?php

namespace Lima\Database;

use ArrayObject;
use Lima\Core\Model;

class Collection extends ArrayObject
{
    public function __construct($array, Model|QueryBuilder $model)
    {
        $itemArray = [];

        foreach ($array as $key => $value) {
            if ($value instanceof Item) {
                $itemArray[$key] = $value;
            } else {
                $itemArray[$key] = new Item($value, $model);
            }
        }

        parent::__construct($itemArray);
    }

    public function toArray(): array
    {
        return (array) $this;
    }
}