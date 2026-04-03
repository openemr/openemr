<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Identity;

use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NormalizedIdentity::class)]
final class NormalizedIdentityTest extends TestCase
{
    public function testConstructionWithAllFields(): void
    {
        $identity = new NormalizedIdentity(
            externalId: 'user-123',
            issuer: 'https://accounts.google.com',
            email: 'jane@example.com',
            emailVerified: true,
            displayName: 'Jane Smith',
            tenantId: 'tenant-abc',
        );

        self::assertSame('user-123', $identity->externalId);
        self::assertSame('https://accounts.google.com', $identity->issuer);
        self::assertSame('jane@example.com', $identity->email);
        self::assertTrue($identity->emailVerified);
        self::assertSame('Jane Smith', $identity->displayName);
        self::assertSame('tenant-abc', $identity->tenantId);
    }

    public function testConstructionWithDefaults(): void
    {
        $identity = new NormalizedIdentity(
            externalId: 'user-456',
            issuer: 'https://login.microsoftonline.com/tenant-id/v2.0',
            email: 'bob@example.com',
        );

        self::assertFalse($identity->emailVerified);
        self::assertSame('', $identity->displayName);
        self::assertNull($identity->tenantId);
    }

    public function testGetCompositeKey(): void
    {
        $identity = new NormalizedIdentity(
            externalId: 'abc-123',
            issuer: 'https://accounts.google.com',
            email: 'user@example.com',
        );

        self::assertSame('https://accounts.google.com|abc-123', $identity->getCompositeKey());
    }

    public function testCompositeKeyIsUniqueAcrossProviders(): void
    {
        $google = new NormalizedIdentity(
            externalId: 'same-id',
            issuer: 'https://accounts.google.com',
            email: 'user@example.com',
        );

        $azure = new NormalizedIdentity(
            externalId: 'same-id',
            issuer: 'https://login.microsoftonline.com/tid/v2.0',
            email: 'user@example.com',
        );

        self::assertNotSame($google->getCompositeKey(), $azure->getCompositeKey());
    }

    #[DataProvider('emptyFieldProvider')]
    public function testRejectsEmptyRequiredFields(string $externalId, string $issuer, string $email, string $expectedMessage): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage($expectedMessage);

        new NormalizedIdentity(
            externalId: $externalId,
            issuer: $issuer,
            email: $email,
        );
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function emptyFieldProvider(): array
    {
        return [
            'empty externalId' => ['', 'https://issuer.example.com', 'user@example.com', 'External ID (sub) must not be empty'],
            'empty issuer' => ['user-123', '', 'user@example.com', 'Issuer (iss) must not be empty'],
            'empty email' => ['user-123', 'https://issuer.example.com', '', 'Email must not be empty'],
        ];
    }

    public function testIsImmutable(): void
    {
        $identity = new NormalizedIdentity(
            externalId: 'user-789',
            issuer: 'https://issuer.example.com',
            email: 'test@example.com',
            emailVerified: true,
            displayName: 'Test User',
            tenantId: 'tenant-1',
        );

        $reflection = new \ReflectionClass($identity);
        self::assertTrue($reflection->isReadOnly());
        self::assertTrue($reflection->isFinal());
    }
}
