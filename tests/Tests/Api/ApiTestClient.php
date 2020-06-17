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
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
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
     * Credentials are optionally provided using the $credentials array. Supported
     * keys include username and password. If credentials are not provided they will be parsed
     * from environment variables or fallback to a reasonable default if the environment variable
     * does not exist.
     *
     * @param $authURL - The URL for authentication requests.
     * @param $credentials - The credentials used for authentication requests (associative array/map)
     * @return the authorization response
     *
     */
    public function setAuthToken($authURL, $credentials = array())
    {
        if (!empty($credentials)) {
            if (!array_key_exists("username", $credentials) || !array_key_exists("password", $credentials)) {
                throw new \InvalidArgumentException("username and password credentials are required");
            }
        } else {
            $credentials["username"] = getenv("OE_USER", true) ?: "admin";
            $credentials["password"] = getenv("OE_PASS", true) ?: "pass";
        }

        $authBody = [
            "grant_type" => "password",
            "username" => $credentials["username"],
            "password" => $credentials["password"],
            "scope" => "default"
        ];

        $authResponse = $this->post($authURL, $authBody);

        if ($authResponse->getStatusCode() == 200) {
            $responseBody = json_decode($authResponse->getBody());
            $this->headers[self::AUTHORIZATION_HEADER] = "Bearer " . $responseBody->access_token;
        }

        return $authResponse;
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
     * @param $baseUrl - The base url (http://someserver) for the OpenEMR host.
     * @param $isHttpErrorEnabled - Indicates if an exceptions are thrown within a HTTP error code is returned.
     *  Defaults to true.
     * @param $timeOut - The HTTP request timeout setting. Defaults to 10 seconds.
     */
    public function __construct($baseUrl, $isHttpErrorEnabled = true, $timeOut = 10)
    {
        $clientOptions = [
            "base_uri" => $baseUrl,
            "timeout" => $timeOut,
            "http_errors" => $isHttpErrorEnabled,
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ]
        ];

        $this->client = new Client($clientOptions);
        $this->headers = [];
    }

    /**
     * Submits a HTTP POST Request.
     * @param $url - The target URL (relative)
     * @param $body - The POST request body (array)
     * @return $postResponse - HTTP response
     */
    public function post($url, $body)
    {
        $postResponse = $this->client->post($url, [
            "headers" => $this->headers,
            "body" => json_encode($body)
        ]);
        return $postResponse;
    }

    /**
     * Submits a HTTP PUT Request.
     * @param $url - The target URL (relative)
     * @param $id - The resource id
     * @param $body - The PUT request body (array)
     * @return $putResponse - HTTP response
     */
    public function put($url, $id, $body)
    {
        $resourceUrl = $url . "/" . $id;

        $putResponse = $this->client->put($resourceUrl, [
            "headers" => $this->headers,
            "body" => json_encode($body)
        ]);
        return $putResponse;
    }

    /**
     * Submits a HTTP PATCH Request.
     * @param $url - The target URL (relative)
     * @param $id - The resource id
     * @param $body - The PATCH request body (array)
     * @return $patchResponse - HTTP response
     */
    public function patch($url, $id, $body)
    {
        $resourceUrl = $url . "/" . $id;

        $patchResponse = $this->client->patch($resourceUrl, [
            "headers" => $this->headers,
            "body" => json_encode($body)
        ]);
        return $patchResponse;
    }

    /**
     * Submits a HTTP GET request for a single resource.
     * @param $url - The target URL (relative)
     * @param $id - The resource id
     * @return $getResponse - HTTP response
     */
    public function getOne($url, $id)
    {
        $resourceUrl = $url . "/" . $id;
        $getResponse = $this->client->get($resourceUrl, ["headers" => $this->headers]);
        return $getResponse;
    }

    /**
     * Submits a HTTP GET request for multiple resources.
     * @param $url - The target URL (relative)
     * @param $params - Array of search parameters. Defaults to empty array.
     * @return $getResponse - HTTP response
     */
    public function get($url, $params = array())
    {
        $getResponse = $this->client->get($url, [
            "headers" => $this->headers,
            "query" => $params
            ]);
        return $getResponse;
    }
}
