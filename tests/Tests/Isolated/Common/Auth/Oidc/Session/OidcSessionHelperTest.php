<?php

/**
 * Isolated tests for OidcSessionHelper.
 *
 * Verifies that OIDC token metadata (expiry, issuer, jti, subject) is
 * correctly stored in and retrieved from the PHP session.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Session;

use OpenEMR\Common\Auth\Oidc\Session\OidcSessionHelper;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OidcSessionHelperTest extends TestCase
{
    protected function setUp(): void
    {
        SessionWrapperFactory::getInstance()->setActiveSession($this->createSessionStub());
    }

    public function testSetAndGetTokenMetadata(): void
    {
        $expiry = new \DateTimeImmutable('2026-04-05 12:00:00');

        OidcSessionHelper::setTokenMetadata($expiry, 'https://issuer.example.com', 'jti-abc', 'sub-123', 'client-456');

        self::assertTrue(OidcSessionHelper::isOidcSession());
        self::assertSame($expiry->getTimestamp(), OidcSessionHelper::getTokenExpiry());
        self::assertSame('https://issuer.example.com', OidcSessionHelper::getIssuer());
        self::assertSame('jti-abc', OidcSessionHelper::getJti());
        self::assertSame('sub-123', OidcSessionHelper::getSubject());
        self::assertSame('client-456', OidcSessionHelper::getAudience());
    }

    public function testSetTokenMetadataWithNullOptionals(): void
    {
        $expiry = new \DateTimeImmutable('2026-04-05 12:00:00');

        OidcSessionHelper::setTokenMetadata($expiry, 'https://issuer.example.com');

        self::assertTrue(OidcSessionHelper::isOidcSession());
        self::assertNull(OidcSessionHelper::getJti());
        self::assertNull(OidcSessionHelper::getSubject());
        self::assertNull(OidcSessionHelper::getAudience());
    }

    public function testIsOidcSessionReturnsFalseWhenNotSet(): void
    {
        self::assertFalse(OidcSessionHelper::isOidcSession());
    }

    public function testGetTokenExpiryReturnsNullWhenNotSet(): void
    {
        self::assertNull(OidcSessionHelper::getTokenExpiry());
    }

    public function testGetIssuerReturnsNullWhenNotSet(): void
    {
        self::assertNull(OidcSessionHelper::getIssuer());
    }

    public function testGetSubjectReturnsNullWhenNotSet(): void
    {
        self::assertNull(OidcSessionHelper::getSubject());
    }

    public function testGetAudienceReturnsNullWhenNotSet(): void
    {
        self::assertNull(OidcSessionHelper::getAudience());
    }

    public function testUpdateTokenExpiry(): void
    {
        $original = new \DateTimeImmutable('2026-04-05 12:00:00');
        OidcSessionHelper::setTokenMetadata($original, 'https://issuer.example.com', 'jti-old', 'sub-123', 'client-456');

        $newExpiry = new \DateTimeImmutable('2026-04-05 13:00:00');
        OidcSessionHelper::updateTokenExpiry($newExpiry, 'jti-new');

        self::assertSame($newExpiry->getTimestamp(), OidcSessionHelper::getTokenExpiry());
        self::assertSame('jti-new', OidcSessionHelper::getJti());
        // Subject, issuer, and audience unchanged
        self::assertSame('https://issuer.example.com', OidcSessionHelper::getIssuer());
        self::assertSame('sub-123', OidcSessionHelper::getSubject());
        self::assertSame('client-456', OidcSessionHelper::getAudience());
    }

    public function testUpdateTokenExpiryWithNullJtiPreservesExisting(): void
    {
        $expiry = new \DateTimeImmutable('2026-04-05 12:00:00');
        OidcSessionHelper::setTokenMetadata($expiry, 'https://issuer.example.com', 'jti-original');

        $newExpiry = new \DateTimeImmutable('2026-04-05 13:00:00');
        OidcSessionHelper::updateTokenExpiry($newExpiry);

        self::assertSame($newExpiry->getTimestamp(), OidcSessionHelper::getTokenExpiry());
        self::assertSame('jti-original', OidcSessionHelper::getJti());
    }

    public function testIsTokenExpiredReturnsFalseBeforeExpiry(): void
    {
        $expiry = new \DateTimeImmutable('2026-04-05 12:00:00');
        OidcSessionHelper::setTokenMetadata($expiry, 'https://issuer.example.com');

        $now = $expiry->getTimestamp() - 60;
        self::assertFalse(OidcSessionHelper::isTokenExpired($now));
    }

    public function testIsTokenExpiredReturnsTrueAfterExpiry(): void
    {
        $expiry = new \DateTimeImmutable('2026-04-05 12:00:00');
        OidcSessionHelper::setTokenMetadata($expiry, 'https://issuer.example.com');

        $now = $expiry->getTimestamp() + 1;
        self::assertTrue(OidcSessionHelper::isTokenExpired($now));
    }

    public function testIsTokenExpiredRespectsGracePeriod(): void
    {
        $expiry = new \DateTimeImmutable('2026-04-05 12:00:00');
        OidcSessionHelper::setTokenMetadata($expiry, 'https://issuer.example.com');

        // 30 seconds after expiry, but within 60-second grace
        $now = $expiry->getTimestamp() + 30;
        self::assertFalse(OidcSessionHelper::isTokenExpired($now, 60));

        // 61 seconds after expiry, beyond grace
        $now = $expiry->getTimestamp() + 61;
        self::assertTrue(OidcSessionHelper::isTokenExpired($now, 60));
    }

    public function testIsTokenExpiredReturnsFalseWhenNotOidcSession(): void
    {
        self::assertFalse(OidcSessionHelper::isTokenExpired(time()));
    }

    public function testIsRefreshOnCooldownReturnsFalseWhenNeverRefreshed(): void
    {
        self::assertFalse(OidcSessionHelper::isRefreshOnCooldown(time()));
    }

    public function testIsRefreshOnCooldownReturnsTrueWithinCooldown(): void
    {
        $now = 1000000;
        OidcSessionHelper::recordRefresh($now);

        // 10 seconds later — within 30s cooldown
        self::assertTrue(OidcSessionHelper::isRefreshOnCooldown($now + 10));
    }

    public function testIsRefreshOnCooldownReturnsFalseAfterCooldown(): void
    {
        $now = 1000000;
        OidcSessionHelper::recordRefresh($now);

        // 31 seconds later — past 30s cooldown
        self::assertFalse(OidcSessionHelper::isRefreshOnCooldown($now + 31));
    }

    public function testIsRefreshOnCooldownReturnsFalseAtExactBoundary(): void
    {
        $now = 1000000;
        OidcSessionHelper::recordRefresh($now);

        // Exactly 30 seconds later — boundary (not less than, so allowed)
        self::assertFalse(OidcSessionHelper::isRefreshOnCooldown($now + 30));
    }

    public function testRecordRefreshUpdatesTimestamp(): void
    {
        $first = 1000000;
        OidcSessionHelper::recordRefresh($first);

        // 31s after first — past cooldown
        self::assertFalse(OidcSessionHelper::isRefreshOnCooldown($first + 31));

        // Record again at first + 31
        $second = $first + 31;
        OidcSessionHelper::recordRefresh($second);

        // 10s after second — within cooldown again
        self::assertTrue(OidcSessionHelper::isRefreshOnCooldown($second + 10));
    }

    public function testClearTokenMetadataClearsCooldown(): void
    {
        OidcSessionHelper::recordRefresh(time());
        OidcSessionHelper::clearTokenMetadata();

        self::assertFalse(OidcSessionHelper::isRefreshOnCooldown(time()));
    }

    public function testClearTokenMetadata(): void
    {
        $expiry = new \DateTimeImmutable('2026-04-05 12:00:00');
        OidcSessionHelper::setTokenMetadata($expiry, 'https://issuer.example.com', 'jti-abc', 'sub-123', 'client-456');

        OidcSessionHelper::clearTokenMetadata();

        self::assertFalse(OidcSessionHelper::isOidcSession());
        self::assertNull(OidcSessionHelper::getTokenExpiry());
        self::assertNull(OidcSessionHelper::getIssuer());
        self::assertNull(OidcSessionHelper::getJti());
        self::assertNull(OidcSessionHelper::getSubject());
        self::assertNull(OidcSessionHelper::getAudience());
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
        $session->method('remove')
            ->willReturnCallback(function (string $key) use (&$store): void {
                unset($store[$key]);
            });
        return $session;
    }
}
