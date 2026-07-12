<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        // A cached config is a frozen array: it ignores the env() overrides in phpunit.xml,
        // so database.default stays mysql and RefreshDatabase wipes the real database.
        // This has happened once. Fail loudly instead.
        if (file_exists($app->getCachedConfigPath())) {
            throw new RuntimeException(
                'Refusing to run tests with a cached config: it would point them at the real database. '
                .'Run `php artisan config:clear` first.'
            );
        }

        $app->make(Kernel::class)->bootstrap();

        $this->assertTestDatabaseIsDisposable($app);

        return $app;
    }

    /**
     * Belt and braces: whatever the config says, never let a test suite that truncates
     * tables point at the application database.
     */
    private function assertTestDatabaseIsDisposable($app): void
    {
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($database === 'zarinalabs') {
            throw new RuntimeException(
                "Refusing to run tests against the real `zarinalabs` database (connection [{$connection}]). "
                .'Tests must use the sqlite in-memory connection from phpunit.xml.'
            );
        }
    }
}
