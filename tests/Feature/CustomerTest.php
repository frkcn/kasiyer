<?php

namespace Frkcn\Kasiyer\Tests\Feature;

use Carbon\Carbon;
use Frkcn\Kasiyer\Subscription;

class CustomerTest extends FeatureTestCase
{
    /** @test */
    public function billable_models_can_create_a_customer_record()
    {
        $this->withoutExceptionHandling();
        $user = $this->createUser();

        $customer = $user->createAsCustomer(['trial_ends_at' => $trialEndsAt = now()->addWeeks(2)]);

        $this->assertSame($trialEndsAt->timestamp, $customer->trial_ends_at->timestamp);
        $this->assertTrue($user->onGenericTrial());
    }

    /** @test */
    public function billable_models_methods_without_customer()
    {
        $user = $this->createUser();

        $this->assertFalse($user->onTrial());
        $this->assertFalse($user->onGenericTrial());
        $this->assertFalse($user->onPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f55'));
        $this->assertFalse($user->subscribed());
        $this->assertFalse($user->subscribedToPlan('438bbdc4-ce41-4dfe-be5f-884fcc7f8f55'));
        $this->assertEmpty($user->subscriptions());
        $this->assertNull($user->subscription());
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

        $subscription = $billable->customer->subscriptions()->create([
            'name' => 'basic',
            'iyzico_id' => '72d67bd0-dcfe-4ca5-8a3d-bd544207fa23',
            'iyzico_plan' => '438bbdc4-ce41-4dfe-be5f-884fcc7f8f55',
            'iyzico_status' => 'ACTIVE',
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

        $subscription = $billable->customer->subscriptions()->create([
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

        $subscription = $billable->customer->subscriptions()->create([
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
}
