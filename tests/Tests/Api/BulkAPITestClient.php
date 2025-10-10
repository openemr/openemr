<?php

namespace OpenEMR\Tests\Api;

use Lcobucci\JWT\Signer\Key\InMemory;
use Monolog\Level;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Tools\OAuth2\ClientCredentialsAssertionGenerator;

/**
 * BulkAPITestClient is a test client for the OpenEMR Bulk API.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2025 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class BulkAPITestClient extends ApiTestClient
{
    protected string $baseUrl;

    public function __construct($baseUrl, $isHttpErrorEnabled = true, $timeOut = 10)
    {
        parent::__construct($baseUrl, $isHttpErrorEnabled, $timeOut);
        $this->baseUrl = $baseUrl;
    }

    const SYSTEM_SCOPES = 'system/Group.$export system/Binary.read system/*.$bulkdata-status system/Patient.read system/Medication.read system/AllergyIntolerance.read system/CarePlan.read system/CareTeam.read system/Condition.read system/Device.read system/DiagnosticReport.read system/DocumentReference.read system/Encounter.read system/Goal.read system/Immunization.read system/Location.read system/MedicationRequest.read system/Observation.read system/Organization.read system/Practitioner.read system/Procedure.read system/Provenance.read';

    public function setAuthToken($authURL, $credentials = [], $client = 'private')
    {
        if (!empty($credentials['client_id'])) {
            $this->client_id = $credentials['client_id'];
        }
        if (!(empty($credentials['jwks']) && empty($credentials['private_key']) && empty($credentials['public_key']))) {
            $privateKey = $credentials['private_key'];
            $publicKey = $credentials['public_key'];
        } else {
            $keyLocation = __DIR__ . "/../data/Unit/Common/Auth/Grant/";
            $jwksFile = $keyLocation . "jwk-public-valid.json";
            if (!file_exists($jwksFile)) {
                throw new \RuntimeException("JWKs file not found: " . $jwksFile);
            }
            $credentials['jwks'] = json_decode(file_get_contents($jwksFile));
            $privateKey = InMemory::file($keyLocation . "openemr-rsa384-private.key");
            $publicKey = InMemory::file($keyLocation . "openemr-rsa384-public.pem");
        }

        if (empty($this->client_id)) {
            $client = $this->registerClient($authURL, $credentials['jwks']);
            $this->client_id = $client->getIdentifier();
        }

        $oauthTokenUrl = $this->baseUrl . $authURL . '/token';
        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $oauthTokenUrl,
            $this->client_id
        );
        $authBody = [
            "client_assertion_type" => CustomClientCredentialsGrant::OAUTH_JWT_CLIENT_ASSERTION_TYPE,
            "client_assertion" => $assertion,
            "grant_type" => "client_credentials",
            "client_id" => $this->client_id,
            "scope" => self::SYSTEM_SCOPES,
        ];
        $this->headers = [
            "Accept" => "application/json",
            "Content-Type" => "application/x-www-form-urlencoded"
        ];
        $authResponse = $this->post($oauthTokenUrl, $authBody, false);
        // set headers back to default
        $this->headers = [
            "Accept" => "application/json",
            "Content-Type" => "application/json"
        ];
        if ($authResponse->getStatusCode() == 200) {
            $responseBody = json_decode($authResponse->getBody());
            $this->headers[self::AUTHORIZATION_HEADER] = "Bearer " . $responseBody->access_token;
            // credentials grant only has access token
            $this->access_token = $responseBody->access_token;
        }

        return $authResponse;
    }


    public function registerClient($authURL, $jwks)
    {
        $clientBody = [
            "application_type" => 'private',
            "redirect_uris" => ["https://client.example.org/callback"],
            "client_name" => "Bulk API Private Test Client",
            "token_endpoint_auth_method" => "client_secret_post",
            "contacts" => ["me@example.org", "them@example.org"],
            "scope" => self::SYSTEM_SCOPES
            ,'jwks' => $jwks
        ];
        $clientResponse = $this->post($authURL . '/registration', $clientBody);
        if ($clientResponse->getStatusCode() >= 400) {
            throw new \RuntimeException("Client registration failed with status code: " . $clientResponse->getStatusCode());
        }
        $clientResponseBodyRaw = $clientResponse->getBody();
        $clientResponseBody = json_decode($clientResponseBodyRaw);
        if ($clientResponseBody === null) {
            throw new \RuntimeException("Client registration response could not be decoded");
        }
        $this->client_id = $clientResponseBody->client_id;
        $this->client_secret = $clientResponseBody->client_secret;
        // we need to enable the app otherwise we can't use it.
        $clientRepository = new ClientRepository();
        $logger = new SystemLogger(Level::Emergency); // suppress logging
        $clientRepository->setSystemLogger($logger);
        $client = $clientRepository->getClientEntity($this->client_id);
        $clientRepository->saveIsEnabled($client, true);
        return $client;
    }
}
