<?php

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;

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
    const OPENEMR_AUTH_ENDPOINT = "/oauth2/default";
    const OAUTH_LOGOUT_ENDPOINT = "/oauth2/default/logout";
    const OAUTH_TOKEN_ENDPOINT = "/oauth2/default/token";
    const OAUTH_INTROSPECTION_ENDPOINT = "/oauth2/default/introspect";
    const BOGUS_CLIENTID = "ugk_IdaC2szz-k0vIqhE6DYIjevkYo41neRGGpZvYfsgg";
    const BOGUS_CLIENTSECRET = "jJVKPZveRiyjAtfWFzxx_MF-3K2rGpDfzzrBjwq52L5_BvnqkCiKitcQDGgz_goJHiQt9yMTh3hu33vhp_UQOg";
    const BOGUS_ACCESS_TOKEN = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJRRl80RlRkV2h4eGJlb1Y4SGU5c1ZRQUl3STlQZWdYM3IyVUdiTTVjaWtNIiwianRpIjoiYWZmYWEyOTk5NWY5MjRjZDlmMjc0YTdhZGM5NmM0YzJmZTYwNGE3MjA0ODFkMTdmMWZiYTg2ZDg4ZWIxOWI0YWVlM2RhZTM1Zjg3MjcwMDAiLCJpYXQiOjE2MDcyMTc5MTksIm5iZiI6MTYwNzIxNzkxOSwiZXhwIjoxNjA3MjIxNTE5LCJzdWIiOiI5MjJjYzYzNi01NTNiLTQ1MGQtYTJkZC1hNmRjYmM4ZDNiMDciLCJzY29wZXMiOlsib3BlbmlkIiwic2l0ZTpkZWZhdWx0Il19.u2Ujtln3vEKIR8E0rSd-xr6m-3huCxiFMaTm9_NQplVNsBkrc6Y3KLy9FRZVS3xSY1Qgav-UvOxikYT2zNNlK-LotEoEvZdtj87X6fh4wh-h5BU87lHh9aNjXFUemSO9DGKJLSZdLSeC_2w4YmbhQykGFISiltD2_PAJRKuKbpcBo3-Lafe2N83mF5i8mZXSCu_fbLrmTMYPCnsRaU_sWStzFp6p0SM3zGfLt1kjw-hcE82Ci1puqRS2nR5Z3kEOXz-hQOdXmQMq0s_gkeQZvLPOJwLGfEX5d4eIU4BfngksjGkKQhC7rUKT-_2F-U_z30P3izzZM6m4dZ10IiP80g";
    const BOGUS_REFRESH_TOKEN = "def50200cd30606a46a09d2ba242e77528d247769112924ccff8e5d9ff9785ad032b6e91e22c9d716106efa0735b134f1bde452c9902ac75e1360ec2b8061b39c4b980ff0ffd18f9d66644c1bb3383feaa2594afd137475f60157ea6f5014cad0f5fa4e142fba5b414b7189e964ca154bbe9ffae90d0843dbf988f47485b41195eae073b5d1fa55c0b5c4a9ff5e876903d55ddd9ca1fbf7d70a0a6dcb70a76a91287b9cfd7e89ae91b4401142e46379ea7a573f9973a282fbd837a176051e25845300bf141033c2fcf28a7675106cc25e405852b13b4ab653eef2ac9f3c43db12f94a15b155c9533d2bad577e316194d179df281124280a993e438a806c5ba6a5b5c31c5a8893e3071ffb1df8507001f7b387c2882e8cd1e0ed50000dc2ad7954d243bdd4fac41e0bbced450a4f87e87317372cda3a6c22a5f9b6b6d8aab66e4d68739588bb4c5412a21d0e4f561fcb081eea24d7e79ba446630a53ebd05634735440181d73268f584ffa0b05e0708b0781ec5f8f3e2e92c0375d71d90f1f8e470d54cc4cb24b15545c4231edae046a9d9dd2fc78cc63768c66ffb19a9008fdd39952cd8e0e626747ac6f1dfdfc373f8064499533914d00452b70fe8a0353626a04ca57723e743";
    const ALL_SCOPES = "openid offline_access api:oemr api:fhir api:port user/allergy.read user/allergy.write user/appointment.read user/appointment.write user/dental_issue.read user/dental_issue.write user/document.read user/document.write user/drug.read user/encounter.read user/encounter.write user/facility.read user/facility.write user/immunization.read user/insurance.read user/insurance.write user/insurance_company.read user/insurance_company.write user/insurance_type.read user/list.read user/medical_problem.read user/medical_problem.write user/medication.read user/medication.write user/message.write user/patient.read user/patient.write user/practitioner.read user/practitioner.write user/prescription.read user/procedure.read user/soap_note.read user/soap_note.write user/surgery.read user/surgery.write user/vital.read user/vital.write user/AllergyIntolerance.read user/CareTeam.read user/Condition.read user/Coverage.read user/Encounter.read user/Immunization.read user/Location.read user/Medication.read user/MedicationRequest.read user/Observation.read user/Organization.read user/Organization.write user/Patient.read user/Patient.write user/Practitioner.read user/Practitioner.write user/PractitionerRole.read user/Procedure.read patient/encounter.read patient/patient.read patient/AllergyIntolerance.read patient/CareTeam.read patient/Condition.read patient/Coverage.read patient/Encounter.read patient/Immunization.read patient/MedicationRequest.read patient/Observation.read patient/Patient.read patient/Procedure.read";
    const PUBLIC_CLIENT_SCOPES = "openid api:oemr api:fhir api:port patient/encounter.read patient/patient.read patient/AllergyIntolerance.read patient/CareTeam.read patient/Condition.read patient/Coverage.read patient/Encounter.read patient/Immunization.read patient/Medication.read patient/MedicationRequest.read patient/Observation.read patient/Patient.read patient/Procedure.read";

    protected $headers;
    protected $client;
    protected $client_id;
    protected $client_secret;
    protected $id_token;
    protected $access_token;
    protected $refresh_token;

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
    public function setAuthToken($authURL, $credentials = array(), $client = 'private')
    {
        if (!empty($credentials) && !array_key_exists("client_id", $credentials)) {
            if (!array_key_exists("username", $credentials) || !array_key_exists("password", $credentials)) {
                throw new \InvalidArgumentException("username and password credentials are required");
            }
        } else {
            if (!empty($credentials['client_id'])) {
                $this->client_id = $credentials['client_id'];
            }
            $credentials["username"] = getenv("OE_USER", true) ?: "admin";
            $credentials["password"] = getenv("OE_PASS", true) ?: "pass";
        }

        if (empty($this->client_id)) {
            $this->getClient($authURL, $client);
        }

        $authBody = [
            "grant_type" => "password",
            "client_id" => $this->client_id,
            "scope" => self::ALL_SCOPES,
            "user_role" => "users",
            "username" => $credentials["username"],
            "password" => $credentials["password"]
        ];
        $this->headers = [
            "Accept" => "application/json",
            "Content-Type" => "application/x-www-form-urlencoded"
        ];
        $authResponse = $this->post($authURL . '/token', $authBody, false);
        // set headers back to default
        $this->headers = [
            "Accept" => "application/json",
            "Content-Type" => "application/json"
        ];
        if ($authResponse->getStatusCode() == 200) {
            $responseBody = json_decode($authResponse->getBody());
            $this->headers[self::AUTHORIZATION_HEADER] = "Bearer " . $responseBody->access_token;
            $this->id_token = $responseBody->id_token;
            $this->access_token = $responseBody->access_token;
            $this->refresh_token = $responseBody->refresh_token ?? null;
        }

        return $authResponse;
    }

    private function getClient($authURL, $client = 'private')
    {
        $scope = self::ALL_SCOPES;
        if ($client != 'private') {
            $scope = self::PUBLIC_CLIENT_SCOPES;
        }

        $clientBody = [
            "application_type" => $client,
            "redirect_uris" => ["https://client.example.org/callback"],
            "client_name" => "A Private App",
            "token_endpoint_auth_method" => "client_secret_post",
            "contacts" => ["me@example.org", "them@example.org"],
            "scope" => $scope
        ];
        $clientResponse = $this->post($authURL . '/registration', $clientBody);
        $clientResponseBody = json_decode($clientResponse->getBody());
        $this->client_id = $clientResponseBody->client_id;
        $this->client_secret = $clientResponseBody->client_secret;
        // we need to enable the app otherwise we can't use it.
        $clientRepository = new ClientRepository();
        $client = $clientRepository->getClientEntity($this->client_id);
        $clientRepository->saveIsEnabled($client, true);
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

    public function cleanupClient()
    {
        sqlStatementNoLog("DELETE FROM `oauth_clients` WHERE `client_id` = ?", [$this->client_id]);
        sqlStatementNoLog("DELETE FROM `api_token` WHERE `client_id` = ?", [$this->client_id]);
    }

    public function cleanupRevokeAuth()
    {
        return $this->get(self::OAUTH_LOGOUT_ENDPOINT, ['id_token_hint' => $this->id_token]);
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getClientSecret()
    {
        return $this->client_secret;
    }

    public function getIdToken()
    {
        return $this->id_token;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    public function setHeaders(array $headers)
    {
        return $this->headers = $headers;
    }

    public function setBearer(string $bearer)
    {
        return $this->headers[self::AUTHORIZATION_HEADER] = $bearer;
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
            "verify" => false,
            "base_uri" => $baseUrl,
            "timeout" => $timeOut,
            "http_errors" => $isHttpErrorEnabled
        ];
        $this->client = new Client($clientOptions);
        $this->headers = [
            "Accept" => "application/json",
            "Content-Type" => "application/json"
        ];
    }

    /**
     * Submits a HTTP POST Request.
     * @param $url - The target URL (relative)
     * @param $body - The POST request body (array)
     * @return $postResponse - HTTP response
     */
    public function post($url, $body, $json = true)
    {
        if ($json) {
            $postResponse = $this->client->post($url, [
                "headers" => $this->headers,
                "body" => json_encode($body)
            ]);
        } else {
            $postResponse = $this->client->post($url, [
                "headers" => $this->headers,
                "form_params" => $body
            ]);
        }
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
