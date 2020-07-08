<?php

namespace Frkcn\Kasiyer;

use Frkcn\Kasiyer\Concerns\ManagesCustomer;
use Frkcn\Kasiyer\Concerns\ManagesSubscriptions;
use Frkcn\Kasiyer\Concerns\ManagesTransactions;
use Frkcn\Kasiyer\Concerns\PerformsCharges;

trait Billable
{
    use ManagesCustomer, ManagesSubscriptions, ManagesTransactions, PerformsCharges;

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
