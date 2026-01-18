<?php

namespace Tests\Database;

use Lima\Database\QueryComposer;
use PHPUnit\Framework\TestCase;

class QueryComposerTest extends TestCase
{
    public function testComposeSelect()
    {
        $parts = [
            'table' => 'users',
            'select' => 'id, name',
            'limit' => -1
        ];

        $composer = new QueryComposer($parts);
        $sql = $composer->compose();

        $this->assertEquals('SELECT id, name FROM users', $sql);
    }

    public function testComposeSelectAll()
    {
        $parts = [
            'table' => 'users',
            'select' => '*',
            'limit' => -1
        ];

        $composer = new QueryComposer($parts);
        $sql = $composer->compose();

        $this->assertEquals('SELECT * FROM users', $sql);
    }

    public function testComposeInsert()
    {
        $parts = [
            'table' => 'users',
            'insert' => ['name' => 'John', 'email' => 'john@example.com'],
            'limit' => -1
        ];

        $composer = new QueryComposer($parts);
        $sql = $composer->compose();
        $values = $composer->getValues();

        $this->assertEquals('INSERT INTO users (`name`, `email`) VALUES (?, ?)', $sql);
        $this->assertEquals(['John', 'john@example.com'], $values);
    }

    public function testComposeUpdate()
    {
        $parts = [
            'table' => 'users',
            'update' => ['name' => 'Jane'],
            'where' => ['id' => 1],
            'limit' => -1
        ];

        $composer = new QueryComposer($parts);
        $sql = $composer->compose();
        $values = $composer->getValues();

        $this->assertEquals('UPDATE users SET `name` = ? WHERE id = ?', $sql);
        $this->assertEquals(['Jane', 1], $values);
    }

    public function testComposeDelete()
    {
        $parts = [
            'table' => 'users',
            'delete' => true,
            'where' => ['id' => 1],
            'limit' => -1
        ];

        $composer = new QueryComposer($parts);
        $sql = $composer->compose();
        $values = $composer->getValues();

        $this->assertEquals('DELETE FROM users WHERE id = ?', $sql);
        $this->assertEquals([1], $values);
    }

    public function testComposeWhere()
    {
        $parts = [
            'table' => 'users',
            'select' => '*',
            'where' => [
                'name' => 'John',
                'age' => [18, '>']
            ],
            'limit' => -1
        ];

        $composer = new QueryComposer($parts);
        $sql = $composer->compose();
        $values = $composer->getValues();

        $this->assertEquals('SELECT * FROM users WHERE name = ? AND age > ?', $sql);
        $this->assertEquals(['John', 18], $values);
    }

    public function testComposeOrderBy()
    {
        $parts = [
            'table' => 'users',
            'select' => '*',
            'order' => ['created_at', 'DESC'],
            'limit' => -1
        ];

        $composer = new QueryComposer($parts);
        $sql = $composer->compose();

        $this->assertEquals('SELECT * FROM users ORDER BY created_at DESC', $sql);
    }

    public function testComposeLimit()
    {
        $parts = [
            'table' => 'users',
            'select' => '*',
            'limit' => 10
        ];

        $composer = new QueryComposer($parts);
        $sql = $composer->compose();

        $this->assertEquals('SELECT * FROM users LIMIT 10', $sql);
    }
}
