<?php

namespace Frkcn\Kasiyer;

use Frkcn\Kasiyer\Exceptions\SubscriptionCancelFailure;
use Illuminate\Database\Eloquent\Model;
use Iyzipay\Model\Subscription\SubscriptionCancel;
use Iyzipay\Model\Subscription\SubscriptionDetails;
use Iyzipay\Model\Subscription\SubscriptionUpgrade;
use Iyzipay\Request\Subscription\SubscriptionCancelRequest;
use Iyzipay\Request\Subscription\SubscriptionDetailsRequest;
use Iyzipay\Request\Subscription\SubscriptionUpgradeRequest;

class Subscription extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'trial_ends_at', 'ends_at',
        'created_at', 'updated_at',
    ];

    /**
     * Iyzico subscription status active.
     */
    const STATUS_ACTIVE = "ACTIVE";

    /**
     * Iyzico subscription status pending.
     */
    const STATUS_PENDING = "PENDING";

    /**
     * Iyzico subscription status canceled.
     */
    const STATUS_CANCELED = "CANCELED";

    /**
     * Get the user that owns the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->owner();
    }

    /**
     * Get the model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $model = config('kasiyer.model');

        return $this->belongsTo($model, (new $model)->getForeignKey());
    }

    /**
     * Determine if the subscription is active, on trial.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->active() || $this->onTrial();
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active()
    {
        return is_null($this->ends_at) && $this->iyzico_status !== self::STATUS_PENDING;
    }

    /**
     * Determine if the subscription is within its trial period.
     *
     * @return bool
     */
    public function onTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Cancel the subscription at the moment.
     *
     * @return $this
     */
    public function cancel()
    {
        $subscription = $this->asIyzicoSubscription();

        $request = new SubscriptionCancelRequest();
        $request->setSubscriptionReferenceCode($subscription->getReferenceCode());

        $subscription = SubscriptionCancel::cancel($request, $this->owner->iyzicoOptions());

        $this->iyzico_status = self::STATUS_CANCELED;
        $this->ends_at = $subscription->getSystemTime();

        $this->save();

        return $this;
    }

    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled()
    {
        return !is_null($this->ends_at);
    }

    /**
     * Determine if the subscription is recurring and not on trial.
     *
     * @return bool
     */
    public function recurring()
    {
        return !$this->onTrial() && ! $this->cancelled();
    }

    /**
     * Determine if the subscription has ended.
     *
     * @return bool
     */
    public function ended()
    {
        return $this->cancelled();
    }

    /**
     * Swap the subscription to a new Iyzico plan.
     *
     * @param $plan
     * @return $this
     */
    public function swap($plan)
    {
        $subscription = $this->asIyzicoSubscription();

        $request = new SubscriptionUpgradeRequest();
        $request->setSubscriptionReferenceCode($subscription->getReferenceCode());
        $request->setNewPricingPlanReferenceCode($plan);
        $request->setUpgradePeriod("NOW");

        // If user on trial include trial to the new plan.
        if ($this->onTrial()) {
            $request->setUseTrial(true);
        } else {
            $request->setUseTrial(false);
        }

        $subscription = SubscriptionUpgrade::update($request, $this->owner->iyzicoOptions());

        $this->fill([
            'iyzico_id' => $subscription->getReferenceCode(),
            'iyzico_plan' => $plan,
            'ends_at' => null,
        ])->save();

        return $this;

    }

    /**
     * Get the subscription as a Iyzico subscription details object.
     *
     * @return SubscriptionDetails
     */
    public function asIyzicoSubscription()
    {
        $request = new SubscriptionDetailsRequest();
        $request->setSubscriptionReferenceCode($this->iyzico_id);

        return SubscriptionDetails::retrieve($request, $this->owner->iyzicoOptions());
    }
}
