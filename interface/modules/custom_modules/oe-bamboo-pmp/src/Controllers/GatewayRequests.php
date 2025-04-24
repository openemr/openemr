<?php

/*
 *   package   OpenEMR
 *   link      http://www.open-emr.org
 *   author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)
 *   All rights reserved
 */

namespace Juggernaut\Module\Bamboo\Controllers;

use Juggernaut\Module\Bamboo\Interfaces\DataRequestXmlBuilders;
use OpenEMR\Common\Crypto\CryptoGen;
use Juggernaut\Module\Bamboo\Controllers\RequestData;
class GatewayRequests
{
    private DataRequestXmlBuilders $dataRequestXmlBuilders;
    private CryptoGen $cryptoGen;
    public string $url;
    public function __construct(DataRequestXmlBuilders $dataRequestXmlBuilders)
    {
        $this->dataRequestXmlBuilders = $dataRequestXmlBuilders;
        $this->cryptoGen = new CryptoGen();

    }
    public function fetchReportData($url)
    {
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

// Define the URL and XML data to be sent by passing in the url and the XML builder
        $xmlData = $this->dataRequestXmlBuilders->buildReportDataRequestXml();
        file_put_contents("/var/www/html/quest/xmlData.xml", print_r($xmlData, true));
// Initialize cURL session
        $ch = curl_init($url);

// Set cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $pfxCertificatePath = dirname(__DIR__, 3) .
            DIRECTORY_SEPARATOR . "oe-bamboo-pmp/certificate/certificate.pfx";
// Set the HTTP headers
        $headers = [
            "X-Auth-Username: $username",
            "X-Auth-Timestamp: $timestamp",
            "X-Auth-Nonce: $nonce",
            "X-Auth-PasswordDigest: $passwordDigest",
            "Content-Type: application/xml",
            "Content-Length: " . strlen($xmlData),
            "Accept-Encoding: gzip,deflate",
            "Accept: */*",
            "Connection: Keep-Alive",
            "Host: secure.prep.pmpgateway.net",
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Set up the PFX certificate and passphrase
        curl_setopt($ch, CURLOPT_SSLCERT, $pfxCertificatePath);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');

// Execute the cURL request and capture the response
        $response = curl_exec($ch);

// Check for errors
        if ($response === false) {
            $error = curl_error($ch);
            // Close the cURL session
            curl_close($ch);
            return "cURL Error: $error";
        } else {
            // Close the cURL session
            curl_close($ch);
            // Print the response
            return $response;
        }
    }
}
