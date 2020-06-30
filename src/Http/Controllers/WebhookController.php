<?php

namespace Frkcn\Kasiyer\Http\Controllers;

use Frkcn\Kasiyer\Kasiyer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Iyzipay\Model\Subscription\RetrieveSubscriptionCheckoutForm;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * Handle an Iyzico webhook call.
     *
     * @param Request $request
     */
    public function __invoke(Request $request)
    {
        $payload = Kasiyer::getCheckoutFormResult($request->token);

        $method = 'handle'.Str::studly($payload->getStatus());

        if (method_exists($this, $method)) {
            $this->{$method}($payload);

            return new Response('Webhook Handled');
        }

        return new Response();
    }

    /**
     * Handle subscription success.
     *
     * @param RetrieveSubscriptionCheckoutForm $payload
     */
    protected function handleSuccess(RetrieveSubscriptionCheckoutForm $payload)
    {
        //
    }

    protected function handleFailure()
    {
        //
    }
}
