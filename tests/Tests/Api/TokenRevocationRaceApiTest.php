<?php

/**
 * Regression gates for the "revoke-after-mint" bearer invariant.
 *
 * The bearer validator at
 * src/RestControllers/Authorization/BearerTokenAuthorizationStrategy.php
 * calls isAccessTokenRevokedInDatabase() on every request and throws
 * accessDenied() when the api_token row's revoked flag is set. OpenEMR
 * has no RFC 7009 revoke endpoint; revocation happens via admin UI
 * disabling the client, direct UPDATE on api_token, or the logout
 * endpoint. All three paths share the same per-request DB re-check,
 * so any future refactor that introduced a per-process or per-worker
 * cache of "token X is valid" would silently flip from "revoke takes
 * immediate effect" to "revoke takes effect only after cache expiry".
 *
 * Scope boundary vs BearerTokenRejectionPropertiesTest: that file
 * mints a token and then flips revoked=1 before any API call, which
 * only proves the DB check runs at request time (sufficient to catch a
 * complete removal of the check). This file additionally proves the
 * check runs on EVERY request by making a successful call first and
 * only then flipping the row — catching regressions that would cache
 * the "valid" result from the first lookup — and locks the shape
 * under batched async request dispatch (several concurrent getAsync()
 * promises straddling a revoke UPDATE).
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
use GuzzleHttp\Promise\Utils as PromiseUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class TokenRevocationRaceApiTest extends TestCase
{
    private string $baseUrl;
    /** @var list<string> */
    private array $registeredClientIds = [];
    private mixed $originalPasswordGrantSetting = null;
    // Nullable so tearDown can distinguish "snapshot not taken" (setUp
    // failed/skipped before the globals read) from "snapshot taken,
    // setting was absent before setUp." PHPUnit still invokes
    // tearDown after a setUp failure, so without the null guard the
    // default false would make tearDown remove oauth_password_grant
    // even when the setting existed before the test, bleeding into
    // later tests in the same process.
    private ?bool $originalPasswordGrantSettingWasSet = null;

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
        // Skip password-grant globals restore if setUp did not record a
        // snapshot (e.g. a server-probe failure skipped the test).
        // Still proceed with client cleanup — any oauth_clients rows
        // registered before the setUp exit must still be removed.
        if ($this->originalPasswordGrantSettingWasSet !== null) {
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPasswordGrantSettingWasSet) {
                $globals->set('oauth_password_grant', $this->originalPasswordGrantSetting);
            } else {
                $globals->remove('oauth_password_grant');
                unset($GLOBALS['oauth_password_grant']);
            }
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

    #[Test]
    public function testTokenKeepsWorkingUntilRevokedThenFailsOnNextRequest(): void
    {
        // Sequential three-call shape:
        //   1. GET /fhir/Patient with the bearer   → 200
        //   2. UPDATE api_token SET revoked = 1    (admin revocation)
        //   3. GET /fhir/Patient with the bearer   → 401
        //
        // Step 1 proves the token is live BEFORE revocation (so the
        // step-3 401 is attributable to the revocation, not to a token
        // that was never accepted). Step 3 proves the resource server
        // re-checks revocation on EVERY request rather than caching the
        // step-1 "valid" result.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'SequentialRevoke');
        $tokens = $this->passwordGrant(
            $http,
            $clientId,
            $clientSecret,
            'openid api:fhir user/Patient.read'
        );
        $accessToken = $tokens['access_token'] ?? null;
        $this->assertIsString($accessToken);
        $this->assertNotSame('', $accessToken);

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/fhir+json',
        ];
        $endpoint = $this->baseUrl . '/apis/default/fhir/Patient';

        // Scope note: this test locks the "re-check revocation on
        // every request" invariant by exercising a happens-before
        // sequence (succeed → revoke → fail) against the server's
        // actual DB-backed check in
        // BearerTokenAuthorizationStrategy::authorizeRequest →
        // AccessTokenRepository::isAccessTokenRevokedInDatabase.
        //
        // It does NOT detect a worker-local token cache. Apache's MPM
        // (or PHP-FPM in other deployments) can distribute sequential
        // requests across worker processes; Guzzle keep-alive reuses
        // one TCP connection but the dispatcher may still route the
        // next request through a different child. If a per-worker
        // cache were introduced, catching it reliably would need
        // either a single-worker test environment or a cache-probe
        // endpoint. The current test is a regression gate against
        // the fresh-DB-read invariant, not a per-process cache
        // detector.
        $before = $http->get($endpoint, ['headers' => $headers]);
        $this->assertSame(
            200,
            $before->getStatusCode(),
            'Freshly minted token must succeed before revocation. '
                . 'Body: ' . (string) $before->getBody()
        );

        // Admin revocation. Three code paths funnel into this UPDATE:
        //   - AccessTokenRepository::revokeAccessToken() (logout, password change)
        //   - Direct admin DELETE/UPDATE in a maintenance script
        //   - MFA re-enroll flows (see PasswordGrantMfaRefreshFlowTest)
        QueryUtils::sqlStatementThrowException(
            'UPDATE `api_token` SET `revoked` = 1 WHERE `client_id` = ?',
            [$clientId]
        );

        $after = $http->get($endpoint, ['headers' => $headers]);
        $this->assertSame(
            401,
            $after->getStatusCode(),
            'A revoked token must be rejected on the very next request. '
                . 'If this returns 200 the resource server has started '
                . 'caching the pre-revocation validation result. '
                . 'Body: ' . (string) $after->getBody()
        );
    }

    #[Test]
    public function testBatchedConcurrentRequestsAfterRevokeAllRejected(): void
    {
        // Weaker-than-true-concurrency shape — Guzzle's getAsync returns a
        // promise but does not dispatch the underlying HTTP request until
        // Utils::unwrap() starts the curl-multi loop. So when the test
        // interleaves four getAsync() calls, a QueryUtils UPDATE, and a
        // fifth getAsync(), ALL five promises start their actual HTTP
        // request AFTER the UPDATE has already committed. This is not a
        // true in-flight race (that would require a long-running endpoint
        // the test could reliably still-in-flight hit — OpenEMR has no
        // such endpoint on the critical path; the Bulk FHIR status poll
        // is the closest and still finishes in sub-second time locally).
        //
        // The test is useful anyway: it locks that batching multiple
        // async requests through the resource server after a revoke
        // UPDATE yields a uniform 401 shape with no 5xx, no 403, and no
        // "sometimes 200". A future refactor that introduced a per-worker
        // or per-process token cache would break this: the first request
        // through a given worker could still return 200 even after the
        // UPDATE committed.
        //
        // A true in-flight race test (revoke commits WHILE a request is
        // mid-handler) is deferred — would need either (a) a reliably
        // slow endpoint under test control or (b) a Panther/Selenium
        // driver to time the UPDATE against observed response bytes.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'BatchedRevoke');
        $tokens = $this->passwordGrant(
            $http,
            $clientId,
            $clientSecret,
            'openid api:fhir user/Patient.read'
        );
        $accessToken = $tokens['access_token'] ?? null;
        $this->assertIsString($accessToken);

        $endpoint = $this->baseUrl . '/apis/default/fhir/Patient';
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/fhir+json',
        ];

        // Prove the token is live before the revoke so a 401 downstream
        // is attributable to the UPDATE, not to a token that was never
        // accepted.
        $warmup = $http->get($endpoint, ['headers' => $headers]);
        $this->assertSame(
            200,
            $warmup->getStatusCode(),
            'Warm-up request should succeed before revoke. '
                . 'Body: ' . (string) $warmup->getBody()
        );

        $promises = [];
        for ($i = 0; $i < 4; $i++) {
            $promises['pre_' . $i] = $http->getAsync($endpoint, ['headers' => $headers]);
        }
        QueryUtils::sqlStatementThrowException(
            'UPDATE `api_token` SET `revoked` = 1 WHERE `client_id` = ?',
            [$clientId]
        );
        $promises['post'] = $http->getAsync($endpoint, ['headers' => $headers]);

        /** @var array<string, ResponseInterface> $responses */
        $responses = PromiseUtils::unwrap($promises);
        $statuses = [];
        foreach ($responses as $key => $response) {
            $statuses[$key] = $response->getStatusCode();
        }
        foreach ($statuses as $key => $status) {
            $this->assertSame(
                401,
                $status,
                "Batched request $key must return 401 after the revoke "
                    . 'UPDATE committed. Observed statuses: '
                    . json_encode($statuses)
            );
        }
    }

    /**
     * @return array{string, string} [client_id, client_secret]
     */
    private function registerPasswordGrantClient(Client $http, string $nameTag): array
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'TokenRevocationRaceApiTest-' . $nameTag . '-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:fhir user/Patient.read',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed. Body: ' . (string) $reg->getBody());
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        // Track the client row for tearDown cleanup BEFORE the
        // remaining fallible operations (client_secret assertion +
        // grant_types UPDATE). If either fails after DCR succeeded,
        // teardown still needs to remove the row.
        $this->registeredClientIds[] = $data['client_id'];
        $this->assertIsString($data['client_secret']);
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['password', $data['client_id']]
        );
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
