<?php

namespace Frkcn\Kasiyer\Tests\Integration;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Frkcn\Kasiyer\Tests\Fixtures\User;
use Frkcn\Kasiyer\Tests\TestCase;
use Iyzipay\Model\Customer;
use Iyzipay\Model\PaymentCard;
use Iyzipay\Options;

abstract class IntegrationTestCase extends TestCase
{
    /**
     * @var string
     */
    protected static $iyzicoPrefix = 'kasiyer-test-';

    /**
     * @var \Iyzipay\Options
     */
    protected static $options;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $options = new Options();
        $options->setApiKey(getenv('IYZICO_KEY'));
        $options->setSecretKey(getenv('IYZICO_SECRET'));
        $options->setBaseUrl(getenv('KASIYER_URL'));

        static::$options = $options;
    }

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

    protected function iyzicoCustomer($email): Customer
    {
        $customer = new Customer();
        $customer->setName('Faruk');
        $customer->setSurname('Can');
        $customer->setGsmNumber('+905555555555');
        $customer->setEmail($email);
        $customer->setIdentityNumber('11111111111');
        $customer->setShippingContactName('Faruk Can');
        $customer->setShippingCity('Istanbul');
        $customer->setShippingCountry('Turkey');
        $customer->setShippingAddress('Beyoglu Huseyinaga Mahallesi');
        $customer->setShippingZipCode('34435');
        $customer->setBillingContactName('Faruk Can');
        $customer->setBillingCity('Istanbul');
        $customer->setBillingCountry('Turkey');
        $customer->setBillingAddress('Beyoglu Huseyinaga Mahallesi');
        $customer->setBillingZipCode('34435');

        return $customer;
    }

    protected function paymentCard(): PaymentCard
    {
        $paymentCard = new PaymentCard();
        $paymentCard->setCardHolderName("John Doe");
        $paymentCard->setCardNumber("5400010000000004"); // Non-Turkish Credit Card
        $paymentCard->setExpireMonth("12");
        $paymentCard->setExpireYear("2030");
        $paymentCard->setCvc("123");
        $paymentCard->setRegisterConsumerCard(true);

        return $paymentCard;
    }
}
