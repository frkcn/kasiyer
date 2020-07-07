<?php

namespace Frkcn\Kasiyer\Concerns;

use Frkcn\Kasiyer\Transaction;

trait ManagesTransactions
{
    /**
     * Get the billable model's transactions.
     */
    public function transactions()
    {
        if (is_null($this->customer)) {
            return collect();
        }

        $result = $this->subscriptions()->first()->iyzicoInfo()->getOrders();

        return collect($result)->map(function ($transaction) {
            return new Transaction($this->customer, $transaction);
        });
    }
}
