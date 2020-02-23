<?php


namespace Frkcn\Kasiyer\Tests\Integration;

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

    /** @test */
    public function retrieve_customers_in_iyzico()
    {
        $user = $this->createCustomer('hakanozdemir');
        $iyzicoCustomer = $this->iyzicoCustomer($user->email);
        $user->createAsIyzicoCustomer($iyzicoCustomer);

        $customer = $user->asIyzicoCustomer();

        $this->assertEquals($user->email, $customer->getEmail());
    }
}
