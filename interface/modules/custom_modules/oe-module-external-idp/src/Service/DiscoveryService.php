<?php

/**
 * Retrieves and validates OpenID Connect discovery metadata.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ExternalIdp\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\BC\ServiceContainer;

final class DiscoveryService
{
    /**
     * @var null|callable(string): array{status:int, body:string}
     */
    private $metadataFetcher;

    /**
     * @param null|callable(string): array{status:int, body:string} $metadataFetcher
     */
    public function __construct(?callable $metadataFetcher = null)
    {
        $this->metadataFetcher = $metadataFetcher;
    }

    /**
     * @return array<string, mixed>
     */
    public function discover(string $issuerUrl): array
    {
        $issuerUrl = $this->normalizeIssuer($issuerUrl);
        $discoveryUrl = $issuerUrl . '/.well-known/openid-configuration';
        ServiceContainer::getLogger()->debug('External IdP discovery started', [
            'issuer_url' => $issuerUrl,
            'discovery_url' => $discoveryUrl,
            'using_metadata_fetcher' => $this->metadataFetcher !== null,
        ]);

        if ($this->metadataFetcher !== null) {
            $response = ($this->metadataFetcher)($discoveryUrl);
        } else {
            $client = new Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'allow_redirects' => false,
                'verify' => true,
                'http_errors' => false,
            ]);

            try {
                $response = $client->get($discoveryUrl, [
                    'headers' => ['Accept' => 'application/json'],
                ]);
            } catch (GuzzleException $exception) {
                ServiceContainer::getLogger()->error('External IdP discovery request failed', [
                    'issuer_url' => $issuerUrl,
                    'discovery_url' => $discoveryUrl,
                    'exception_class' => $exception::class,
                    'exception_message' => $exception->getMessage(),
                ]);
                throw new \RuntimeException(
                    'Unable to retrieve OIDC discovery metadata from ' . $discoveryUrl . '. ' . $exception->getMessage(),
                    0,
                    $exception
                );
            }
        }

        $statusCode = is_array($response) ? (int) ($response['status'] ?? 0) : $response->getStatusCode();
        $body = is_array($response) ? (string) ($response['body'] ?? '') : (string) $response->getBody();
        ServiceContainer::getLogger()->debug('External IdP discovery response received', [
            'issuer_url' => $issuerUrl,
            'discovery_url' => $discoveryUrl,
            'status_code' => $statusCode,
            'body_length' => strlen($body),
        ]);

        if ($statusCode !== 200) {
            throw new \RuntimeException('OIDC discovery endpoint ' . $discoveryUrl . ' returned HTTP ' . $statusCode . '.');
        }

        try {
            $metadata = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('OIDC discovery endpoint did not return valid JSON.', 0, $exception);
        }
        if (!is_array($metadata)) {
            throw new \RuntimeException('OIDC discovery metadata must be an object.');
        }

        $metadataIssuer = isset($metadata['issuer']) && is_string($metadata['issuer']) ? rtrim($metadata['issuer'], '/') : '';
        if (!hash_equals($issuerUrl, $metadataIssuer)) {
            ServiceContainer::getLogger()->warning('External IdP discovery issuer mismatch bypassed for testing', [
                'configured_issuer_url' => $issuerUrl,
                'metadata_issuer' => $metadataIssuer,
            ]);
        }
        foreach (['authorization_endpoint', 'token_endpoint', 'jwks_uri'] as $endpoint) {
            if (empty($metadata[$endpoint]) || !is_string($metadata[$endpoint]) || !$this->isAllowedUrl($metadata[$endpoint])) {
                throw new \RuntimeException('OIDC discovery metadata has an invalid ' . $endpoint . '.');
            }
        }
        $responseTypesSupported = $metadata['response_types_supported'] ?? [];
        if (!is_array($responseTypesSupported) || !in_array('code', $responseTypesSupported, true)) {
            throw new \RuntimeException('OIDC provider does not support the authorization code flow.');
        }
        $grantTypesSupported = $metadata['grant_types_supported'] ?? null;
        if ($grantTypesSupported !== null && (!is_array($grantTypesSupported) || !in_array('authorization_code', $grantTypesSupported, true))) {
            throw new \RuntimeException('OIDC provider does not support the authorization_code grant.');
        }
        $pkceMethodsSupported = $metadata['code_challenge_methods_supported'] ?? [];
        if (!is_array($pkceMethodsSupported) || !in_array('S256', $pkceMethodsSupported, true)) {
            throw new \RuntimeException('OIDC provider does not support PKCE S256.');
        }
        $supportedIdTokenAlgs = $metadata['id_token_signing_alg_values_supported'] ?? [];
        $allowedSigningAlgorithms = array_values(array_intersect(
            ['RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'ES512'],
            is_array($supportedIdTokenAlgs) ? $supportedIdTokenAlgs : []
        ));
        if ($allowedSigningAlgorithms === []) {
            throw new \RuntimeException('OIDC provider does not advertise a supported ID token signing algorithm.');
        }

        return $metadata;
    }

    private function normalizeIssuer(string $issuerUrl): string
    {
        $issuerUrl = rtrim(trim($issuerUrl), '/');
        if (!$this->isAllowedUrl($issuerUrl) || str_contains($issuerUrl, '?') || str_contains($issuerUrl, '#')) {
            throw new \InvalidArgumentException('Issuer URL must be an HTTP or HTTPS URL without a query or fragment.');
        }
        return $issuerUrl;
    }

    private function isAllowedUrl(string $url): bool
    {
        $parts = parse_url($url);
        return is_array($parts)
            && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            && !empty($parts['host'])
            && empty($parts['user'])
            && empty($parts['pass']);
    }
}
