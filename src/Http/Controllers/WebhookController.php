<?php

namespace Frkcn\Kasiyer\Http\Controllers;

use Frkcn\Kasiyer\Events\WebhookHandled;
use Frkcn\Kasiyer\Events\WebhookReceived;
use Frkcn\Kasiyer\Kasiyer;
use Frkcn\Kasiyer\Subscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Iyzipay\Model\Subscription\RetrieveSubscriptionCheckoutForm;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * Handle an Iyzico webhook call.
     *
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request)
    {
        $payload = $request->all();

        $method = 'handle' . Str::studly(str_replace('.', '', $payload['iyziEventType']));

        WebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            $this->{$method}($payload);

            WebhookHandled::dispatch($payload);

            return new Response('Webhook Handled');
        }

        return new Response();
    }

    /**
     * Handle subscription order success.
     *
     * @param array $payload
     */
    protected function handleSubscriptionOrderSuccess(array $payload)
    {
        if ($subscription = Subscription::firstWhere('iyzico_id', $payload['subscriptionReferenceCode'])) {
            $subscription->iyzico_status = Subscription::STATUS_ACTIVE;

            $subscription->save();
        }
    }

    /**
     * Handle subscription order failed.
     *
     * @param array $payload
     */
    protected function handleSubscriptionOrderFailed(array $payload)
    {
        if ($subscription = Subscription::firstWhere('iyzico_id', $payload['subscriptionReferenceCode'])) {
            $subscription->iyzico_status = Subscription::STATUS_PAST_DUE;

            $subscription->save();
        }
    }
}
