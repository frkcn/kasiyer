<?php


namespace Frkcn\Kasiyer;

use Iyzipay\Model\Subscription\SubscriptionCreateCheckoutForm;
use Iyzipay\Request\Subscription\RetrieveSubscriptionCreateCheckoutFormRequest;
use Iyzipay\Request\Subscription\SubscriptionCreateCheckoutFormRequest;

class SubscriptionBuilder
{
    /**
     * The Billable model that is subscribing.
     *
     * @var \Frkcn\Kasiyer\Billable
     */
    protected $billable;

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
     * The return url which will be triggered upon starting the subscription.
     *
     * @var string|null
     */
    protected $returnTo;

    /**
     * Create a new SubscriptionBuilder instance.
     *
     * @param $billable
     * @param $name
     * @param $plan
     */
    public function __construct($billable, $name, $plan)
    {
        $this->billable = $billable;
        $this->name = $name;
        $this->plan = $plan;
    }

    /**
     * The return url which will be triggered upon starting the subscription.
     *
     * @param $returnTo
     * @return SubscriptionBuilder
     */
    public function returnTo(string $returnTo)
    {
       $this->returnTo = $returnTo;

       return $this;
    }

    /**
     * Generate checkout form for a subscription.
     *
     * @return SubscriptionCreateCheckoutForm
     */
    public function create()
    {
        $request = new SubscriptionCreateCheckoutFormRequest();
        $request->setConversationId($this->billable->id);
        $request->setLocale(config('kasiyer.locale'));
        $request->setPricingPlanReferenceCode($this->plan);
        $request->setCallbackUrl($this->returnTo);
        $request->setCustomer($this->billable->customer->asIyzicoCustomer());

        return SubscriptionCreateCheckoutForm::create($request, $this->billable->iyzicoOptions())
            ->getCheckoutFormContent();
    }
}
