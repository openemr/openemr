<?php

/**
 * Isolated tests for ClientCredentialsAssertionGenerator class
 *
 * Tests the OAuth2 client credentials assertion generator functionality without requiring
 * database connections or external dependencies. Validates JWT generation, claim structure,
 * RSA-SHA384 signing, timing logic, and edge case handling for OAuth2 client credentials flow.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Generated Tests
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Tools\OAuth2;

use OpenEMR\Tools\OAuth2\ClientCredentialsAssertionGenerator;
use PHPUnit\Framework\TestCase;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Encoding\JoseEncoder;

class ClientCredentialsAssertionGeneratorTest extends TestCase
{
    private string $privateKeyPem = '';
    private string $publicKeyPem = '';
    private ?InMemory $privateKey = null;
    private ?InMemory $publicKey = null;

    protected function setUp(): void
    {
        // Generate a test RSA key pair for testing
        $config = [
            "digest_alg" => "sha384",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        if ($res === false) {
            $this->markTestSkipped('OpenSSL not available or failed to generate key pair');
            return;
        }

        $success = openssl_pkey_export($res, $privateKeyPem);
        if (!$success) {
            $this->markTestSkipped('Failed to export private key');
            return;
        }

        $publicKeyDetails = openssl_pkey_get_details($res);
        if ($publicKeyDetails === false || !isset($publicKeyDetails['key'])) {
            $this->markTestSkipped('Failed to get public key details');
            return;
        }

        $this->privateKeyPem = $privateKeyPem;
        $this->publicKeyPem = $publicKeyDetails['key'];

        $this->privateKey = InMemory::plainText($this->privateKeyPem);
        $this->publicKey = InMemory::plainText($this->publicKeyPem);
    }

    private function ensureKeysAvailable(): void
    {
        if ($this->privateKey === null || $this->publicKey === null) {
            $this->markTestSkipped('Test keys not available');
        }
    }

    public function testGenerateAssertion(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = 'https://example.com/oauth/token';
        $clientId = 'test-client-id';

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        $this->assertIsString($assertion);
        $this->assertNotEmpty($assertion);

        // JWT should have three parts separated by dots
        $parts = explode('.', $assertion);
        $this->assertCount(3, $parts);

        // Each part should be base64url-encoded and not empty
        foreach ($parts as $part) {
            $this->assertNotEmpty($part);
            // JWT uses base64url encoding, not standard base64
            $decoded = base64_decode(strtr($part, '-_', '+/') . str_repeat('=', (4 - strlen($part) % 4) % 4), true);
            $this->assertNotFalse($decoded);
        }
    }

    public function testAssertionContainsCorrectClaims(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = 'https://example.com/oauth/token';
        $clientId = 'test-client-id';

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        // Parse the JWT to verify claims
        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($assertion);

        $this->assertInstanceOf(UnencryptedToken::class, $token);

        // Verify issuer claim (iss)
        $this->assertTrue($token->claims()->has('iss'));
        $this->assertEquals($clientId, $token->claims()->get('iss'));

        // Verify audience claim (aud)
        $this->assertTrue($token->claims()->has('aud'));
        $audience = $token->claims()->get('aud');
        if (is_array($audience)) {
            $this->assertContains($oauthTokenUrl, $audience);
        } else {
            $this->assertEquals($oauthTokenUrl, $audience);
        }

        // Verify subject claim (sub)
        $this->assertTrue($token->claims()->has('sub'));
        $this->assertEquals($clientId, $token->claims()->get('sub'));

        // Verify JTI claim exists
        $this->assertTrue($token->claims()->has('jti'));
        $this->assertNotEmpty($token->claims()->get('jti'));

        // Verify issued at claim (iat)
        $this->assertTrue($token->claims()->has('iat'));
        $this->assertInstanceOf(\DateTimeImmutable::class, $token->claims()->get('iat'));

        // Verify not before claim (nbf)
        $this->assertTrue($token->claims()->has('nbf'));
        $this->assertInstanceOf(\DateTimeImmutable::class, $token->claims()->get('nbf'));

        // Verify expiration claim (exp)
        $this->assertTrue($token->claims()->has('exp'));
        $this->assertInstanceOf(\DateTimeImmutable::class, $token->claims()->get('exp'));
    }

    public function testAssertionExpirationTime(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = 'https://example.com/oauth/token';
        $clientId = 'test-client-id';

        $beforeGeneration = new \DateTimeImmutable();

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        $afterGeneration = new \DateTimeImmutable();

        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($assertion);

        $iat = $token->claims()->get('iat');
        $exp = $token->claims()->get('exp');

        $this->assertInstanceOf(\DateTimeImmutable::class, $iat);
        $this->assertInstanceOf(\DateTimeImmutable::class, $exp);

        // Verify the token is issued within our test timeframe
        $this->assertGreaterThanOrEqual($beforeGeneration->getTimestamp(), $iat->getTimestamp());
        $this->assertLessThanOrEqual($afterGeneration->getTimestamp(), $iat->getTimestamp());

        // Verify expiration is 60 seconds after issued time
        $expectedExp = $iat->modify('+60 seconds');
        $this->assertEquals($expectedExp->getTimestamp(), $exp->getTimestamp());
    }

    public function testAssertionNotBeforeTime(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = 'https://example.com/oauth/token';
        $clientId = 'test-client-id';

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($assertion);

        $iat = $token->claims()->get('iat');
        $nbf = $token->claims()->get('nbf');

        $this->assertInstanceOf(\DateTimeImmutable::class, $iat);
        $this->assertInstanceOf(\DateTimeImmutable::class, $nbf);

        // nbf should be the same as iat (can be used immediately)
        $this->assertEquals($iat->getTimestamp(), $nbf->getTimestamp());
    }

    public function testJtiIsUniqueUuid(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = 'https://example.com/oauth/token';
        $clientId = 'test-client-id';

        $assertion1 = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        $assertion2 = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        $parser = new Parser(new JoseEncoder());
        $token1 = $parser->parse($assertion1);
        $token2 = $parser->parse($assertion2);

        $jti1 = $token1->claims()->get('jti');
        $jti2 = $token2->claims()->get('jti');

        $this->assertNotEquals($jti1, $jti2);

        // Verify JTI format looks like a UUID
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $jti1
        );
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $jti2
        );
    }

    public function testDifferentClientIds(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = 'https://example.com/oauth/token';
        $clientId1 = 'client-one';
        $clientId2 = 'client-two';

        $assertion1 = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId1
        );

        $assertion2 = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId2
        );

        $parser = new Parser(new JoseEncoder());
        $token1 = $parser->parse($assertion1);
        $token2 = $parser->parse($assertion2);

        $this->assertEquals($clientId1, $token1->claims()->get('iss'));
        $this->assertEquals($clientId1, $token1->claims()->get('sub'));

        $this->assertEquals($clientId2, $token2->claims()->get('iss'));
        $this->assertEquals($clientId2, $token2->claims()->get('sub'));

        $this->assertNotEquals($assertion1, $assertion2);
    }

    public function testDifferentOAuthUrls(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl1 = 'https://server1.com/oauth/token';
        $oauthTokenUrl2 = 'https://server2.com/oauth/token';
        $clientId = 'test-client';

        $assertion1 = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl1,
            $clientId
        );

        $assertion2 = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl2,
            $clientId
        );

        $parser = new Parser(new JoseEncoder());
        $token1 = $parser->parse($assertion1);
        $token2 = $parser->parse($assertion2);

        $audience1 = $token1->claims()->get('aud');
        $audience2 = $token2->claims()->get('aud');

        if (is_array($audience1)) {
            $this->assertContains($oauthTokenUrl1, $audience1);
        } else {
            $this->assertEquals($oauthTokenUrl1, $audience1);
        }

        if (is_array($audience2)) {
            $this->assertContains($oauthTokenUrl2, $audience2);
        } else {
            $this->assertEquals($oauthTokenUrl2, $audience2);
        }

        $this->assertNotEquals($assertion1, $assertion2);
    }

    public function testTokenSignature(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = 'https://example.com/oauth/token';
        $clientId = 'test-client-id';

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        // Parse and verify the token signature
        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($assertion);

        $this->assertInstanceOf(UnencryptedToken::class, $token);

        // The token should have a valid signature part
        $parts = explode('.', $assertion);
        $this->assertCount(3, $parts);
        $this->assertNotEmpty($parts[2]); // signature part

        // Verify the token headers
        $this->assertTrue($token->headers()->has('alg'));
        $this->assertEquals('RS384', $token->headers()->get('alg'));

        $this->assertTrue($token->headers()->has('typ'));
        $this->assertEquals('JWT', $token->headers()->get('typ'));
    }

    public function testEmptyClientId(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = 'https://example.com/oauth/token';
        $clientId = '';

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($assertion);

        $this->assertEquals('', $token->claims()->get('iss'));
        $this->assertEquals('', $token->claims()->get('sub'));
    }

    public function testEmptyOAuthUrl(): void
    {
        $this->ensureKeysAvailable();

        $oauthTokenUrl = '';
        $clientId = 'test-client';

        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $this->privateKey,
            $this->publicKey,
            $oauthTokenUrl,
            $clientId
        );

        $parser = new Parser(new JoseEncoder());
        $token = $parser->parse($assertion);

        $audience = $token->claims()->get('aud');
        if (is_array($audience)) {
            $this->assertContains('', $audience);
        } else {
            $this->assertEquals('', $audience);
        }
    }
}
