<?php

/**
 * Extended JWT client-assertion negative-path tests.
 *
 * Covers the four rejection branches in JWTClientAuthenticationService
 * that sit outside performAdditionalValidations() and had no coverage:
 *
 *   - line 203: wrong client_assertion_type (not just missing)
 *   - line 210: empty client_assertion string
 *   - line 218/228: JWT parse failure or missing sub claim
 *   - line 239: catch block for JWT parse exceptions
 *
 * Sibling to JwtClientAssertionClaimValidationTest which covers the
 * inner claim-shape validations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

class JwtAssertionExtendedNegativesTest extends TestCase
{
    private const REDIRECT_URI = 'https://client.example/cb';

    private string $baseUrl;
    private ?string $clientId = null;
    private ?string $originalSiteAddrOath = null;
    private bool $siteAddrOathWasInserted = false;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }

        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            ['site_addr_oath']
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $this->originalSiteAddrOath = is_string($glValue) ? $glValue : null;
            if ($this->originalSiteAddrOath !== $this->baseUrl) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$this->baseUrl, 'site_addr_oath']
                );
            }
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                ['site_addr_oath', $this->baseUrl]
            );
            $this->siteAddrOathWasInserted = true;
        }
    }

    protected function tearDown(): void
    {
        try {
            if ($this->clientId !== null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$this->clientId]
                );
            }
        } finally {
            if ($this->siteAddrOathWasInserted) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `globals` WHERE gl_name = ?',
                    ['site_addr_oath']
                );
            } elseif ($this->originalSiteAddrOath !== null && $this->originalSiteAddrOath !== $this->baseUrl) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$this->originalSiteAddrOath, 'site_addr_oath']
                );
            }
        }
    }

    #[Test]
    public function testTokenRequestWithWrongClientAssertionTypeIsRejected(): void
    {
        // JWTClientAuthenticationService::extractClientIdFromJWT rejects
        // any client_assertion_type value that is not the jwt-bearer
        // constant (line 203). Locks that in.
        $http = $this->buildClient();
        $clientId = $this->registerJwtClient($http);
        $this->clientId = $clientId;

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_assertion_type' => 'urn:something:not-jwt-bearer',
                'client_assertion' => 'not-checked-because-type-fails-first',
                'scope' => 'system/Patient.read',
            ],
        ]);
        $this->assertTokenRejected($response, ['invalid_request', 'invalid_client']);
    }

    #[Test]
    public function testTokenRequestWithEmptyClientAssertionIsRejected(): void
    {
        // JWTClientAuthenticationService::extractClientIdFromJWT rejects
        // an empty client_assertion (line 210). Different from a
        // malformed one — this is "the parameter is present but empty."
        $http = $this->buildClient();
        $clientId = $this->registerJwtClient($http);
        $this->clientId = $clientId;

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => '',
                'scope' => 'system/Patient.read',
            ],
        ]);
        $this->assertTokenRejected($response, ['invalid_request', 'invalid_client']);
    }

    #[Test]
    public function testTokenRequestWithJwtMissingSubClaimIsRejected(): void
    {
        // JWTClientAuthenticationService::extractClientIdFromJWT line
        // 224-229: parses the JWT, reads $claims->get('sub'), throws
        // invalidClient if sub is empty. Build a signed JWT without a
        // sub claim so this branch fires.
        $http = $this->buildClient();
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $clientId = $this->registerJwtClient($http);
        $this->clientId = $clientId;

        $privateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $publicKey = InMemory::file($keyLocation . 'openemr-rsa384-public.pem');
        $configuration = Configuration::forAsymmetricSigner(new Sha384(), $privateKey, $publicKey);
        $now = new DateTimeImmutable();
        $token = $configuration->builder()
            // Intentionally omit ->relatedTo() so sub is missing.
            ->issuedBy($clientId)
            ->permittedFor($this->baseUrl . '/oauth2/default/token')
            ->identifiedBy(Uuid::uuid4()->toString())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+60 seconds'))
            ->getToken($configuration->signer(), $configuration->signingKey());

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $token->toString(),
                'scope' => 'system/Patient.read',
            ],
        ]);
        $this->assertTokenRejected($response, ['invalid_request', 'invalid_client']);
    }

    #[Test]
    public function testTokenRequestWithMalformedClientAssertionIsRejected(): void
    {
        // JWTClientAuthenticationService::extractClientIdFromJWT catches
        // CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound
        // at line 234 and re-throws as invalid_client (line 239). Send a
        // garbage string that superficially looks like a JWT (three
        // dot-separated segments) but has no valid content.
        $http = $this->buildClient();
        $clientId = $this->registerJwtClient($http);
        $this->clientId = $clientId;

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => 'this.is.not-a-valid-jwt-token-at-all',
                'scope' => 'system/Patient.read',
            ],
        ]);
        $this->assertTokenRejected($response, ['invalid_request', 'invalid_client']);
    }

    /**
     * @param list<string> $allowedErrors
     */
    private function assertTokenRejected(ResponseInterface $response, array $allowedErrors): void
    {
        $this->assertContains(
            $response->getStatusCode(),
            [400, 401],
            'JWT-assertion rejection must return 4xx. Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertContains(
            $body['error'] ?? null,
            $allowedErrors,
            'Error type must be one of ' . implode('|', $allowedErrors)
                . '. Body: ' . (string) $response->getBody()
        );
    }

    private function registerJwtClient(Client $http): string
    {
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $jwks = json_decode((string) file_get_contents($keyLocation . 'jwk-public-valid.json'));

        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [self::REDIRECT_URI],
                'client_name' => 'JwtAssertionExtendedNegativesTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'system/Patient.read',
                'jwks' => $jwks,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        return $data['client_id'];
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
