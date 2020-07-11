<?php

namespace Frkcn\Kasiyer;

use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\Locale;
use Iyzipay\Model\Subscription\RetrieveSubscriptionCheckoutForm;
use Iyzipay\Model\Subscription\SubscriptionPricingPlan;
use Iyzipay\Options;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Iyzipay\Request\RetrievePaymentRequest;
use Iyzipay\Request\Subscription\RetrieveSubscriptionCreateCheckoutFormRequest;
use Iyzipay\Request\Subscription\SubscriptionRetrievePricingPlanRequest;

class Kasiyer
{
    /**
     * The Kasiyer library version.
     *
     * @var string
     */
    const VERSION = '0.1.0';

    /**
     * The Iyzico API version.
     *
     * @var string
     */
    const IYZICO_VERSION = '2019-12-10';

    /**
     * Indicates if Kasiyer migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Get the default Iyzico API options.
     *
     * @return Options
     */
    public static function iyzicoOptions()
    {
        $options = new Options();
        $options->setApiKey(config("kasiyer.key"));
        $options->setSecretKey(config("kasiyer.secret"));
        $options->setBaseUrl(config("kasiyer.base_url"));

        return $options;
    }

    /**
     * Indicates if Kasiyer will mark past due subscriptions as inactive.
     *
     * @var bool
     */
    public static $deactivatePastDue = true;

    /**
     * Get Iyzico subscription checkout form result.
     *
     * @param string $token
     * @return RetrieveSubscriptionCheckoutForm
     */
    public static function getSubscriptionCheckoutFormResult(string $token)
    {
        $request = new RetrieveSubscriptionCreateCheckoutFormRequest();
        $request->setCheckoutFormToken($token);

        return RetrieveSubscriptionCheckoutForm::retrieve($request, self::iyzicoOptions());
    }

    /**
     * Get Iyzico generic checkout form result.
     *
     * @param string $token
     * @return CheckoutForm
     */
    public static function getCheckoutFormResult(string $token)
    {
        $request = new RetrieveCheckoutFormRequest();
        $request->setToken($token);

        return CheckoutForm::retrieve($request, self::iyzicoOptions());
    }

    /**
     * Retrieve given payment.
     *
     * @param $paymentId
     * @return \Iyzipay\Model\Payment
     */
    public static function getPayment($paymentId)
    {
        $request = new RetrievePaymentRequest();
        $request->setPaymentId($paymentId);

        return \Iyzipay\Model\Payment::retrieve($request, Kasiyer::iyzicoOptions());
    }

    /**
     * Get Iyzico plan for given reference code.
     *
     * @param string $referenceCode
     * @return SubscriptionPricingPlan
     */
    public static function plan(string $referenceCode)
    {
        $request = new SubscriptionRetrievePricingPlanRequest();
        $request->setPricingPlanReferenceCode($referenceCode);

        return SubscriptionPricingPlan::retrieve($request, self::iyzicoOptions());
    }

    /**
     * Configure Kasiyer to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Configure Kasiyer to maintain past due subscriptions as active.
     *
     * @return static
     */
    public static function keepPastDueSubscriptionsActive()
    {
        static::$deactivatePastDue = false;

        return new static;
    }
}
