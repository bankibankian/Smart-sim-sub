<?php

use App\Helpers\signatureHelper;

it('trims leading and trailing whitespace from values before signing, per PalmPay spec', function () {
    $strA = signatureHelper::params_sort([
        'orderId' => ' ABC123 ',
        'amount'  => 100,
    ]);

    expect($strA)->toBe('amount=100&orderId=ABC123');
});

it('sorts keys in ascending ASCII order and drops empty/null values and the sign key', function () {
    $strA = signatureHelper::params_sort([
        'version'     => 'V1.1',
        'nonceStr'    => 'abc',
        'requestTime' => 1662171389940,
        'empty'       => '',
        'missing'     => null,
        'sign'        => 'should-be-removed',
    ]);

    expect($strA)->toBe('nonceStr=abc&requestTime=1662171389940&version=V1.1');
});
