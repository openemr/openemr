<?php

/**
 * Isolated tests for the External IdP discovery validator.
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ExternalIdp;

require_once __DIR__ . '/bootstrap.php';

use OpenEMR\Modules\ExternalIdp\Service\DiscoveryService;
use PHPUnit\Framework\TestCase;

final class DiscoveryServiceTest extends TestCase
{
    public function testDiscoverReturnsValidatedMetadataFromFetcher(): void
    {
        $issuer = 'https://idp.example.com/realms/clinic';
        $service = new DiscoveryService(
            fn (string $url): array => [
                'status' => 200,
                'body' => json_encode([
                    'issuer' => $issuer,
                    'authorization_endpoint' => $issuer . '/protocol/openid-connect/auth',
                    'token_endpoint' => $issuer . '/protocol/openid-connect/token',
                    'jwks_uri' => $issuer . '/protocol/openid-connect/certs',
                    'response_types_supported' => ['code'],
                    'grant_types_supported' => ['authorization_code'],
                    'code_challenge_methods_supported' => ['S256'],
                    'id_token_signing_alg_values_supported' => ['RS256'],
                ], JSON_THROW_ON_ERROR),
            ]
        );

        $metadata = $service->discover($issuer);

        self::assertSame($issuer, $metadata['issuer']);
    }

    public function testDiscoverRejectsIssuerMismatch(): void
    {
        $service = new DiscoveryService(
            fn (string $url): array => [
                'status' => 200,
                'body' => json_encode([
                    'issuer' => 'https://idp.example.com/other',
                    'authorization_endpoint' => 'https://idp.example.com/other/auth',
                    'token_endpoint' => 'https://idp.example.com/other/token',
                    'jwks_uri' => 'https://idp.example.com/other/certs',
                    'response_types_supported' => ['code'],
                    'grant_types_supported' => ['authorization_code'],
                    'code_challenge_methods_supported' => ['S256'],
                    'id_token_signing_alg_values_supported' => ['RS256'],
                ], JSON_THROW_ON_ERROR),
            ]
        );

        $this->expectException(\RuntimeException::class);
        $service->discover('https://idp.example.com/realms/clinic');
    }

    public function testDiscoverRejectsMetadataWithoutPkceSupport(): void
    {
        $issuer = 'https://idp.example.com/realms/clinic';
        $service = new DiscoveryService(
            fn (string $url): array => [
                'status' => 200,
                'body' => json_encode([
                    'issuer' => $issuer,
                    'authorization_endpoint' => $issuer . '/protocol/openid-connect/auth',
                    'token_endpoint' => $issuer . '/protocol/openid-connect/token',
                    'jwks_uri' => $issuer . '/protocol/openid-connect/certs',
                    'response_types_supported' => ['code'],
                    'grant_types_supported' => ['authorization_code'],
                    'id_token_signing_alg_values_supported' => ['RS256'],
                ], JSON_THROW_ON_ERROR),
            ]
        );

        $this->expectException(\RuntimeException::class);
        $service->discover($issuer);
    }

    public function testDiscoverRejectsInvalidIssuerUrl(): void
    {
        $service = new DiscoveryService(
            fn (string $url): array => ['status' => 200, 'body' => '{}']
        );

        $this->expectException(\InvalidArgumentException::class);
        $service->discover('http://idp.example.com/realm');
    }
}
