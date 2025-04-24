<?php

/*
 *
 *   package   OpenEMR
 *   link      http://www.open-emr.org
 *   author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)
 *   All rights reserved
 */

namespace Juggernaut\Module\Bamboo\Controllers;

use OpenEMR\Common\Crypto\CryptoGen;

class ReportDisplayRequest
{
    public string $url;
    private $cryptoGen;

    public function __construct()
    {
        $this->cryptoGen = new CryptoGen();
    }
    public function fetchReportDisplay()
    {
        $certPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "oe-bamboo-pmp/certificate/certificate.pfx";

        $credentials = new ResourcesConfig();
        $usernamePassword = $credentials->getConnectionData();
        $password = $this->cryptoGen->decryptStandard($usernamePassword['password'], null, 'database');
        $username = $usernamePassword['username'];

        // Define variables
        $nonce = bin2hex(random_bytes(16)); // Generate a random nonce
        $timestamp = time(); // Current Unix timestamp


// Create the combined string and hash it
        $combinedString = $password . ":" . $nonce . ":" . $timestamp;
        $passwordDigest = hash('sha256', $combinedString);
        $passwordDigest = strtolower($passwordDigest);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        // Set the HTTP headers
        $headers = [
            "X-Auth-Username: $username",
            "X-Auth-Timestamp: $timestamp",
            "X-Auth-Nonce: $nonce",
            "X-Auth-PasswordDigest: $passwordDigest",
        ];
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
        //curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');

        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            // Output the response (HTML) directly to the browser
            header('Content-Type: text/html'); // Set the content type to HTML
            echo $response;
        }

// Close the cURL session
        curl_close($ch);
    }
}
