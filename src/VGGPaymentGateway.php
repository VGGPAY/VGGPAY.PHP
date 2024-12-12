<?php

namespace VggPay;

use Exception;


class VGGPaymentGateway
{


    private $APIServerUrl;
    // Get the project ID, KEY and IV value from the website backend management
    private $projectId;
    private $SecretKey;
    private $SecretIV;

    public function __construct($config)
    {
        $this->APIServerUrl = 'https://sapi.vggpay.com';
        $this->projectId = isset($config['projectId']) ? $config['projectId'] : '';
        $this->SecretKey = isset($config['SecretKey']) ? $config['SecretKey'] : '';
        $this->SecretIV = isset($config['SecretIV']) ? $config['SecretIV'] : '';

    }


    /**
     * POST /api/v2/createorder
     *
     */

    function CreateOrder($OrderData)
    {
        $path = '/api/v2/createorder';


        $m_orderid = isset($OrderData['m_orderid']) ? $OrderData['m_orderid'] : '';
        $currency = isset($OrderData['currency']) ? $OrderData['currency'] : '';
        $amount = isset($OrderData['amount']) ? $OrderData['amount'] : '';
        if (empty($m_orderid)) {
            return '"m_orderid" cannot be empty ';
        }
        if (empty($currency)) {
            return '"currency" cannot be empty ';
        }
        if (empty($amount)) {
            return '"amount" cannot be empty ';
        }

        $data = $OrderData;
        $data['projectid'] = $this->projectId;

        // Encapsulate the encrypted data and project ID into a JSON string for sending requests
        $postData = json_encode([
            'data' => $this->encryptData(json_encode($data)), // Encrypted data
            'projectid' => $this->projectId,// Project ID
        ]);

        // Send an API request to create an order and get a response
        return $this->sendRequest($postData, $this->APIServerUrl . $path);

    }


    /**
     * POST /api/v2/createtopup
     */

    function createTopUp($topupData)
    {

        $e = $this->ErrorDetection();
        if (!empty($e)) {
            return $e;
        }
        $m_userid = isset($topupData['m_userid']) ? $topupData['m_userid'] : '';
        if (empty($m_userid)) {
            return '"m_userid" cannot be empty ';
        }

        $path = '/api/v2/createtopup';

        $data = $topupData;
        $data['projectid'] = $this->projectId;


        $StringsData = json_encode($data);
        $encryptedData = $this->encryptData($StringsData);
        $postData = json_encode([
            'data' => $encryptedData,
            'projectid' => $this->projectId,
        ]);
        return $this->sendRequest($postData, $this->APIServerUrl . $path);
    }


    /**
     *Send an HTTP POST request and return a response
     *
     * @param string $postData POST data to be sent
     * @param string $url The URL of the target API
     * @return bool|string Returns API response data or error information
     */
    function sendRequest($postData, $url)
    {

        try {
            $ch = curl_init();
            if ($ch === false) {
                return false;
            }
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ];
            curl_setopt_array($ch, $options);
            $response = curl_exec($ch);
            if ($response === false) {
                curl_close($ch);
                return false;
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode >= 400) {
                $error = "Error: HTTP status code $httpCode received.";
                curl_close($ch);
                return $error;
            }
            curl_close($ch);
            return $response;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Encrypt data using the AES-256-CBC algorithm
     *
     * @param string $StringsData Plaintext data that needs to be encrypted
     * @return string Returns Base64-encoded ciphertext
     */
    function encryptData($StringsData)
    {
        $Key = hex2bin($this->SecretKey);  // Convert the hexadecimal SecretKey to binary format
        $iv = hex2bin($this->SecretIV);   // Convert the hexadecimal IV to binary format
        $encrypted = openssl_encrypt($StringsData, 'AES-256-CBC', $Key, OPENSSL_RAW_DATA, $iv); // Encrypt using AES-256-CBC
        return base64_encode($encrypted); //Convert the encrypted data into Base64 encoding
    }



    function decryptData($encryptedData)
    {
        $Key = hex2bin($this->SecretKey);  // Convert the hexadecimal SecretKey to binary format
        $iv = hex2bin($this->SecretIV); // Convert IV from hexadecimal to binary
        $method = 'AES-256-CBC'; // Encryption algorithm and mode
        $encrypted = base64_decode($encryptedData); // Decode Base64 encoded encrypted data
        // Decrypt data using OpenSSL
        $decrypted = openssl_decrypt($encrypted, $method, $Key, OPENSSL_RAW_DATA, $iv);
        // Return the decrypted original data (assuming decrypted data is in JSON format)
        return json_decode($decrypted, true);
    }

    function ErrorDetection()
    {
        if (empty($this->projectId)) {
            return '"projectId" cannot be empty';
        }
        if (empty($this->SecretKey)) {
            return '"SecretKey" cannot be empty';
        }
        if (empty($this->SecretIV)) {
            return '"SecretIV" cannot be empty';
        }
        return '';
    }


}