<?php

/**
 * Audit log integrity regression gates for OAuth2 flows.
 *
 * OpenEMR writes audit entries to the `log` table (plain columns:
 * event / category / user / success) and, for API traffic, a joined
 * row in `api_log` carrying client_id + user_id + method + request_url.
 * This file locks the entries that OAuth2 flows produce today so a
 * future refactor cannot silently drop the trail.
 *
 * Observed behavior that these tests lock in:
 *
 *   - A successful API call under an OAuth2 bearer produces TWO `log`
 *     rows with event='api': one written by
 *     BearerTokenAuthorizationStrategy at auth-success time (user is
 *     blank, success=1, comments carry the "Token successfully used
 *     for client[...] and user ..." sentence) and one written by
 *     ApiResponseLoggerListener at request-terminate time (user is
 *     the resolved authUser, success=1, generic "api log" comment),
 *     plus an api_log row joined to the second entry with client_id
 *     + user_id + method + request + request_url populated.
 *
 *   - A failed password-grant mint (wrong password) produces a
 *     `log` row with event='api', success=0, user=<attempted-username>,
 *     and a comments payload identifying the failure reason.
 *
 * Non-coverage notes (gaps, not security findings):
 *
 *   - The `/oauth2/{site}/token` POST itself is logged by the generic
 *     api-response listener, but client_id is written blank on the
 *     api_log row for that endpoint — the request is pre-auth and
 *     the resolver has not yet attached a client. The "token issued"
 *     signal is instead carried by the "Token successfully used ..."
 *     entry at first-API-call time. These tests lock the current
 *     shape; if a dedicated token-mint audit entry is ever added
 *     (naming the client at mint time, not at first use), update the
 *     expected-row assertions accordingly.
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
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use OpenEMR\Tests\Support\OAuth2\PasswordGrant;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AuditLogIntegrityApiTest extends TestCase
{
    private const SCOPE = 'openid api:oemr user/patient.read';
    private const PROBE_URL_PATH = '/apis/default/api/version';

    private string $baseUrl;
    private ?string $clientId = null;
    private ?string $clientSecret = null;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }
        if (stripos($probe->getHeaderLine('Server'), 'nginx') !== false) {
            $this->markTestSkipped(
                'API routes under /apis/* not reachable via the nginx test config — '
                . 'apache lane covers these invariants.'
            );
        }

        try {
            [$cid, $csec] = DcrClient::registerConfidential(
                $this->baseUrl,
                'AuditLogIntegrityApiTest-' . bin2hex(random_bytes(3)),
                self::SCOPE,
                'password',
            );
            $this->clientId = $cid;
            $this->clientSecret = $csec;
        } catch (\Throwable $setUpFailure) {
            try {
                $this->tearDown();
            } catch (\Throwable $rollbackFailure) {
                throw new \RuntimeException(
                    'setUp failed; rollback via tearDown also failed: ' . $rollbackFailure->getMessage(),
                    0,
                    $setUpFailure,
                );
            }
            throw $setUpFailure;
        }
    }

    protected function tearDown(): void
    {
        if ($this->clientId !== null) {
            // Order matters: delete the dependent rows before the
            // parent oauth_clients row.
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_token` WHERE `client_id` = ?',
                [$this->clientId],
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_refresh_token` WHERE `client_id` = ?',
                [$this->clientId],
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                [$this->clientId],
            );
            // Also delete api_log rows produced by this test's traffic.
            // Scoping by client_id leaves other suites' audit entries
            // untouched while keeping this suite idempotent.
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_log` WHERE `client_id` = ?',
                [$this->clientId],
            );
            DcrClient::remove($this->clientId);
        }
    }

    #[Test]
    public function testApiCallUnderOAuth2BearerWritesApiLogRowCarryingClientIdAndUserId(): void
    {
        self::assertIsString($this->clientId);
        self::assertIsString($this->clientSecret);

        // Mint a token and make one authenticated API call. The
        // api_log table (joined from the `log` entry written by
        // ApiResponseLoggerListener at request-terminate) is the
        // canonical audit record for API traffic: client_id,
        // user_id, method, and request_url all live there in plain
        // text. Lock that the row is written and carries all four.
        $mint = PasswordGrant::mintUserRoleToken(
            $this->baseUrl,
            $this->clientId,
            $this->clientSecret,
            self::SCOPE,
        );
        $accessToken = $mint['access_token'];

        $callResp = $this->buildClient()->get($this->baseUrl . self::PROBE_URL_PATH, [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
        ]);
        self::assertSame(
            200,
            $callResp->getStatusCode(),
            'Precondition failed: API probe call must return 200 to produce a success audit row',
        );

        // ApiResponseLoggerListener writes the api_log row on
        // kernel.terminate, AFTER the HTTP response flushes to the
        // client. That means the Guzzle response arrives before the
        // row commits — a direct SELECT right after $callResp can
        // miss it. Bounded retry polling (20 × 50ms = 1s max) waits
        // for the terminate-time write to settle before asserting.
        $apiLogRow = [];
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $apiLogRow = QueryUtils::fetchRecords(
                'SELECT user_id, client_id, method, request, request_url '
                . 'FROM api_log WHERE client_id = ? ORDER BY id DESC LIMIT 1',
                [$this->clientId],
            );
            if (count($apiLogRow) === 1) {
                break;
            }
            usleep(50_000);
        }
        self::assertCount(
            1,
            $apiLogRow,
            'An api_log row must be written for an authenticated API call under an OAuth2 bearer',
        );
        $row = $apiLogRow[0];
        // QueryUtils::fetchRecords returns array<string, mixed>; narrow
        // each column to its real DB type so the assertions below can
        // bind to non-mixed values. user_id is a bigint column, so it
        // comes back as int; the others are VARCHAR/TEXT and come
        // back as string.
        self::assertIsString($row['client_id']);
        self::assertIsInt($row['user_id']);
        self::assertIsString($row['method']);
        self::assertIsString($row['request']);
        self::assertIsString($row['request_url']);

        self::assertSame(
            $this->clientId,
            $row['client_id'],
            'api_log.client_id must carry the OAuth2 client whose bearer was presented',
        );
        // Bind the user_id to the resolved admin id specifically, not
        // just "any positive integer." A regression that wrote a
        // different user's id to api_log.user_id (e.g. the first row
        // of users, or a service account) would still have passed the
        // previous assertGreaterThan(0, ...) check.
        $adminIdRaw = QueryUtils::fetchSingleValue(
            "SELECT id FROM users WHERE username = 'admin' AND active = 1",
            'id',
            [],
        );
        self::assertIsNumeric($adminIdRaw, 'Seeded active admin user must exist');
        $adminId = (int) $adminIdRaw;
        self::assertGreaterThan(0, $adminId);
        self::assertSame(
            $adminId,
            $row['user_id'],
            'api_log.user_id must equal the seeded admin id (not just a positive integer)',
        );
        self::assertSame(
            'GET',
            $row['method'],
            'api_log.method must carry the HTTP method of the audited call',
        );
        self::assertStringContainsString(
            'version',
            $row['request'],
            'api_log.request must identify the resource that was called',
        );
        self::assertStringContainsString(
            '/api/version',
            $row['request_url'],
            'api_log.request_url must carry the full dispatched URL',
        );
    }

    #[Test]
    public function testBearerTokenAuthorizationWritesTokenSuccessAuditEntry(): void
    {
        self::assertIsString($this->clientId);
        self::assertIsString($this->clientSecret);

        // BearerTokenAuthorizationStrategy writes a success entry
        // every time a bearer passes validation on an API call. The
        // comments payload names the client_id and user_id, so a
        // forensic reader can reconstruct "which client, which user,
        // against which endpoint" from this row + the paired api_log
        // row written by ApiResponseLoggerListener. This test locks
        // that the entry fires at all; the exact comments shape is
        // intentionally not asserted because `log.comments` is
        // populated by the EventAuditLogger pipeline (which base64
        // encodes the payload before insert via log_comment_encrypt
        // -- the table name is misleading; `encrypt` is a per-row
        // enum flag currently set to 'No' for these events, and the
        // paired columns are sha3-512 tamper-detection checksums,
        // not ciphertext).
        //
        // Mint FIRST, then capture the baseline, so the mint's own
        // kernel.terminate audit rows do not count toward the probe
        // call's expected 2 success rows. The mint writes its own
        // ApiResponseLoggerListener row post-flush; a 100ms wait
        // lets that row commit before we snapshot the max id.
        $mint = PasswordGrant::mintUserRoleToken(
            $this->baseUrl,
            $this->clientId,
            $this->clientSecret,
            self::SCOPE,
        );
        $accessToken = $mint['access_token'];
        usleep(100_000); // 100ms: wait for mint's terminate-time audit row

        $maxLogIdBefore = $this->fetchIntScalar(
            'SELECT COALESCE(MAX(id), 0) AS max_id FROM `log`',
            'max_id',
            [],
        );

        $callResp = $this->buildClient()->get($this->baseUrl . self::PROBE_URL_PATH, [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
        ]);
        self::assertSame(
            200,
            $callResp->getStatusCode(),
            'Precondition failed: API probe call must return 200',
        );

        // Two event='api' rows SHOULD land for THIS probe call:
        //   row A  — BearerTokenAuthorizationStrategy "Token
        //            successfully used ..." (user blank, success=1).
        //            No api_log join because this row is written
        //            BEFORE the dispatcher runs — api_log is written
        //            by the response listener on kernel.terminate.
        //   row B  — ApiResponseLoggerListener (user=admin,
        //            success=1, joined api_log row with matching
        //            client_id + request_url).
        //
        // Rabbit flagged that an unrelated parallel-test API call
        // could inflate the aggregate count without this probe's
        // specific rows being present. Bind each assertion separately:
        //   - Row B via JOIN on api_log, matching client_id + request_url
        //     (api_log carries both as plaintext columns).
        //   - Row A by requiring that a bearer-validation row exists
        //     with an id LESS THAN the listener row's id (same request
        //     lifecycle: bearer check runs first, listener writes last)
        //     AND no api_log join (listener rows always join; bearer
        //     rows never do).
        // The response listener records request_url as the dispatch
        // internal path (e.g. "/apis/dispatch.php/default/api/version")
        // rather than the client-facing path. Match on the trailing
        // "/api/version" suffix — unique enough to bind to this probe.
        $probePathSuffix = '/api/version';

        // Row B first — poll for the listener row to settle.
        $listenerRowId = 0;
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $listenerRowId = $this->fetchIntScalar(
                'SELECT COALESCE(MAX(l.id), 0) AS lid FROM `log` l '
                . 'JOIN `api_log` a ON a.log_id = l.id '
                . 'WHERE l.id > ? AND l.event = ? AND l.success = ? '
                . 'AND a.client_id = ? AND a.request_url LIKE ?',
                'lid',
                [$maxLogIdBefore, 'api', 1, $this->clientId, '%' . $probePathSuffix . '%'],
            );
            if ($listenerRowId > 0) {
                break;
            }
            usleep(50_000);
        }
        self::assertGreaterThan(
            0,
            $listenerRowId,
            'ApiResponseLoggerListener must write a success row joined to an '
            . 'api_log row carrying this probe\'s client_id + request_url. '
            . "No matching row found for client $this->clientId at $probePathSuffix.",
        );

        // Row A — bearer-validation row. In the same request lifecycle
        // as Row B, so its log.id is strictly less than $listenerRowId
        // and greater than $maxLogIdBefore. Distinguished from the
        // listener row by the absence of a joined api_log row.
        $bearerRows = $this->fetchIntScalar(
            'SELECT COUNT(*) AS c FROM `log` l '
            . 'LEFT JOIN `api_log` a ON a.log_id = l.id '
            . 'WHERE l.id > ? AND l.id < ? '
            . 'AND l.event = ? AND l.success = ? '
            . 'AND a.log_id IS NULL',
            'c',
            [$maxLogIdBefore, $listenerRowId, 'api', 1],
        );
        self::assertGreaterThanOrEqual(
            1,
            $bearerRows,
            'BearerTokenAuthorizationStrategy must write a success row '
            . 'in the same request lifecycle as the listener row '
            . "(id between $maxLogIdBefore and $listenerRowId, no api_log join). "
            . "Got $bearerRows rows.",
        );
    }

    #[Test]
    public function testFailedPasswordGrantWritesFailureAuditEntry(): void
    {
        self::assertIsString($this->clientId);
        self::assertIsString($this->clientSecret);

        // Capture the attempted username so the assertion can bind to
        // this test's exact attempt, not a background one. Using
        // a per-test unique string proves the audit row carries the
        // actual attempted principal, not a sanitized placeholder.
        $attemptedUser = 'audit-nouser-' . bin2hex(random_bytes(4));

        $maxLogIdBefore = $this->fetchIntScalar(
            'SELECT COALESCE(MAX(id), 0) AS max_id FROM `log`',
            'max_id',
            [],
        );

        $tokenResp = $this->buildClient()->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => self::SCOPE,
                'user_role' => 'users',
                'username' => $attemptedUser,
                'password' => 'definitely-not-the-password',
            ],
        ]);
        self::assertSame(
            400,
            $tokenResp->getStatusCode(),
            'Precondition failed: password grant must reject an unknown user',
        );

        // AuthUtils::confirmUserPassword branches on $this->apiAuth
        // and writes event='api', success=0, user=<attempted-username>.
        // Locking all three columns proves the audit row carries both
        // the "it failed" bit AND the attempted principal — a refactor
        // that logs the failure but drops the username (anonymization
        // regression) would still trip this test.
        $failureRows = QueryUtils::fetchRecords(
            'SELECT event, success, user FROM `log` '
            . 'WHERE id > ? AND event = ? AND success = ? AND user = ? '
            . 'ORDER BY id DESC LIMIT 1',
            [$maxLogIdBefore, 'api', 0, $attemptedUser],
        );
        self::assertCount(
            1,
            $failureRows,
            'A failed password-grant mint must write an event=api,success=0 row carrying the attempted username',
        );
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }

    /**
     * Fetch a single numeric scalar from a one-column aggregate query
     * and narrow it to int. QueryUtils::fetchSingleValue returns mixed;
     * every scalar this test file reads is a numeric aggregate from
     * the `log` table and MUST be an int for the assertions below to
     * mean anything. Fail loudly here (not silently via a cast) if the
     * underlying query ever starts returning something else.
     *
     * @param list<mixed> $binds
     */
    private function fetchIntScalar(string $sql, string $column, array $binds): int
    {
        $value = QueryUtils::fetchSingleValue($sql, $column, $binds);
        self::assertIsNumeric($value);
        return (int) $value;
    }
}
