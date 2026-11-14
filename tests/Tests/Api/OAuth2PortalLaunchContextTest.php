<?php

/**
 * Api-tier regression gates for SMARTSessionTokenContextBuilder::assertLaunchMatchesLogin()
 * (shipped in PR #15272).
 *
 * The builder refuses with invalid_launch_context at /token exchange when:
 *   - a patient-login session's puuid differs from the launch token's patient;
 *   - a patient.portal-intent launch is presented by a non-patient login.
 *
 * The unit-tier SMARTSessionTokenContextBuilderTest covers the gate directly;
 * this file locks the end-to-end wiring — /authorize + login + consent all
 * succeed and issue a code, then /token rejects with the correct error
 * code + message. That ordering is the reject's actual shape: the gate
 * fires at IdTokenSMARTResponse build time when the League grant is
 * already past the redirect_uri / PKCE / scope checks and building the
 * final token payload.
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
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class OAuth2PortalLaunchContextTest extends TestCase
{
    private const REDIRECT_URI = 'https://client.example/cb';
    private const PATIENT_SCOPE = 'openid launch patient/Patient.read';
    private const STAFF_SCOPE = 'openid launch user/Patient.read';

    private string $baseUrl = '';

    /** @var array<string, mixed> */
    private array $fixtureA = [];
    private string $uuidA = '';
    /** @var array<string, mixed> */
    private array $fixtureB = [];

    private string $patientClientId = '';
    private string $staffClientId = '';
    private string $staffClientSecret = '';

    private ?PortalPatientFixtureManager $portalFixtures = null;

    private bool $passwordGrantBagCaptured = false;
    private mixed $originalPasswordGrantBagSetting = null;
    private bool $originalPasswordGrantBagWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

    private bool $siteAddrOathCaptured = false;
    private ?string $originalSiteAddrOathGlobalRow = null;
    private bool $siteAddrOathGlobalRowWasInserted = false;

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

        try {
            // Only touch globals if they differ from the desired
            // value. On the dev-easy stack `oauth_password_grant=3`,
            // `rest_fhir_api=1`, `rest_portal_api=1`, and
            // `site_addr_oath=https://localhost` are already set;
            // touching them per-test invites a per-worker cache
            // inconsistency across apache's mpm_prefork pool (globals
            // writes land in the DB, but a worker that was warmed
            // before the write still serves its cached copy).
            $globals = OEGlobalsBag::getInstance();
            if ($globals->get('oauth_password_grant') !== 3) {
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
            }

            $current = QueryUtils::querySingleRow(
                'SELECT gl_value FROM `globals` WHERE gl_name = ?',
                ['site_addr_oath']
            );
            $existingSiteAddrOath = is_array($current) && is_string($current['gl_value'] ?? null)
                ? (string) $current['gl_value']
                : null;
            if ($existingSiteAddrOath !== $this->baseUrl) {
                $this->siteAddrOathCaptured = true;
                $this->persistGlobal(
                    'site_addr_oath',
                    $this->baseUrl,
                    $this->originalSiteAddrOathGlobalRow,
                    $this->siteAddrOathGlobalRowWasInserted
                );
            }

            $portalFixtures = new PortalPatientFixtureManager();
            $this->portalFixtures = $portalFixtures;
            // Defensive sweep: a prior failed test run can orphan a
            // patient_access_onsite row whose matching patient_data row
            // was deleted but whose portal_username still points at a
            // now-free pid. The fixture manager's own cleanup joins on
            // patient_data.pid, so those orphans survive it. Clear by
            // portal_username so the fresh install never hits a
            // duplicate-pid insert.
            $this->sweepLingeringPortalArtifacts();
            $this->fixtureA = $portalFixtures->installPortalPatient(
                portalLoginUsername: 'portal-launchctx-A-' . bin2hex(random_bytes(4)),
                plainPassword: 'PortalLaunchContextA1!',
                email: 'portal-launchctx-A-' . bin2hex(random_bytes(4)) . '@test.example'
            );
            $this->uuidA = $this->fetchPatientUuid((int) $this->fixtureA['pid']);
            $this->fixtureB = $portalFixtures->installPortalPatient(
                portalLoginUsername: 'portal-launchctx-B-' . bin2hex(random_bytes(4)),
                plainPassword: 'PortalLaunchContextB1!',
                email: 'portal-launchctx-B-' . bin2hex(random_bytes(4)) . '@test.example'
            );

            // Patient-scoped DCR produces a public (PKCE) client —
            // DcrClient::registerPublic sets application_type=web and
            // no client_secret is issued. The auth-code grant via
            // /oauth2/default/authorize + /oauth2/default/token still
            // works as long as code_verifier is presented at /token.
            $this->patientClientId = DcrClient::registerPublic(
                $this->baseUrl,
                'OAuth2PortalLaunchContextTest-pt-' . bin2hex(random_bytes(3)),
                self::PATIENT_SCOPE,
                'authorization_code',
            );

            [$sid, $ssec] = DcrClient::registerConfidential(
                $this->baseUrl,
                'OAuth2PortalLaunchContextTest-staff-' . bin2hex(random_bytes(3)),
                self::STAFF_SCOPE,
                'authorization_code',
            );
            $this->staffClientId = $sid;
            $this->staffClientSecret = $ssec;
        } catch (\Throwable $setUpFailure) {
            // Narrow catches below handle recoverable cleanup. This
            // outer catch's job is to attempt tearDown-level rollback
            // and re-raise with context so setUp's failure still
            // propagates to phpunit — the `throw` at the end keeps
            // the ForbiddenCatchType rule satisfied.
            $this->safeTearDown();
            throw $setUpFailure;
        }
    }

    protected function tearDown(): void
    {
        $this->safeTearDown();
    }

    private function safeTearDown(): void
    {
        $failures = [];
        foreach ([$this->patientClientId, $this->staffClientId] as $id) {
            if ($id !== '') {
                try {
                    DcrClient::remove($id);
                } catch (SqlQueryException $e) {
                    $failures[] = 'client cleanup: ' . $e->getMessage();
                }
            }
        }
        try {
            $this->portalFixtures?->removePortalPatientFixtures();
        } catch (SqlQueryException $e) {
            $failures[] = 'fixture cleanup: ' . $e->getMessage();
        }
        try {
            if ($this->passwordGrantBagCaptured) {
                $globals = OEGlobalsBag::getInstance();
                if ($this->originalPasswordGrantBagWasSet) {
                    $globals->set('oauth_password_grant', $this->originalPasswordGrantBagSetting);
                } else {
                    $globals->remove('oauth_password_grant');
                }
                $this->restoreGlobal(
                    'oauth_password_grant',
                    $this->originalPasswordGrantGlobalRow,
                    $this->passwordGrantGlobalRowWasInserted
                );
            }
            if ($this->siteAddrOathCaptured) {
                $this->restoreGlobal(
                    'site_addr_oath',
                    $this->originalSiteAddrOathGlobalRow,
                    $this->siteAddrOathGlobalRowWasInserted
                );
            }
        } catch (SqlQueryException $e) {
            $failures[] = 'globals restore: ' . $e->getMessage();
        }
        if ($failures !== []) {
            throw new \RuntimeException('tearDown: ' . implode('; ', $failures));
        }
    }

    /**
     * All four launch-vs-login scenarios exercised sequentially in a
     * single Guzzle process, matching the walkthrough shape under
     * which this PR's gate was characterised. Running one scenario
     * per phpunit method was flaky on test-3 (own-patient positive
     * control) despite a clean DB + reset fixtures — the Apache
     * worker's view of global config between the setUp persist and
     * the authorize-time read did not reliably reflect the test's
     * writes in the per-method isolation shape. Collapsing to one
     * method avoids the cross-method churn and still locks every
     * branch of assertLaunchMatchesLogin() end-to-end.
     */
    #[Test]
    public function testInvalidLaunchContextRejectsAtTokenExchange(): void
    {
        // Scenario 1 — patient B logs in; launch token names patient A (portal intent).
        // Gate leg: patient-login + puuid != launchToken.patient → "Launch context is not for the logged in patient".
        $launch = $this->mintLaunchToken($this->uuidA, SMARTLaunchToken::INTENT_PATIENT_PORTAL);
        $r1 = $this->runPatientRoleFlow($this->fixtureB, $launch);
        $this->assertSame(400, $r1['status'], 'S1 /token should reject with 400. Body: ' . $r1['body']);
        $this->assertSame('invalid_launch_context', $r1['json']['error'] ?? null, 'S1 error field');
        $this->assertSame(
            'Launch context is not for the logged in patient',
            $r1['json']['error_description'] ?? null,
            'S1 error_description'
        );

        // Scenario 2 — patient B logs in; launch token names patient A (staff intent).
        // The non-portal intent still trips the same mismatch gate — a patient
        // login can never use a launch naming someone else, regardless of intent.
        $launch = $this->mintLaunchToken($this->uuidA, SMARTLaunchToken::INTENT_ENCOUNTER_DIALOG);
        $r2 = $this->runPatientRoleFlow($this->fixtureB, $launch);
        $this->assertSame(400, $r2['status'], 'S2 /token should reject with 400. Body: ' . $r2['body']);
        $this->assertSame('invalid_launch_context', $r2['json']['error'] ?? null, 'S2 error field');
        $this->assertSame(
            'Launch context is not for the logged in patient',
            $r2['json']['error_description'] ?? null,
            'S2 error_description'
        );

        // Scenario 3 — patient A logs in; launch token names patient A (portal intent).
        // Positive control: the gate does NOT fire when launchToken.patient
        // matches the logged-in puuid. A regression that always-rejects
        // would leave S1/S2/S4 passing; this assertion catches that.
        $launch = $this->mintLaunchToken($this->uuidA, SMARTLaunchToken::INTENT_PATIENT_PORTAL);
        $r3 = $this->runPatientRoleFlow($this->fixtureA, $launch);
        $this->assertSame(200, $r3['status'], 'S3 /token should succeed. Body: ' . $r3['body']);
        $this->assertArrayHasKey('access_token', $r3['json'], 'S3 access_token presence');
        $this->assertIsString($r3['json']['access_token'], 'S3 access_token shape');
        $this->assertNotSame('', $r3['json']['access_token'], 'S3 access_token non-empty');
        $this->assertSame($this->uuidA, $r3['json']['patient'] ?? null, 'S3 token-bound patient = A');

        // Scenario 4 — staff admin logs in; launch token has patient.portal intent.
        // Gate leg: portal-intent + !isPatientLogin → "A patient portal launch must be used by its patient".
        // The error message is distinct from S1/S2 so a regression that
        // collapses the two code paths surfaces here.
        $launch = $this->mintLaunchToken($this->uuidA, SMARTLaunchToken::INTENT_PATIENT_PORTAL);
        $r4 = $this->runStaffRoleFlow($launch);
        $this->assertSame(400, $r4['status'], 'S4 /token should reject with 400. Body: ' . $r4['body']);
        $this->assertSame('invalid_launch_context', $r4['json']['error'] ?? null, 'S4 error field');
        $this->assertSame(
            'A patient portal launch must be used by its patient',
            $r4['json']['error_description'] ?? null,
            'S4 error_description'
        );
    }

    /**
     * @param array<string, mixed> $fixture
     * @return array{status: int, body: string, json: array<string, mixed>}
     */
    private function runPatientRoleFlow(array $fixture, string $launch): array
    {
        $verifier = $this->generatePkceVerifier();
        $challenge = $this->deriveS256Challenge($verifier);
        $username = $fixture['portal_login_username'] ?? null;
        $password = $fixture['plain_password'] ?? null;
        $email = $fixture['email'] ?? null;
        $this->assertIsString($username);
        $this->assertIsString($password);
        $this->assertIsString($email);
        return $this->runFlow(
            clientId: $this->patientClientId,
            clientSecret: null,
            scope: self::PATIENT_SCOPE,
            launch: $launch,
            loginParams: [
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'user_role' => 'portal-api',
            ],
            pkceVerifier: $verifier,
            pkceChallenge: $challenge,
        );
    }

    /**
     * @return array{status: int, body: string, json: array<string, mixed>}
     */
    private function runStaffRoleFlow(string $launch): array
    {
        return $this->runFlow(
            clientId: $this->staffClientId,
            clientSecret: $this->staffClientSecret,
            scope: self::STAFF_SCOPE,
            launch: $launch,
            loginParams: [
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'user_role' => 'api',
            ],
            pkceVerifier: null,
            pkceChallenge: null,
        );
    }

    /**
     * @param array<string, string> $loginParams
     * @return array{status: int, body: string, json: array<string, mixed>}
     */
    private function runFlow(
        string $clientId,
        ?string $clientSecret,
        string $scope,
        string $launch,
        array $loginParams,
        ?string $pkceVerifier,
        ?string $pkceChallenge,
    ): array {
        $http = $this->buildClient();
        $state = bin2hex(random_bytes(8));
        $nonce = bin2hex(random_bytes(8));
        $authParams = [
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'nonce' => $nonce,
            // aud must match the server-advertised audience so the
            // launch deserialize is actually reached (per
            // CustomAuthCodeGrant::validateAuthorizationRequest).
            'aud' => $this->baseUrl . '/apis/default/api',
            'launch' => $launch,
        ];
        if ($pkceChallenge !== null) {
            $authParams['code_challenge'] = $pkceChallenge;
            $authParams['code_challenge_method'] = 'S256';
        }

        $authResp = $http->get($this->baseUrl . '/oauth2/default/authorize?' . http_build_query($authParams));
        $this->assertSame(
            200,
            $authResp->getStatusCode(),
            '/authorize GET must land on the login page (200). Body: ' . (string) $authResp->getBody()
        );
        [$loginCsrf, $loginAction] = $this->extractCsrfAndAction((string) $authResp->getBody(), 'login page');

        $loginResp = $http->post(
            $this->resolveActionUrl($loginAction),
            [
                'form_params' => array_merge([
                    'csrf_token_form' => $loginCsrf,
                    'persist_login' => '0',
                ], $loginParams),
                'allow_redirects' => true,
            ]
        );
        $consentHtml = (string) $loginResp->getBody();
        $this->assertStringContainsString(
            'proceed',
            $consentHtml,
            'Expected to land on consent page after login. Body: ' . substr($consentHtml, 0, 400)
        );
        [$consentCsrf, $consentAction] = $this->extractCsrfAndAction($consentHtml, 'consent page');

        $scopeGrant = [];
        foreach (explode(' ', $scope) as $s) {
            if ($s !== '') {
                $scopeGrant[$s] = $s;
            }
        }
        $consentResp = $http->post(
            $this->resolveActionUrl($consentAction),
            [
                'form_params' => [
                    'csrf_token_form' => $consentCsrf,
                    'proceed' => '1',
                    'scope' => $scopeGrant,
                ],
                'allow_redirects' => false,
            ]
        );
        $this->assertSame(
            302,
            $consentResp->getStatusCode(),
            'Consent POST should 302 back to redirect_uri. Body: ' . (string) $consentResp->getBody()
        );
        $location = $consentResp->getHeaderLine('Location');
        $query = [];
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $this->assertArrayHasKey('code', $query, 'Callback must carry the authorization code');
        $codeValue = $query['code'];
        $this->assertIsString($codeValue);

        $tokenForm = [
            'grant_type' => 'authorization_code',
            'code' => $codeValue,
            'redirect_uri' => self::REDIRECT_URI,
            'client_id' => $clientId,
        ];
        if ($clientSecret !== null) {
            $tokenForm['client_secret'] = $clientSecret;
        }
        if ($pkceVerifier !== null) {
            $tokenForm['code_verifier'] = $pkceVerifier;
        }
        $tokenResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => $tokenForm,
            'allow_redirects' => false,
        ]);
        $body = (string) $tokenResp->getBody();
        $json = json_decode($body, true);
        /** @var array<string, mixed> $jsonArr */
        $jsonArr = is_array($json) ? $json : [];
        return [
            'status' => $tokenResp->getStatusCode(),
            'body' => $body,
            'json' => $jsonArr,
        ];
    }

    private function mintLaunchToken(string $patientUuid, string $intent): string
    {
        $token = new SMARTLaunchToken($patientUuid);
        $token->setIntent($intent);
        return $token->serialize();
    }

    private function generatePkceVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function deriveS256Challenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    private function sweepLingeringPortalArtifacts(): void
    {
        // A prior failed test run can leave orphans in
        // `patient_access_onsite` whose `patient_data` row is already
        // gone (the fixture manager's cleanup joins on patient_data.pid,
        // so those orphans survive it). The fixture manager allocates
        // next pid via MAX(pid)+1 on patient_data only — not on
        // patient_access_onsite — so an orphan at pid=N makes the
        // second install in setUp collide with a duplicate-pid error.
        // Direct orphan sweep by not-exists subquery covers any prior
        // test's leftovers regardless of portal_username pattern.
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM patient_access_onsite "
                . "WHERE NOT EXISTS (SELECT 1 FROM patient_data WHERE patient_data.pid = patient_access_onsite.pid)"
        );
        // Then ask the fixture manager to sweep matched pairs
        // including uuid_registry entries.
        $this->portalFixtures?->removePortalPatientFixtures();
    }

    private function fetchPatientUuid(int $pid): string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT LOWER(HEX(uuid)) AS hex_uuid FROM patient_data WHERE pid = ?',
            [$pid]
        );
        $this->assertIsArray($row, "No patient_data row for pid=$pid");
        $hex = $row['hex_uuid'] ?? null;
        $this->assertIsString($hex, "uuid column was non-string for pid=$pid");
        $this->assertSame(32, strlen($hex), "uuid hex length wrong for pid=$pid: $hex");
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    /**
     * @return array{string, string} [csrf_token_form, form_action]
     */
    private function extractCsrfAndAction(string $html, string $where): array
    {
        $crawler = new Crawler($html);
        $form = $crawler->filterXPath('//form[.//input[@name="csrf_token_form"]]')->first();
        $this->assertGreaterThan(0, $form->count(), "No form with csrf_token_form on $where");
        $csrf = (string) $form->filterXPath('.//input[@name="csrf_token_form"]')->first()->attr('value');
        $action = (string) $form->attr('action');
        $this->assertNotSame('', $csrf, "csrf_token_form was empty on $where");
        $this->assertNotSame('', $action, "form action was empty on $where");
        return [$csrf, $action];
    }

    private function resolveActionUrl(string $action): string
    {
        if (str_starts_with($action, 'http://') || str_starts_with($action, 'https://')) {
            return $action;
        }
        return $this->baseUrl . $action;
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
     * Insert the global row if absent; update it if present. Records
     * the pre-state in by-ref outputs so tearDown can roll back.
     */
    private function persistGlobal(string $name, string $value, ?string &$originalRow, bool &$wasInserted): void
    {
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            [$name]
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $originalRow = is_string($glValue) ? $glValue : null;
            if ($originalRow !== $value) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$value, $name]
                );
            }
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                [$name, $value]
            );
            $wasInserted = true;
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
}
