<?php

namespace Frkcn\Kasiyer;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Money\Currency;

class Transaction implements Arrayable, Jsonable
{
    /**
     * The customer model instance.
     *
     * @var \Frkcn\Kasiyer\Customer
     */
    protected $customer;

    /**
     * The Iyzico transaction attribute.
     *
     * @var
     */
    protected $transaction;

    /**
     * Create a new transaction instance.
     *
     * @param Customer $customer
     * @param $transaction
     */
    public function __construct(Customer $customer, $transaction)
    {
        $this->customer = $customer;
        $this->transaction = $transaction;
    }

    /**
     * Get the total amount that was paid or waiting payment.
     *
     * @return mixed
     */
    public function amount()
    {
        return $this->transaction->price;
    }

    /**
     * Get the used currency for the transaction.
     *
     * @return Currency
     */
    public function currency(): Currency
    {
        return new Currency($this->transaction->currencyCode);
    }

    /**
     * Get the created at Carbon instance.
     *
     * @return Carbon
     */
    public function date()
    {
        return Carbon::createFromTimestampMs($this->transaction->startPeriod, 'UTC');
    }

    /**
     * Get the related customer.
     *
     * @return Customer
     */
    public function customer()
    {
        return $this->customer;
    }

    /**
     * Get the status for the transaction.
     *
     * @return string
     */
    public function status()
    {
        return $this->transaction->orderStatus;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->transaction;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
