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
        $connection = $app['config']->get('database.default');
        $driver = $app['config']->get("database.connections.{$connection}.driver", $connection);

        // SQLite (including :memory:) is always allowed.
        if ($driver === 'sqlite' || in_array($connection, ['sqlite', 'sqlite_testing'], true)) {
            return;
        }

        // For MySQL/PostgreSQL, database name MUST end with '_test'.
        $dbName = (string) $app['config']->get("database.connections.{$connection}.database", '');

        if (!str_ends_with($dbName, '_test')) {
            throw new \RuntimeException(
                PHP_EOL .
                '╔══════════════════════════════════════════════════════════════╗' . PHP_EOL .
                '║  ABORTING: refusing to run tests against a non-test DB!      ║' . PHP_EOL .
                '║                                                              ║' . PHP_EOL .
                '║  Connection : ' . str_pad($connection, 47) . '║' . PHP_EOL .
                '║  Database   : ' . str_pad($dbName,     47) . '║' . PHP_EOL .
                '║                                                              ║' . PHP_EOL .
                '║  The database name must end with "_test" (e.g.              ║' . PHP_EOL .
                '║  "corevisys_test"). Set DB_DATABASE in phpunit.xml or env.   ║' . PHP_EOL .
                '╚══════════════════════════════════════════════════════════════╝' . PHP_EOL
            );
        }
    }
}
