<?php


namespace Frkcn\Kasiyer;

use Iyzipay\Model\Subscription\SubscriptionCreateCheckoutForm;
use Iyzipay\Request\Subscription\SubscriptionCreateCheckoutFormRequest;

class SubscriptionBuilder
{
    /**
     * The model that is subscribing.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    /**
     * The name of the plan being subscribing.
     *
     * @var string
     */
    protected $plan;

    /**
     * Create a new SubscriptionBuilder instance.
     *
     * @param $owner
     * @param $plan
     */
    public function __construct($owner, $plan)
    {
        $this->owner = $owner;
        $this->plan = $plan;
    }

    /**
     * Initialize checkout form for creating a new Iyzico subscription.
     *
     * @return SubscriptionCreateCheckoutForm
     */
    public function create()
    {
        $request = new SubscriptionCreateCheckoutFormRequest();
        $request->setPricingPlanReferenceCode($this->plan);
        $request->setCallbackUrl(config('kasiyer.callback_url'));

        $customer = $this->owner->asIyzicoCustomer();

        $request->setCustomer($customer);
        return SubscriptionCreateCheckoutForm::create($request, $this->owner->iyzicoOptions());
    }
}
