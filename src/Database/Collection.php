<?php

namespace Lima\Database;

use Lima\Core\Model;

class Collection
{
    private array $items = [];

    public function __construct($array, Model $model)
    {
        foreach ($array as $key => $value) {
            $this->items[$key] = new Item($value, $model);
        }
    }

    public function items(): array
    {
        return $this->items;
    }

    public function first(): ?Item
    {
        return array_values($this->items)[0] ?? null;
    }

    public function last(): ?Item
    {
        return end($this->items) ?? null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}