<?php

/**
 * Tests for the SSO TokenService
 *
 * @package   OpenEMR\Tests\Unit\Modules\SSO\Services
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Modules\SSO\Services;

use OpenEMR\Modules\SSO\Services\TokenService;
use PHPUnit\Framework\TestCase;

final class TokenServiceTest extends TestCase
{
    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = new TokenService();
    }

    public function testGenerateCodeVerifier(): void
    {
        $verifier = $this->tokenService->generateCodeVerifier();

        $this->assertIsString($verifier);
        $this->assertGreaterThanOrEqual(43, strlen($verifier));
        // Should be URL-safe base64
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $verifier);
    }

    public function testGenerateCodeChallenge(): void
    {
        $verifier = $this->tokenService->generateCodeVerifier();
        $challenge = $this->tokenService->generateCodeChallenge($verifier);

        $this->assertIsString($challenge);
        $this->assertGreaterThanOrEqual(43, strlen($challenge));
        // Should be URL-safe base64
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $challenge);
        // Challenge should be different from verifier
        $this->assertNotEquals($verifier, $challenge);
    }

    public function testGenerateCodeChallengeIsDeterministic(): void
    {
        $verifier = 'test_verifier_string_12345';
        $challenge1 = $this->tokenService->generateCodeChallenge($verifier);
        $challenge2 = $this->tokenService->generateCodeChallenge($verifier);

        $this->assertEquals($challenge1, $challenge2);
    }

    public function testGenerateState(): void
    {
        $state = $this->tokenService->generateState();

        $this->assertIsString($state);
        $this->assertEquals(32, strlen($state));
        // Should be hex
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $state);
    }

    public function testGenerateStateIsUnique(): void
    {
        $state1 = $this->tokenService->generateState();
        $state2 = $this->tokenService->generateState();

        $this->assertNotEquals($state1, $state2);
    }

    public function testGenerateNonce(): void
    {
        $nonce = $this->tokenService->generateNonce();

        $this->assertIsString($nonce);
        $this->assertEquals(32, strlen($nonce));
        // Should be hex
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $nonce);
    }

    public function testGenerateNonceIsUnique(): void
    {
        $nonce1 = $this->tokenService->generateNonce();
        $nonce2 = $this->tokenService->generateNonce();

        $this->assertNotEquals($nonce1, $nonce2);
    }

    public function testValidateTokenWithInvalidFormat(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT format');

        $this->tokenService->validateToken(
            'not.a.valid.jwt.token',
            'https://example.com/.well-known/jwks.json',
            'client_id',
            'https://issuer.example.com'
        );
    }

    public function testValidateTokenWithInvalidHeader(): void
    {
        // Create a token with invalid header
        $invalidToken = base64_encode('invalid') . '.' . base64_encode('{}') . '.signature';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT header');

        $this->tokenService->validateToken(
            $invalidToken,
            'https://example.com/.well-known/jwks.json',
            'client_id',
            'https://issuer.example.com'
        );
    }

    public function testValidateTokenWithMissingKid(): void
    {
        // Create a token with header missing kid
        $header = base64_encode(json_encode(['alg' => 'RS256']));
        $payload = base64_encode(json_encode(['sub' => 'user']));
        $token = $header . '.' . $payload . '.signature';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JWT header');

        $this->tokenService->validateToken(
            $token,
            'https://example.com/.well-known/jwks.json',
            'client_id',
            'https://issuer.example.com'
        );
    }
}
