<?php

namespace App\Services\Steadfast;

use Exception;

/**
 * Raised for anything that stops a consignment being booked or read: missing
 * credentials, a rejected payload, a transport failure, or a non 200 reply.
 * Callers catch this one type rather than inspecting HTTP internals.
 */
class SteadfastException extends Exception
{
    protected $context = [];

    public function __construct($message, array $context = [], $code = 0)
    {
        parent::__construct($message, $code);

        $this->context = $context;
    }

    public function context()
    {
        return $this->context;
    }
}
