<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $database = $app['config']->get('database.connections.mysql.database');
        if (!is_string($database) || !str_contains($database, '_test_')) {
            throw new \RuntimeException('Tests require an isolated MySQL database with _test_ in its name.');
        }
        return $app;
    }
}
