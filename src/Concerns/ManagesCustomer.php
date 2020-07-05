<?php

namespace Frkcn\Kasiyer\Concerns;

use Frkcn\Kasiyer\Customer;

trait ManagesCustomer
{
    /**
     * Create a customer record for the billable mode.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createAsCustomer(array $attributes = [])
    {
        return $this->customer()->create($attributes);
    }

    /**
     * Get the customer related to the billable mode.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function customer()
    {
        return $this->morphOne(Customer::class, 'billable');
    }

    /**
     * Get the billable model's email address to associate with Iyzico.
     *
     * @return string|null
     */
    public function iyzicoEmail()
    {
        return $this->email;
    }
}
