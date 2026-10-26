<?php

/**
 * Regression gates for the dual-path access-token lifetime boundary.
 *
 * OpenEMR validates every bearer token on two layers:
 *
 *   1. JWT claim validation (League's BearerTokenValidator) with a
 *      `JWT_CLOCK_SKEW_LEEWAY = PT60S` tolerance window on `iat` / `nbf` / `exp`.
 *      Within the 60-second window the token is accepted even past `exp`.
 *   2. Database expiry check (`BearerTokenAuthorizationStrategy::authenticateUserToken`
 *      against `api_token.expiry`) with zero leeway — a `<=` comparison against NOW().
 *
 * The DB check runs after JWT validation, so when the two layers disagree the
 * DB wins. That is the design intent: effective TTL observed by external clients
 * is the DB value, not the JWT value. A refactor that drops the DB check (or
 * swaps the `<=` for `<`) would silently extend TTL by the 60s leeway or by one
 * second at the exact boundary.
 *
 * These tests lock that dual-path intent:
 *
 *   - Database `expiry` slightly in the future → request accepted.
 *   - Database `expiry` 1 second in the past → request rejected (pass/fail
 *     transition on the DB check; a strict-equality probe would need a
 *     controlled clock in production code).
 *   - DB `expiry` past, JWT claim still valid → request rejected
 *     (DB enforced even when JWT would accept).
 *
 * Companion to `BearerTokenRejectionPropertiesTest` (wide-margin expiry) and the
 * JWT-leeway coverage in `BearerTokenAuthorizationStrategyTest` (isolated tier).
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
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TokenLifetimeBoundaryApiTest extends TestCase
{
    private string $baseUrl;
    /** @var list<string> */
    private array $registeredClientIds = [];
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            if (getenv('CI') !== false) {
                self::fail('OAuth flow requires a real webserver (Apache/nginx) — hard failure in CI');
            }
            $this->markTestSkipped('OAuth flow requires a real webserver');
        }
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);
    }

    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($this->originalPasswordGrantSettingWasSet) {
            $globals->set('oauth_password_grant', $this->originalPasswordGrantSetting);
        } else {
            $globals->remove('oauth_password_grant');
            unset($GLOBALS['oauth_password_grant']);
        }
        foreach ($this->registeredClientIds as $clientId) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_token` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                [$clientId]
            );
        }
    }

    /**
     * `api_token.expiry` slightly in the future → request accepted.
     * Pins the pass-side of the `<=` comparison at the exact boundary. If the
     * DB check regressed to `<` instead of `<=`, this test would still pass —
     * paired with `testTokenRejectedWhenDatabaseExpiryEqualsNow` below.
     */
    #[Test]
    public function testTokenAcceptedWhenDatabaseExpiryIsSlightlyInTheFuture(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'FutureExpiry');
        $tokens = $this->passwordGrant($http, $clientId, $clientSecret, 'openid api:oemr user/patient.read');
        $this->assertIsString($tokens['access_token']);

        // Clamp the DB expiry to five seconds from now. The authenticateUserToken
        // check compares DB `expiry` with NOW() using `<=`, so a positive delta
        // of a few seconds should accept the request. The test window is tight
        // enough that the request will complete well inside the 5-second budget.
        QueryUtils::sqlStatementThrowException(
            "UPDATE `api_token` SET `expiry` = ? WHERE `client_id` = ?",
            [date('Y-m-d H:i:s', time() + 5), $clientId]
        );

        $resp = $http->get($this->baseUrl . '/apis/default/api/patient', [
            'headers' => ['Authorization' => 'Bearer ' . $tokens['access_token']],
        ]);
        $this->assertSame(
            200,
            $resp->getStatusCode(),
            'Token with api_token.expiry 5 seconds in the future must be accepted. Body: '
                . (string) $resp->getBody()
        );
    }

    /**
     * `api_token.expiry` one second in the past → request rejected.
     *
     * Locks the pass/fail transition of the DB check against a non-equal past
     * expiry. A strict-equality probe (expiry = NOW() at the moment the DB
     * check fires) would need either a controlled clock injected into
     * production or race-prone timing; both are out of scope for a test-only
     * PR. This test still catches the common regressions: deleting the check,
     * inverting the comparison, or widening the window — any of which would
     * flip this 401 to 200.
     */
    #[Test]
    public function testTokenRejectedWhenDatabaseExpiryIsOneSecondInThePast(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'PastExpiry');
        $tokens = $this->passwordGrant($http, $clientId, $clientSecret, 'openid api:oemr user/patient.read');
        $this->assertIsString($tokens['access_token']);

        // One second in the past is enough to be reliable across the DB's
        // 1-second DATETIME precision. The JWT still has an hour of life on
        // its exp claim — JWT validation accepts the token unconditionally;
        // the DB check must do the rejecting.
        QueryUtils::sqlStatementThrowException(
            "UPDATE `api_token` SET `expiry` = ? WHERE `client_id` = ?",
            [date('Y-m-d H:i:s', time() - 1), $clientId]
        );

        $resp = $http->get($this->baseUrl . '/apis/default/api/patient', [
            'headers' => ['Authorization' => 'Bearer ' . $tokens['access_token']],
        ]);
        $this->assertSame(
            401,
            $resp->getStatusCode(),
            'Token with api_token.expiry 1 second in the past must be rejected. Body: '
                . (string) $resp->getBody()
        );
    }

    /**
     * DB `expiry` past, JWT `exp` claim still valid → request rejected.
     *
     * The JWT in this test still has an hour of life on its `exp` claim (the
     * password-grant mint sets `exp = iat + 1h`), so the JWT layer accepts
     * the request unconditionally — including unconditionally with any amount
     * of leeway. The DB `expiry` is pushed 30 seconds into the past; the DB
     * check must still reject.
     *
     * The leeway-only acceptance subscenario (JWT `exp` *within* the 60s window
     * past expiry, so JWT accepts because-of-leeway rather than because-fully-
     * valid) is covered at the isolated tier by
     * `testTokenExpiredWithinLeewayIsAccepted`. Reproducing it here would
     * require minting a JWT with the server's runtime private key under a
     * crafted `exp` — currently out of scope for the api tier.
     *
     * A regression that dropped `authenticateUserToken` entirely (deleted per
     * the TODO comment at BearerTokenAuthorizationStrategy.php line 222) would
     * flip this test to 200. Locking the 401 keeps the dual-path intent
     * intact: whatever the JWT layer accepts, the DB check still has final say.
     */
    #[Test]
    public function testDatabaseExpiryEnforcedEvenWhenJwtClaimIsStillValid(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'DbWinsOverLeeway');
        $tokens = $this->passwordGrant($http, $clientId, $clientSecret, 'openid api:oemr user/patient.read');
        $this->assertIsString($tokens['access_token']);

        QueryUtils::sqlStatementThrowException(
            "UPDATE `api_token` SET `expiry` = ? WHERE `client_id` = ?",
            [date('Y-m-d H:i:s', time() - 30), $clientId]
        );

        $resp = $http->get($this->baseUrl . '/apis/default/api/patient', [
            'headers' => ['Authorization' => 'Bearer ' . $tokens['access_token']],
        ]);
        $this->assertSame(
            401,
            $resp->getStatusCode(),
            'DB expiry 30s in the past must override the JWT exp claim '
                . 'that is still an hour in the future — DB wins over JWT leeway. Body: '
                . (string) $resp->getBody()
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function registerPasswordGrantClient(Client $http, string $nameTag): array
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'TokenLifetimeBoundaryApiTest-' . $nameTag . '-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:oemr offline_access user/patient.read',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        $this->assertIsString($data['client_secret']);
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['password', $data['client_id']]
        );
        $this->registeredClientIds[] = $data['client_id'];
        return [$data['client_id'], $data['client_secret']];
    }

    /**
     * @return array<string, mixed>
     */
    private function passwordGrant(Client $http, string $clientId, string $clientSecret, string $scope): array
    {
        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => $scope,
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Password grant should succeed — body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        /** @var array<string, mixed> $body */
        return $body;
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'timeout' => 15,
        ]);
    }
}
