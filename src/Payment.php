<?php

namespace Frkcn\Kasiyer;

use Carbon\Carbon;
use Money\Currency;

class Payment
{
    /**
     * The object of next payment.
     *
     * @var
     */
    public $payment;

    /**
     * Create a new Payment instance.
     *
     * @param $payment
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the total amount of the payment.
     *
     * @return mixed
     */
    public function amount()
    {
        return $this->payment->price;
    }

    /**
     * Get the used currency for the payment.
     *
     * @return Currency
     */
    public function currency(): Currency
    {
        return new Currency($this->payment->currencyCode);
    }

    /**
     * Get the date of the payment as a Carbon instance.
     *
     * @return Carbon
     */
    public function date()
    {
        return Carbon::createFromTimestampMs($this->payment->startPeriod, 'UTC')->startOfDay();
    }
}
