<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Discovery;

use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryException;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcProviderMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OidcProviderMetadata::class)]
final class OidcProviderMetadataTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function validDocument(): array
    {
        return [
            'issuer' => 'https://accounts.example.com',
            'authorization_endpoint' => 'https://accounts.example.com/o/oauth2/v2/auth',
            'token_endpoint' => 'https://oauth2.example.com/token',
            'jwks_uri' => 'https://www.example.com/oauth2/v3/certs',
            'userinfo_endpoint' => 'https://openidconnect.example.com/v1/userinfo',
            'end_session_endpoint' => 'https://accounts.example.com/logout',
            'revocation_endpoint' => 'https://oauth2.example.com/revoke',
            'response_types_supported' => ['code', 'token', 'id_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'email', 'profile'],
            'claims_supported' => ['sub', 'email', 'name'],
        ];
    }

    public function testFromDiscoveryDocumentParsesAllFields(): void
    {
        $doc = self::validDocument();
        $metadata = OidcProviderMetadata::fromDiscoveryDocument($doc);

        self::assertSame('https://accounts.example.com', $metadata->issuer);
        self::assertSame('https://accounts.example.com/o/oauth2/v2/auth', $metadata->authorizationEndpoint);
        self::assertSame('https://www.example.com/oauth2/v3/certs', $metadata->jwksUri);
        self::assertSame('https://oauth2.example.com/token', $metadata->tokenEndpoint);
        self::assertSame('https://openidconnect.example.com/v1/userinfo', $metadata->userinfoEndpoint);
        self::assertSame('https://accounts.example.com/logout', $metadata->endSessionEndpoint);
        self::assertSame('https://oauth2.example.com/revoke', $metadata->revocationEndpoint);
        self::assertSame(['code', 'token', 'id_token'], $metadata->responseTypesSupported);
        self::assertSame(['public'], $metadata->subjectTypesSupported);
        self::assertSame(['RS256'], $metadata->idTokenSigningAlgValuesSupported);
        self::assertSame(['openid', 'email', 'profile'], $metadata->scopesSupported);
        self::assertSame(['sub', 'email', 'name'], $metadata->claimsSupported);
    }

    public function testFromDiscoveryDocumentPreservesRawDocument(): void
    {
        $doc = self::validDocument();
        $doc['custom_field'] = 'custom_value';

        $metadata = OidcProviderMetadata::fromDiscoveryDocument($doc);

        self::assertSame('custom_value', $metadata->raw['custom_field']);
    }

    public function testFromDiscoveryDocumentWithMinimalDocument(): void
    {
        $doc = [
            'issuer' => 'https://issuer.example.com',
            'authorization_endpoint' => 'https://issuer.example.com/auth',
            'jwks_uri' => 'https://issuer.example.com/jwks',
        ];

        $metadata = OidcProviderMetadata::fromDiscoveryDocument($doc);

        self::assertSame('https://issuer.example.com', $metadata->issuer);
        self::assertSame('', $metadata->tokenEndpoint);
        self::assertNull($metadata->userinfoEndpoint);
        self::assertNull($metadata->endSessionEndpoint);
        self::assertNull($metadata->revocationEndpoint);
        self::assertSame([], $metadata->responseTypesSupported);
        self::assertSame([], $metadata->scopesSupported);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function missingRequiredFieldProvider(): array
    {
        return [
            'missing issuer' => ['issuer'],
            'missing authorization_endpoint' => ['authorization_endpoint'],
            'missing jwks_uri' => ['jwks_uri'],
        ];
    }

    #[DataProvider('missingRequiredFieldProvider')]
    public function testFromDiscoveryDocumentThrowsOnMissingRequiredField(string $field): void
    {
        $doc = self::validDocument();
        unset($doc[$field]);

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage($field);

        OidcProviderMetadata::fromDiscoveryDocument($doc);
    }

    public function testFromDiscoveryDocumentThrowsOnEmptyRequiredField(): void
    {
        $doc = self::validDocument();
        $doc['issuer'] = '';

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('issuer');

        OidcProviderMetadata::fromDiscoveryDocument($doc);
    }

    public function testFromDiscoveryDocumentThrowsOnNonStringRequiredField(): void
    {
        $doc = self::validDocument();
        $doc['jwks_uri'] = 12345;

        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('jwks_uri');

        OidcProviderMetadata::fromDiscoveryDocument($doc);
    }

    public function testFromDiscoveryDocumentReportsAllMissingFields(): void
    {
        $this->expectException(OidcDiscoveryException::class);
        $this->expectExceptionMessage('issuer, authorization_endpoint, jwks_uri');

        OidcProviderMetadata::fromDiscoveryDocument([]);
    }

    public function testStringListFiltersNonStringValues(): void
    {
        $doc = self::validDocument();
        $doc['scopes_supported'] = ['openid', 42, 'email', null, 'profile'];

        $metadata = OidcProviderMetadata::fromDiscoveryDocument($doc);

        self::assertSame(['openid', 'email', 'profile'], $metadata->scopesSupported);
    }

    public function testStringListHandlesNonArrayGracefully(): void
    {
        $doc = self::validDocument();
        $doc['scopes_supported'] = 'not-an-array';

        $metadata = OidcProviderMetadata::fromDiscoveryDocument($doc);

        self::assertSame([], $metadata->scopesSupported);
    }

    public function testIsImmutable(): void
    {
        $metadata = OidcProviderMetadata::fromDiscoveryDocument(self::validDocument());

        $reflection = new \ReflectionClass($metadata);
        self::assertTrue($reflection->isReadOnly());
    }
}
