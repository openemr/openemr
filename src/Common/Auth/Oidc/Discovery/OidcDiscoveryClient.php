<?php

/**
 * Fetches and caches OIDC provider metadata from /.well-known/openid-configuration.
 *
 * Discovery documents change rarely, so results are cached with a configurable
 * TTL (default 24 hours). The client is provider-agnostic — it works with any
 * spec-compliant OIDC provider (Google/GCIP, Azure AD, Okta, Keycloak, etc.).
 *
 * @see https://openid.net/specs/openid-connect-discovery-1_0.html
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Discovery;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;

readonly class OidcDiscoveryClient
{
    private const WELL_KNOWN_PATH = '/.well-known/openid-configuration';
    private const CACHE_KEY_PREFIX = 'oidc_discovery_';
    private const DEFAULT_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private ClientInterface $httpClient,
        private CacheInterface $cache,
        private int $ttlSeconds = self::DEFAULT_TTL_SECONDS,
        private ?OidcUrlValidator $urlValidator = null,
    ) {
    }

    /**
     * Fetch provider metadata, using the cache when available.
     *
     * @param string $issuerUrl The provider's issuer URL (e.g. "https://accounts.google.com").
     */
    public function getMetadata(string $issuerUrl): OidcProviderMetadata
    {
        $cacheKey = self::CACHE_KEY_PREFIX . hash('sha256', $issuerUrl);

        /** @var array<string, mixed>|null $cached */
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached)) {
            return OidcProviderMetadata::fromDiscoveryDocument($cached);
        }

        return $this->fetchAndCache($issuerUrl, $cacheKey);
    }

    /**
     * Fetch provider metadata directly, bypassing and refreshing the cache.
     *
     * Use this when a cached JWKS key ID doesn't match a token's kid —
     * the provider may have rotated keys and published a new jwks_uri
     * or new metadata.
     *
     * @param string $issuerUrl The provider's issuer URL.
     */
    public function refreshMetadata(string $issuerUrl): OidcProviderMetadata
    {
        $cacheKey = self::CACHE_KEY_PREFIX . hash('sha256', $issuerUrl);

        return $this->fetchAndCache($issuerUrl, $cacheKey);
    }

    private function fetchAndCache(string $issuerUrl, string $cacheKey): OidcProviderMetadata
    {
        $discoveryUrl = $this->buildDiscoveryUrl($issuerUrl);
        $this->assertSafeDiscoveryUrl($discoveryUrl);

        $document = $this->fetchDiscoveryDocument($discoveryUrl);
        $metadata = OidcProviderMetadata::fromDiscoveryDocument($document);

        $this->validateIssuerMatch($issuerUrl, $metadata->issuer);
        $this->assertSafeJwksUri($metadata->jwksUri, $issuerUrl);

        $this->cache->set($cacheKey, $document, $this->ttlSeconds);

        return $metadata;
    }

    private function assertSafeDiscoveryUrl(string $discoveryUrl): void
    {
        if ($this->urlValidator === null) {
            return;
        }

        try {
            $this->urlValidator->validateDiscoveryUrl($discoveryUrl);
        } catch (OidcUrlValidationException $e) {
            throw new OidcDiscoveryException('Refusing to fetch from unsafe discovery URL', 0, $e);
        }
    }

    private function assertSafeJwksUri(string $jwksUri, string $issuerUrl): void
    {
        if ($this->urlValidator === null) {
            return;
        }

        try {
            $this->urlValidator->validateJwksUri($jwksUri, $issuerUrl);
        } catch (OidcUrlValidationException $e) {
            throw new OidcDiscoveryException('Refusing to use unsafe jwks_uri from discovery document', 0, $e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchDiscoveryDocument(string $url): array
    {
        try {
            $request = new Request('GET', $url);
            $response = $this->httpClient->sendRequest($request);
        } catch (\Throwable $e) {
            throw new OidcDiscoveryException(
                'Failed to fetch OIDC discovery document',
                0,
                $e,
            );
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new OidcDiscoveryException(
                "OIDC discovery endpoint returned HTTP {$statusCode}",
            );
        }

        $body = $response->getBody()->getContents();

        try {
            $document = json_decode($body, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new OidcDiscoveryException(
                'OIDC discovery document is not valid JSON',
                0,
                $e,
            );
        }

        if (!is_array($document)) {
            throw new OidcDiscoveryException(
                'OIDC discovery document root must be a JSON object',
            );
        }

        /** @var array<string, mixed> $document JSON objects decode to string-keyed arrays */
        return $document;
    }

    private function buildDiscoveryUrl(string $issuerUrl): string
    {
        return rtrim($issuerUrl, '/') . self::WELL_KNOWN_PATH;
    }

    /**
     * The OIDC spec requires that the issuer in the discovery document
     * exactly matches the issuer URL used to fetch it.
     */
    private function validateIssuerMatch(string $expectedIssuer, string $actualIssuer): void
    {
        $expected = rtrim($expectedIssuer, '/');
        $actual = rtrim($actualIssuer, '/');

        if ($expected !== $actual) {
            throw new OidcDiscoveryException(
                "Issuer mismatch: expected '{$expected}', got '{$actual}'",
            );
        }
    }
}
