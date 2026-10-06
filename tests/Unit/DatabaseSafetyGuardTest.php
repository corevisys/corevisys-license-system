<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\TestCase;

class DatabaseSafetyGuardTest extends BaseTestCase
{
    private function invokeGuard(string $connection, string $driver, string $dbName): ?\Throwable
    {
        $testCase = new class('test') extends TestCase {
            public function __construct(?string $name = null)
            {
                parent::__construct($name ?? 'test');
            }

            public function runGuard(string $connection, string $driver, string $dbName): void
            {
                $config = new class($connection, $driver, $dbName) {
                    public function __construct(
                        private string $connection,
                        private string $driver,
                        private string $dbName
                    ) {}

                    public function get(string $key, $default = null)
                    {
                        if ($key === 'database.default') {
                            return $this->connection;
                        }
                        if ($key === "database.connections.{$this->connection}.driver") {
                            return $this->driver;
                        }
                        if ($key === "database.connections.{$this->connection}.database") {
                            return $this->dbName;
                        }
                        return $default;
                    }
                };

                $appObj = \Mockery::mock(\Illuminate\Foundation\Application::class);
                $appObj->shouldReceive('offsetGet')->with('config')->andReturn($config);

                $refMethod = new \ReflectionMethod(TestCase::class, 'guardAgainstProductionDatabase');
                $refMethod->setAccessible(true);
                $refMethod->invoke($this, $appObj);
            }
        };

        try {
            $testCase->runGuard($connection, $driver, $dbName);
            return null;
        } catch (\Throwable $e) {
            return $e;
        }
    }

    public function test_sqlite_in_memory_passes_guard(): void
    {
        $error = $this->invokeGuard('sqlite', 'sqlite', ':memory:');
        $this->assertNull($error, 'sqlite in-memory must be allowed.');
    }

    public function test_mysql_ending_with_test_passes_guard(): void
    {
        $error = $this->invokeGuard('mysql', 'mysql', 'corevisys_test');
        $this->assertNull($error, 'mysql ending with _test must be allowed.');
    }

    public function test_sqlite_file_database_throws_runtime_exception(): void
    {
        $error = $this->invokeGuard('sqlite', 'sqlite', '/path/to/database.sqlite');
        $this->assertInstanceOf(\RuntimeException::class, $error, 'sqlite file database must be rejected.');
        $this->assertStringContainsString('ABORTING: refusing to run tests against a non-test DB!', $error->getMessage());
    }

    public function test_mysql_non_test_database_throws_runtime_exception(): void
    {
        $error = $this->invokeGuard('mysql', 'mysql', 'corevisys');
        $this->assertInstanceOf(\RuntimeException::class, $error, 'mysql non-test database must be rejected.');
        $this->assertStringContainsString('ABORTING: refusing to run tests against a non-test DB!', $error->getMessage());
    }

    public function test_mysql_production_database_throws_runtime_exception(): void
    {
        $error = $this->invokeGuard('mysql', 'mysql', 'corevisys_production');
        $this->assertInstanceOf(\RuntimeException::class, $error, 'mysql production database must be rejected.');
        $this->assertStringContainsString('ABORTING: refusing to run tests against a non-test DB!', $error->getMessage());
    }
}
