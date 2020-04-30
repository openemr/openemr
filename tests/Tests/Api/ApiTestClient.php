<?php

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;

/**
 * A simple and lightweight test client based off of GuzzleHttp, used in Rest Controller/API test cases.
 * The HTTP client supports:
 * - generating an OAuth2 access token for use with OpenEMR APIs
 * - submitting requests via relative URLs
 * - standard HTTP methods/verbs: POST, PUT, GET
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixon.whitmire@ibm.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixon.whitmire@ibm.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class ApiTestClient
{
    const AUTHORIZATION_HEADER = "Authorization";
    const OPENEMR_API_AUTH_ENDPOINT = "/apis/api/auth";
    const OPENEMR_FHIR_API_AUTH_ENDPOINT = "/apis/fhir/auth";

    protected $headers;
    protected $client;
    
    /**
     * Configures the HTTP client with reasonable defaults.
     * The Accept and Content-Type headers are both set for convenience.
     *
     * @param $baseUrl - The base URL consisting of protocol, host, and port (optional)
     * @param $isHttpErrorEnabled - Indicates if an exception is thrown for an error status code.
     *  Deaults to true.
     * @return configured client instance.
     */
    private function createClient($baseUrl, $isHttpErrorEnabled = true)
    {
        $this->headers = [];

        $client = new Client($baseUrl, [
            "timeout" => 10,
            "http_errors" => $isHttpErrorEnabled,
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ]
        ]);
        return $client;
    }

    /**
     * Returns a configuration settings from the GuzzleHTTP client instance.
     * If headers are requested, the default client headers are merged with the headers currently associated
     * with the client instance.
     */
    public function getConfig($config)
    {
        if ($config == null) {
            $message = "\$config is null. Expecting \$config to be a valid GuzzleHttp configuration setting";
            throw new \InvalidArgumentException($message);
        }

        $parsedConfig =  $this->client->getConfig($config);

        if ($config == 'headers') {
            $parsedConfig = array_merge_recursive($parsedConfig, $this->headers);
        }

        return $parsedConfig;
    }

    /**
     * Requests an auth token from an OpenEMR Auth Endpoint.
     * If the request succeeds the token is set in the HTTP Authorization header.
     *
     * @param $authURL - The URL for authentication requests.
     */
    public function setAuthToken($authURL)
    {
        $authBody = [
            "grant_type" => "password",
            "username" => "admin",
            "password" => "pass",
            "scope" => "default"
        ];

        $authResponse = $this->post($authURL, $authBody);
        $responseBody = json_decode($authResponse->getBody());
        $this->headers[self::AUTHORIZATION_HEADER] = "Bearer " . $responseBody->access_token;
    }

    /**
     * Removes the current authorization token from this instance's HTTP headers if present.
     */
    public function removeAuthToken()
    {
        if (array_key_exists(self::AUTHORIZATION_HEADER, $this->headers)) {
            unset($this->headers[self::AUTHORIZATION_HEADER]);
        }
    }

    /**
     * Creates a client instance with "reasonable" defaults.
     * @param $isHttpErrorEnabled - Indicates if an exceptions are thrown within a HTTP error code is returned.
     *  Defaults to true.
     * @param $timeOut - The HTTP request timeout setting. Defaults to 10 seconds.
     */
    public function __construct($isHttpErrorEnabled = true, $timeOut = 10)
    {
        $clientOptions = [
            "base_uri" => getenv("OPENEMR_BASE_URL", true) ?: "http://localhost",
            "timeout" => $timeOut,
            "headers" => [
                "Accept" => "application/json"
            ]
        ];

        $this->client = $this->createClient($clientOptions);
    }

    /**
     * Submits a HTTP Post Request.
     * @param $url - The target URL (relative)
     * @param $body - The POST request body (array)
     */
    public function post($url, $body)
    {
        $postResponse = $this->client->post($url, [
            "headers" => $this->headers,
            "body" => json_encode($body)
        ]);

        return $postResponse;
    }
}
