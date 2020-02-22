<?php


namespace Frkcn\Kasiyer\Tests\Integration;


use Iyzipay\Model\Customer;

class CustomerTest extends IntegrationTestCase
{
    /** @test */
    public function create_customer_in_iyzico()
    {
        $user = $this->createCustomer();
        $iyzicoCustomer = $this->iyzicoCustomer($user->email);
        $customer = $user->createAsIyzicoCustomer($iyzicoCustomer);

        $this->assertEquals($user->email, $customer->getEmail());
    }
    
    /** @test */
    public function customers_in_iyzico_can_be_updated()
    {
        $user = $this->createCustomer('yunusemredeligoz');
        $iyzicoCustomer = $this->iyzicoCustomer($user->email);
        $user->createAsIyzicoCustomer($iyzicoCustomer);

        $iyzicoCustomer->setName('Yunus Emre');

        $customer = $user->updateIyzicoCustomer($iyzicoCustomer);

        $this->assertEquals('Yunus Emre', $customer->getName());
    }

    private function iyzicoCustomer($email): Customer
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
}
