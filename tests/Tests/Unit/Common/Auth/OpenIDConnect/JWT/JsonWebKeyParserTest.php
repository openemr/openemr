<?php

/**
 * Direct coverage for JsonWebKeyParser.
 *
 * The class sits in the active token-validation path: every
 * RFC 7662 introspection call and every refresh-token consumer
 * routes through one of its three public methods. It had no
 * dedicated tests before this file — only indirect exercise via
 * controllers that mocked it out. Negative-path coverage here
 * pins the library/format-handling contracts so a regression
 * (e.g. expired tokens accepted, signature failures silently
 * swallowed, malformed input crashing instead of surfacing as a
 * structured error) is caught at the unit level.
 *
 * Setup generates a fresh RSA-SHA256 keypair per test run via
 * openssl_pkey_new — no committed fixture. The encryption key
 * is a constant so the encrypt-helper subclass and the parser
 * share the same defuse/php-encryption password.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\JWT;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeyParser;
use PHPUnit\Framework\TestCase;

final class JsonWebKeyParserTest extends TestCase
{
    private const ENCRYPTION_KEY = 'def00000-test-encryption-password-32bytesABCD';

    /** @var non-empty-string */
    private string $publicKeyFile;
    /** @var non-empty-string */
    private string $privateKeyPem;
    /** @var non-empty-string */
    private string $publicKeyPem;
    private JsonWebKeyParser $parser;
    private JsonWebKeyParserTestEncryptHelper $encrypt;

    protected function setUp(): void
    {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $res = openssl_pkey_new($config);
        if ($res === false) {
            self::markTestSkipped('OpenSSL not available');
        }

        if (!openssl_pkey_export($res, $privateKeyPem)) {
            self::markTestSkipped('Failed to export private key');
        }
        $publicKeyDetails = openssl_pkey_get_details($res);
        if ($publicKeyDetails === false || !isset($publicKeyDetails['key']) || !is_string($publicKeyDetails['key'])) {
            self::markTestSkipped('Failed to extract public key');
        }
        // assert() narrows for phpstan — markTestSkipped above already
        // exits when these aren't non-empty strings, but the static
        // analyzer can't follow that flow. assert() pins the post-
        // condition so the property assignments below satisfy the
        // non-empty-string @var docblocks.
        assert(is_string($privateKeyPem) && $privateKeyPem !== '');
        assert($publicKeyDetails['key'] !== '');

        $this->privateKeyPem = $privateKeyPem;
        $this->publicKeyPem = $publicKeyDetails['key'];

        // Public key on disk because JsonWebKeyParser uses
        // InMemory::file() — needs a real path, not raw PEM.
        $tempPath = tempnam(sys_get_temp_dir(), 'jwkp-pub-');
        if ($tempPath === false) {
            self::markTestSkipped('Cannot create temp file for public key');
        }
        $this->publicKeyFile = $tempPath . '.pem';
        rename($tempPath, $this->publicKeyFile);
        file_put_contents($this->publicKeyFile, $this->publicKeyPem);

        $this->parser = new JsonWebKeyParser(self::ENCRYPTION_KEY, $this->publicKeyFile);
        $this->encrypt = new JsonWebKeyParserTestEncryptHelper(self::ENCRYPTION_KEY, $this->publicKeyFile);
    }

    protected function tearDown(): void
    {
        if (isset($this->publicKeyFile) && file_exists($this->publicKeyFile)) {
            unlink($this->publicKeyFile);
        }
    }

    // -----------------------------------------------------------------
    // parseRefreshToken — decrypt + unpack contract
    // -----------------------------------------------------------------

    public function testParseRefreshTokenThrowsOnEmptyInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token cannot be empty');

        $this->parser->parseRefreshToken('');
    }

    public function testParseRefreshTokenReturnsActiveStatusForFreshToken(): void
    {
        // Future expire_time → active. Pin every claim the
        // RFC 7662 introspection response derives from this method
        // (scope/exp/sub/jti/client_id) so a refactor that drops a
        // mapping is loud.
        $blob = $this->encrypt->buildRefreshToken([
            'scopes' => ['openid', 'api:fhir'],
            'expire_time' => time() + 3600,
            'user_id' => 'user-uuid-fresh',
            'refresh_token_id' => 'jti-fresh',
            'client_id' => 'client-id-fresh',
        ]);

        $result = $this->parser->parseRefreshToken($blob);
        assert(is_array($result));

        self::assertTrue($result['active']);
        self::assertSame('active', $result['status']);
        self::assertSame(['openid', 'api:fhir'], $result['scope']);
        self::assertSame('user-uuid-fresh', $result['sub']);
        self::assertSame('jti-fresh', $result['jti']);
        self::assertSame('client-id-fresh', $result['client_id']);
    }

    public function testParseRefreshTokenMarksExpiredTokenInactive(): void
    {
        // expire_time strictly in the past — must surface as
        // active:false, status:'expired'. RFC 7662 contract.
        $blob = $this->encrypt->buildRefreshToken([
            'expire_time' => time() - 60,
        ]);

        $result = $this->parser->parseRefreshToken($blob);
        assert(is_array($result));

        self::assertFalse($result['active']);
        self::assertSame('expired', $result['status']);
    }

    public function testParseRefreshTokenThrowsWhenDecryptedWithWrongKey(): void
    {
        // Build a blob with a *different* encryption key. The parser's
        // decrypt() should fail to authenticate — the underlying
        // defuse/php-encryption library throws on tampered/wrong-key
        // input. RFC 7662 callers map any throw here to active:false,
        // but the throw has to actually fire — silent acceptance of a
        // malformed/wrong-key token would let an attacker replay
        // arbitrary content.
        $foreign = new JsonWebKeyParserTestEncryptHelper(
            'def00000-DIFFERENT-encryption-password-32B',
            $this->publicKeyFile,
        );
        $blob = $foreign->buildRefreshToken(['user_id' => 'attacker']);

        $this->expectException(\Throwable::class);

        $this->parser->parseRefreshToken($blob);
    }

    // -----------------------------------------------------------------
    // parseAccessToken — JWT signature + expiry contract
    // -----------------------------------------------------------------

    public function testParseAccessTokenThrowsOnEmptyInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token cannot be empty');

        $this->parser->parseAccessToken('');
    }

    public function testParseAccessTokenPropagatesParseFailureForMalformedJwt(): void
    {
        // Pin the actual contract: parseAccessToken does NOT wrap
        // lcobucci's Parser::parse() in try/catch — only the later
        // validate() call is caught. So a malformed JWT (bad base64,
        // wrong shape) throws and propagates. The TokenIntrospection
        // controller's outer Throwable catch turns this into RFC 7662
        // active:false; this test pins that the throw actually
        // happens, so a refactor that silently absorbs parse failures
        // (and accepts garbage tokens as "active") would fail here.
        $this->expectException(\Throwable::class);

        $this->parser->parseAccessToken('not.a.jwt');
    }

    public function testParseAccessTokenReturnsFailedVerificationForWrongKeySignature(): void
    {
        // Build a JWT signed with a *different* RSA keypair. The
        // parser tries to verify against $this->publicKeyFile and
        // fails — should yield failed_verification, not throw.
        $foreignKeyPair = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        if ($foreignKeyPair === false) {
            self::markTestSkipped('Cannot generate foreign key');
        }
        openssl_pkey_export($foreignKeyPair, $foreignPrivatePem);
        assert(is_string($foreignPrivatePem) && $foreignPrivatePem !== '');
        $foreignDetails = openssl_pkey_get_details($foreignKeyPair);
        $foreignPublicPem = is_array($foreignDetails) && isset($foreignDetails['key']) && is_string($foreignDetails['key'])
            ? $foreignDetails['key']
            : '';
        assert($foreignPublicPem !== '');

        $jwt = $this->buildJwtWithKeys(
            $foreignPrivatePem,
            $foreignPublicPem,
            ['exp' => new \DateTimeImmutable('+1 hour')],
        );

        $result = $this->parser->parseAccessToken($jwt);
        assert(is_array($result));

        self::assertFalse($result['active']);
        self::assertSame('failed_verification', $result['status']);
    }

    public function testParseAccessTokenMarksExpiredJwtInactive(): void
    {
        // Valid signature, but exp in the past. The expiry check
        // (line 93) must override the active:true default even
        // though signature validation succeeded.
        $jwt = $this->buildJwt([
            'exp' => new \DateTimeImmutable('-10 minutes'),
            'nbf' => new \DateTimeImmutable('-1 hour'),
            'iat' => new \DateTimeImmutable('-1 hour'),
        ]);

        $result = $this->parser->parseAccessToken($jwt);
        assert(is_array($result));

        self::assertFalse($result['active']);
        self::assertSame('expired', $result['status']);
    }

    /**
     * Aisle round-6 #3 (CWE-287) regression. Pre-fix the access-
     * token introspection check enforced only `exp` via
     * `isExpired()`. A JWT with valid signature and a future `nbf`
     * was reported as active:true — resource servers relying on
     * introspection would accept the token before its intended
     * validity window. Post-fix `LooseValidAt` enforces nbf, iat,
     * and exp together with a 1-minute drift tolerance, and the
     * status field distinguishes "not_yet_valid" (future nbf)
     * from "expired" (past exp).
     */
    public function testParseAccessTokenMarksNotYetValidJwtInactive(): void
    {
        // exp far in the future, but nbf hasn't kicked in yet.
        // Set nbf well outside the 1-minute LooseValidAt drift
        // tolerance so the test isn't flaky on slow CI.
        $jwt = $this->buildJwt([
            'iat' => new \DateTimeImmutable('+30 minutes'),
            'nbf' => new \DateTimeImmutable('+30 minutes'),
            'exp' => new \DateTimeImmutable('+2 hours'),
        ]);

        $result = $this->parser->parseAccessToken($jwt);
        assert(is_array($result));

        self::assertFalse($result['active']);
        self::assertSame(
            'not_yet_valid',
            $result['status'],
            'Future-nbf JWT must be rejected with the not_yet_valid status — distinct from expired',
        );
    }

    public function testParseAccessTokenReturnsActiveForValidSignedNonExpiredJwt(): void
    {
        $jwt = $this->buildJwt([
            'exp' => new \DateTimeImmutable('+1 hour'),
        ]);

        $result = $this->parser->parseAccessToken($jwt);
        assert(is_array($result));

        self::assertTrue($result['active']);
        self::assertSame('active', $result['status']);
        self::assertSame('user-sub-abc', $result['sub']);
        self::assertSame('jti-test-123', $result['jti']);
    }

    // -----------------------------------------------------------------
    // getTokenHintFromToken — string-shape heuristic
    // -----------------------------------------------------------------

    public function testGetTokenHintIdentifiesAccessTokenByThreePartDotShape(): void
    {
        // Three dot-separated parts is the JWT shape per RFC 7519 §3.
        // Doesn't mean the token is *valid* — just that the format
        // advertises itself as JWT. The hint guides downstream
        // dispatch to parseAccessToken vs parseRefreshToken.
        self::assertSame(
            'access_token',
            $this->parser->getTokenHintFromToken('header.payload.signature'),
        );
    }

    public function testGetTokenHintIdentifiesRefreshTokenByNonJwtShape(): void
    {
        // Encrypted refresh-token blobs are defuse/php-encryption
        // ciphertext — single base64-encoded blob, not 3 parts.
        self::assertSame(
            'refresh_token',
            $this->parser->getTokenHintFromToken('opaque-encrypted-blob-no-dots'),
        );
        // Two-part input is also classified as refresh_token (fewer
        // than 3 parts → not JWT).
        self::assertSame(
            'refresh_token',
            $this->parser->getTokenHintFromToken('two.parts'),
        );
    }

    public function testGetTokenHintThrowsOnEmptyInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token cannot be empty');

        $this->parser->getTokenHintFromToken('');
    }

    // -----------------------------------------------------------------
    // JWT-builder helpers
    // -----------------------------------------------------------------

    /**
     * Build an access-token-shaped JWT signed with the test fixture's
     * private key. Defaults match what JsonWebKeyParser unpacks; tests
     * override specific claims (mainly `exp`).
     *
     * @param array<string, mixed> $opts
     */
    private function buildJwt(array $opts = []): string
    {
        return $this->buildJwtWithKeys($this->privateKeyPem, $this->publicKeyPem, $opts);
    }

    /**
     * @param non-empty-string $privateKeyPem
     * @param non-empty-string $publicKeyPem
     * @param array<string, mixed> $opts
     */
    private function buildJwtWithKeys(string $privateKeyPem, string $publicKeyPem, array $opts = []): string
    {
        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKeyPem),
            InMemory::plainText($publicKeyPem),
        );

        $now = new \DateTimeImmutable();
        $iat = $opts['iat'] ?? $now;
        $nbf = $opts['nbf'] ?? $now;
        $exp = $opts['exp'] ?? $now->modify('+5 minutes');
        assert($iat instanceof \DateTimeImmutable);
        assert($nbf instanceof \DateTimeImmutable);
        assert($exp instanceof \DateTimeImmutable);

        $token = $config->builder()
            ->issuedAt($iat)
            ->canOnlyBeUsedAfter($nbf)
            ->expiresAt($exp)
            ->relatedTo('user-sub-abc')
            ->permittedFor('https://example.test/fhir')
            ->issuedBy('https://example.test/oauth2')
            ->identifiedBy('jti-test-123')
            ->withClaim('scopes', ['openid', 'api:fhir'])
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }
}

/**
 * Subclass that re-exposes CryptTrait::encrypt() as a public method
 * for tests. Inherits the same trait JsonWebKeyParser uses, so blobs
 * produced here decrypt cleanly through the parser's own decrypt()
 * when both share the same encryption key.
 *
 * @internal
 */
final class JsonWebKeyParserTestEncryptHelper extends JsonWebKeyParser
{
    /**
     * @param array<string, mixed> $claims
     */
    public function buildRefreshToken(array $claims = []): string
    {
        $defaults = [
            'scopes' => ['openid', 'api:fhir'],
            'expire_time' => time() + 3600,
            'user_id' => 'default-user-id',
            'refresh_token_id' => 'default-jti',
            'client_id' => 'default-client',
        ];
        $payload = json_encode(array_merge($defaults, $claims), JSON_THROW_ON_ERROR);
        return $this->encrypt($payload);
    }
}
