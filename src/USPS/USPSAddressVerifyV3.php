<?php

/**
 * USPS Address Verification API v3 Client
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2025 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\USPS;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Core\OEGlobalsBag;

class USPSAddressVerifyV3
{
    private Client $client;
    private string $clientId;
    private string $clientSecret;
    private static ?string $token = null;
    private static int $expiresAt = 0;
    private ?array $response = null;
    private ?string $error = null;

    private const BASE_URI = 'https://apis.usps.com/';
    private const TOKEN_ENDPOINT = 'oauth2/v3/token';
    private const ADDRESS_ENDPOINT = 'addresses/v3/address';
    private const TOKEN_BUFFER_SECONDS = 300;

    /**
     * Constructor - decrypts credentials from globals
     */
    public function __construct()
    {
        $cryptoGen = new CryptoGen();

        $globals = OEGlobalsBag::getInstance();
        $encryptedClientId = $globals->get('usps_apiv3_client_id');
        $encryptedClientSecret = $globals->get('usps_apiv3_client_secret');

        $this->clientId = !empty($encryptedClientId)
            ? $cryptoGen->decryptStandard($encryptedClientId)
            : '';
        $this->clientSecret = !empty($encryptedClientSecret)
            ? $cryptoGen->decryptStandard($encryptedClientSecret)
            : '';

        $this->client = new Client([
            'base_uri' => self::BASE_URI,
            'timeout' => 30
        ]);
    }

    /**
     * Check if v3 credentials are configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Get OAuth access token, refreshing if needed
     */
    private function getToken(): string
    {
        if (time() >= (self::$expiresAt - self::TOKEN_BUFFER_SECONDS)) {
            $this->refreshToken();
        }
        return self::$token;
    }

    /**
     * Fetch new OAuth token from USPS
     */
    private function refreshToken(): void
    {
        try {
            $response = $this->client->post(self::TOKEN_ENDPOINT, [
                'json' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            self::$token = $data['access_token'];
            self::$expiresAt = time() + (int)$data['expires_in'];
        } catch (GuzzleException $e) {
            $this->error = 'Failed to obtain access token: ' . $e->getMessage();
            throw $e;
        }
    }

    /**
     * Verify an address
     *
     * @param string $streetAddress Primary street address
     * @param string $secondaryAddress Apt/Suite/Unit (optional)
     * @param string $city City name
     * @param string $state State abbreviation
     * @param string $zip5 5-digit ZIP code
     * @param string $zip4 4-digit ZIP extension (optional)
     * @return bool Success status
     */
    public function verify(
        string $streetAddress,
        string $secondaryAddress = '',
        string $city = '',
        string $state = '',
        string $zip5 = '',
        string $zip4 = ''
    ): bool {
        $this->error = null;
        $this->response = null;

        if (!$this->isConfigured()) {
            $this->error = 'USPS API v3 credentials not configured';
            return false;
        }

        // Build query params, excluding empty values
        $query = ['streetAddress' => $streetAddress];

        if (!empty($secondaryAddress)) {
            $query['secondaryAddress'] = $secondaryAddress;
        }
        if (!empty($city)) {
            $query['city'] = $city;
        }
        if (!empty($state)) {
            $query['state'] = $state;
        }
        if (!empty($zip5)) {
            $query['ZIPCode'] = $zip5;
        }
        if (!empty($zip4) && preg_match('/^\d{4}$/', $zip4)) {
            $query['ZIPPlus4'] = $zip4;
        }

        try {
            $response = $this->client->get(self::ADDRESS_ENDPOINT, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getToken()
                ],
                'query' => $query
            ]);

            $this->response = json_decode($response->getBody()->getContents(), true);
            return true;
        } catch (ClientException $e) {
            $errorBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($errorBody, true);
            $this->error = $errorData['error']['message'] ?? $errorBody;
            return false;
        } catch (GuzzleException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Check if last request was successful
     */
    public function isSuccess(): bool
    {
        return $this->error === null && $this->response !== null;
    }

    /**
     * Check if last request resulted in error
     */
    public function isError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Get error message from last request
     */
    public function getErrorMessage(): ?string
    {
        return $this->error;
    }

    /**
     * Get the standardized address from response
     */
    public function getAddress(): ?array
    {
        if (!$this->isSuccess() || empty($this->response['address'])) {
            return null;
        }

        $addr = $this->response['address'];

        return [
            'streetAddress' => $addr['streetAddress'] ?? '',
            'secondaryAddress' => $addr['secondaryAddress'] ?? '',
            'city' => $addr['city'] ?? '',
            'state' => $addr['state'] ?? '',
            'ZIPCode' => $addr['ZIPCode'] ?? '',
            'ZIPPlus4' => $addr['ZIPPlus4'] ?? ''
        ];
    }

    /**
     * Get raw API response
     */
    public function getRawResponse(): ?array
    {
        return $this->response;
    }
}
