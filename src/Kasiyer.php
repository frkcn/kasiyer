<?php

namespace Frkcn\Kasiyer;

use Iyzipay\Model\Subscription\RetrieveSubscriptionCheckoutForm;
use Iyzipay\Options;
use Iyzipay\Request\Subscription\RetrieveSubscriptionCreateCheckoutFormRequest;

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
     * Get Iyzico subscription checkout form result.
     *
     * @param string $token
     * @return RetrieveSubscriptionCheckoutForm
     */
    public static function getCheckoutFormResult(string $token)
    {
        $request = new RetrieveSubscriptionCreateCheckoutFormRequest();
        $request->setCheckoutFormToken($token);

        return RetrieveSubscriptionCheckoutForm::retrieve($request, Kasiyer::iyzicoOptions());
    }
}
