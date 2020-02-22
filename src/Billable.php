<?php


namespace Frkcn\Kasiyer;


use Iyzipay\Model\Customer;
use Iyzipay\Model\Subscription\SubscriptionCustomer;
use Iyzipay\Request\Subscription\SubscriptionCreateCustomerRequest;
use Iyzipay\Request\Subscription\SubscriptionUpdateCustomerRequest;

trait Billable
{
    /**
     * Create a Iyzico customer for the given model.
     *
     * @param Customer $options
     * @return SubscriptionCustomer
     */
    public function createAsIyzicoCustomer(Customer $options)
    {
        $request = new SubscriptionCreateCustomerRequest();
        $request->setCustomer($options);

        $customer = SubscriptionCustomer::create($request, $this->iyzicoOptions());

        $this->iyzico_id = $customer->getReferenceCode();

        $this->save();

        return $customer;
    }

    /**
     *  Update the underlying Iyzico customer information for the model.
     *
     * @param Customer $options
     * @return SubscriptionCustomer
     */
    public function updateIyzicoCustomer(Customer $options)
    {
        $request = new SubscriptionUpdateCustomerRequest();
        $request->setCustomerReferenceCode($this->iyzico_id);
        $request->setCustomer($options);

        return SubscriptionCustomer::update($request, $this->iyzicoOptions());
    }

    /**
     * Get the default Iyzico API options for the current Billable model.
     *
     * @param  array  $options
     * @return \Iyzipay\Options
     */
    public function iyzicoOptions()
    {
        return Kasiyer::iyzicoOptions();
    }
}
