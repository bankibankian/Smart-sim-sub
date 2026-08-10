<?php

use App\Support\LogRedactor;

it('masks known sensitive keys', function () {
    $redacted = LogRedactor::redact([
        'mobile_number' => '08031234567',
        'bvn'           => '22212345678',
        'pin'           => '1234',
        'token'         => 'abc123',
        'password'      => 'secret',
        'account_no'    => '0123456789',
    ]);

    expect($redacted['mobile_number'])->toBe('08031234567')
        ->and($redacted['bvn'])->toBe('[REDACTED]')
        ->and($redacted['pin'])->toBe('[REDACTED]')
        ->and($redacted['token'])->toBe('[REDACTED]')
        ->and($redacted['password'])->toBe('[REDACTED]')
        ->and($redacted['account_no'])->toBe('[REDACTED]');
});

it('masks sensitive keys nested inside arrays', function () {
    $redacted = LogRedactor::redact([
        'data' => [
            'payeeBankAccNo' => '9876543210',
            'payeeName'      => 'Jane Doe',
        ],
    ]);

    expect($redacted['data']['payeeBankAccNo'])->toBe('[REDACTED]')
        ->and($redacted['data']['payeeName'])->toBe('Jane Doe');
});

it('is case-insensitive when matching sensitive keys', function () {
    $redacted = LogRedactor::redact(['BVN' => '22212345678', 'ApiKey' => 'xyz']);

    expect($redacted['BVN'])->toBe('[REDACTED]')
        ->and($redacted['ApiKey'])->toBe('[REDACTED]');
});

it('leaves non-sensitive payloads untouched', function () {
    $payload = ['network' => 'MTN', 'amount' => 500, 'plan' => '1GB'];

    expect(LogRedactor::redact($payload))->toBe($payload);
});
