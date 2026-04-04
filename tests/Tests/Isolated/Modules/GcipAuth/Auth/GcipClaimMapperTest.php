<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\GcipAuth\Auth;

use OpenEMR\Common\Auth\Oidc\Identity\ClaimMappingException;
use OpenEMR\Modules\GcipAuth\Auth\GcipClaimMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GcipClaimMapper::class)]
final class GcipClaimMapperTest extends TestCase
{
    private GcipClaimMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new GcipClaimMapper();
    }

    /**
     * @return array<string, mixed>
     */
    private static function gcipClaims(): array
    {
        return [
            'sub' => 'firebase-uid-123',
            'iss' => 'https://securetoken.google.com/my-project',
            'aud' => 'my-project',
            'email' => 'user@example.com',
            'email_verified' => true,
            'name' => 'Jane Smith',
            'firebase' => [
                'tenant' => 'tenant-abc',
                'sign_in_provider' => 'google.com',
            ],
        ];
    }

    public function testMapStandardGcipToken(): void
    {
        $identity = $this->mapper->map(self::gcipClaims());

        self::assertSame('firebase-uid-123', $identity->externalId);
        self::assertSame('https://securetoken.google.com/my-project', $identity->issuer);
        self::assertSame('user@example.com', $identity->email);
        self::assertTrue($identity->emailVerified);
        self::assertSame('Jane Smith', $identity->displayName);
        self::assertSame('tenant-abc', $identity->tenantId);
    }

    public function testMapExtractsGivenAndFamilyNameAsFallback(): void
    {
        $claims = self::gcipClaims();
        unset($claims['name']);
        $claims['given_name'] = 'Jane';
        $claims['family_name'] = 'Smith';

        $identity = $this->mapper->map($claims);

        self::assertSame('Jane Smith', $identity->displayName);
    }

    public function testMapWithoutFirebaseClaim(): void
    {
        $claims = self::gcipClaims();
        unset($claims['firebase']);

        $identity = $this->mapper->map($claims);

        self::assertNull($identity->tenantId);
    }

    public function testMapWithFirebaseButNoTenant(): void
    {
        $claims = self::gcipClaims();
        /** @var array<string, mixed> $firebase */
        $firebase = $claims['firebase'];
        unset($firebase['tenant']);
        $claims['firebase'] = $firebase;

        $identity = $this->mapper->map($claims);

        self::assertNull($identity->tenantId);
    }

    public function testThrowsOnMissingEmail(): void
    {
        $claims = self::gcipClaims();
        unset($claims['email']);

        $this->expectException(ClaimMappingException::class);
        $this->expectExceptionMessage('email');

        $this->mapper->map($claims);
    }

    public function testMapWithoutEmailVerified(): void
    {
        $claims = self::gcipClaims();
        unset($claims['email_verified']);

        $identity = $this->mapper->map($claims);

        self::assertFalse($identity->emailVerified);
    }

    public function testThrowsOnMissingSub(): void
    {
        $claims = self::gcipClaims();
        unset($claims['sub']);

        $this->expectException(ClaimMappingException::class);
        $this->expectExceptionMessage('sub');

        $this->mapper->map($claims);
    }

    public function testThrowsOnMissingIss(): void
    {
        $claims = self::gcipClaims();
        unset($claims['iss']);

        $this->expectException(ClaimMappingException::class);
        $this->expectExceptionMessage('iss');

        $this->mapper->map($claims);
    }

    public function testSupportsReturnsTrueWithSubAndIss(): void
    {
        self::assertTrue($this->mapper->supports(self::gcipClaims()));
    }

    public function testSupportsReturnsFalseWithoutSub(): void
    {
        $claims = self::gcipClaims();
        unset($claims['sub']);

        self::assertFalse($this->mapper->supports($claims));
    }

    public function testSupportsReturnsFalseWithoutIss(): void
    {
        $claims = self::gcipClaims();
        unset($claims['iss']);

        self::assertFalse($this->mapper->supports($claims));
    }
}
