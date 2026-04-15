<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Event;

use OpenEMR\Common\Auth\Oidc\Event\OidcAuthenticationEvent;
use OpenEMR\Common\Auth\Oidc\Identity\NormalizedIdentity;
use PHPUnit\Framework\TestCase;

final class OidcAuthenticationEventTest extends TestCase
{
    private function createIdentity(): NormalizedIdentity
    {
        return new NormalizedIdentity(
            externalId: 'sub-123',
            issuer: 'https://accounts.example.com',
            email: 'user@example.com',
            emailVerified: true,
            displayName: 'Test User',
        );
    }

    public function testExposesAllProperties(): void
    {
        $identity = $this->createIdentity();
        $expiresAt = new \DateTimeImmutable('2026-01-15T13:00:00Z');
        $claims = ['sub' => 'sub-123', 'email' => 'user@example.com'];

        $event = new OidcAuthenticationEvent(
            identity: $identity,
            userId: 42,
            username: 'dr.smith',
            expiresAt: $expiresAt,
            jti: 'unique-jti',
            claims: $claims,
        );

        self::assertSame($identity, $event->getIdentity());
        self::assertSame(42, $event->getUserId());
        self::assertSame('dr.smith', $event->getUsername());
        self::assertSame($expiresAt, $event->getExpiresAt());
        self::assertSame('unique-jti', $event->getJti());
        self::assertSame($claims, $event->getClaims());
    }

    public function testOptionalFieldsDefaultToNull(): void
    {
        $event = new OidcAuthenticationEvent(
            identity: $this->createIdentity(),
            userId: 1,
            username: 'admin',
            expiresAt: new \DateTimeImmutable(),
        );

        self::assertNull($event->getJti());
        self::assertSame([], $event->getClaims());
    }

    public function testHasEventNameConstant(): void
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType (intentional — guards against accidental constant changes) */
        self::assertSame('oidc.authentication.success', OidcAuthenticationEvent::EVENT_NAME);
    }
}
