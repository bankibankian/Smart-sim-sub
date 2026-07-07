<?php

namespace App\Repositories;

use Exception;
use App\Helpers\noncestrHelper;
use App\Helpers\signatureHelper;
use App\Models\User;
use App\Models\VirtualAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VirtualAccountRepository
{

    public function createVirtualAccount(  $loginUserId)
    {

         
            $userDetails = User::where('id', $loginUserId)->first();


            $customer_name = trim($userDetails->first_name . ' ' . $userDetails->last_name);
            
            try {

                $requestTime = (int) (microtime(true) * 1000);
                $noncestr = noncestrHelper::generateNonceStr();
                $accountReference = "F24" . strtoupper(bin2hex(random_bytes(5)));

                $data = [
                    'merchantId' => config('services.palmpay.merchant_id'),
                    'requestTime' => $requestTime,
                    'identityType' => 'personal',
                    'licenseNumber' =>  $userDetails->bvn,
                    'virtualAccountName' => $customer_name,
                    'version' => config('services.palmpay.version', 'V2.0'),
                    'customerName' => $customer_name,
                    'email' => $userDetails->email,
                    'accountReference' => $accountReference,
                    'nonceStr' => $noncestr,
                ];

                 Log::info($data); 

                $signature = signatureHelper::generate_signature($data, config('keys.private'));

                $baseUrl = config('services.palmpay.base_url', 'https://open-gw-prod.palmpay-inc.com/');
                $url = rtrim($baseUrl, '/') . '/api/v2/virtual/account/label/create';
                $token = config('services.palmpay.bearer_token');
                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'CountryCode: NG',
                    "Authorization: Bearer $token",
                    "Signature: $signature",
                    'Content-Type: application/json',
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Disable SSL verification in local environment
                if (config('app.env') === 'local') {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                }

                // Execute request
                $response = curl_exec($ch);

                Log::info($response);         
                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: ' . curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                // Decode the JSON response to an associative array
                $response = json_decode($response, true);

                // Check if decoding was successful
                if ($response === null) {
                    throw new Exception('Request was not successful.');
                }

                // Check for success
                if (isset($response['respCode']) && $response['respCode'] === '00000000') {

                    $res = VirtualAccount::create([
                        'user_id' => $loginUserId,
                        'account_reference' => $response['data']['accountReference'],
                        'account_number' => $response['data']['virtualAccountNo'],
                        'account_name' => $response['data']['virtualAccountName'],
                        'bank_name' => 'PalmPay',
                        'provider' => 'palmpay',
                        'is_active' => true,
                    ]);

                      return ['success' => true, 'message' => 'Virtual Account Created'];
                }
            } catch (\Exception $e) {
                Log::error('Error creating virtual account for user ' . $loginUserId . ': ' . $e->getMessage());

                return ['success' => false, 'message' => 'Failed to create virtual account'];
            }
        
    }
}
