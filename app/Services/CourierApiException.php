<?php

namespace App\Services;

class CourierApiException extends \RuntimeException
{
    public function __construct(public bool $definitelyRejected = false)
    {
        parent::__construct($definitelyRejected ? 'Courier rejected this request. Check account settings, delivery address and required fields.' : 'Courier response is uncertain. Check the merchant portal before retrying or attach the existing consignment ID.');
    }
}
