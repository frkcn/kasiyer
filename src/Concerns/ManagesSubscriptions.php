<?php

namespace Frkcn\Kasiyer\Concerns;

use Frkcn\Kasiyer\Customer;
use Frkcn\Kasiyer\Kasiyer;
use Frkcn\Kasiyer\SubscriptionBuilder;
use Illuminate\Database\Eloquent\Collection;
use Iyzipay\Model\Subscription\RetrieveSubscriptionCheckoutForm;
use Iyzipay\Request\Subscription\RetrieveSubscriptionCreateCheckoutFormRequest;

trait ManagesSubscriptions
{
    /**
     * Begin creating new subscription.
     *
     * @param $name
     * @param $plan
     * @return SubscriptionBuilder
     */
    public function newSubscription($name, $plan)
    {
        return new SubscriptionBuilder($this, $name, $plan);
    }

    /**
     * Get all of the subscriptions for the Iyzico model.
     *
     * @return \Frkcn\Kasiyer\Subscription[]|\Illuminate\Database\Eloquent\Collection
     */
    public function subscriptions()
    {
        if (is_null($this->customer)) {
            return new Collection;
        }

        return $this->customer->subscriptions();
    }

    /**
     * Get a subscription instance by name.
     *
     * @param  string  $name
     * @return \Frkcn\Kasiyer\Subscription|null
     */
    public function subscription($name = 'default')
    {
        return optional($this->customer)->subscription($name);
    }

    /**
     * Determine if the Billable model has a given subscription.
     *
     * @param string $name
     * @param null $plan
     * @return bool
     */
    public function subscribed($name = 'default', $plan = null)
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    /**
     * Determine if the Billable model is actively subscribed to one of the given plans.
     *
     * @param string|array $plan
     * @param string $name
     * @return bool
     */
    public function subscribedToPlan($plan, $name = 'default')
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $subscription->hasPlan($plan);
    }

    /**
     * Determine if the entity has a valid subscription on the given plan.
     *
     * @param  int  $plan
     * @return bool
     */
    public function onPlan($plan)
    {
        return $this->customer->onPlan($plan);
    }

    /**
     * Handle checkout form result.
     *
     * @param string $token
     * @return bool
     */
    public function handleSubscription(string $token)
    {
        $result = Kasiyer::getCheckoutFormResult($token);

        if ($result->getStatus() == "success") {
            $plan = Kasiyer::plan($result->getPricingPlanReferenceCode());

            $this->customer->subscriptions()->create([
                'name' => $plan->getName(),
                'iyzico_id' => $result->getReferenceCode(),
                'iyzico_plan' => $result->getPricingPlanReferenceCode(),
                'iyzico_status' => $result->getSubscriptionStatus(),
                'trial_ends_at' => $result->getTrialEndDate(),
            ]);

            return true;
        }

        return false;
    }
}
