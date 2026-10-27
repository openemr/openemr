<?php

/**
 * JWT-authenticated grant with a valid signed JWT whose sub points at
 * a client that does not exist in oauth_clients.
 *
 * Extracts fine (sub is a non-empty string) but the subsequent
 * getClientEntity(sub) returns false, so both CustomAuthCodeGrant:196
 * and CustomClientCredentialsGrant:200 throw invalidClient. The JWT
 * signature is never verified because the client entity lookup fails
 * first — no JWKS to verify against anyway.
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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class JwtAssertionUnknownClientTest extends TestCase
{
    private string $baseUrl;

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
    }

    #[Test]
    public function testClientCredentialsGrantWithUnknownClientInJwtSubIsRejected(): void
    {
        $http = $this->buildClient();
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $privateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $publicKey = InMemory::file($keyLocation . 'openemr-rsa384-public.pem');

        // Non-existent client id — no oauth_clients row has this value.
        $unknownClientId = 'no-such-client-' . Uuid::uuid4()->toString();

        $configuration = Configuration::forAsymmetricSigner(new Sha384(), $privateKey, $publicKey);
        $now = new DateTimeImmutable();
        $token = $configuration->builder()
            ->issuedBy($unknownClientId)
            ->relatedTo($unknownClientId)
            ->permittedFor($this->baseUrl . '/oauth2/default/token')
            ->identifiedBy(Uuid::uuid4()->toString())
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify('+60 seconds'))
            ->getToken($configuration->signer(), $configuration->signingKey());

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $unknownClientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $token->toString(),
                'scope' => 'system/Patient.read',
            ],
        ]);
        $this->assertContains(
            $response->getStatusCode(),
            [400, 401],
            'JWT for a nonexistent client_id must be rejected. '
                . 'Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertContains(
            $body['error'] ?? null,
            ['invalid_request', 'invalid_client'],
            'Nonexistent client must return invalid_request or invalid_client. '
                . 'Body: ' . (string) $response->getBody()
        );
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
