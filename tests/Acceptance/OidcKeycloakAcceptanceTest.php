<?php

/**
 * Cross-site staff login against the configured Keycloak development realm.
 * Enable with OIDC_KEYCLOAK_TESTS=1 against an isolated artifact with SSO enabled.
 *
 * @package OpenEMR
 * @author Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance;

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use OpenEMR\Tests\Acceptance\Support\PantherAcceptanceTestCase;

final class OidcKeycloakAcceptanceTest extends PantherAcceptanceTestCase
{
    public function testCrossSiteIdpLoginPreservesAuthorizationSession(): void
    {
        if (getenv('OIDC_KEYCLOAK_TESTS') !== '1') {
            self::markTestSkipped('Requires the isolated Keycloak SSO development stack.');
        }
        $client = $this->client = BrowserSession::create();
        $client->request('GET', '/interface/login/login.php?site=default');
        $client->wait(20)->until(
            static fn(WebDriver $driver): bool => $driver->findElements(WebDriverBy::id('username')) !== [],
        );
        $client->submitForm('kc-login', [
            'username' => getenv('OIDC_TEST_USERNAME') ?: 'admin',
            'password' => getenv('OIDC_TEST_PASSWORD') ?: 'pass',
        ]);
        $client->wait(20)->until(
            static fn(WebDriver $driver): bool => $driver->findElements(WebDriverBy::id('mainMenu')) !== [],
        );
        self::assertStringContainsString('/interface/main/tabs/main.php', $client->getCurrentURL());
        $cookie = $client->getWebDriver()->manage()->getCookieNamed('OpenEMR');
        self::assertNotNull($cookie);
        self::assertSame('Strict', $cookie['sameSite']);
    }
}
