<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Token;

use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;
use OpenEMR\Common\Auth\Oidc\Token\ValidatedToken;
use PHPUnit\Framework\TestCase;

final class ValidatedTokenTest extends TestCase
{
    public function testConstructionWithAllFields(): void
    {
        $identity = new NormalizedIdentity(
            externalId: 'user-123',
            issuer: 'https://accounts.google.com',
            email: 'user@example.com',
        );
        $expiresAt = new \DateTimeImmutable('2026-01-01T00:00:00Z');
        $claims = ['sub' => 'user-123', 'iss' => 'https://accounts.google.com', 'custom' => 'value'];

        $token = new ValidatedToken(
            identity: $identity,
            claims: $claims,
            expiresAt: $expiresAt,
            jti: 'token-id-abc',
        );

        self::assertSame($identity, $token->identity);
        self::assertSame($claims, $token->claims);
        self::assertSame($expiresAt, $token->expiresAt);
        self::assertSame('token-id-abc', $token->jti);
    }

    public function testConstructionWithDefaultJti(): void
    {
        $identity = new NormalizedIdentity(
            externalId: 'user-456',
            issuer: 'https://login.microsoftonline.com/tid/v2.0',
            email: 'user@example.com',
        );
        $expiresAt = new \DateTimeImmutable('2026-06-15T12:00:00Z');

        $token = new ValidatedToken(
            identity: $identity,
            claims: ['sub' => 'user-456'],
            expiresAt: $expiresAt,
        );

        self::assertNull($token->jti);
    }

    public function testIsImmutable(): void
    {
        $reflection = new \ReflectionClass(ValidatedToken::class);
        self::assertTrue($reflection->isReadOnly());
        self::assertTrue($reflection->isFinal());
    }
}
