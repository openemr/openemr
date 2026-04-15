<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Token;

use OpenEMR\Common\Auth\Oidc\Cache\FilesystemCache;
use OpenEMR\Common\Auth\Oidc\Token\JwksClient;
use OpenEMR\Common\Auth\Oidc\Token\JwksException;
use OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery\FakeHttpClient;
use PHPUnit\Framework\TestCase;

final class JwksClientTest extends TestCase
{
    private const JWKS_URI = 'https://provider.example.com/jwks';

    private FakeHttpClient $httpClient;
    private FilesystemCache $cache;
    private string $cacheDir;
    private JwksClient $client;

    protected function setUp(): void
    {
        $this->httpClient = new FakeHttpClient();
        $this->cacheDir = sys_get_temp_dir() . '/oidc_jwks_test_' . bin2hex(random_bytes(8));
        mkdir($this->cacheDir, 0o755, true);
        $this->cache = new FilesystemCache($this->cacheDir);
        $this->client = new JwksClient($this->httpClient, $this->cache);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
        @rmdir($this->cacheDir);
    }

    /**
     * @param list<array<string, mixed>> $keys
     * @throws \JsonException
     */
    private function setJwksResponse(array $keys): void
    {
        $this->httpClient->setNextResponse(200, json_encode(
            ['keys' => $keys],
            JSON_THROW_ON_ERROR,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private static function rsaKey(string $kid, string $alg = 'RS256'): array
    {
        return [
            'kty' => 'RSA',
            'kid' => $kid,
            'alg' => $alg,
            'use' => 'sig',
            'n' => 'test-modulus',
            'e' => 'AQAB',
        ];
    }

    public function testGetSigningKeyReturnsMatchingKey(): void
    {
        $this->setJwksResponse([self::rsaKey('key-1'), self::rsaKey('key-2')]);

        $key = $this->client->getSigningKey(self::JWKS_URI, 'key-2');

        self::assertSame('key-2', $key->kid);
        self::assertSame('RSA', $key->kty);
    }

    public function testGetSigningKeyUsesCache(): void
    {
        $this->setJwksResponse([self::rsaKey('key-1')]);

        $this->client->getSigningKey(self::JWKS_URI, 'key-1');

        // Second call — should use cache
        $this->setJwksResponse([self::rsaKey('key-1')]);
        $this->client->getSigningKey(self::JWKS_URI, 'key-1');

        // Only 1 HTTP request: cached call doesn't fetch, but rotation
        // check doesn't trigger because kid was found in cache
        self::assertSame(1, $this->httpClient->getRequestCount());
    }

    public function testGetSigningKeyRefetchesOnKidMiss(): void
    {
        // First response has only key-1
        $this->setJwksResponse([self::rsaKey('key-1')]);
        $this->client->getKeys(self::JWKS_URI); // Prime cache

        // Now ask for key-2 — not in cache, should refetch
        $this->setJwksResponse([self::rsaKey('key-1'), self::rsaKey('key-2')]);
        $key = $this->client->getSigningKey(self::JWKS_URI, 'key-2');

        self::assertSame('key-2', $key->kid);
        self::assertSame(2, $this->httpClient->getRequestCount());
    }

    public function testGetSigningKeyThrowsAfterRefetchIfKidStillMissing(): void
    {
        $this->setJwksResponse([self::rsaKey('key-1')]);
        $this->client->getKeys(self::JWKS_URI); // Prime cache

        // Refetch still doesn't have key-999
        $this->setJwksResponse([self::rsaKey('key-1')]);

        $this->expectException(JwksException::class);
        $this->expectExceptionMessage("kid 'key-999'");

        $this->client->getSigningKey(self::JWKS_URI, 'key-999');
    }

    public function testGetSigningKeySkipsEncryptionKeys(): void
    {
        $encKey = self::rsaKey('enc-key');
        $encKey['use'] = 'enc';

        $this->setJwksResponse([$encKey, self::rsaKey('sig-key')]);

        $key = $this->client->getSigningKey(self::JWKS_URI, 'sig-key');
        self::assertSame('sig-key', $key->kid);
    }

    public function testGetSigningKeyIgnoresEncryptionKeyEvenIfKidMatches(): void
    {
        $encKey = self::rsaKey('shared-kid');
        $encKey['use'] = 'enc';

        // Prime cache with only an enc key
        $this->setJwksResponse([$encKey]);
        $this->client->getKeys(self::JWKS_URI);

        // Refetch still only has enc key
        $this->setJwksResponse([$encKey]);

        $this->expectException(JwksException::class);
        $this->expectExceptionMessage("kid 'shared-kid'");

        $this->client->getSigningKey(self::JWKS_URI, 'shared-kid');
    }

    public function testGetKeysReturnsAllKeys(): void
    {
        $this->setJwksResponse([self::rsaKey('a'), self::rsaKey('b'), self::rsaKey('c')]);

        $keys = $this->client->getKeys(self::JWKS_URI);

        self::assertCount(3, $keys);
        self::assertSame('a', $keys[0]->kid);
        self::assertSame('b', $keys[1]->kid);
        self::assertSame('c', $keys[2]->kid);
    }

    public function testGetKeysSkipsMalformedKeys(): void
    {
        $this->setJwksResponse([
            self::rsaKey('good-key'),
            ['kty' => 'RSA'], // Missing kid
            ['kid' => 'no-kty'], // Missing kty
            self::rsaKey('another-good-key'),
        ]);

        $keys = $this->client->getKeys(self::JWKS_URI);

        self::assertCount(2, $keys);
        self::assertSame('good-key', $keys[0]->kid);
        self::assertSame('another-good-key', $keys[1]->kid);
    }

    public function testRefreshKeysBypassesCache(): void
    {
        $this->setJwksResponse([self::rsaKey('key-1')]);
        $this->client->getKeys(self::JWKS_URI); // Prime cache

        $this->setJwksResponse([self::rsaKey('key-1'), self::rsaKey('key-2')]);
        $keys = $this->client->refreshKeys(self::JWKS_URI);

        self::assertCount(2, $keys);
        self::assertSame(2, $this->httpClient->getRequestCount());
    }

    public function testThrowsOnHttpFailure(): void
    {
        $this->httpClient->setNextException(new \RuntimeException('Connection refused'));

        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('Failed to fetch JWKS');

        $this->client->getKeys(self::JWKS_URI);
    }

    public function testThrowsOnNon200Response(): void
    {
        $this->httpClient->setNextResponse(500, 'Server Error');

        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('HTTP 500');

        $this->client->getKeys(self::JWKS_URI);
    }

    public function testThrowsOnInvalidJson(): void
    {
        $this->httpClient->setNextResponse(200, 'not-json');

        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('not valid JSON');

        $this->client->getKeys(self::JWKS_URI);
    }

    public function testThrowsOnMissingKeysArray(): void
    {
        $this->httpClient->setNextResponse(200, '{"no_keys": true}');

        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('"keys" array');

        $this->client->getKeys(self::JWKS_URI);
    }

    public function testDoesNotCacheFailedFetches(): void
    {
        $this->httpClient->setNextResponse(500, 'Error');

        try {
            $this->client->getKeys(self::JWKS_URI);
        } catch (JwksException) {
            // expected
        }

        // Now succeed — should fetch again (error not cached)
        $this->setJwksResponse([self::rsaKey('key-1')]);
        $keys = $this->client->getKeys(self::JWKS_URI);

        self::assertCount(1, $keys);
        self::assertSame(2, $this->httpClient->getRequestCount());
    }

    public function testFetchesFromCorrectUri(): void
    {
        $this->setJwksResponse([self::rsaKey('key-1')]);

        $this->client->getKeys(self::JWKS_URI);

        self::assertSame(self::JWKS_URI, $this->httpClient->getLastRequestUri());
    }
}
