<?php

namespace Frkcn\Kasiyer\Tests\Feature;

use Frkcn\Kasiyer\KasiyerServiceProvider;
use Frkcn\Kasiyer\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase;

abstract class FeatureTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        $this->artisan('migrate')->run();
    }

    protected function createBillable($description = 'faruk', array $options = []): User
    {
        $user = $this->createUser($description);

        $user->customer()->create(array_merge([
            'iyzico_id' => '72d67bd0-dcfe-4ca5-8a3d-bd544207fa23',
            'iyzico_email' => $user->email,
            'name' => 'Faruk',
            'surname' => 'Can',
            'gsm_number' => '+905555555555',
            'identity_number' => '11111111111',
            'shipping_contact_name' => $user->name,
            'shipping_city' => 'Istanbul',
            'shipping_country' => 'Turkey',
            'shipping_address' => 'Besiktas Nisbetiye Mahallesi',
            'shipping_zip_code' => '34340',
            'billing_contact_name' => $user->name,
            'billing_city' => 'Istanbul',
            'billing_country' => 'Turkey',
            'billing_address' => 'Besiktas Nisbetiye Mahallesi',
            'billing_zip_code' => '34340',
        ], $options));

        return $user;
    }

    protected function createUser($description = 'faruk', array $options = []): User
    {
        return User::create(array_merge([
            'email' => "{$description}@iyzico-test.com",
            'name' => 'Faruk Can',
            'password' => '$2y$12$rFXO/CyMUQ2mqqmDnBHClOqIGS7UIZGIHtOLFd6X6EQ5k1eJgWWzS',
        ]), $options);
    }

    protected function getPackageProviders($app)
    {
        return [KasiyerServiceProvider::class];
    }
}
