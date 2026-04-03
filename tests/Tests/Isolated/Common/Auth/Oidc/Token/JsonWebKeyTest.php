<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Token;

use OpenEMR\Common\Auth\Oidc\Token\JsonWebKey;
use OpenEMR\Common\Auth\Oidc\Token\JwksException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonWebKey::class)]
final class JsonWebKeyTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function validRsaKey(): array
    {
        return [
            'kty' => 'RSA',
            'kid' => 'test-key-1',
            'alg' => 'RS256',
            'use' => 'sig',
            'n' => 'some-modulus-base64url',
            'e' => 'AQAB',
        ];
    }

    public function testFromArrayParsesAllFields(): void
    {
        $data = self::validRsaKey();
        $key = JsonWebKey::fromArray($data);

        self::assertSame('RSA', $key->kty);
        self::assertSame('test-key-1', $key->kid);
        self::assertSame('RS256', $key->alg);
        self::assertSame('sig', $key->use);
    }

    public function testFromArrayPreservesAllParameters(): void
    {
        $data = self::validRsaKey();
        $key = JsonWebKey::fromArray($data);

        self::assertSame('some-modulus-base64url', $key->getParameter('n'));
        self::assertSame('AQAB', $key->getParameter('e'));
    }

    public function testFromArrayWithMinimalFields(): void
    {
        $key = JsonWebKey::fromArray(['kty' => 'RSA', 'kid' => 'minimal']);

        self::assertSame('RSA', $key->kty);
        self::assertSame('minimal', $key->kid);
        self::assertNull($key->alg);
        self::assertNull($key->use);
    }

    public function testFromArrayThrowsOnMissingKty(): void
    {
        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('kty');

        JsonWebKey::fromArray(['kid' => 'no-kty']);
    }

    public function testFromArrayThrowsOnMissingKid(): void
    {
        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('kid');

        JsonWebKey::fromArray(['kty' => 'RSA']);
    }

    public function testFromArrayThrowsOnEmptyKty(): void
    {
        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('kty');

        JsonWebKey::fromArray(['kty' => '', 'kid' => 'empty-kty']);
    }

    public function testFromArrayThrowsOnEmptyKid(): void
    {
        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('kid');

        JsonWebKey::fromArray(['kty' => 'RSA', 'kid' => '']);
    }

    public function testFromArrayThrowsOnNonStringKty(): void
    {
        $this->expectException(JwksException::class);
        $this->expectExceptionMessage('kty');

        JsonWebKey::fromArray(['kty' => 123, 'kid' => 'bad-kty']);
    }

    public function testGetParameterReturnsNullForMissing(): void
    {
        $key = JsonWebKey::fromArray(['kty' => 'RSA', 'kid' => 'test']);

        self::assertNull($key->getParameter('nonexistent'));
    }

    public function testIsSigningKeyReturnsTrueWhenUseIsSig(): void
    {
        $key = JsonWebKey::fromArray(['kty' => 'RSA', 'kid' => 'test', 'use' => 'sig']);

        self::assertTrue($key->isSigningKey());
    }

    public function testIsSigningKeyReturnsTrueWhenUseIsNull(): void
    {
        $key = JsonWebKey::fromArray(['kty' => 'RSA', 'kid' => 'test']);

        self::assertTrue($key->isSigningKey());
    }

    public function testIsSigningKeyReturnsFalseWhenUseIsEnc(): void
    {
        $key = JsonWebKey::fromArray(['kty' => 'RSA', 'kid' => 'test', 'use' => 'enc']);

        self::assertFalse($key->isSigningKey());
    }

    public function testIsImmutable(): void
    {
        $key = JsonWebKey::fromArray(self::validRsaKey());

        $reflection = new \ReflectionClass($key);
        self::assertTrue($reflection->isReadOnly());
    }

    public function testFromArrayIgnoresNonStringAlg(): void
    {
        $key = JsonWebKey::fromArray(['kty' => 'RSA', 'kid' => 'test', 'alg' => 123]);

        self::assertNull($key->alg);
    }

    public function testEcKeyType(): void
    {
        $key = JsonWebKey::fromArray([
            'kty' => 'EC',
            'kid' => 'ec-key-1',
            'alg' => 'ES256',
            'use' => 'sig',
            'crv' => 'P-256',
            'x' => 'some-x-coord',
            'y' => 'some-y-coord',
        ]);

        self::assertSame('EC', $key->kty);
        self::assertSame('ES256', $key->alg);
        self::assertSame('P-256', $key->getParameter('crv'));
    }
}
