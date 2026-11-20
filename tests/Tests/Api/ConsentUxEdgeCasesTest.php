<?php

/**
 * Consent-page UX edge-case regression gates.
 *
 * Companion to ConsentScopeNarrowingTest (the "unchecked scopes drop
 * from the token" invariant) and ConsentPageRenderTest (the rendered
 * HTML shape). This file locks the *submit-time* edge-case behaviors
 * of the SMART consent form:
 *
 *   - a consent-form POST reused twice in a row (session invalidated
 *     after the first submit, second must not reissue a code)
 *   - a consent POST with zero scope checkboxes checked
 *   - a consent POST carrying both `proceed=1` AND an explicit
 *     `deny=1` (the template does not emit a deny control, so the
 *     controller treats proceed as the only trigger — this test locks
 *     that behavior rather than the paper-spec deny-wins behavior)
 *   - a consent POST that carries scope keys the client did not ask
 *     for at /authorize (unknown keys must be ignored, requested
 *     keys honored)
 *   - a consent POST that carries scope keys with leading/trailing
 *     whitespace (whitespace must not accidentally match a requested
 *     scope by trim-coercion)
 *
 * These cases do not invent new product requirements — they encode
 * the behavior the controller ships with today, so a future refactor
 * that silently changes the surface regresses here.
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
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Support\OAuth2\AuthCodeFlow;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConsentUxEdgeCasesTest extends TestCase
{
    private string $baseUrl;

    private bool $passwordGrantBagCaptured = false;
    private mixed $originalPasswordGrantBagSetting = null;
    private bool $originalPasswordGrantBagWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

    private bool $siteAddrOathCaptured = false;
    private ?string $originalSiteAddrOathGlobalRow = null;
    private bool $siteAddrOathGlobalRowWasInserted = false;

    /** @var list<string> client_ids registered during tests for cleanup */
    private array $clientsToRemove = [];
    /** @var list<string> */
    private array $tearDownFailures = [];

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
            $this->markTestSkipped('Auth-code flow test skipped on nginx config');
        }

        try {
            $globals = OEGlobalsBag::getInstance();
            $this->originalPasswordGrantBagWasSet = $globals->has('oauth_password_grant');
            $this->originalPasswordGrantBagSetting = $globals->get('oauth_password_grant');
            $this->passwordGrantBagCaptured = true;
            $globals->set('oauth_password_grant', 3);
            $this->persistGlobal(
                'oauth_password_grant',
                '3',
                $this->originalPasswordGrantGlobalRow,
                $this->passwordGrantGlobalRowWasInserted
            );

            // Point site_addr_oath at the container-reachable baseUrl
            // so the auth-code flow's server-emitted redirects resolve.
            $this->siteAddrOathCaptured = true;
            $this->persistGlobal(
                'site_addr_oath',
                $this->baseUrl,
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );
        } catch (\Throwable $setUpFailure) {
            try {
                $this->tearDown();
            } catch (\Throwable $rollbackFailure) {
                throw new \RuntimeException(
                    'setUp failed; rollback via tearDown also failed: ' . $rollbackFailure->getMessage(),
                    0,
                    $setUpFailure
                );
            }
            throw $setUpFailure;
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->clientsToRemove as $cid) {
            $this->safeRun(function () use ($cid): void {
                DcrClient::remove($cid);
            });
        }
        $this->safeRun(function (): void {
            if (!$this->siteAddrOathCaptured) {
                return;
            }
            $this->restoreGlobal(
                'site_addr_oath',
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );
        });
        $this->safeRun(function (): void {
            $this->restoreGlobal(
                'oauth_password_grant',
                $this->originalPasswordGrantGlobalRow,
                $this->passwordGrantGlobalRowWasInserted
            );
        });
        $this->safeRun(function (): void {
            if (!$this->passwordGrantBagCaptured) {
                return;
            }
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPasswordGrantBagWasSet) {
                $globals->set('oauth_password_grant', $this->originalPasswordGrantBagSetting);
            } else {
                $globals->remove('oauth_password_grant');
                unset($GLOBALS['oauth_password_grant']);
            }
        });

        if ($this->tearDownFailures !== []) {
            $failures = $this->tearDownFailures;
            $this->tearDownFailures = [];
            self::fail(
                "tearDown cleanup had " . count($failures) . " failure(s); "
                . "shared state may be stale for later tests:\n  - "
                . implode("\n  - ", $failures)
            );
        }
    }

    /**
     * Replay the same consent POST body twice on the same session.
     *
     * The first POST issues an auth code and invalidates the OAuth
     * session (AuthorizationController::authorizeUser calls
     * $this->session->invalidate() after a successful completion).
     * The second POST therefore cannot restore the authorization
     * request from the session — it must NOT re-emit a code to the
     * registered redirect_uri, because that would be a free second
     * grant on a stale CSRF token.
     */
    #[Test]
    public function testConsentFormDoubleSubmitRejectsSecondPost(): void
    {
        $scope = 'openid user/Patient.read';
        $prep = AuthCodeFlow::renderConsentPageForUserRole(
            $this->baseUrl,
            'ConsentUxEdgeCases-double-' . bin2hex(random_bytes(3)),
            $scope,
        );
        $this->clientsToRemove[] = $prep['client_id'];

        $form = [
            'csrf_token_form' => $prep['consent_csrf'],
            'proceed' => '1',
            'scope' => ['openid' => 'openid', 'user/Patient.read' => 'user/Patient.read'],
        ];
        $actionUrl = $this->resolveActionUrl($this->baseUrl, $prep['consent_action']);

        $first = $prep['http']->post($actionUrl, [
            'form_params' => $form,
            'allow_redirects' => false,
        ]);
        self::assertSame(
            302,
            $first->getStatusCode(),
            'First consent POST must 302 to redirect_uri. Body: ' . (string) $first->getBody()
        );
        self::assertStringStartsWith(
            AuthCodeFlow::REDIRECT_URI,
            $first->getHeaderLine('Location'),
            'First consent POST must redirect to the registered callback'
        );

        // Replay the identical body on the same cookie jar. Session
        // was invalidated by the first POST, so the controller cannot
        // deserialize the auth request from session storage and must
        // not reissue a code.
        $second = $prep['http']->post($actionUrl, [
            'form_params' => $form,
            'allow_redirects' => false,
        ]);
        $secondLocation = $second->getHeaderLine('Location');
        $secondRedirectsToCallbackWithCode = $second->getStatusCode() === 302
            && str_starts_with($secondLocation, AuthCodeFlow::REDIRECT_URI)
            && str_contains($secondLocation, 'code=');
        self::assertFalse(
            $secondRedirectsToCallbackWithCode,
            'Replayed consent POST must NOT reissue a code to the registered '
                . 'redirect_uri on an invalidated session. Status: '
                . $second->getStatusCode() . ', Location: ' . $secondLocation
        );
    }

    /**
     * Submit the consent form with no scope checkboxes checked at all
     * (no `scope[...]` keys, no `grant[...]` keys). The current server
     * behavior is to accept the submission, issue a code, and exchange
     * it for a token whose scope string is empty — i.e. the grant is
     * minimized to only the implicit OIDC artifacts the server surfaces
     * (nonce) with no resource scopes. Locking this because a future
     * refactor that interpreted "no checkboxes" as "grant everything
     * the client registered" would be a silent widening.
     */
    #[Test]
    public function testEmptyConsentGrantIssuesMinimalScopeToken(): void
    {
        $scope = 'openid user/Patient.read user/Encounter.read';
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'ConsentUxEdgeCases-empty-' . bin2hex(random_bytes(3)),
            $scope,
            [], // empty consent grant override: no scope checkboxes checked
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $granted = $this->filterNonSemanticArtifacts($mint['scopes_granted']);

        self::assertSame(
            [],
            $granted,
            'Empty consent grant must NOT silently widen to the requested set. '
                . 'Granted (after artifact filter): ' . json_encode($granted)
        );
    }

    /**
     * Submit the consent form with both `proceed=1` AND an explicit
     * `deny=1` key. The scope-authorize template ships only a proceed
     * button and no deny control, so the controller treats proceed
     * presence as the only trigger — a stray deny field alongside
     * proceed is ignored, the flow completes normally.
     *
     * This test LOCKS the current proceed-wins behavior. A real
     * server-side deny branch is a product gap (there is no templated
     * deny control to exercise it) — if a future PR introduces one,
     * this test should be updated to assert the new behavior rather
     * than silently flipping.
     */
    #[Test]
    public function testProceedWinsWhenDenyFieldAlsoPresent(): void
    {
        $scope = 'openid user/Patient.read';
        $prep = AuthCodeFlow::renderConsentPageForUserRole(
            $this->baseUrl,
            'ConsentUxEdgeCases-deny-' . bin2hex(random_bytes(3)),
            $scope,
        );
        $this->clientsToRemove[] = $prep['client_id'];

        $resp = $prep['http']->post(
            $this->resolveActionUrl($this->baseUrl, $prep['consent_action']),
            [
                'form_params' => [
                    'csrf_token_form' => $prep['consent_csrf'],
                    'proceed' => '1',
                    'deny' => '1', // dual-field submission — proceed wins today
                    'scope' => [
                        'openid' => 'openid',
                        'user/Patient.read' => 'user/Patient.read',
                    ],
                ],
                'allow_redirects' => false,
            ]
        );

        self::assertSame(
            302,
            $resp->getStatusCode(),
            'proceed=1 with a stray deny=1 should still complete the flow '
                . 'under the current server behavior (no server-side deny branch). '
                . 'Body: ' . (string) $resp->getBody()
        );
        $location = $resp->getHeaderLine('Location');
        self::assertStringStartsWith(
            AuthCodeFlow::REDIRECT_URI,
            $location,
            'Must redirect to the registered callback'
        );
        self::assertStringContainsString(
            'code=',
            $location,
            'Callback URL must carry an authorization code'
        );
    }

    /**
     * Submit the consent form with a scope key that was NOT in the
     * /authorize request. ScopeConsentResolver walks the requested set
     * and only emits a scope if it was both requested and left checked
     * — unknown keys must be silently dropped. Known keys that WERE
     * requested are honored normally.
     */
    #[Test]
    public function testUnknownConsentScopeKeyIsDropped(): void
    {
        // Only openid + user/Patient.read are REQUESTED. The grant
        // override pretends the submitted form carried a sneaky extra
        // user/Encounter.read checkbox value that was never offered.
        // That key must not surface in the issued token.
        $scope = 'openid user/Patient.read';
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'ConsentUxEdgeCases-unknown-' . bin2hex(random_bytes(3)),
            $scope,
            [
                'openid',
                'user/Patient.read',
                'user/Encounter.read',    // not requested
                'user/Observation.read',  // not requested
            ],
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $granted = $this->filterNonSemanticArtifacts($mint['scopes_granted']);

        self::assertContains('openid', $granted);
        self::assertContains('user/Patient.read', $granted);
        self::assertNotContains(
            'user/Encounter.read',
            $granted,
            'A submitted-but-not-requested scope must not appear in the token. '
                . 'Granted: ' . json_encode($granted)
        );
        self::assertNotContains(
            'user/Observation.read',
            $granted,
            'A submitted-but-not-requested scope must not appear in the token. '
                . 'Granted: ' . json_encode($granted)
        );
    }

    /**
     * Submit a known scope key with leading/trailing whitespace. The
     * resolver's `in_array($requested, $plainApproved, true)` check is
     * strict, so " openid " and "openid" do not match — whitespace
     * variants are dropped silently and the trimmed companion is NOT
     * auto-added. This test locks that behavior so a future "be
     * generous, trim incoming values" refactor regresses here.
     */
    #[Test]
    public function testWhitespacePaddedConsentScopeKeyIsDropped(): void
    {
        $scope = 'openid user/Patient.read user/Encounter.read';
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'ConsentUxEdgeCases-whitespace-' . bin2hex(random_bytes(3)),
            $scope,
            [
                'openid',
                ' user/Patient.read',   // leading space
                'user/Encounter.read ', // trailing space
            ],
        );
        $this->clientsToRemove[] = $mint['client_id'];
        $granted = $this->filterNonSemanticArtifacts($mint['scopes_granted']);

        self::assertContains(
            'openid',
            $granted,
            'The exact-match "openid" entry must still be honored'
        );
        self::assertNotContains(
            'user/Patient.read',
            $granted,
            'Leading-whitespace variant must NOT auto-trim into the trimmed scope. '
                . 'Granted: ' . json_encode($granted)
        );
        self::assertNotContains(
            'user/Encounter.read',
            $granted,
            'Trailing-whitespace variant must NOT auto-trim into the trimmed scope. '
                . 'Granted: ' . json_encode($granted)
        );
    }

    /**
     * Filter OIDC-related artifacts the server surfaces into the scope
     * claim (e.g. nonce) so the assertion compares against the
     * resource-scope semantic set only.
     *
     * @param list<string> $scopes
     * @return list<string>
     */
    private function filterNonSemanticArtifacts(array $scopes): array
    {
        return array_values(array_filter($scopes, static fn (string $s): bool => $s !== 'nonce'));
    }

    private function resolveActionUrl(string $baseUrl, string $action): string
    {
        if (str_starts_with($action, 'http://') || str_starts_with($action, 'https://')) {
            return $action;
        }
        if (str_starts_with($action, '/')) {
            return $baseUrl . $action;
        }
        return $baseUrl . '/' . $action;
    }

    private function persistGlobal(
        string $name,
        string $value,
        ?string &$originalCapture,
        bool &$insertedCapture
    ): void {
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            [$name]
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $originalCapture = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$value, $name]
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                [$name, $value]
            );
            $insertedCapture = true;
        }
    }

    private function restoreGlobal(string $name, ?string $originalRow, bool $wasInserted): void
    {
        if ($wasInserted) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `globals` WHERE gl_name = ?',
                [$name]
            );
        } elseif ($originalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$originalRow, $name]
            );
        }
    }

    private function safeRun(callable $cleanup): void
    {
        try {
            $cleanup();
        } catch (SqlQueryException $e) {
            $this->tearDownFailures[] = $e->getMessage();
            fwrite(STDERR, '[' . static::class . "::tearDown] cleanup step failed: " . $e->getMessage() . "\n");
        }
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
}
