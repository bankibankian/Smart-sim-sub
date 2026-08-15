<?php

namespace App\Contracts;

interface SmeDataProviderInterface
{
    /**
     * Purchase a data plan for $mobile on $network. $vendorAmount is the
     * vendor's own price for $planId — only 9PSB requires it in its
     * payload; other vendors ignore it.
     *
     * @return array{success: bool, data: array, api_data: array, transaction_ref: string, message: ?string}
     */
    public function purchase(string $mobile, string $network, string $planId, string $requestId, ?string $vendorAmount = null): array;
}
