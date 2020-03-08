<?php


namespace Frkcn\Kasiyer\Tests\Integration;


use Illuminate\Support\Str;
use Iyzipay\Model\Subscription\SubscriptionPricingPlan;
use Iyzipay\Model\Subscription\SubscriptionProduct;
use Iyzipay\Request\Subscription\SubscriptionCreatePricingPlanRequest;
use Iyzipay\Request\Subscription\SubscriptionCreateProductRequest;
use Iyzipay\Request\Subscription\SubscriptionDeletePricingPlanRequest;
use Iyzipay\Request\Subscription\SubscriptionDeleteProductRequest;

class SubscriptionsTest extends IntegrationTestCase
{
    /**
     * @var string
     */
    protected static $planId;

    /**
     * @var string
     */
    protected static $productId;

    /**
     * @var string
     */
    protected static $subscriptionEmail;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $productName = static::$iyzicoPrefix.'product-1'.Str::random(10);
        $planName = static::$iyzicoPrefix.'monthly-10-'.Str::random(10);

        $productRequest = new SubscriptionCreateProductRequest();
        $productRequest->setName($productName);

        static::$productId = SubscriptionProduct::create($productRequest, static::$options)->getReferenceCode();

        $planRequest = new SubscriptionCreatePricingPlanRequest();
        $planRequest->setProductReferenceCode(static::$productId);
        $planRequest->setName($planName);
        $planRequest->setPrice('10.0');
        $planRequest->setCurrencyCode('USD');
        $planRequest->setPaymentInterval('MONTHLY');
        $planRequest->setTrialPeriodDays(14);
        $planRequest->setRecurrenceCount(5);
        $planRequest->setPlanPaymentType('RECURRING');

        static::$planId = SubscriptionPricingPlan::create($planRequest, static::$options)->getReferenceCode();
        static::$subscriptionEmail = Str::random(10);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $planRequest = new SubscriptionDeletePricingPlanRequest();
        $planRequest->setPricingPlanReferenceCode(static::$planId);
        SubscriptionPricingPlan::delete($planRequest, static::$options);

        $productRequest = new SubscriptionDeleteProductRequest();
        $productRequest->setProductReferenceCode(static::$productId);
        SubscriptionProduct::delete($productRequest, static::$options);
    }

    /** @test */
    public function subscription_checkout_form_can_be_initialized()
    {
        $user = $this->createCustomer(static::$subscriptionEmail);
        $iyzicoCustomer = $this->iyzicoCustomer($user->email);

        $response = $user->newSubscription('main', static::$planId)
            ->init($iyzicoCustomer);

        $this->assertEquals("success", $response->getStatus());
    }
    
    /** @test */
    public function retrieve_subscribtion_checkout_form_with_success()
    {
        $this->markTestSkipped();

        $user = $this->createCustomer(static::$subscriptionEmail);

        $response = $user->newSubscription('main', static::$planId)
            ->successful('817ff890-424c-4e15-b190-d178834750cc');

        $this->assertEquals("success", $response->getStatus());
    }
    
    /** @test */
    public function create_subscription_with_api()
    {
        $user = $this->createCustomer(static::$subscriptionEmail);
        $iyzicoCustomer = $this->iyzicoCustomer($user->email);

        // Create subscription.
        $user->newSubscription('main', static::$planId)
            ->setCustomer($iyzicoCustomer)
            ->create($this->paymentCard());

        $this->assertEquals(1, count($user->subscriptions));
        $this->assertNotNull($user->subscription('main')->iyzico_id);

        $this->assertTrue($user->subscribed('main'));
        $this->assertTrue($user->subscribed('main', static::$planId));
        $this->assertTrue($user->subscription('main')->active());

        $subscription = $user->subscription('main');
        $subscription->cancel();

        $this->assertFalse($subscription->active());
        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->ended());


    }
}
