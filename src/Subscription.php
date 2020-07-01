<?php

namespace Frkcn\Kasiyer;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Iyzipay\Model\Subscription\SubscriptionCancel;
use Iyzipay\Model\Subscription\SubscriptionDetails;
use Iyzipay\Model\Subscription\SubscriptionUpgrade;
use Iyzipay\Request\Subscription\SubscriptionCancelRequest;
use Iyzipay\Request\Subscription\SubscriptionDetailsRequest;
use Iyzipay\Request\Subscription\SubscriptionUpgradeRequest;

class Subscription extends Model
{
    const STATUS_ACTIVE = "ACTIVE";
    const STATUS_PENDING = "PENDING";
    const STATUS_UNPAID = "UNPAID";
    const STATUS_UPGRADED = "UPGRADED";
    const STATUS_CANCELED = "CANCELED";
    const STATUS_EXPIRED = "EXPIRED";
    const STATUS_PAST_DUE = 'PAST_DUE';

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
     * Get the customer related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Determine if the subscription has a specific plan.
     *
     * @param $plan
     * @return bool
     */
    public function hasPlan($plan)
    {
        return $this->iyzico_plan == $plan;
    }

    /**
     * Determine if the subscription is active, on trial.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is pending.
     *
     * @return bool
     */
    public function pending()
    {
        return $this->iyzico_status === self::STATUS_PENDING;
    }

    /**
     * Filter query by pending.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopePending($query)
    {
        $query->where('iyzico_status', self::STATUS_PENDING);
    }

    /**
     * Determine if the subscription is past due.
     *
     * @return bool
     */
    public function pastDue()
    {
        return $this->stripe_iyzico === self::STATUS_PAST_DUE;
    }

    /**
     * Filter query by past due.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopePastDue($query)
    {
        $query->where('iyzico_status', self::STATUS_PAST_DUE);
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active()
    {
        return (is_null($this->ends_at) || $this->onGracePeriod()) &&
            $this->iyzico_status !== self::STATUS_PENDING &&
            $this->iyzico_status !== self::STATUS_EXPIRED &&
            (! Kasiyer::$deactivatePastDue || $this->iyzico_status !== self::STATUS_PAST_DUE) &&
            $this->iyzico_status !== self::STATUS_UNPAID;
    }

    /**
     * Filter query by active
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeActive($query)
    {
        $query->where(function ($query) {
            $query->whereNull('ends_at')
                ->orWhere(function ($query) {
                    $query->onGracePeriod();
                });
        })->where('iyzico_status', '!=', self::STATUS_PENDING)
            ->where('iyzico_status', '!=', self::STATUS_EXPIRED)
        ->where('iyzico_status', '!=', self::STATUS_UNPAID);

        if (Kasiyer::$deactivatePastDue) {
            $query->where('iyzico_status', '!=', self::STATUS_PAST_DUE);
        }

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
     * Filter query by on trial.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeOnTrial($query)
    {
        $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', Carbon::now());
    }

    /**
     * Filter query by not on trial.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNotOnTrial($query)
    {
        $query->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<=', Carbon::now());
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
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * Filter query by on grace period.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeOnGracePeriod($query)
    {
        $query->whereNotNull('ends_at')->where('ends_at', '>', Carbon::now());
    }

    /**
     * Filter query by not on grace period.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNotOnGracePeriod($query)
    {
        $query->whereNull('ends_at')->orWhere('ends_at', '<=', Carbon::now());
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
     * Filter query by cancelled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeCancelled($query)
    {
        $query->whereNotNull('ends_at');
    }

    /**
     * Filter query by not cancelled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNotCancelled($query)
    {
        $query->whereNull('ends_at');
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
     * Filter query by recurring.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeRecurring($query)
    {
        $query->notOnTrial()->notCancelled();
    }

    /**
     * Determine if the subscription has ended.
     *
     * @return bool
     */
    public function ended()
    {
        return $this->cancelled() && ! $this->onGracePeriod();
    }

    /**
     * Filter query by ended.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeEnded($query)
    {
        $query->cancelled()->notOnGracePeriod();
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
