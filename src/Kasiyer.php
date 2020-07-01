<?php

namespace Frkcn\Kasiyer;

use Iyzipay\Model\Subscription\RetrieveSubscriptionCheckoutForm;
use Iyzipay\Model\Subscription\SubscriptionPricingPlan;
use Iyzipay\Options;
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
    public static function getCheckoutFormResult(string $token)
    {
        $request = new RetrieveSubscriptionCreateCheckoutFormRequest();
        $request->setCheckoutFormToken($token);

        return RetrieveSubscriptionCheckoutForm::retrieve($request, self::iyzicoOptions());
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
