<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use Lcobucci\JWT\Signer\Key\InMemory;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Tools\OAuth2\ClientCredentialsAssertionGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;

/**
 * BulkAPITestClient is a test client for the OpenEMR Bulk API.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class BulkAPITestClient extends ApiTestClient
{
    protected string $baseUrl;

    private string $scopes = self::SYSTEM_SCOPES;

    public function __construct(string $baseUrl, bool $isHttpErrorEnabled = true, int $timeOut = 10)
    {
        parent::__construct($baseUrl, $isHttpErrorEnabled, $timeOut);
        $this->baseUrl = $baseUrl;
    }

    const SYSTEM_SCOPES = 'system/Group.$export system/Binary.read system/*.$bulkdata-status system/Patient.read system/Medication.read system/AllergyIntolerance.read system/CarePlan.read system/CareTeam.read system/Condition.read system/Device.read system/DiagnosticReport.read system/DocumentReference.read system/Encounter.read system/Goal.read system/Immunization.read system/Location.read system/MedicationRequest.read system/Observation.read system/Organization.read system/Practitioner.read system/Procedure.read system/Provenance.read';
    const SYSTEM_SCOPES_V2 = 'system/Patient.$export system/Group.$export system/*.$bulkdata-status system/*.$export system/Patient.rs system/Group.rs system/Medication.rs system/AllergyIntolerance.rs system/CarePlan.rs system/CareTeam.rs system/Condition.rs system/Device.rs system/DiagnosticReport.rs system/DocumentReference.rs system/Encounter.rs system/Goal.rs system/Immunization.rs system/Location.rs system/MedicationRequest.rs system/Observation.rs system/Organization.rs system/Practitioner.rs system/Procedure.rs system/Provenance.rs system/Binary.rs system/ServiceRequest.rs system/Specimen.rs system/QuestionnaireResponse.rs';

    public function setScopesForBulkData(string $scopes): void
    {
        $this->scopes = $scopes;
    }

    /**
     * @param array<string, mixed> $credentials
     */
    public function setAuthToken(string $authURL, array $credentials = [], string $client = 'private'): ResponseInterface
    {
        if (($credentials['client_id'] ?? '') !== '') {
            assert(is_string($credentials['client_id']));
            $this->client_id = $credentials['client_id'];
        }
        if (!(($credentials['jwks'] ?? null) === null && ($credentials['private_key'] ?? null) === null && ($credentials['public_key'] ?? null) === null)) {
            $privateKey = $credentials['private_key'];
            $publicKey = $credentials['public_key'];
        } else {
            $keyLocation = __DIR__ . "/../data/Unit/Common/Auth/Grant/";
            $jwksFile = $keyLocation . "jwk-public-valid.json";
            if (!file_exists($jwksFile)) {
                throw new \RuntimeException("JWKs file not found: " . $jwksFile);
            }
            $credentials['jwks'] = json_decode((string) file_get_contents($jwksFile));
            $privateKey = InMemory::file($keyLocation . "openemr-rsa384-private.key");
            $publicKey = InMemory::file($keyLocation . "openemr-rsa384-public.pem");
        }

        if ($this->client_id === null || $this->client_id === '') {
            $clientEntity = $this->registerClient($authURL, $credentials['jwks']);
            /** @var string $identifier */
            $identifier = $clientEntity->getIdentifier();
            $this->client_id = $identifier;
        }

        $clientId = $this->client_id;
        assert($clientId !== '');

        $oauthTokenUrl = $this->baseUrl . $authURL . '/token';

        // SMART Backend Services / RFC 7515 §4.1.4: the OAuth server's
        // JWT client-assertion validator requires a `kid` header so it
        // can pick the right JWK out of the client's registered set.
        // Pull the kid off the first key of the JWKS we just registered.
        $kid = $this->extractKidFromJwks($credentials['jwks']);

        /** @var InMemory $privateKey */
        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            /** @var InMemory $publicKey */
            $publicKey,
            $oauthTokenUrl,
            $clientId,
            $kid,
        );
        $authBody = [
            "client_assertion_type" => CustomClientCredentialsGrant::OAUTH_JWT_CLIENT_ASSERTION_TYPE,
            "client_assertion" => $assertion,
            "grant_type" => "client_credentials",
            "client_id" => $this->client_id,
            "scope" => $this->scopes
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
        if ($authResponse->getStatusCode() === 200) {
            /** @var \stdClass&object{access_token: string} $responseBody */
            $responseBody = json_decode((string) $authResponse->getBody());
            $this->headers[self::AUTHORIZATION_HEADER] = "Bearer " . $responseBody->access_token;
            // credentials grant only has access token
            $this->access_token = $responseBody->access_token;
        }

        return $authResponse;
    }

    /**
     * Extract the first key's `kid` from a registered JWKS.
     *
     * Accepts either the decoded JSON object form (stdClass with a
     * `keys` array, what `json_decode((string) file_get_contents(...))`
     * yields here) or a plain array form. Normalize to an array via a
     * json round-trip so the rest of the method works on a single
     * shape — keeps PHPStan from chasing dynamic stdClass properties
     * and lets us treat both call paths identically. Returns null only
     * when the structure is unrecognizable; callers then leave the
     * JWT `kid` header off and the server-side rejection makes the
     * fixture problem loud.
     *
     * @param mixed $jwks
     * @return non-empty-string|null Filtered to non-empty so the caller
     *   can pass it straight to `ClientCredentialsAssertionGenerator`,
     *   whose `$kid` parameter is `non-empty-string|null`.
     */
    private function extractKidFromJwks($jwks): ?string
    {
        $encoded = json_encode($jwks);
        if (!is_string($encoded)) {
            return null;
        }
        $normalized = json_decode($encoded, true);
        if (!is_array($normalized) || !isset($normalized['keys']) || !is_array($normalized['keys'])) {
            return null;
        }

        $first = $normalized['keys'][0] ?? null;
        if (!is_array($first) || !isset($first['kid']) || !is_string($first['kid']) || $first['kid'] === '') {
            return null;
        }

        return $first['kid'];
    }

    /**
     * @param mixed $jwks
     */
    public function registerClient(string $authURL, $jwks): ClientEntity
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
        $clientResponseBodyRaw = (string) $clientResponse->getBody();
        /** @var (\stdClass&object{client_id: string, client_secret: string})|null $clientResponseBody */
        $clientResponseBody = json_decode($clientResponseBodyRaw);
        if ($clientResponseBody === null) {
            throw new \RuntimeException("Client registration response could not be decoded");
        }
        $this->client_id = $clientResponseBody->client_id;
        $this->client_secret = $clientResponseBody->client_secret;
        // we need to enable the app otherwise we can't use it.
        $clientRepository = new ClientRepository();
        $clientRepository->setSystemLogger(new NullLogger());
        $clientEntity = $clientRepository->getClientEntity($this->client_id);
        assert($clientEntity instanceof ClientEntity);
        $clientRepository->saveIsEnabled($clientEntity, true);
        return $clientEntity;
    }
}
