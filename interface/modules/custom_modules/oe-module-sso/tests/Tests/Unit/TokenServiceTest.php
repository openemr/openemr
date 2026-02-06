<?php

/**
 * TokenService Unit Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SSO\Tests\Unit;

use OpenEMR\Modules\SSO\Services\TokenService;
use PHPUnit\Framework\TestCase;

class TokenServiceTest extends TestCase
{
    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = new TokenService();
    }

    public function testGenerateCodeVerifierReturnsValidLength(): void
    {
        $verifier = $this->tokenService->generateCodeVerifier();

        // PKCE code verifier should be 43-128 characters
        $this->assertGreaterThanOrEqual(43, strlen($verifier));
        $this->assertLessThanOrEqual(128, strlen($verifier));
    }

    public function testGenerateCodeVerifierReturnsUrlSafeString(): void
    {
        $verifier = $this->tokenService->generateCodeVerifier();

        // Should only contain URL-safe base64 characters (no + / =)
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $verifier);
    }

    public function testGenerateCodeVerifierReturnsUniqueValues(): void
    {
        $verifier1 = $this->tokenService->generateCodeVerifier();
        $verifier2 = $this->tokenService->generateCodeVerifier();

        $this->assertNotEquals($verifier1, $verifier2);
    }

    public function testGenerateCodeChallengeReturnsSha256Hash(): void
    {
        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
        $expectedChallenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';

        $challenge = $this->tokenService->generateCodeChallenge($verifier);

        $this->assertEquals($expectedChallenge, $challenge);
    }

    public function testGenerateCodeChallengeReturnsUrlSafeString(): void
    {
        $verifier = $this->tokenService->generateCodeVerifier();
        $challenge = $this->tokenService->generateCodeChallenge($verifier);

        // Should only contain URL-safe base64 characters
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $challenge);
    }

    public function testGenerateStateReturnsHexString(): void
    {
        $state = $this->tokenService->generateState();

        // Should be 32 hex characters (16 bytes)
        $this->assertEquals(32, strlen($state));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $state);
    }

    public function testGenerateStateReturnsUniqueValues(): void
    {
        $state1 = $this->tokenService->generateState();
        $state2 = $this->tokenService->generateState();

        $this->assertNotEquals($state1, $state2);
    }

    public function testGenerateNonceReturnsHexString(): void
    {
        $nonce = $this->tokenService->generateNonce();

        // Should be 32 hex characters (16 bytes)
        $this->assertEquals(32, strlen($nonce));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $nonce);
    }

    public function testGenerateNonceReturnsUniqueValues(): void
    {
        $nonce1 = $this->tokenService->generateNonce();
        $nonce2 = $this->tokenService->generateNonce();

        $this->assertNotEquals($nonce1, $nonce2);
    }

    public function testValidateTokenWithInvalidFormatThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT format');

        $this->tokenService->validateToken(
            'not.a.valid.jwt.token',
            'https://example.com/.well-known/jwks.json',
            'client_id',
            'https://example.com'
        );
    }

    public function testValidateTokenWithMissingPartsThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT format');

        $this->tokenService->validateToken(
            'header.payload',
            'https://example.com/.well-known/jwks.json',
            'client_id',
            'https://example.com'
        );
    }

    public function testValidateTokenWithInvalidHeaderThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT header');

        // Create a JWT with invalid header (not JSON)
        $invalidHeader = base64_encode('not-json');
        $payload = base64_encode(json_encode(['sub' => '123']));
        $signature = base64_encode('signature');

        $this->tokenService->validateToken(
            "$invalidHeader.$payload.$signature",
            'https://example.com/.well-known/jwks.json',
            'client_id',
            'https://example.com'
        );
    }
}
