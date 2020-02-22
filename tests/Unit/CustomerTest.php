<?php


namespace Frkcn\Kasiyer\Tests\Unit;


use Frkcn\Kasiyer\Exceptions\InvalidIyzicoCustomer;
use Frkcn\Kasiyer\Tests\Fixtures\User;
use Frkcn\Kasiyer\Tests\TestCase;

class CustomerTest extends TestCase
{
    /** @test */
    public function iyzico_customer_method_throws_exception_when_iyzico_id_is_not_set()
    {
        $user = new User;

        $this->expectException(InvalidIyzicoCustomer::class);

        $user->asIyzicoCustomer();
    }
}
