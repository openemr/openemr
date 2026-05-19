<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Logging;

use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;
use OpenEMR\Common\Auth\OpenIDConnect\Logging\OAuthLogContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Round-4 Aisle finding #2 (CWE-532) regression. The
 * AuthorizationController used to dump $this->session->all() and
 * $request->getQueryParams() into debug logs during the
 * authorization_code token exchange. The session bag carries CSRF
 * state, the OIDC nonce, the raw SMART launch token, the PKCE
 * codeChallenge (inside authRequestSerial), and the authenticated
 * user's PII. This test pins the redaction contract: every sensitive
 * slot is reduced to a presence boolean, no value survives.
 */
final class OAuthLogContextTest extends TestCase
{
    private function makeSession(): Session
    {
        // Symfony's MockArraySessionStorage avoids touching the
        // session.use_cookies/headers_sent path, which lets the test
        // run in pure isolation (no Docker, no PHP session configured).
        return new Session(new MockArraySessionStorage());
    }

    public function testForSessionExposesKeysAndClientIdWithoutSensitiveValues(): void
    {
        $session = $this->makeSession();
        $session->set('client_id', 'client-abc');
        $session->set('csrf', 'super-secret-csrf-token');
        $session->set('nonce', 'nonce-12345');
        $session->set('launch', 'opaque-launch-bearer-value');
        $session->set('authRequestSerial', '{"outer":{"codeChallenge":"S256-PKCE-CHALLENGE"}}');
        $session->set('user_id', 4242);
        $session->set('username', 'dr.smith');
        $session->set('email', 'smith@hospital.test');
        $session->set('redirect_uri', 'https://app.example/callback');

        $context = OAuthLogContext::forSession($session);

        // Public-ish values that operators legitimately need for triage.
        self::assertSame('client-abc', $context['client_id']);
        self::assertEqualsCanonicalizing(
            ['client_id', 'csrf', 'nonce', 'launch', 'authRequestSerial', 'user_id', 'username', 'email', 'redirect_uri'],
            $context['session_keys'],
        );

        // Every sensitive key is present-as-boolean only.
        self::assertTrue($context['has_csrf']);
        self::assertTrue($context['has_nonce']);
        self::assertTrue($context['has_launch']);
        self::assertTrue($context['has_authRequestSerial']);
        self::assertTrue($context['has_user_id']);
        self::assertTrue($context['has_username']);
        self::assertTrue($context['has_email']);

        // The actual value-leak assertion: serialize the whole context
        // and grep for any sensitive value. If a future refactor adds
        // a value-bearing key, this catches it.
        $serialized = json_encode($context, JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('super-secret-csrf-token', $serialized);
        self::assertStringNotContainsString('nonce-12345', $serialized);
        self::assertStringNotContainsString('opaque-launch-bearer-value', $serialized);
        self::assertStringNotContainsString('S256-PKCE-CHALLENGE', $serialized);
        self::assertStringNotContainsString('dr.smith', $serialized);
        self::assertStringNotContainsString('smith@hospital.test', $serialized);
    }

    public function testForSessionMarksAbsentKeysFalse(): void
    {
        $session = $this->makeSession();
        $session->set('client_id', 'client-xyz');
        // Deliberately set no sensitive keys.

        $context = OAuthLogContext::forSession($session);

        self::assertFalse($context['has_csrf']);
        self::assertFalse($context['has_nonce']);
        self::assertFalse($context['has_launch']);
        self::assertFalse($context['has_authRequestSerial']);
        self::assertFalse($context['has_user_id']);
        self::assertFalse($context['has_username']);
        self::assertFalse($context['has_email']);
        self::assertSame('client-xyz', $context['client_id']);
    }

    public function testForSessionTreatsEmptyStringAsAbsent(): void
    {
        // Matches the application's own !empty() guard semantics: an
        // empty-string CSRF or nonce would already trip the controller's
        // "missing CSRF" error path. The presence flag should reflect
        // the same notion of "populated".
        $session = $this->makeSession();
        $session->set('csrf', '');
        $session->set('nonce', '0');

        $context = OAuthLogContext::forSession($session);

        self::assertFalse($context['has_csrf'], 'empty string should read as absent');
        self::assertFalse($context['has_nonce'], '"0" reads as empty under PHP empty() — matches app guards');
    }

    public function testForAuthorizeQueryParamsRedactsSensitiveValues(): void
    {
        $params = [
            'client_id'             => 'client-abc',
            'response_type'         => 'code',
            'scope'                 => 'launch openid fhirUser patient/*.read',
            'state'                 => 'csrf-state-value',
            'nonce'                 => 'oidc-nonce-value',
            'code_challenge'        => 'S256-pkce-challenge-value',
            'code_challenge_method' => 'S256',
            'launch'                => 'opaque-launch-bearer',
            'redirect_uri'          => 'https://app.example/cb',
            'aud'                   => 'https://server.example',
        ];

        $context = OAuthLogContext::forAuthorizeQueryParams($params);

        // Public-ish OAuth params surface intact.
        self::assertSame('client-abc', $context['client_id']);
        self::assertSame('code', $context['response_type']);
        self::assertSame('launch openid fhirUser patient/*.read', $context['scope']);
        self::assertSame('S256', $context['code_challenge_method']);

        // Sensitive params present-as-boolean only.
        self::assertTrue($context['has_state']);
        self::assertTrue($context['has_nonce']);
        self::assertTrue($context['has_code_challenge']);
        self::assertTrue($context['has_launch']);
        self::assertTrue($context['has_redirect_uri']);

        $serialized = json_encode($context, JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('csrf-state-value', $serialized);
        self::assertStringNotContainsString('oidc-nonce-value', $serialized);
        self::assertStringNotContainsString('S256-pkce-challenge-value', $serialized);
        self::assertStringNotContainsString('opaque-launch-bearer', $serialized);
        self::assertStringNotContainsString('app.example/cb', $serialized);
    }

    public function testForAuthorizeQueryParamsKeepsKeyListForOperatorTriage(): void
    {
        $params = [
            'client_id' => 'c1',
            'state'     => 's',
            'launch'    => 'l',
            'unknown'   => 'whatever',
        ];

        $context = OAuthLogContext::forAuthorizeQueryParams($params);

        self::assertEqualsCanonicalizing(
            ['client_id', 'state', 'launch', 'unknown'],
            $context['queryParam_keys'],
            'Key list should be preserved so operators can spot extra/missing params',
        );
    }

    public function testSensitiveKeyListsAreNonEmpty(): void
    {
        // Drift guard: if either list is ever drained the redaction
        // becomes a no-op. Force a deliberate decision to do that
        // rather than letting it slip through.
        self::assertNotEmpty(OAuthLogContext::sensitiveSessionKeys());
        self::assertNotEmpty(OAuthLogContext::sensitiveQueryKeys());
        self::assertContains('csrf', OAuthLogContext::sensitiveSessionKeys());
        self::assertContains('launch', OAuthLogContext::sensitiveSessionKeys());
        self::assertContains('state', OAuthLogContext::sensitiveQueryKeys());
        self::assertContains('code_challenge', OAuthLogContext::sensitiveQueryKeys());
    }

    /**
     * Aisle round-5 #3 (CWE-532 / CWE-117 / CWE-400) — primary
     * happy-path contract for forJwtAssertion: returns the
     * non-attacker-controlled client_id, sanitized scalar
     * identifiers (kid/alg/jti), and the *names* of claims and
     * headers — never the values. The full claim/header bags must
     * not leak into the output.
     */
    public function testForJwtAssertionReturnsSanitizedFingerprint(): void
    {
        $token = $this->buildJwtToken(
            headers: ['alg' => 'RS384', 'kid' => 'fixture-kid', 'typ' => 'JWT'],
            claims: [
                'iss' => 'attacker-supplied-issuer',
                'sub' => 'attacker-supplied-subject',
                'jti' => 'fixture-jti-uuid',
                'exp' => 1234567890,
            ],
        );

        $context = OAuthLogContext::forJwtAssertion($token, 'real-client-id');

        self::assertSame('real-client-id', $context['client_id']);
        self::assertSame('RS384', $context['alg']);
        self::assertSame('fixture-kid', $context['kid']);
        self::assertSame('fixture-jti-uuid', $context['jti']);
        // claim/header *names* surface; values do not.
        self::assertEqualsCanonicalizing(
            ['iss', 'sub', 'jti', 'exp'],
            $context['claim_keys'],
        );
        self::assertEqualsCanonicalizing(
            ['alg', 'kid', 'typ'],
            $context['header_keys'],
        );
        // No claim/header value should appear in the output.
        $serialized = json_encode($context);
        self::assertIsString($serialized);
        self::assertStringNotContainsString('attacker-supplied-issuer', $serialized);
        self::assertStringNotContainsString('attacker-supplied-subject', $serialized);
    }

    /**
     * Aisle round-5 #3 (CWE-117) — log-injection regression. JWT
     * scalars and claim/header names are attacker-controlled. A
     * value with embedded newlines, CR, or other control characters
     * could forge log lines if dumped verbatim into a structured
     * log record. The sanitizer must replace any character outside
     * `[A-Za-z0-9._-]` with an underscore.
     */
    public function testForJwtAssertionStripsControlCharsFromScalars(): void
    {
        $token = $this->buildJwtToken(
            headers: ['kid' => "good-kid\n[INJECTED] rogue line\rERROR"],
            claims: ['jti' => "jti\twith\ttabs"],
        );

        $context = OAuthLogContext::forJwtAssertion($token, 'client');

        // Newlines, CR, tabs all replaced with underscores. Brackets
        // also fall outside the safe charset (`[A-Za-z0-9._-]`) and
        // get stripped — the safe charset is intentionally narrow so
        // anything that could end up structurally meaningful in a log
        // viewer (brackets, quotes, control chars) is neutralized.
        self::assertSame('good-kid__INJECTED__rogue_line_ERROR', $context['kid']);
        self::assertSame('jti_with_tabs', $context['jti']);
        self::assertStringNotContainsString("\n", $context['kid']);
        self::assertStringNotContainsString("\r", $context['kid']);
        self::assertStringNotContainsString("\t", $context['jti']);
    }

    /**
     * Aisle round-5 #3 (CWE-400) — log amplification regression.
     * An attacker who can submit a JWT with a megabyte-scale
     * `kid` or `jti` value would otherwise expand each request
     * into a giant log record. The sanitizer caps scalars at 64
     * chars.
     */
    public function testForJwtAssertionCapsScalarLengthAt64Chars(): void
    {
        $longKid = str_repeat('a', 5000);
        $token = $this->buildJwtToken(
            headers: ['kid' => $longKid],
            claims: ['jti' => str_repeat('b', 5000)],
        );

        $context = OAuthLogContext::forJwtAssertion($token, 'client');

        self::assertLessThanOrEqual(64, strlen($context['kid']));
        self::assertLessThanOrEqual(64, strlen($context['jti']));
    }

    /**
     * Aisle round-5 #3 (CWE-400) — log amplification via huge
     * claim/header bags. A JWT with thousands of synthetic claims
     * would otherwise produce a thousand-entry list in the log.
     * The sanitizer caps the key list at 50 entries.
     */
    public function testForJwtAssertionCapsKeyListSize(): void
    {
        $manyClaims = ['jti' => 'fixture-jti'];
        for ($i = 0; $i < 200; $i++) {
            $manyClaims['claim_' . $i] = $i;
        }
        $token = $this->buildJwtToken(
            headers: ['alg' => 'RS384', 'kid' => 'k'],
            claims: $manyClaims,
        );

        $context = OAuthLogContext::forJwtAssertion($token, 'client');

        self::assertCount(50, $context['claim_keys']);
    }

    /**
     * Build a Plain JWT token directly from DataSets — bypasses the
     * Parser entirely so tests can craft adversarial inputs without
     * going through the full JOSE builder. The encoded portions of
     * each DataSet and the Signature are irrelevant to
     * forJwtAssertion's output but must be non-empty per the
     * lcobucci constructors' type contracts.
     *
     * @param array<non-empty-string, mixed> $headers
     * @param array<non-empty-string, mixed> $claims
     */
    private function buildJwtToken(array $headers, array $claims): Plain
    {
        return new Plain(
            new DataSet($headers, 'test-encoded-headers'),
            new DataSet($claims, 'test-encoded-claims'),
            new Signature('test-sig-hash', 'test-sig-encoded'),
        );
    }
}
