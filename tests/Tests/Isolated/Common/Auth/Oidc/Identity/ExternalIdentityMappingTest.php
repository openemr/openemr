<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Identity;

use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityMapping;
use PHPUnit\Framework\TestCase;

final class ExternalIdentityMappingTest extends TestCase
{
    public function testCreateBuildsMapping(): void
    {
        $mapping = ExternalIdentityMapping::create(
            userId: 42,
            issuer: 'https://accounts.google.com',
            externalId: 'abc123',
            email: 'user@example.com',
        );

        self::assertSame(42, $mapping->userId);
        self::assertSame('https://accounts.google.com', $mapping->issuer);
        self::assertSame('abc123', $mapping->externalId);
        self::assertSame('user@example.com', $mapping->email);
        self::assertNull($mapping->id);
        self::assertNull($mapping->createdAt);
        self::assertNull($mapping->updatedAt);
    }

    public function testCreateWithoutEmail(): void
    {
        $mapping = ExternalIdentityMapping::create(
            userId: 1,
            issuer: 'https://issuer.example.com',
            externalId: 'sub-1',
        );

        self::assertNull($mapping->email);
    }

    public function testFromDatabaseRow(): void
    {
        $row = [
            'id' => '7',
            'user_id' => '42',
            'issuer' => 'https://accounts.google.com',
            'external_id' => 'abc123',
            'email' => 'user@example.com',
            'created_at' => '2026-01-15 12:00:00',
            'updated_at' => '2026-01-15 13:00:00',
        ];

        $mapping = ExternalIdentityMapping::fromDatabaseRow($row);

        self::assertSame(7, $mapping->id);
        self::assertSame(42, $mapping->userId);
        self::assertSame('https://accounts.google.com', $mapping->issuer);
        self::assertSame('abc123', $mapping->externalId);
        self::assertSame('user@example.com', $mapping->email);
        self::assertNotNull($mapping->createdAt);
        self::assertSame('2026-01-15 12:00:00', $mapping->createdAt->format('Y-m-d H:i:s'));
        self::assertNotNull($mapping->updatedAt);
        self::assertSame('2026-01-15 13:00:00', $mapping->updatedAt->format('Y-m-d H:i:s'));
    }

    public function testFromDatabaseRowWithNullEmail(): void
    {
        $row = [
            'id' => '1',
            'user_id' => '10',
            'issuer' => 'https://issuer.example.com',
            'external_id' => 'sub-10',
            'email' => null,
            'created_at' => '2026-01-15 12:00:00',
            'updated_at' => '2026-01-15 12:00:00',
        ];

        $mapping = ExternalIdentityMapping::fromDatabaseRow($row);

        self::assertNull($mapping->email);
    }

    public function testRejectsZeroUserId(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User ID must be positive');

        ExternalIdentityMapping::create(0, 'https://issuer.example.com', 'sub-1');
    }

    public function testRejectsNegativeUserId(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User ID must be positive');

        ExternalIdentityMapping::create(-1, 'https://issuer.example.com', 'sub-1');
    }

    public function testRejectsEmptyIssuer(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Issuer must not be empty');

        ExternalIdentityMapping::create(1, '', 'sub-1');
    }

    public function testRejectsEmptyExternalId(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('External ID must not be empty');

        ExternalIdentityMapping::create(1, 'https://issuer.example.com', '');
    }

    public function testIsImmutable(): void
    {
        $mapping = ExternalIdentityMapping::create(1, 'https://issuer.example.com', 'sub-1');

        $reflection = new \ReflectionClass($mapping);
        self::assertTrue($reflection->isReadOnly());
    }
}
