<?php

namespace App\Repositories;

use Exception;
use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VirtualAccountRepository
{
    /**
     * Create a PalmPay virtual account for the given user.
     */
    public function createVirtualAccount(int $loginUserId): array
    {
        $userDetails = User::where('id', $loginUserId)->first();

        if (!$userDetails) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $customer_name = trim($userDetails->first_name . ' ' . $userDetails->last_name);

        try {
            $requestTime    = (int) (microtime(true) * 1000);
            $noncestr       = noncestrHelper::generateNonceStr();
            $accountReference = 'F24' . strtoupper(bin2hex(random_bytes(5)));

            $data = [
                'requestTime'        => $requestTime,
                'identityType'       => 'personal',
                'licenseNumber'      => $userDetails->bvn,
                'virtualAccountName' => $customer_name,
                'version'            => config('services.palmpay.version', 'V2.0'),
                'customerName'       => $customer_name,
                'email'              => $userDetails->email,
                'accountReference'   => $accountReference,
                'nonceStr'           => $noncestr,
            ];

            Log::info('PalmPay virtual account request', $data);

            $signature = signatureHelper::generate_signature($data, config('keys.private'));

            // Use config() — NOT env() — so this works when config is cached in production
            $baseUrl = config('services.palmpay.base_url');
            $token   = config('services.palmpay.bearer_token');

            if (empty($baseUrl) || empty($token)) {
                throw new Exception('PalmPay API credentials are not configured.');
            }

            $url = rtrim($baseUrl, '/') . '/api/v2/virtual/account/label/create';

            $headers = [
                'Accept: application/json, text/plain, */*',
                'CountryCode: NG',
                "Authorization: Bearer {$token}",
                "Signature: {$signature}",
                'Content-Type: application/json',
            ];

            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $curlError = curl_error($ch);
                curl_close($ch);
                throw new Exception('cURL Error: ' . $curlError);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('PalmPay virtual account response', [
                'http_code' => $httpCode,
                'body'      => $response,
            ]);

            $decoded = json_decode($response, true);

            if ($decoded === null) {
                throw new Exception('Invalid JSON response from PalmPay API.');
            }

            if (isset($decoded['respCode']) && $decoded['respCode'] === '00000000') {
                DB::table('virtual_accounts')->insert([
                    'user_id'           => $loginUserId,
                    'account_reference' => $decoded['data']['accountReference'],
                    'account_number'    => $decoded['data']['virtualAccountNo'],
                    'account_name'      => $decoded['data']['virtualAccountName'],
                    'bank_name'         => 'PalmPay',
                    'bank_code'         => null,
                    'provider'          => 'palmpay',
                    'is_active'         => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                return ['success' => true, 'message' => 'Virtual account created successfully.'];
            }

            $errorMsg = $decoded['respMsg'] ?? $decoded['message'] ?? 'Unknown error from PalmPay.';
            throw new Exception('PalmPay API error: ' . $errorMsg);

        } catch (Exception $e) {
            Log::error('Error creating virtual account for user ' . $loginUserId . ': ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
