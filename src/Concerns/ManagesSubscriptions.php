<?php

namespace Frkcn\Kasiyer\Concerns;

use Frkcn\Kasiyer\Kasiyer;
use Frkcn\Kasiyer\Subscription;
use Frkcn\Kasiyer\SubscriptionBuilder;
use Illuminate\Database\Eloquent\Collection;

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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'billable')->orderByDesc('created_at');
    }

    /**
     * Get a subscription instance by name.
     *
     * @param  string  $name
     * @return \Frkcn\Kasiyer\Subscription|null
     */
    public function subscription($name = 'default')
    {
        return $this->subscriptions()->where('name', $name)->first();
    }

    /**
     * Determine if the Billable model is on trial.
     *
     * @param string $name
     * @param null $plan
     * @return bool
     */
    public function onTrial($name = 'default', $plan = null)
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->onTrial()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    /**
     * Determine if the Billable model is on a "generic" trial at the model level.
     *
     * @return bool
     */
    public function onGenericTrial()
    {
        if (is_null($this->customer)) {
            return false;
        }

        return $this->customer->onGenericTrial();
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
     * @param  string  $plan
     * @return bool
     */
    public function onPlan($plan)
    {
        return !is_null($this->subscriptions()
            ->where('iyzico_plan', $plan)
            ->get()
            ->first(function (Subscription $subscription) use ($plan) {
                return $subscription->valid();
            }));
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
