<?php

namespace Frkcn\Kasiyer\Tests\Feature;

use Frkcn\Kasiyer\Subscription;

class WebhooksTest extends FeatureTestCase
{
    /** @test */
    public function it_can_handle_a_order_success_event()
    {
        $billable = $this->createBillable();

        $subscription = $billable->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => 'b0f6d38f-b2d1-4a72-9bf2-bc9375665f3a',
            'iyzico_plan' => '438bbdc4-ce41-4dfe-be5f-884fcc7f8f55',
            'iyzico_status' => Subscription::STATUS_PENDING,
        ]);

        $this->postJson('iyzico/webhook', [
            'orderReferenceCode'        => '9ed2d128-b106-464b-8170-84325e75703b',
            'customerReferenceCode'     => '042f0b61-079a-4a38-9454-6564a3c11a5a',
            'subscriptionReferenceCode' => 'b0f6d38f-b2d1-4a72-9bf2-bc9375665f3a',
            'iyziReferenceCode'         => 'aac139a9-43db-4f40-82dd-d4e5a77a3d2e',
            'iyziEventType'             => 'subscription.order.success',
            'iyziEventTime'             => 1579612261619,
        ])->assertOk();

        $this->assertDatabaseHas('subscriptions', [
            'iyzico_id' => 'b0f6d38f-b2d1-4a72-9bf2-bc9375665f3a',
            'iyzico_status' => Subscription::STATUS_ACTIVE,
        ]);
    }

    /** @test */
    public function it_can_handle_a_order_failed_event()
    {
        $billable = $this->createBillable();

        $subscription = $billable->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => 'b0f6d38f-b2d1-4a72-9bf2-bc9375665f3a',
            'iyzico_plan' => '438bbdc4-ce41-4dfe-be5f-884fcc7f8f55',
            'iyzico_status' => Subscription::STATUS_ACTIVE,
        ]);

        $this->postJson('iyzico/webhook', [
            'orderReferenceCode'        => '9ed2d128-b106-464b-8170-84325e75703b',
            'customerReferenceCode'     => '042f0b61-079a-4a38-9454-6564a3c11a5a',
            'subscriptionReferenceCode' => 'b0f6d38f-b2d1-4a72-9bf2-bc9375665f3a',
            'iyziReferenceCode'         => 'aac139a9-43db-4f40-82dd-d4e5a77a3d2e',
            'iyziEventType'             => 'subscription.order.failed',
            'iyziEventTime'             => 1579612261619,
        ])->assertOk();

        $this->assertDatabaseHas('subscriptions', [
            'iyzico_id' => 'b0f6d38f-b2d1-4a72-9bf2-bc9375665f3a',
            'iyzico_status' => Subscription::STATUS_PAST_DUE,
        ]);
    }
}
