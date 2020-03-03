<?php


namespace Frkcn\Kasiyer;

use Carbon\Carbon;
use Iyzipay\Model\Customer;
use Iyzipay\Model\PaymentCard;
use Iyzipay\Model\Subscription\RetrieveSubscriptionCheckoutForm;
use Iyzipay\Model\Subscription\SubscriptionCreate;
use Iyzipay\Model\Subscription\SubscriptionCreateCheckoutForm;
use Iyzipay\Request\Subscription\RetrieveSubscriptionCreateCheckoutFormRequest;
use Iyzipay\Request\Subscription\SubscriptionCreateCheckoutFormRequest;
use Iyzipay\Request\Subscription\SubscriptionCreateRequest;

class SubscriptionBuilder
{
    /**
     * The model that is subscribing.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The name of the subscription.
     *
     * @var
     */
    protected $name;

    /**
     * The name of the plan being subscribing.
     *
     * @var string
     */
    protected $plan;
    /**
     * The model that is subscribing as iyzico customer.
     *
     * @var
     */
    protected $customer;

    /**
     * Create a new SubscriptionBuilder instance.
     *
     * @param $owner
     * @param $name
     * @param $plan
     */
    public function __construct($owner, $name, $plan)
    {
        $this->owner = $owner;
        $this->name = $name;
        $this->plan = $plan;
    }

    /**
     * Set Iyzico model customer.
     *
     * @param Customer $customer
     * @return SubscriptionBuilder
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Initialize checkout form for creating a new Iyzico subscription.
     *
     * @param Customer $customer
     * @return SubscriptionCreateCheckoutForm
     */
    public function init(Customer $customer)
    {
        $request = new SubscriptionCreateCheckoutFormRequest();
        $request->setPricingPlanReferenceCode($this->plan);
        $request->setCallbackUrl(config('kasiyer.callback_url'));

        $request->setCustomer($customer);
        return SubscriptionCreateCheckoutForm::create($request, $this->owner->iyzicoOptions());
    }

    /**
     * Return checkout form result.
     *
     * @param $token
     * @return bool|RetrieveSubscriptionCheckoutForm
     */
    public function successful($token)
    {
        $request = new RetrieveSubscriptionCreateCheckoutFormRequest();
        $request->setCheckoutFormToken($token);

        $response = RetrieveSubscriptionCheckoutForm::retrieve($request, $this->owner->iyzicoOptions());

        if ($response->getStatus() === "failure") {
            return false;
        }

        return  $response;
    }

    /**
     * Return card's last four number.
     *
     * @param string $cardNumber
     * @return false|string
     */
    public function getCardLastFour(string $cardNumber)
    {
        return substr($cardNumber, -4);
    }

    /**
     * Create a new Iyzico Subscription.
     *
     * @param PaymentCard $paymentCard
     * @return Subscription
     */
    public function create(PaymentCard $paymentCard)
    {
        $request = new SubscriptionCreateRequest();

        $request->setPricingPlanReferenceCode($this->plan);
        $request->setPaymentCard($paymentCard);
        $request->setCustomer($this->customer);

        $iyzicoSubscription = SubscriptionCreate::create($request, $this->owner->iyzicoOptions());

        $trialEndsAt = $iyzicoSubscription->getTrialEndDate();

        // Update user with newly created iyzico reference code.
        $this->owner->update([
            'iyzico_id' => $iyzicoSubscription->getCustomerReferenceCode(),
            'card_last_four' => $this->getCardLastFour($paymentCard->getCardNumber()),
            'trial_ends_at' => $trialEndsAt,
        ]);

        /** @var \Frkcn\Kasiyer\Subscription $subscription */
        $subscription = $this->owner->subscriptions()->create([
            'name' => $this->name,
            'iyzico_id' => $iyzicoSubscription->getReferenceCode(),
            'iyzico_status' => $iyzicoSubscription->getSubscriptionStatus(),
            'iyzico_plan' => $this->plan,
            'trial_ends_at' => $trialEndsAt,
            'ends_at' => null,
        ]);

        return $subscription;
    }
}
