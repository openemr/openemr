<?php

/**
 * OIDC RP-Initiated Logout regression tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AuthorizationLogoutTest extends TestCase
{
    private const LOGOUT_ENDPOINT = '/oauth2/default/logout';
    private const TEST_CLIENT_ID = 'test_logout_regression_client';
    private const LOGOUT_URI_ONE = 'https://client1.example';
    private const LOGOUT_URI_TWO = 'https://client2.example';
    private const LOGOUT_URI_WITH_QUERY = 'https://has-query.example?extra=1';

    private Client $http;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->http = new Client([
            'base_uri' => $baseUrl,
            'verify' => false,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);

        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
            [self::TEST_CLIENT_ID]
        );
        $allowlist = self::LOGOUT_URI_ONE . '|' . self::LOGOUT_URI_TWO . '|' . self::LOGOUT_URI_WITH_QUERY;
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `oauth_clients` '
            . '(`client_id`, `client_name`, `client_role`, `client_secret`, `logout_redirect_uris`, `register_date`, `is_enabled`) '
            . 'VALUES (?, ?, ?, ?, ?, NOW(), 1)',
            [self::TEST_CLIENT_ID, 'Logout Regression Test', 'users', 'sec', $allowlist]
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
            [self::TEST_CLIENT_ID]
        );
    }

    #[Test]
    public function testLogoutRejectsUnregisteredUri(): void
    {
        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt(),
                'post_logout_redirect_uri' => 'https://evil.example',
                'state' => 'abc',
            ],
        ]);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Location'));
    }

    #[Test]
    public function testLogoutRedirectsToRegisteredUri(): void
    {
        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt(),
                'post_logout_redirect_uri' => self::LOGOUT_URI_ONE,
                'state' => 'abc',
            ],
        ]);
        $this->assertSame(307, $response->getStatusCode());
        $this->assertSame(
            self::LOGOUT_URI_ONE . '?state=abc',
            $response->getHeaderLine('Location')
        );
    }

    #[Test]
    public function testLogoutMatchesLaterUriInPipeDelimitedAllowlist(): void
    {
        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt(),
                'post_logout_redirect_uri' => self::LOGOUT_URI_TWO,
                'state' => 'abc',
            ],
        ]);
        $this->assertSame(307, $response->getStatusCode());
        $this->assertSame(
            self::LOGOUT_URI_TWO . '?state=abc',
            $response->getHeaderLine('Location')
        );
    }

    #[Test]
    public function testLogoutUsesAmpersandSeparatorWhenUriContainsQueryString(): void
    {
        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt(),
                'post_logout_redirect_uri' => self::LOGOUT_URI_WITH_QUERY,
                'state' => 'abc',
            ],
        ]);
        $this->assertSame(307, $response->getStatusCode());
        $this->assertSame(
            self::LOGOUT_URI_WITH_QUERY . '&state=abc',
            $response->getHeaderLine('Location')
        );
    }

    #[Test]
    public function testLogoutUrlEncodesStateWithSpecialChars(): void
    {
        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt(),
                'post_logout_redirect_uri' => self::LOGOUT_URI_ONE,
                'state' => 'foo&bar=baz',
            ],
        ]);
        $this->assertSame(307, $response->getStatusCode());
        $this->assertSame(
            self::LOGOUT_URI_ONE . '?state=foo%26bar%3Dbaz',
            $response->getHeaderLine('Location')
        );
    }

    #[Test]
    public function testLogoutOmitsStateWhenNotProvided(): void
    {
        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt(),
                'post_logout_redirect_uri' => self::LOGOUT_URI_ONE,
            ],
        ]);
        $this->assertSame(307, $response->getStatusCode());
        $this->assertSame(
            self::LOGOUT_URI_ONE,
            $response->getHeaderLine('Location')
        );
    }

    private function makeUnsignedJwt(): string
    {
        $header = $this->b64url('{"alg":"none","typ":"JWT"}');
        $payload = $this->b64url((string) json_encode([
            'aud' => self::TEST_CLIENT_ID,
            'sub' => 'nobody',
            'nonce' => '',
        ]));
        return $header . '.' . $payload . '.sig';
    }

    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
