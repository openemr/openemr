<?php

/**
 * @package OpenEMR
 * @author Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OidcRp;

use OpenEMR\Common\Auth\OidcRp\OidcSessionCookie;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class OidcSessionCookieTest extends TestCase
{
    public function testRedirectCookiePreservesSessionAndSecurityAttributes(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->setName('OpenEMR');
        $session->setId('test-oidc-session');
        $params = ['lifetime' => 0, 'path' => '/clinic/', 'domain' => 'clinic.example',
            'secure' => true, 'httponly' => true, 'samesite' => 'Strict'];
        $cookie = OidcSessionCookie::create($session, $params, true);
        self::assertSame('OpenEMR', $cookie->getName());
        self::assertSame('test-oidc-session', $cookie->getValue());
        self::assertSame('lax', $cookie->getSameSite());
        self::assertSame('/clinic/', $cookie->getPath());
        self::assertSame('clinic.example', $cookie->getDomain());
        self::assertTrue($cookie->isSecure());
        self::assertTrue($cookie->isHttpOnly());
        self::assertSame(0, $cookie->getExpiresTime());
        self::assertSame('strict', OidcSessionCookie::create($session, $params, false)->getSameSite());
    }
}
