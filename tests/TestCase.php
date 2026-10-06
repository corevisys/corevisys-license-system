<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application and verifies immediately that tests are not
     * running against a production/staging database.
     *
     * IMPORTANT: This guard MUST run inside createApplication() right after
     * bootstrapping and BEFORE any traits (such as RefreshDatabase) boot and
     * execute migrate:fresh.
     *
     * Rules:
     *   - SQLite (including :memory: and test files) is always allowed.
     *   - MySQL / PostgreSQL (or any other non-SQLite connection) is allowed
     *     ONLY if the database name ends with '_test'.
     *
     * Throws a RuntimeException (never terminates process via exit) so PHPUnit can report the
     * error cleanly.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $this->guardAgainstProductionDatabase($app);

        return $app;
    }

    protected function guardAgainstProductionDatabase(Application $app): void
    {
        $connection = (string) $app['config']->get('database.default');
        $driver = (string) $app['config']->get("database.connections.{$connection}.driver", $connection);
        $dbName = (string) $app['config']->get("database.connections.{$connection}.database", '');

        $isSqlite = $driver === 'sqlite' || in_array($connection, ['sqlite', 'sqlite_testing'], true);
        $isSqliteInMemory = $isSqlite && $dbName === ':memory:';
        $nameEndsWithTest = str_ends_with($dbName, '_test');

        if ($isSqliteInMemory || $nameEndsWithTest) {
            return;
        }

        throw new \RuntimeException(
            PHP_EOL .
            '╔══════════════════════════════════════════════════════════════╗' . PHP_EOL .
            '║  ABORTING: refusing to run tests against a non-test DB!      ║' . PHP_EOL .
            '║                                                              ║' . PHP_EOL .
            '║  Connection : ' . str_pad($connection, 47) . '║' . PHP_EOL .
            '║  Database   : ' . str_pad($dbName,     47) . '║' . PHP_EOL .
            '║                                                              ║' . PHP_EOL .
            '║  The database must be SQLite in-memory (:memory:) or its    ║' . PHP_EOL .
            '║  name must end with "_test" (e.g. "corevisys_test").         ║' . PHP_EOL .
            '║  Set DB_DATABASE in phpunit.xml or env.                      ║' . PHP_EOL .
            '╚══════════════════════════════════════════════════════════════╝' . PHP_EOL
        );
    }
}
