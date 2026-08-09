<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The Fadeelposdatasub data-purchase vendor call, extracted out of
 * SmeDataController so it can be reused for the silent post-activation
 * bonus top-up without duplicating the integration.
 */
class SmeDataPurchaseApi
{
    private const NETWORK_ID_MAP = [
        'MTN' => 1,
        'GLO' => 2,
        'AIRTEL' => 3,
        '9MOBILE' => 4,
    ];

    public static function baseUrl(): string
    {
        return env('BASE_URL', 'https://fadeelposdatasub.com.ng/api/data/purchase');
    }

    public static function token(): ?string
    {
        return env('API_KEYS');
    }

    /**
     * Calls the vendor's data-purchase endpoint for a mobile number.
     *
     * @return array{success: bool, data: array, api_data: array, transaction_ref: string, message: ?string}
     */
    public static function purchase(string $mobile, string $network, string $planId, string $requestId): array
    {
        $networkId = self::NETWORK_ID_MAP[strtoupper($network)] ?? 1;

        Log::info('SME Data API Request Payload', [
            'url' => self::baseUrl(),
            'payload' => [
                'mobile_number' => $mobile,
                'network'       => $networkId,
                'plan'          => $planId,
                'request-id'    => $requestId,
            ],
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . self::token(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->withoutVerifying()->post(self::baseUrl(), [
            'mobile_number' => $mobile,
            'network'       => $networkId,
            'plan'          => $planId,
            'request-id'    => $requestId,
        ]);

        $data = $response->json();
        Log::info('SME Data API Response', [
            'status'   => $response->status(),
            'response' => $data,
            'raw_body' => $response->body(),
        ]);

        $isSuccess = $response->successful() && (
            (isset($data['status']) && ($data['status'] === 'success' || $data['status'] === 'successful')) ||
            (isset($data['success']) && $data['success'] === true) ||
            (isset($data['code']) && in_array((string) $data['code'], ['200', '201', '0', '00', '000'], true))
        );

        $apiData = $data['data'] ?? [];
        $transactionRef = $data['transid'] ?? $data['reference'] ?? $data['transaction_id'] ?? $apiData['transaction_ref'] ?? $apiData['reference'] ?? $requestId;

        return [
            'success'         => $isSuccess,
            'data'            => $data,
            'api_data'        => $apiData,
            'transaction_ref' => $transactionRef,
            'message'         => $data['message'] ?? $data['msg'] ?? null,
        ];
    }
}
