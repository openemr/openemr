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
            'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
            [self::TEST_CLIENT_ID]
        );
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

    #[Test]
    public function testLogoutWithoutRedirectUriNotLoggedInReturns401(): void
    {
        // No post_logout_redirect_uri at all, not-logged-in branch.
        // Covers a common RP shape (id_token_hint only, no redirect target).
        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt(),
            ],
        ]);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Location'));
    }

    #[Test]
    public function testLogoutRejectsAudMismatch(): void
    {
        // Token claims aud = a client that isn't TEST_CLIENT_ID. The controller
        // looks up oauth_clients WHERE client_id = <aud from token>; the request
        // must not resolve LOGOUT_URI_ONE against TEST_CLIENT_ID's allowlist just
        // because the RP asks for it — the allowlist is scoped to the token's aud.
        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt('nobody', '', 'a_different_client_that_does_not_exist'),
                'post_logout_redirect_uri' => self::LOGOUT_URI_ONE,
                'state' => 'abc',
            ],
        ]);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Location'));
    }

    #[Test]
    public function testLogoutResolvesBothUrisWhenDcrRegistersMultiple(): void
    {
        // Verifies that a client whose `logout_redirect_uris` was set via the
        // real DCR flow (not a hand-crafted DB row like TEST_CLIENT_ID) still
        // resolves against both registered URIs. This is the coverage gap:
        // pipe-delimited storage encoding is only tested against a manually
        // inserted row above, so a mismatch between the DCR write path and the
        // logout read path would otherwise slip through.
        $uriA = 'https://dcr-multi-a.example';
        $uriB = 'https://dcr-multi-b.example';

        $dcrClientId = null;
        try {
            $reg = $this->http->post('/oauth2/default/registration', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => (string) json_encode([
                    'application_type' => 'private',
                    'redirect_uris' => ['https://dcr-multi.example/cb'],
                    'post_logout_redirect_uris' => [$uriA, $uriB],
                    'client_name' => 'DCR multi-URI logout test',
                    'token_endpoint_auth_method' => 'client_secret_post',
                    'contacts' => ['multi@test.example'],
                    'scope' => 'openid',
                ]),
            ]);
            // Capture the client_id best-effort before any assertion, so the
            // finally cleanup runs even if a response-shape assertion below fails.
            $clientData = json_decode((string) $reg->getBody(), true);
            if (is_array($clientData) && isset($clientData['client_id']) && is_string($clientData['client_id'])) {
                $dcrClientId = $clientData['client_id'];
            }

            $this->assertSame(200, $reg->getStatusCode(), 'DCR registration should succeed');
            $this->assertIsArray($clientData);
            $this->assertArrayHasKey('client_id', $clientData);
            $this->assertIsString($clientData['client_id']);
            $verifiedClientId = $clientData['client_id'];

            foreach ([$uriA, $uriB] as $uri) {
                $response = $this->http->get(self::LOGOUT_ENDPOINT, [
                    'query' => [
                        'id_token_hint' => $this->makeUnsignedJwt('nobody', '', $verifiedClientId),
                        'post_logout_redirect_uri' => $uri,
                        'state' => 'abc',
                    ],
                ]);
                $this->assertSame(307, $response->getStatusCode(), "URI '$uri' should be accepted");
                $this->assertSame(
                    $uri . '?state=abc',
                    $response->getHeaderLine('Location'),
                    "URI '$uri' should redirect back with state preserved"
                );
            }
        } finally {
            if ($dcrClientId !== null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                    [$dcrClientId]
                );
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$dcrClientId]
                );
            }
        }
    }

    /**
     * All the "not-logged-in branch" tests above use `sub = nobody`, so no
     * matching `oauth_trusted_user` row is found and the controller takes the
     * not-logged-in branch. The tests below seed a matching trust row so the
     * controller enters the trusted-user branch — the branch that actually
     * calls `deleteTrustedUserById`.
     *
     * Not covered here: signature-verified `id_token_hint` (expired token,
     * tampered signature). `AuthorizationController::decodeToken()` currently
     * does no signature verification, so a bad-signature token behaves
     * identically to a good one. That gap tracks separately.
     */
    #[Test]
    public function testLogoutTrustedUserBranchDeletesTrustRowAndRedirects(): void
    {
        $userId = 'test-logout-user-' . bin2hex(random_bytes(4));
        $this->seedTrustedUser($userId);
        $this->assertSame(1, $this->countTrustedUsersForTestClient($userId), 'Seeding should succeed');

        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt($userId),
                'post_logout_redirect_uri' => self::LOGOUT_URI_ONE,
                'state' => 'abc',
            ],
        ]);
        $this->assertSame(307, $response->getStatusCode());
        $this->assertSame(
            self::LOGOUT_URI_ONE . '?state=abc',
            $response->getHeaderLine('Location')
        );
        $this->assertSame(
            0,
            $this->countTrustedUsersForTestClient($userId),
            'Trust row must be deleted after successful logout in the trusted-user branch'
        );
    }

    #[Test]
    public function testLogoutTrustedUserBranchWithoutRedirectUriReturns200AndDeletesTrustRow(): void
    {
        // Trusted-user branch when the RP omits post_logout_redirect_uri: server
        // must still tear down the trust record (the actual security property)
        // and return a 200 with the signed-out message, not a redirect.
        $userId = 'test-logout-user-' . bin2hex(random_bytes(4));
        $this->seedTrustedUser($userId);

        $response = $this->http->get(self::LOGOUT_ENDPOINT, [
            'query' => [
                'id_token_hint' => $this->makeUnsignedJwt($userId),
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Location'));
        $this->assertStringContainsStringIgnoringCase(
            'signed out',
            (string) $response->getBody(),
            'Response body should carry the "signed out" confirmation'
        );
        $this->assertSame(
            0,
            $this->countTrustedUsersForTestClient($userId),
            'Trust row must be deleted even when no redirect URI is provided'
        );
    }

    private function makeUnsignedJwt(string $sub = 'nobody', string $nonce = '', string $aud = self::TEST_CLIENT_ID): string
    {
        $header = $this->b64url('{"alg":"none","typ":"JWT"}');
        $payload = $this->b64url((string) json_encode([
            'aud' => $aud,
            'sub' => $sub,
            'nonce' => $nonce,
        ]));
        return $header . '.' . $payload . '.sig';
    }

    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function seedTrustedUser(string $userId, string $nonce = ''): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `oauth_trusted_user` '
            . '(`user_id`, `client_id`, `scope`, `persist_login`, `time`, `code`, `session_cache`, `grant_type`) '
            . 'VALUES (?, ?, ?, 0, NOW(), ?, ?, ?)',
            [
                $userId,
                self::TEST_CLIENT_ID,
                'openid',
                'test-code-' . bin2hex(random_bytes(4)),
                (string) json_encode(['nonce' => $nonce]),
                'authorization_code',
            ]
        );
    }

    private function countTrustedUsersForTestClient(string $userId): int
    {
        $row = QueryUtils::querySingleRow(
            'SELECT COUNT(*) AS n FROM `oauth_trusted_user` WHERE `client_id` = ? AND `user_id` = ?',
            [self::TEST_CLIENT_ID, $userId]
        );
        if (!is_array($row) || !isset($row['n'])) {
            return 0;
        }
        $n = $row['n'];
        return is_int($n) ? $n : (is_string($n) && ctype_digit($n) ? (int) $n : 0);
    }
}
