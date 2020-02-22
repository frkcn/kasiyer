<?php

namespace Frkcn\Kasiyer\Tests\Integration;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Frkcn\Kasiyer\Tests\Fixtures\User;
use Frkcn\Kasiyer\Tests\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    /**
     * @var string
     */
    protected static $iyzicoPrefix = 'kasiyer-test-';

    protected function setUp(): void
    {
        parent::setUp();

        Eloquent::unguard();

        $this->loadLaravelMigrations();

        $this->artisan('migrate')->run();
    }

    protected function createCustomer($description = 'faruk'): User
    {
        return User::create([
            'email' => "{$description}@kasiyer-test.com",
            'name' => 'Faruk Can',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ]);
    }
}
