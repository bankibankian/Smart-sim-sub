<?php

namespace App\Helpers;

class signatureHelper
{
    /**
     * Generate the signature
     *
     * @param  array  $data  The request body as an array
     * @param string private_key The RSA private key
     * @return string The base64-encoded signature
     */
    public static function generate_signature(array $data, string $private_key)
    {
        // Create an MD5 hash of the sorted parameters and convert it to uppercase
        $encry_data = strtoupper(md5(self::params_sort($data)));

        // Sign the hashed data using the private key with SHA-1 RSA
        $signature = self::sha1_with_rsa($encry_data, $private_key);

        return $signature;
    }
    

    /**
     * Sorts the given array of data by keys in ascending order and construcs a query string
     *
     * @param  array  $param  The array to be processed
     * @return string The resulting query string.
     */
    public static function params_sort(array $data): string
    {
        // Filter empty string from the array
        $filtered_data = array_filter($data, function ($value) {
            return $value !== '' && $value !== null;
        });

        // PalmPay's server trims leading/trailing whitespace from values
        // before recomputing its own check signature — values that carry
        // incidental whitespace (e.g. bank account names round-tripped
        // from a queryBankAccount() response) must be trimmed here too,
        // or the two sides hash different strings and the signature fails.
        $filtered_data = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $filtered_data);

        // Sort the array by its keys in ascending ASCII/byte order
        ksort($filtered_data, SORT_STRING);

        // Remove the sign key if it exists
        unset($filtered_data['sign']);

        $pairs = [];
        foreach ($filtered_data as $key => $value) {
            $pairs[] = "$key=$value";
        }

        return implode('&', $pairs);
    }

    /**
     * Sign encrypted data using SHA-1 with RSA encryption and a given private key.
     *
     * @param  string  $encry_data  The data to be signed.
     * @param  string  $private_key  The RSA private key.
     * @return string The base64-encoded signature.
     */
    public static function sha1_with_rsa(string $encry_data, string $private_key): string
    {
        // Retrieve the private key resource
        $privateKey = self::validate_rsa_key($private_key, 'private');
        // Sign the data using the private key and SHA-1 algorithm
        openssl_sign($encry_data, $signature, $privateKey, OPENSSL_ALGO_SHA1);

        //encode the signature in base64 and return it
        return base64_encode($signature);
    }

    /**
     * Verifies the callback signature against the provided plain text using the public key.
     *
     * @param  array  $data  Notification payload as an array
     * @param  string  $signature  The signature to be verified.
     * @param  string  $public_key  The RSA public key
     * @return bool Whether the signature is verified (true) or not (false).
     */
    public static function verify_callback_signature(array $data, string $signature, string $public_key)
    {
        // Retrieve the public key resource
        $publicKey = self::validate_rsa_key($public_key, 'public');
        
        // Build the signing string and calculate MD5
        $paramsString = self::params_sort($data);
        $md5 = strtoupper(md5($paramsString));
        
        // Safely handle signature encoding
        // If it contains '%', it's likely URL-encoded. If not, treat as plain base64.
        $rawSignature = str_contains($signature, '%') ? urldecode($signature) : $signature;
        $decodedSig = base64_decode($rawSignature);

        // Verify the signature using the public key and SHA-1 algorithm
        $is_verified = openssl_verify($md5, $decodedSig, $publicKey, OPENSSL_ALGO_SHA1);

        if ($is_verified !== 1) {
            \Illuminate\Support\Facades\Log::error('Palmpay Signature Verification Failed', [
                'built_string' => $paramsString,
                'md5_hash' => $md5,
                'signature_received' => $signature,
                'openssl_error' => openssl_error_string()
            ]);
        }

        return $is_verified === 1;
    }

    /**
     * Validates and formats an RSA key (private or public) in PEM format.
     *
     * @param  string  $value  The raw RSA key value.
     * @param  string  $key_type  The type of the RSA key ('private' for private key, 'public' for public key).
     * @return resource|false The OpenSSL key resource if the key is valid, or false if the key is invalid.
     */
    public static function validate_rsa_key($value, $key_type)
    {
        // Remove spaces from the private key
        $formatted_key = str_replace(' ', '', $value);

        // Remove trailing spaces from the private key
        $formatted_key = trim($formatted_key);

        // Split the key into chunks of 64 characters with newline breaks
        $formatted_key = chunk_split($formatted_key, 64, "\n");

        // Add appropriate header and footer based on key type
        if ($key_type === 'private') {
            $pem_formatted_key = "-----BEGIN RSA PRIVATE KEY-----\n$formatted_key-----END RSA PRIVATE KEY-----\n";
            $key_resource = openssl_pkey_get_private($pem_formatted_key);
        } else {
            $pem_formatted_key = "-----BEGIN PUBLIC KEY-----\n$formatted_key-----END PUBLIC KEY-----\n";
            $key_resource = openssl_pkey_get_public($pem_formatted_key);
        }

        return $key_resource;
    }
}
