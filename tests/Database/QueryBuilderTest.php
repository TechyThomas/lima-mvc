<?php

namespace Tests\Database;

use Lima\Database\Database;
use Lima\Database\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        // Setup SQLite in-memory database
        $_ENV['DB_HOST'] = 'sqlite::memory:';
        $_ENV['DB_NAME'] = null;
        $_ENV['DB_USER'] = null;
        $_ENV['DB_PASS'] = null;
        $_ENV['DB_DEFAULT_LIMIT'] = null;

        // Reset singleton (hacky but necessary since it's a singleton)
        // We can't easily reset a singleton pattern without reflection or helper
        // But since we are running in a fresh process per test file usually, or we can use reflection.

        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);

        // Initialize Database with SQLite
        $db = Database::getInstance($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);

        // Create a test table
        $pdo = $db->getPDO();
        $pdo->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)");
        $pdo->exec("INSERT INTO users (name, email) VALUES ('John Doe', 'john@example.com')");
        $pdo->exec("INSERT INTO users (name, email) VALUES ('Jane Doe', 'jane@example.com')");
    }

    public function testSelectAll()
    {
        $qb = new QueryBuilder();
        $results = $qb->table('users')->get();
        // get() returns Collection (which is ArrayObject) or Item

        $this->assertCount(2, $results->toArray());
        $this->assertEquals('John Doe', $results->toArray()[0]['name']);
    }

    public function testSelectOne()
    {
        $qb = new QueryBuilder();
        $result = $qb->table('users')->where('id', 1)->getSingle();

        $this->assertNotNull($result);
        // Item extends ArrayObject. We can access like array
        $this->assertEquals('John Doe', $result['name']);
    }

    public function testInsert()
    {
        $qb = new QueryBuilder();
        $id = $qb->table('users')->insert([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com'
        ]);

        $this->assertNotFalse($id);

        $result = $qb->table('users')->where('id', $id)->getSingle();
        $this->assertEquals('Bob Smith', $result['name']);
    }

    public function testUpdate()
    {
        $qb = new QueryBuilder();
        $result = $qb->table('users')
            ->where('id', 1)
            ->update(['name' => 'John Updated']);

        $this->assertTrue($result);

        $user = $qb->table('users')->where('id', 1)->getSingle();
        $this->assertEquals('John Updated', $user['name']);
    }

    public function testDelete()
    {
        $qb = new QueryBuilder();
        $result = $qb->table('users')->where('id', 2)->delete();

        $this->assertTrue($result);

        $user = $qb->table('users')->where('id', 2)->getSingle();
        $this->assertNull($user);
    }

    public function testWhereOperator()
    {
        $qb = new QueryBuilder();
        $users = $qb->table('users')
            ->where('id', 1, '>')
            ->getAll();

        $this->assertCount(1, $users->toArray());
        $this->assertEquals('Jane Doe', $users->toArray()[0]['name']);
    }

    public function testLimit()
    {
        $qb = new QueryBuilder();
        $users = $qb->table('users')->limit(1)->getAll();

        $this->assertCount(1, $users->toArray());
    }

    public function testOrderBy()
    {
        $qb = new QueryBuilder();
        $users = $qb->table('users')->order('id', 'DESC')->getAll();

        $data = $users->toArray();
        $this->assertEquals(2, $data[0]['id']);
        $this->assertEquals(1, $data[1]['id']);
    }
}
