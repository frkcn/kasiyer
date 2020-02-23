<?php


namespace Frkcn\Kasiyer\Tests\Integration;


use Frkcn\Kasiyer\Kasiyer;
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
        $planRequest->setRecurrenceCount(5);
        $planRequest->setPlanPaymentType('RECURRING');

        static::$planId = SubscriptionPricingPlan::create($planRequest, static::$options)->getReferenceCode();
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
        $user = $this->createCustomer('subscription');
        $iyzicoCustomer = $this->iyzicoCustomer($user->email);
        $user->createAsIyzicoCustomer($iyzicoCustomer);

        $response = $user->newSubscription(static::$planId)->create();

        $this->assertNotNull($response->getCheckoutFormContent());
    }
}
