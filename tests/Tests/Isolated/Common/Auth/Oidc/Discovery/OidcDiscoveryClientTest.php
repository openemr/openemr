<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery;

use OpenEMR\Common\Auth\Oidc\Cache\FilesystemCache;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OidcDiscoveryClient::class)]
final class OidcDiscoveryClientTest extends TestCase
{
    private const ISSUER = 'https://accounts.example.com';

    private FakeHttpClient $httpClient;
    private FilesystemCache $cache;
    private string $cacheDir;
    private OidcDiscoveryClient $client;

    protected function setUp(): void
    {
        $this->httpClient = new FakeHttpClient();
        $this->cacheDir = sys_get_temp_dir() . '/oidc_discovery_test_' . bin2hex(random_bytes(8));
        mkdir($this->cacheDir, 0o755, true);
        $this->cache = new FilesystemCache($this->cacheDir);
        $this->client = new OidcDiscoveryClient($this->httpClient, $this->cache);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
        @rmdir($this->cacheDir);
    }

    private function setValidDiscoveryResponse(string $issuer = self::ISSUER): void
    {
        $this->httpClient->setNextResponse(200, json_encode([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer . '/o/oauth2/v2/auth',
            'token_endpoint' => $issuer . '/token',
            'jwks_uri' => $issuer . '/jwks',
            'userinfo_endpoint' => $issuer . '/userinfo',
            'response_types_supported' => ['code'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'email', 'profile'],
        ], JSON_THROW_ON_ERROR));
    }

    public function testGetMetadataFetchesAndReturnsMetadata(): void
    {
        $this->setValidDiscoveryResponse();

        $metadata = $this->client->getMetadata(self::ISSUER);

        self::assertSame(self::ISSUER, $metadata->issuer);
        self::assertSame(self::ISSUER . '/jwks', $metadata->jwksUri);
        self::assertSame(self::ISSUER . '/o/oauth2/v2/auth', $metadata->authorizationEndpoint);
    }

    public function testGetMetadataFetchesFromCorrectUrl(): void
    {
        $this->setValidDiscoveryResponse();

        $this->client->getMetadata(self::ISSUER);

        self::assertSame(
            self::ISSUER . '/.well-known/openid-configuration',
            $this->httpClient->getLastRequestUri(),
        );
    }

    public function testGetMetadataStripsTrailingSlashFromIssuer(): void
    {
        $this->setValidDiscoveryResponse();

        $this->client->getMetadata(self::ISSUER . '/');

        self::assertSame(
            self::ISSUER . '/.well-known/openid-configuration',
            $this->httpClient->getLastRequestUri(),
        );
    }

    public function testGetMetadataUsesCache(): void
    {
        $this->setValidDiscoveryResponse();

        $first = $this->client->getMetadata(self::ISSUER);
        $second = $this->client->getMetadata(self::ISSUER);

        self::assertSame($first->issuer, $second->issuer);
        self::assertSame(1, $this->httpClient->getRequestCount(), 'Second call should use cache');
    }

    public function testRefreshMetadataBypassesCache(): void
    {
        $this->setValidDiscoveryResponse();

        $this->client->getMetadata(self::ISSUER);

        // Change the response for the refresh
        $this->setValidDiscoveryResponse();
        $this->client->refreshMetadata(self::ISSUER);

        self::assertSame(2, $this->httpClient->getRequestCount(), 'refreshMetadata should bypass cache');
    }

    public function testRefreshMetadataUpdatesCacheForSubsequentGet(): void
    {
        $this->setValidDiscoveryResponse();

        $this->client->refreshMetadata(self::ISSUER);

        // Next getMetadata should use the refreshed cache
        $this->client->getMetadata(self::ISSUER);

        self::assertSame(1, $this->httpClient->getRequestCount(), 'getMetadata after refresh should use cache');
    }

    public function testThrowsOnHttpFailure(): void
    {
        $this->httpClient->setNextException(new \RuntimeException('Connection refused'));

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('Failed to fetch OIDC discovery document');

        $this->client->getMetadata(self::ISSUER);
    }

    public function testThrowsOnNon200Response(): void
    {
        $this->httpClient->setNextResponse(404, 'Not Found');

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('HTTP 404');

        $this->client->getMetadata(self::ISSUER);
    }

    public function testThrowsOnInvalidJson(): void
    {
        $this->httpClient->setNextResponse(200, 'not json at all');

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('not valid JSON');

        $this->client->getMetadata(self::ISSUER);
    }

    public function testThrowsOnNonObjectJson(): void
    {
        $this->httpClient->setNextResponse(200, '"just a string"');

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('JSON object');

        $this->client->getMetadata(self::ISSUER);
    }

    public function testThrowsOnIssuerMismatch(): void
    {
        $this->httpClient->setNextResponse(200, json_encode([
            'issuer' => 'https://evil.example.com',
            'authorization_endpoint' => 'https://evil.example.com/auth',
            'jwks_uri' => 'https://evil.example.com/jwks',
        ], JSON_THROW_ON_ERROR));

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('Issuer mismatch');

        $this->client->getMetadata(self::ISSUER);
    }

    public function testIssuerMatchIgnoresTrailingSlash(): void
    {
        $this->httpClient->setNextResponse(200, json_encode([
            'issuer' => self::ISSUER . '/',
            'authorization_endpoint' => self::ISSUER . '/auth',
            'jwks_uri' => self::ISSUER . '/jwks',
        ], JSON_THROW_ON_ERROR));

        $metadata = $this->client->getMetadata(self::ISSUER);

        self::assertSame(self::ISSUER . '/', $metadata->issuer);
    }

    public function testThrowsOnMissingRequiredFields(): void
    {
        $this->httpClient->setNextResponse(200, json_encode([
            'issuer' => self::ISSUER,
            // missing authorization_endpoint and jwks_uri
        ], JSON_THROW_ON_ERROR));

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('authorization_endpoint');

        $this->client->getMetadata(self::ISSUER);
    }

    public function testDoesNotCacheFailedFetches(): void
    {
        $this->httpClient->setNextResponse(500, 'Internal Server Error');

        try {
            $this->client->getMetadata(self::ISSUER);
        } catch (OidcDiscoveryException) {
            // expected
        }

        // Now provide a valid response — should fetch again (not cached error)
        $this->setValidDiscoveryResponse();
        $metadata = $this->client->getMetadata(self::ISSUER);

        self::assertSame(self::ISSUER, $metadata->issuer);
        self::assertSame(2, $this->httpClient->getRequestCount());
    }

    public function testDoesNotCacheIssuerMismatch(): void
    {
        $this->httpClient->setNextResponse(200, json_encode([
            'issuer' => 'https://wrong.example.com',
            'authorization_endpoint' => 'https://wrong.example.com/auth',
            'jwks_uri' => 'https://wrong.example.com/jwks',
        ], JSON_THROW_ON_ERROR));

        try {
            $this->client->getMetadata(self::ISSUER);
        } catch (OidcDiscoveryException) {
            // expected
        }

        $this->setValidDiscoveryResponse();
        $metadata = $this->client->getMetadata(self::ISSUER);

        self::assertSame(self::ISSUER, $metadata->issuer);
        self::assertSame(2, $this->httpClient->getRequestCount());
    }

    public function testCustomTtlIsUsed(): void
    {
        $shortTtlClient = new OidcDiscoveryClient($this->httpClient, $this->cache, 60);
        $this->setValidDiscoveryResponse();

        $shortTtlClient->getMetadata(self::ISSUER);

        // Verify it was cached (second call doesn't fetch)
        $shortTtlClient->getMetadata(self::ISSUER);
        self::assertSame(1, $this->httpClient->getRequestCount());
    }
}
