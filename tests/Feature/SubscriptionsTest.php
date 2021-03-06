<?php

namespace Frkcn\Kasiyer\Tests\Feature;

use Carbon\Carbon;
use Frkcn\Kasiyer\Subscription;

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

    /** @test */
    public function customers_can_check_if_they_are_on_a_generic_trial()
    {
        $billable = $this->createBillable('faruk', ['trial_ends_at' => Carbon::tomorrow()]);

        $this->assertTrue($billable->onGenericTrial());
        $this->assertTrue($billable->onTrial());
        $this->assertFalse($billable->onTrial('basic'));
    }

    /** @test */
    public function customers_can_check_if_their_subscription_is_on_trial()
    {
        $billable = $this->createBillable('faruk');

        $subscription = $billable->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => '72d67bd0-dcfe-4ca5-8a3d-bd544207fa23',
            'iyzico_plan' => '438bbdc4-ce41-4dfe-be5f-884fcc7f8f55',
            'iyzico_status' => Subscription::STATUS_ACTIVE,
            'trial_ends_at' => Carbon::tomorrow(),
        ]);

        $this->assertTrue($billable->subscribed('basic'));
        $this->assertFalse($billable->subscribed('default'));
        $this->assertFalse($billable->subscribedToPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f55'));
        $this->assertTrue($billable->subscribedToPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f55', 'basic'));
        $this->assertTrue($billable->onPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f55'));
        $this->assertFalse($billable->onPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f52'));
        $this->assertTrue($billable->onTrial('basic'));
        $this->assertTrue($billable->onTrial('basic', '438bbdc4-ce41-4dfe-be5f-884fcc7f8f55'));
        $this->assertFalse($billable->onTrial('basic', '438bbdc4-ce41-4dfe-be5f-884fcc7f8f52'));
        $this->assertFalse($billable->onGenericTrial());

        $this->assertTrue($subscription->valid());
        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->onTrial());
        $this->assertFalse($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertFalse($subscription->recurring());
        $this->assertFalse($subscription->ended());
    }

    /** @test */
    public function customers_can_check_if_their_subscription_is_cancelled()
    {
        $billable = $this->createBillable('faruk');

        $subscription = $billable->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => '72d67bd0-dcfe-4ca5-8a3d-bd544207fa23',
            'iyzico_plan' => '438bbdc4-ce41-4dfe-be5f-884fcc7f8f55',
            'iyzico_status' => Subscription::STATUS_CANCELED,
            'ends_at' => Carbon::tomorrow(),
        ]);

        $this->assertTrue($subscription->valid());
        $this->assertTrue($subscription->active());
        $this->assertFalse($subscription->onTrial());
        $this->assertTrue($subscription->cancelled());
        $this->assertTrue($subscription->onGracePeriod());
        $this->assertFalse($subscription->recurring());
        $this->assertFalse($subscription->ended());
    }

    /** @test */
    public function customers_can_check_if_the_grace_period_is_over()
    {
        $billable = $this->createBillable('faruk');

        $subscription = $billable->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => '72d67bd0-dcfe-4ca5-8a3d-bd544207fa23',
            'iyzico_plan' => '438bbdc4-ce41-4dfe-be5f-884fcc7f8f55',
            'iyzico_status' => Subscription::STATUS_CANCELED,
            'ends_at' => Carbon::yesterday(),
        ]);

        $this->assertFalse($subscription->valid());
        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->onTrial());
        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->ended());
    }

    public function test_subscriptions_can_retrieve_their_payment_info()
    {
        $this->markTestSkipped();

        $billable = $this->createBillable('faruk');

        $subscription = $billable->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => 'b28c1303-ab11-4056-819b-aecf9ce33829',
            'iyzico_plan' => 'ba304be8-f17a-4f23-8500-f7d47b6927a1',
            'iyzico_status' => Subscription::STATUS_ACTIVE,
        ]);

        $this->assertSame('MASTER_CARD', $subscription->cardBrand());
        $this->assertSame('0003', $subscription->cardLastFour());
    }
}
