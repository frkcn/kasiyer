<?php


namespace Frkcn\Kasiyer;


use Frkcn\Kasiyer\Exceptions\InvalidIyzicoCustomer;
use Iyzipay\Model\Customer;
use Iyzipay\Model\Subscription\SubscriptionCustomer;
use Iyzipay\Request\Subscription\SubscriptionCreateCustomerRequest;
use Iyzipay\Request\Subscription\SubscriptionRetrieveCustomerRequest;
use Iyzipay\Request\Subscription\SubscriptionUpdateCustomerRequest;

trait Billable
{
    /**
     * Determine if the entity has a Iyzico customer ID.
     *
     * @return bool
     */
    public function hasIyzicoId()
    {
        return !is_null($this->iyzico_id);
    }

    /**
     * Determine if the entity has a Iyzico customer ID and throw an exception if not.
     *
     * @return void
     *
     * @throws InvalidIyzicoCustomer
     */
    protected function assertCustomerExists()
    {
        if (!$this->iyzico_id) {
            throw InvalidIyzicoCustomer::nonCustomer($this);
        }
    }

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
     * Get the Iyzico customer instance for the current user or create one.
     *
     * @param Customer $options
     * @return SubscriptionCustomer
     * @throws InvalidIyzicoCustomer
     */
    public function createOrGetIyzicoCustomer(Customer $options)
    {
        if ($this->iyzico_id) {
            return $this->asIyzicoCustomer();
        }

        return $this->createAsIyzicoCustomer($options);
    }

    /**
     * Get the Iyzico customer for the model.
     *
     * @return SubscriptionCustomer
     * @throws InvalidIyzicoCustomer
     */
    public function asIyzicoCustomer()
    {
        $this->assertCustomerExists();

        $request = new SubscriptionRetrieveCustomerRequest();
        $request->setCustomerReferenceCode($this->iyzico_id);

        return SubscriptionCustomer::retrieve($request, $this->iyzicoOptions());
    }

    /**
     * Get the default Iyzico API options for the current Billable model.
     *
     * @return \Iyzipay\Options
     */
    public function iyzicoOptions()
    {
        return Kasiyer::iyzicoOptions();
    }
}
