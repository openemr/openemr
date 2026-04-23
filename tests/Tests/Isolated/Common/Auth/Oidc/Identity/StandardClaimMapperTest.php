<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Identity;

use OpenEMR\Common\Auth\Oidc\Identity\ClaimMappingException;
use OpenEMR\Common\Auth\Oidc\Identity\StandardClaimMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StandardClaimMapperTest extends TestCase
{
    private StandardClaimMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new StandardClaimMapper();
    }

    public function testMapWithAllStandardClaims(): void
    {
        $claims = [
            'sub' => 'user-123',
            'iss' => 'https://accounts.google.com',
            'email' => 'jane@example.com',
            'email_verified' => true,
            'name' => 'Jane Smith',
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('user-123', $identity->externalId);
        self::assertSame('https://accounts.google.com', $identity->issuer);
        self::assertSame('jane@example.com', $identity->email);
        self::assertTrue($identity->emailVerified);
        self::assertSame('Jane Smith', $identity->displayName);
        self::assertNull($identity->tenantId);
    }

    public function testMapWithMinimalClaims(): void
    {
        $claims = [
            'sub' => 'abc-def',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('abc-def', $identity->externalId);
        self::assertSame('https://issuer.example.com', $identity->issuer);
        self::assertSame('user@example.com', $identity->email);
        self::assertFalse($identity->emailVerified);
        self::assertSame('', $identity->displayName);
    }

    public function testMapBuildsDisplayNameFromGivenAndFamilyName(): void
    {
        $claims = [
            'sub' => 'user-1',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
            'given_name' => 'Jane',
            'family_name' => 'Smith',
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('Jane Smith', $identity->displayName);
    }

    public function testMapPrefersNameOverGivenAndFamilyName(): void
    {
        $claims = [
            'sub' => 'user-1',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
            'name' => 'Dr. Jane Smith',
            'given_name' => 'Jane',
            'family_name' => 'Smith',
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('Dr. Jane Smith', $identity->displayName);
    }

    public function testMapHandlesOnlyGivenName(): void
    {
        $claims = [
            'sub' => 'user-1',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
            'given_name' => 'Jane',
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('Jane', $identity->displayName);
    }

    public function testMapHandlesOnlyFamilyName(): void
    {
        $claims = [
            'sub' => 'user-1',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
            'family_name' => 'Smith',
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('Smith', $identity->displayName);
    }

    public function testMapTreatsEmailVerifiedFalseCorrectly(): void
    {
        $claims = [
            'sub' => 'user-1',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
            'email_verified' => false,
        ];

        $identity = $this->mapper->map($claims);

        self::assertFalse($identity->emailVerified);
    }

    /**
     * @param array<string, mixed> $claims
     */
    #[DataProvider('missingRequiredClaimProvider')]
    public function testMapThrowsOnMissingRequiredClaim(array $claims, string $missingClaim): void
    {
        $this->expectException(ClaimMappingException::class);
        $this->expectExceptionMessage("Required claim '{$missingClaim}' is missing or empty");

        $this->mapper->map($claims);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function missingRequiredClaimProvider(): array
    {
        $base = [
            'sub' => 'user-1',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
        ];

        return [
            'missing sub' => [array_diff_key($base, ['sub' => true]), 'sub'],
            'missing iss' => [array_diff_key($base, ['iss' => true]), 'iss'],
            'missing email' => [array_diff_key($base, ['email' => true]), 'email'],
            'empty sub' => [array_merge($base, ['sub' => '']), 'sub'],
            'empty iss' => [array_merge($base, ['iss' => '']), 'iss'],
            'empty email' => [array_merge($base, ['email' => '']), 'email'],
            'null sub' => [array_merge($base, ['sub' => null]), 'sub'],
            'integer sub' => [array_merge($base, ['sub' => 123]), 'sub'],
        ];
    }

    public function testSupportsReturnsTrueForValidClaims(): void
    {
        $claims = [
            'sub' => 'user-1',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
        ];

        self::assertTrue($this->mapper->supports($claims));
    }

    public function testSupportsReturnsTrueWithExtraClaims(): void
    {
        $claims = [
            'sub' => 'user-1',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
            'name' => 'Jane',
            'custom_claim' => 'value',
        ];

        self::assertTrue($this->mapper->supports($claims));
    }

    /**
     * @param array<string, mixed> $claims
     */
    #[DataProvider('unsupportedClaimsProvider')]
    public function testSupportsReturnsFalseForInvalidClaims(array $claims): void
    {
        self::assertFalse($this->mapper->supports($claims));
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function unsupportedClaimsProvider(): array
    {
        return [
            'missing sub' => [['iss' => 'https://issuer.example.com', 'email' => 'a@b.com']],
            'missing iss' => [['sub' => 'user-1', 'email' => 'a@b.com']],
            'missing email' => [['sub' => 'user-1', 'iss' => 'https://issuer.example.com']],
            'empty claims' => [[]],
            'non-string sub' => [['sub' => 123, 'iss' => 'https://issuer.example.com', 'email' => 'a@b.com']],
            'non-string iss' => [['sub' => 'user-1', 'iss' => 123, 'email' => 'a@b.com']],
            'non-string email' => [['sub' => 'user-1', 'iss' => 'https://issuer.example.com', 'email' => 123]],
        ];
    }

    public function testMapWithGcipLikeToken(): void
    {
        $claims = [
            'sub' => 'firebase-uid-abc123',
            'iss' => 'https://securetoken.google.com/my-project',
            'aud' => 'my-project',
            'email' => 'clinician@hospital.org',
            'email_verified' => true,
            'name' => 'Dr. Alice Johnson',
            'firebase' => [
                'tenant' => 'clinic-a',
                'sign_in_provider' => 'google.com',
            ],
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('firebase-uid-abc123', $identity->externalId);
        self::assertSame('https://securetoken.google.com/my-project', $identity->issuer);
        self::assertSame('clinician@hospital.org', $identity->email);
        self::assertTrue($identity->emailVerified);
        self::assertSame('Dr. Alice Johnson', $identity->displayName);
        // StandardClaimMapper does NOT extract tenantId — that's GcipClaimMapper's job
        self::assertNull($identity->tenantId);
    }

    public function testMapWithAzureAdLikeToken(): void
    {
        $claims = [
            'sub' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'iss' => 'https://login.microsoftonline.com/tenant-guid/v2.0',
            'email' => 'nurse@hospital.onmicrosoft.com',
            'name' => 'Bob Williams',
            'tid' => 'tenant-guid',
            'preferred_username' => 'bob.williams@hospital.com',
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $identity->externalId);
        self::assertSame('https://login.microsoftonline.com/tenant-guid/v2.0', $identity->issuer);
        self::assertSame('nurse@hospital.onmicrosoft.com', $identity->email);
        self::assertSame('Bob Williams', $identity->displayName);
    }

    public function testMapWithKeycloakLikeToken(): void
    {
        $claims = [
            'sub' => '550e8400-e29b-41d4-a716-446655440000',
            'iss' => 'https://keycloak.hospital.local/realms/clinical',
            'email' => 'admin@hospital.local',
            'email_verified' => true,
            'given_name' => 'Carol',
            'family_name' => 'Davis',
            'realm_access' => ['roles' => ['admin', 'clinician']],
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $identity->externalId);
        self::assertSame('https://keycloak.hospital.local/realms/clinical', $identity->issuer);
        self::assertSame('admin@hospital.local', $identity->email);
        self::assertTrue($identity->emailVerified);
        self::assertSame('Carol Davis', $identity->displayName);
    }
}
