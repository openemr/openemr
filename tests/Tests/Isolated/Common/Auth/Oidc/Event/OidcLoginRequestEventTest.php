<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Event;

use OpenEMR\Common\Auth\Oidc\Event\OidcLoginRequestEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OidcLoginRequestEvent::class)]
final class OidcLoginRequestEventTest extends TestCase
{
    public function testIsNotHandledByDefault(): void
    {
        $event = new OidcLoginRequestEvent(['key' => 'value']);

        self::assertFalse($event->isHandled());
        self::assertSame('', $event->getUsername());
        self::assertSame('', $event->getPasswordHash());
        self::assertSame([], $event->getUserInfo());
        self::assertSame('', $event->getAuthGroup());
    }

    public function testExposesPostData(): void
    {
        $post = ['oidc_token' => 'jwt-string', 'other' => 'data'];
        $event = new OidcLoginRequestEvent($post);

        self::assertSame($post, $event->getPostData());
        self::assertSame('jwt-string', $event->getPostParam('oidc_token'));
        self::assertNull($event->getPostParam('nonexistent'));
    }

    public function testExposesGetData(): void
    {
        $get = ['auth' => 'login', 'site' => 'default'];
        $event = new OidcLoginRequestEvent([], $get);

        self::assertSame($get, $event->getGetData());
    }

    public function testSetAuthenticatedUserMarksHandled(): void
    {
        $event = new OidcLoginRequestEvent([]);
        $userInfo = ['id' => 42, 'authorized' => 1, 'see_auth' => '1'];

        $event->setAuthenticatedUser('dr.smith', 'hash123', $userInfo, 'admin');

        self::assertTrue($event->isHandled());
        self::assertSame('dr.smith', $event->getUsername());
        self::assertSame('hash123', $event->getPasswordHash());
        self::assertSame($userInfo, $event->getUserInfo());
        self::assertSame('admin', $event->getAuthGroup());
    }

    public function testHasEventNameConstant(): void
    {
        self::assertSame('oidc.login.request', OidcLoginRequestEvent::EVENT_NAME);
    }
}
