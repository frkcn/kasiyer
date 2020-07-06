<?php

namespace Frkcn\Kasiyer\Tests\Feature;

class SubscriptionsTest extends FeatureTestCase
{
    /** @test */
    public function customers_can_perform_subscription_checks()
    {
        $billable = $this->createBillable();

        $subscription = $billable->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => '72d67bd0-dcfe-4ca5-8a3d-bd544207fa23',
            'iyzico_plan' => '438bbdc4-ce41-4dfe-be5f-884fcc7f8f55',
            'iyzico_status' => 'ACTIVE',
        ]);

        $this->assertTrue($billable->subscribed('basic'));
        $this->assertFalse($billable->subscribed('default'));
        $this->assertFalse($billable->subscribedToPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f52'));
        $this->assertTrue($billable->subscribedToPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f55', 'basic'));
        $this->assertTrue($billable->onPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f55'));
        $this->assertFalse($billable->onPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f52'));
        $this->assertFalse($billable->onTrial('basic'));
        $this->assertFalse($billable->onGenericTrial());

        $this->assertTrue($subscription->valid());
        $this->assertTrue($subscription->active());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertTrue($subscription->recurring());
        $this->assertFalse($subscription->ended());
    }
}
