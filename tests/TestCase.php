<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * SAFETY GUARD: Abort if RefreshDatabase is used with MySQL.
     * RefreshDatabase runs migrate:fresh which DROPS ALL TABLES.
     * This has caused data loss twice. Never again.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $uses = class_uses_recursive(static::class);

        if (isset($uses[\Illuminate\Foundation\Testing\RefreshDatabase::class])) {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver !== 'sqlite') {
                throw new \RuntimeException(
                    "ABORT: RefreshDatabase is being used with '{$driver}' (connection: '{$connection}'). "
                    . "RefreshDatabase DROPS ALL TABLES. Use DatabaseTransactions instead, or configure SQLite."
                );
            }
        }
    }
}
