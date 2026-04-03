<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Event;

use OpenEMR\Common\Auth\Oidc\Event\OidcLogoutEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OidcLogoutEvent::class)]
final class OidcLogoutEventTest extends TestCase
{
    public function testExposesProperties(): void
    {
        $event = new OidcLogoutEvent(
            issuer: 'https://accounts.example.com',
            username: 'dr.smith',
            jti: 'token-id-123',
        );

        self::assertSame('https://accounts.example.com', $event->getIssuer());
        self::assertSame('dr.smith', $event->getUsername());
        self::assertSame('token-id-123', $event->getJti());
    }

    public function testJtiDefaultsToNull(): void
    {
        $event = new OidcLogoutEvent('https://issuer.example.com', 'admin');

        self::assertNull($event->getJti());
    }

    public function testNoRedirectUrlByDefault(): void
    {
        $event = new OidcLogoutEvent('https://issuer.example.com', 'admin');

        self::assertFalse($event->hasRedirectUrl());
        self::assertNull($event->getRedirectUrl());
    }

    public function testSetRedirectUrl(): void
    {
        $event = new OidcLogoutEvent('https://issuer.example.com', 'admin');

        $event->setRedirectUrl('https://issuer.example.com/logout?post_logout_redirect_uri=https://emr.local');

        self::assertTrue($event->hasRedirectUrl());
        self::assertSame(
            'https://issuer.example.com/logout?post_logout_redirect_uri=https://emr.local',
            $event->getRedirectUrl(),
        );
    }

    public function testHasEventNameConstant(): void
    {
        self::assertSame('oidc.logout', OidcLogoutEvent::EVENT_NAME);
    }
}
