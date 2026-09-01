<?php

namespace SocialiteProviders\Teleserv\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use SocialiteProviders\Manager\ServiceProvider as ManagerServiceProvider;
use SocialiteProviders\Teleserv\HydraServiceProvider;
use SocialiteProviders\Teleserv\Tests\Fixtures\User;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ManagerServiceProvider::class,
            HydraServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('hydra.user', User::class);
        $app['config']->set('services.teleserv', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'redirect' => 'https://app.example.test/auth/teleserv/callback',
            'base' => 'https://hydra.example.test',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->createUsersTable();
    }

    /**
     * @param  list<string>  $columns
     */
    protected function createUsersTable(array $columns = [
        'first_name',
        'middle_name',
        'last_name',
        'employee_code',
    ]): void
    {
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) use ($columns): void {
            $table->unsignedBigInteger('id')->primary();

            foreach ($columns as $column) {
                $table->string($column)->nullable();
            }

            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
