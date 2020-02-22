<?php

namespace Frkcn\Kasiyer\Exceptions;

use Exception;

class InvalidIyzicoCustomer extends Exception
{
    /**
     * Create a new CustomerFailure instance.
     * 
     * @param $owner
     * @return static
     */
    public static function nonCustomer($owner)
    {
        return new static(class_basename($owner).' is not a Iyzico customer. See the createAsIyzicoCustomer method.');
    }
}
