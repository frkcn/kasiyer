<?php

namespace Frkcn\Kasiyer;

use Frkcn\Kasiyer\Concerns\ManagesCustomer;
use Frkcn\Kasiyer\Concerns\ManagesSubscriptions;

trait Billable
{
    use ManagesCustomer;
    use ManagesSubscriptions;

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
