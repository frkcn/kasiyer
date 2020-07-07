<?php

namespace Frkcn\Kasiyer\Tests\Feature;

use Frkcn\Kasiyer\Subscription;

class TransactionTest extends FeatureTestCase
{
    /** @test */
    public function it_can()
    {
        $billable = $this->createBillable('faruk');

        $subscription = $billable->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => 'b28c1303-ab11-4056-819b-aecf9ce33829',
            'iyzico_plan' => 'ba304be8-f17a-4f23-8500-f7d47b6927a1',
            'iyzico_status' => Subscription::STATUS_ACTIVE,
        ]);

        $foo = $billable->transactions();
    }
}
