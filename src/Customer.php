<?php

namespace Frkcn\Kasiyer;

use Illuminate\Database\Eloquent\Model;
use Iyzipay\Model\Customer as IyzicoCustomer;

class Customer extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the billable model related to the customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function billable()
    {
        return $this->morphTo();
    }

    /**
     * Get all of the subscriptions for the Customer model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->orderByDesc('created_at');
    }

    /**
     * Get a subscription instance by name.
     *
     * @param  string  $name
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object
     */
    public function subscription($name = 'default')
    {
        return $this->subscriptions()->where('name', $name)->first();
    }

    /**
     * Get customer as iyzico customer.
     *
     * @return IyzicoCustomer
     */
    public function asIyzicoCustomer()
    {
        $customer = new IyzicoCustomer();

        $customer->setName($this->name);
        $customer->setSurname($this->surname);
        $customer->setGsmNumber($this->gsm_number);
        $customer->setEmail($this->iyzico_email);
        $customer->setIdentityNumber($this->identity_number);
        $customer->setShippingContactName($this->shipping_contact_name);
        $customer->setShippingCity($this->shipping_city);
        $customer->setShippingCountry($this->shipping_country);
        $customer->setShippingAddress($this->shipping_address);
        $customer->setShippingZipCode($this->shipping_zip_code);
        $customer->setBillingContactName($this->billing_contact_name);
        $customer->setBillingCity($this->billing_city);
        $customer->setBillingCountry($this->billing_country);
        $customer->setBillingAddress($this->billing_address);
        $customer->setBillingZipCode($this->billing_zip_code);

        return $customer;
    }
}
