<?php

/**
 * Isolated tests for OIDC login CSRF protection.
 *
 * Verifies that the HMAC-based CSRF token with subject 'oidc_login' works
 * correctly for protecting the OIDC token POST on the login page.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc;

use OpenEMR\Common\Csrf\CsrfUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OidcLoginCsrfTest extends TestCase
{
    private const SUBJECT = 'oidc_login';

    public function testOidcLoginTokenRoundTrip(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $token = CsrfUtils::collectCsrfToken($session, self::SUBJECT);

        self::assertTrue(CsrfUtils::verifyCsrfToken($token, $session, self::SUBJECT));
    }

    public function testOidcLoginTokenIsDistinctFromDefaultSubject(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $oidcToken = CsrfUtils::collectCsrfToken($session, self::SUBJECT);
        $defaultToken = CsrfUtils::collectCsrfToken($session, 'default');

        self::assertNotSame($oidcToken, $defaultToken);
    }

    public function testOidcLoginTokenRejectedWithDefaultSubject(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $oidcToken = CsrfUtils::collectCsrfToken($session, self::SUBJECT);

        self::assertFalse(
            CsrfUtils::verifyCsrfToken($oidcToken, $session, 'default'),
            'OIDC login token must not validate under a different subject',
        );
    }

    public function testEmptyTokenRejected(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        self::assertFalse(CsrfUtils::verifyCsrfToken('', $session, self::SUBJECT));
    }

    public function testNullTokenRejected(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        self::assertFalse(CsrfUtils::verifyCsrfToken(null, $session, self::SUBJECT));
    }

    public function testWrongTokenRejected(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        self::assertFalse(CsrfUtils::verifyCsrfToken('forged-token', $session, self::SUBJECT));
    }

    public function testTokenIsStableAcrossMultipleCollections(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $first = CsrfUtils::collectCsrfToken($session, self::SUBJECT);
        $second = CsrfUtils::collectCsrfToken($session, self::SUBJECT);

        self::assertSame($first, $second, 'Same key + same subject must produce the same token');
    }

    public function testKeySetupOnlyIfAbsentDoesNotRegenerateExisting(): void
    {
        $session = $this->createSessionStub();

        // First setup — simulate what Bootstrap does
        if ($session->get('csrf_private_key', null) === null) {
            CsrfUtils::setupCsrfKey($session);
        }
        $tokenBefore = CsrfUtils::collectCsrfToken($session, self::SUBJECT);

        // Second call — key already exists, should NOT regenerate
        if ($session->get('csrf_private_key', null) === null) {
            CsrfUtils::setupCsrfKey($session);
        }
        $tokenAfter = CsrfUtils::collectCsrfToken($session, self::SUBJECT);

        self::assertSame($tokenBefore, $tokenAfter, 'Conditional setup must not regenerate existing key');
    }

    public function testRegeneratedKeyInvalidatesPreviousToken(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $tokenBefore = CsrfUtils::collectCsrfToken($session, self::SUBJECT);

        // Simulate post-login key regeneration (main_screen.php calls setupCsrfKey)
        CsrfUtils::setupCsrfKey($session);

        self::assertFalse(
            CsrfUtils::verifyCsrfToken($tokenBefore, $session, self::SUBJECT),
            'Token from previous key must be invalid after key regeneration',
        );
    }

    private function createSessionStub(): SessionInterface
    {
        $store = [];
        $session = $this->createStub(SessionInterface::class);
        $session->method('set')
            ->willReturnCallback(function (string $key, mixed $value) use (&$store): void {
                $store[$key] = $value;
            });
        $session->method('get')
            ->willReturnCallback(function (string $key, mixed $default = null) use (&$store): mixed {
                return $store[$key] ?? $default;
            });
        return $session;
    }
}
