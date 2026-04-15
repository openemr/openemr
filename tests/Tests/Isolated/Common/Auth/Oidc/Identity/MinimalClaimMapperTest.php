<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Identity;

use OpenEMR\Common\Auth\Oidc\Identity\ClaimMappingException;
use OpenEMR\Common\Auth\Oidc\Identity\MinimalClaimMapper;
use PHPUnit\Framework\TestCase;

final class MinimalClaimMapperTest extends TestCase
{
    private MinimalClaimMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new MinimalClaimMapper();
    }

    public function testMapWithSubAndIssOnly(): void
    {
        $claims = ['sub' => 'user-123', 'iss' => 'https://issuer.example.com'];

        $identity = $this->mapper->map($claims);

        self::assertSame('user-123', $identity->externalId);
        self::assertSame('https://issuer.example.com', $identity->issuer);
        self::assertSame('', $identity->email);
        self::assertFalse($identity->emailVerified);
    }

    public function testMapWithEmailPresent(): void
    {
        $claims = [
            'sub' => 'user-123',
            'iss' => 'https://issuer.example.com',
            'email' => 'user@example.com',
            'email_verified' => true,
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('user-123', $identity->externalId);
        self::assertSame('https://issuer.example.com', $identity->issuer);
        self::assertSame('user@example.com', $identity->email);
        self::assertTrue($identity->emailVerified);
    }

    public function testMapThrowsOnMissingSub(): void
    {
        $this->expectException(ClaimMappingException::class);

        $this->mapper->map(['iss' => 'https://issuer.example.com']);
    }

    public function testMapThrowsOnMissingIss(): void
    {
        $this->expectException(ClaimMappingException::class);

        $this->mapper->map(['sub' => 'user-123']);
    }

    public function testMapThrowsOnEmptySub(): void
    {
        $this->expectException(ClaimMappingException::class);

        $this->mapper->map(['sub' => '', 'iss' => 'https://issuer.example.com']);
    }

    public function testMapThrowsOnEmptyIss(): void
    {
        $this->expectException(ClaimMappingException::class);

        $this->mapper->map(['sub' => 'user-123', 'iss' => '']);
    }

    public function testSupportsReturnsTrueWithSubAndIss(): void
    {
        self::assertTrue($this->mapper->supports([
            'sub' => 'user-123',
            'iss' => 'https://issuer.example.com',
        ]));
    }

    public function testSupportsReturnsTrueWithoutEmail(): void
    {
        self::assertTrue($this->mapper->supports([
            'sub' => 'user-123',
            'iss' => 'https://issuer.example.com',
        ]));
    }

    public function testSupportsReturnsFalseWithoutSub(): void
    {
        self::assertFalse($this->mapper->supports([
            'iss' => 'https://issuer.example.com',
        ]));
    }

    public function testSupportsReturnsFalseWithoutIss(): void
    {
        self::assertFalse($this->mapper->supports([
            'sub' => 'user-123',
        ]));
    }

    public function testMapIgnoresNonStringEmail(): void
    {
        $claims = [
            'sub' => 'user-123',
            'iss' => 'https://issuer.example.com',
            'email' => 12345,
        ];

        $identity = $this->mapper->map($claims);

        self::assertSame('', $identity->email);
    }
}
