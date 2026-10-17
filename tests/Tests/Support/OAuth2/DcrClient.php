<?php

/**
 * DCR helpers for api-tier OAuth2 tests.
 *
 * Thin wrappers over /oauth2/{site}/registration + a direct-DB enable
 * step (grant_types / is_enabled) so a test file can register a client
 * and get it ready for a specific grant in two lines, without
 * re-implementing the DCR POST / scope-grant plumbing each time.
 *
 * Separate from `tests/Acceptance/Support/OAuth2/AuthCodeFlow.php`,
 * which enables clients by navigating the admin panel (artifact-tier,
 * no DB access). The api-tier variant has DB access, so grant_types +
 * is_enabled are set directly for predictable, fast setup.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Support\OAuth2;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Assert;

class DcrClient
{
    /**
     * Register a confidential client (returns client_secret) with the
     * given scope list, then set its grant_types + is_enabled directly
     * via DB so it's immediately usable for the named grant.
     *
     * @return array{string, string} [client_id, client_secret]
     */
    public static function registerConfidential(
        string $baseUrl,
        string $clientName,
        string $scope,
        string $grantType,
        string $redirectUri = 'https://client.example/cb',
    ): array {
        $reg = self::buildClient()->post($baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [$redirectUri],
                'client_name' => $clientName,
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => $scope,
            ],
        ]);
        Assert::assertSame(200, $reg->getStatusCode(), 'DCR should succeed. Body: ' . (string) $reg->getBody());
        $data = json_decode((string) $reg->getBody(), true);
        Assert::assertIsArray($data);
        Assert::assertIsString($data['client_id'] ?? null);
        Assert::assertIsString($data['client_secret'] ?? null);
        $clientId = $data['client_id'];
        try {
            self::enableViaDb($clientId, $grantType);
        } catch (\Throwable $e) {
            // Enable step failed after a successful DCR — remove the
            // registered client so a caller that never receives the
            // ID doesn't leak the row. Then rethrow.
            self::remove($clientId);
            throw $e;
        }
        return [$clientId, $data['client_secret']];
    }

    /**
     * Register a public client (no client_secret, PKCE required) with
     * the given scope list, then enable it for the named grant.
     *
     * OpenEMR DCR restricts token_endpoint_auth_method to
     * client_secret_basic | client_secret_post | private_key_jwt
     * (AuthorizationController::registerClient allowed list — "none"
     * is rejected with invalid_client_metadata). The flip to a public
     * client is driven by application_type != 'private'; with
     * application_type=web the server skips client_secret generation,
     * so the registered client is effectively public even though
     * token_endpoint_auth_method is declared as client_secret_post.
     * See `AuthorizationGrantPublicClientPkceTest::doPkceAuthorize`
     * for the reference registration pattern.
     */
    public static function registerPublic(
        string $baseUrl,
        string $clientName,
        string $scope,
        string $grantType,
        string $redirectUri = 'https://client.example/cb',
    ): string {
        $reg = self::buildClient()->post($baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'web',
                'redirect_uris' => [$redirectUri],
                'client_name' => $clientName,
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => $scope,
            ],
        ]);
        Assert::assertSame(200, $reg->getStatusCode(), 'DCR should succeed. Body: ' . (string) $reg->getBody());
        $data = json_decode((string) $reg->getBody(), true);
        Assert::assertIsArray($data);
        Assert::assertIsString($data['client_id'] ?? null);
        $clientId = $data['client_id'];
        try {
            self::enableViaDb($clientId, $grantType);
        } catch (\Throwable $e) {
            // Same orphan-avoidance pattern as registerConfidential.
            self::remove($clientId);
            throw $e;
        }
        return $clientId;
    }

    /**
     * Direct-DB enable: set the client's grant_types + is_enabled.
     * The DCR endpoint registers clients in a pre-approved state for
     * the standard SMART grants, but password grant requires an admin
     * action (grant_types='password') that the DCR endpoint does not
     * perform. Doing it inline here matches the admin-approval step
     * that the acceptance-tier AuthCodeFlow does via the admin panel.
     */
    public static function enableViaDb(string $clientId, string $grantType): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            [$grantType, $clientId]
        );
    }

    /**
     * Cleanup: remove the client row. Safe to call for a client that
     * does not exist (no error, 0 rows affected).
     */
    public static function remove(string $clientId): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
            [$clientId]
        );
    }

    private static function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
