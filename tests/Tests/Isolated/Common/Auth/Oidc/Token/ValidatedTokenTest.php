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
            revocationKey: 'token-id-abc',
        );

        self::assertSame($identity, $token->identity);
        self::assertSame($claims, $token->claims);
        self::assertSame($expiresAt, $token->expiresAt);
        self::assertSame('token-id-abc', $token->jti);
        self::assertSame('token-id-abc', $token->revocationKey);
    }

    public function testConstructionWithoutLiteralJtiCarriesSyntheticRevocationKey(): void
    {
        // IdPs that omit `jti` (Firebase/GCIP in some configurations) still
        // need a stable per-issuance identifier so the validator can do
        // replay protection AND revocation lookups. The validator's
        // computeReplayKey() returns an `oidc-synthetic:hash(...)` value
        // in that case, which the ValidatedToken carries in revocationKey
        // even when jti is null.
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
            jti: null,
            revocationKey: 'oidc-synthetic:abc123def456',
        );

        self::assertNull($token->jti);
        self::assertSame('oidc-synthetic:abc123def456', $token->revocationKey);
    }

    public function testIsImmutable(): void
    {
        $reflection = new \ReflectionClass(ValidatedToken::class);
        self::assertTrue($reflection->isReadOnly());
        self::assertTrue($reflection->isFinal());
    }
}
