<?php

namespace Frkcn\Kasiyer;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Iyzipay\Model\Subscription\SubscriptionCancel;
use Iyzipay\Model\Subscription\SubscriptionCardUpdate;
use Iyzipay\Model\Subscription\SubscriptionDetails;
use Iyzipay\Model\Subscription\SubscriptionRetry;
use Iyzipay\Model\Subscription\SubscriptionUpgrade;
use Iyzipay\Request\RetrievePaymentRequest;
use Iyzipay\Request\Subscription\SubscriptionCancelRequest;
use Iyzipay\Request\Subscription\SubscriptionCardUpdateWithSubscriptionReferenceCodeRequest;
use Iyzipay\Request\Subscription\SubscriptionDetailsRequest;
use Iyzipay\Request\Subscription\SubscriptionRetryRequest;
use Iyzipay\Request\Subscription\SubscriptionUpgradeRequest;
use LogicException;

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
     * The Iyzico info for the subscription.
     *
     * @var
     */
    protected $iyzicoInfo;

    /**
     * The return url which will be triggered upon starting the card update.
     *
     * @var string|null
     */
    protected $returnTo;

    /**
     * Indicates that the trial should end immediately..
     *
     * @var bool
     */
    protected $skipTrial = false;


    /**
     * Get the billable model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function billable()
    {
        return $this->morphTo();
    }

    /**
     * The return url which will be triggered upon starting the card update.
     *
     * @param string $returnTo
     * @return Subscription
     */
    public function returnTo(string $returnTo)
    {
        $this->returnTo = $returnTo;

        return $this;
    }

    /**
     * Force the trial to end immediately.
     *
     * @return $this
     */
    public function skipTrial()
    {
        $this->skipTrial = true;

        return $this;
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
     * Get the Iyzico card update form.
     *
     * @return mixed
     */
    public function cardUpdate()
    {
        $request = new SubscriptionCardUpdateWithSubscriptionReferenceCodeRequest();
        $request->setLocale(config('kasiyer.currency_locale'));
        $request->setConversationId($this->billable_id);
        $request->setSubscriptionReferenceCode($this->iyzico_id);
        $request->setCallbackUrl($this->returnTo);

        return SubscriptionCardUpdate::updateWithSubscriptionReferenceCode($request, Kasiyer::iyzicoOptions())
            ->getCheckoutFormContent();
    }

    /**
     * Swap the subscription to a new Iyzico plan.
     *
     * @param $plan
     * @return bool|Subscription
     */
    public function swap($plan)
    {
        if ($this->pastDue()) {
            throw new LogicException('Cannot swap plans for past due subscriptions.');
        }

        $request = new SubscriptionUpgradeRequest();
        $request->setLocale(config('kasiyer.currency_locale'));
        $request->setSubscriptionReferenceCode($this->iyzico_id);
        $request->setNewPricingPlanReferenceCode($plan);
        $request->setUpgradePeriod("NOW");
        $request->setUseTrial($this->skipTrial);
        $request->setResetRecurrenceCount(true);

        $subscription = SubscriptionUpgrade::update($request, Kasiyer::iyzicoOptions());

        if ( $subscription->getStatus() == 'success') {
            $this->forceFill([
                'iyzico_id' => $subscription->getReferenceCode(),
                'iyzico_plan' => $subscription->getPricingPlanReferenceCode(),
            ])->save();

            return $this;
        }

        return false;
    }

    /**
     * Cancel the subscription at the moment.
     *
     * @return $this
     */
    public function cancel()
    {
        $nextPayment = $this->nextPayment();

        $request = new SubscriptionCancelRequest();
        $request->setSubscriptionReferenceCode($this->iyzico_id);

        SubscriptionCancel::cancel($request, Kasiyer::iyzicoOptions());

        $this->iyzico_status = self::STATUS_CANCELED;

        if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        } else {
            $this->ends_at = $nextPayment->date();
        }

        $this->save();

        return $this;
    }

    /**
     * Retry subscription for failed payment.
     *
     * @return bool
     */
    public function retry()
    {
        if (!$this->pastDue()) {
            throw new LogicException('Cannot retry subscription for active subscriptions.');
        }

       $request = new SubscriptionRetryRequest();
       $request->setReferenceCode($this->iyzico_id);

       $result = SubscriptionRetry::update($request, Kasiyer::iyzicoOptions());

       if ($result === 'success') {
           $this->iyzico_status = self::STATUS_ACTIVE;
           $this->save();

           return true;
       }

       return false;
    }

    /**
     * Get the next order for the subscription.
     *
     * @return Payment
     */
    public function nextPayment()
    {
        $subscription = $this->iyzicoInfo();
        $nextOrder = $subscription->getOrders()[0];

        return new Payment($nextOrder);
    }

    /**
     * Get info from Iyzico about subscription.
     *
     * @return SubscriptionDetails
     */
    public function iyzicoInfo()
    {
        if ($this->iyzicoInfo) {
            return $this->iyzicoInfo;
        }

        $request = new SubscriptionDetailsRequest();
        $request->setSubscriptionReferenceCode($this->iyzico_id);

        return $this->iyzicoInfo = SubscriptionDetails::retrieve($request, Kasiyer::iyzicoOptions());
    }

    /**
     * Get the billable model's transactions.
     */
    public function transactions()
    {
        $result = $this->iyzicoInfo()->getOrders();

        return collect($result)->map(function ($transaction) {
            return new Transaction($this->billable->customer, $transaction);
        });
    }

    /**
     * Get info about last subscription payment.
     *
     * @return \Iyzipay\Model\Payment
     */
    public function lastPayment()
    {
       $paymentId = $this->iyzicoInfo()->getOrders()[1]->paymentAttempts[0]->paymentId;

       return Kasiyer::getPayment($paymentId);
    }

    /**
     * Get the card brand from the subscription.
     *
     * @return string
     */
    public function cardBrand()
    {
        return (string) $this->lastPayment()->getCardAssociation();
    }

    /**
     * Get the last four digits from the subscription.
     *
     * @return string
     */
    public function cardLastFour()
    {
        return (string) $this->lastPayment()->getLastFourDigits();
    }
}
