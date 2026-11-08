<?php

/**
 * API-tier regression gate for the consent-page HTML output.
 *
 * The SMART authorization controller renders `/scope-authorize-confirm`
 * via templates/oauth2/scope-authorize.html.twig. Isolated-tier tests
 * cover the controller internals; this file adds the api-tier shape
 * assertions on the rendered HTML so a refactor that silently breaks
 * the form structure (missing csrf input, renamed proceed button,
 * dropped client-name display, etc) regresses here instead of
 * surfacing as a broken real-world consent flow.
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
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use OpenEMR\Tests\Support\OAuth2\AuthCodeFlow;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class ConsentPageRenderTest extends TestCase
{
    private string $baseUrl;
    /** @var array<string, mixed>|null */
    private ?array $fixture = null;
    private ?PortalPatientFixtureManager $portalFixtures = null;

    private bool $passwordGrantBagCaptured = false;
    private mixed $originalPasswordGrantBagSetting = null;
    private bool $originalPasswordGrantBagWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

    private bool $siteAddrOathCaptured = false;
    private ?string $originalSiteAddrOathGlobalRow = null;
    private bool $siteAddrOathGlobalRowWasInserted = false;

    /** @var list<string> */
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

            $this->siteAddrOathCaptured = true;
            $this->persistGlobal(
                'site_addr_oath',
                $this->baseUrl,
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );

            $this->portalFixtures = new PortalPatientFixtureManager();
            $this->fixture = $this->portalFixtures->installPortalPatient(
                portalLoginUsername: 'consent-render-' . bin2hex(random_bytes(4)),
                plainPassword: 'ConsentPageRenderTestPassword1!',
                email: 'consent-render-' . bin2hex(random_bytes(4)) . '@test.example'
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
            $this->portalFixtures?->removePortalPatientFixtures();
        });
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

    #[Test]
    public function testConsentPageRendersCsrfTokenHiddenInput(): void
    {
        // The server-side CSRF guard in AuthorizationController reads
        // the `csrf_token_form` POST field; the rendered form must
        // provide it as a hidden input, or every real consent-POST
        // would 403 at the CSRF check. Lock the structural marker.
        $clientName = 'ConsentPageRenderTest-csrf-' . bin2hex(random_bytes(3));
        $prep = AuthCodeFlow::renderConsentPageForUserRole(
            $this->baseUrl,
            $clientName,
            'openid user/Patient.read',
        );
        $this->clientsToRemove[] = $prep['client_id'];

        $crawler = new Crawler($prep['consent_html']);
        $csrfInputs = $crawler->filterXPath('//input[@type="hidden"][@name="csrf_token_form"]');
        $this->assertGreaterThan(
            0,
            $csrfInputs->count(),
            'Consent page must render a hidden csrf_token_form input'
        );
        $this->assertNotSame(
            '',
            (string) $csrfInputs->first()->attr('value'),
            'csrf_token_form value must not be empty'
        );
    }

    #[Test]
    public function testConsentPageRendersProceedAndCancelControls(): void
    {
        // `proceed` is the server's signal that the user authorized.
        // The template has used name="proceed" value="1" since the
        // SMART consent screen was introduced; renaming either silently
        // would turn every submitted consent form into a de-facto
        // denial. Cancel is a secondary control (currently a plain
        // button triggering window.history.back()) — assert it exists
        // so a refactor that drops it (no deny path) is caught.
        $clientName = 'ConsentPageRenderTest-controls-' . bin2hex(random_bytes(3));
        $prep = AuthCodeFlow::renderConsentPageForUserRole(
            $this->baseUrl,
            $clientName,
            'openid user/Patient.read',
        );
        $this->clientsToRemove[] = $prep['client_id'];

        $crawler = new Crawler($prep['consent_html']);
        $proceedButtons = $crawler->filterXPath(
            '//button[@type="submit"][@name="proceed"][@value="1"]'
        );
        $this->assertGreaterThan(
            0,
            $proceedButtons->count(),
            'Consent page must render a submit button with name="proceed" value="1"'
        );
        $this->assertStringContainsStringIgnoringCase(
            'cancel',
            $prep['consent_html'],
            'Consent page should expose a cancel/deny control (text "cancel" must appear)'
        );
    }

    #[Test]
    public function testConsentPageDisplaysRegisteredClientName(): void
    {
        // The authorizing-user needs to see WHICH client they are
        // granting access to. The template puts client.name|text into
        // the <h4> at the top of the form. Register with a
        // distinctive marker and assert it appears — a refactor that
        // dropped the client-name display would hide the identity of
        // the requesting app from the user.
        $clientNameMarker = 'ConsentPageRenderTest-identity-marker-' . bin2hex(random_bytes(4));
        $prep = AuthCodeFlow::renderConsentPageForUserRole(
            $this->baseUrl,
            $clientNameMarker,
            'openid user/Patient.read',
        );
        $this->clientsToRemove[] = $prep['client_id'];

        $this->assertStringContainsString(
            $clientNameMarker,
            $prep['consent_html'],
            'Consent page must display the registered client name '
                . 'so the user can see which app is requesting access'
        );
    }

    #[Test]
    public function testConsentPagePostsBackToOAuth2Server(): void
    {
        // The template's `redirect` variable is populated server-side
        // with whatever endpoint the league OAuth2 server library
        // routes consent submissions through. The specific endpoint
        // name is a league-library implementation detail (currently
        // `/oauth2/default/device/code` which is league's shared
        // confirm-handler for auth-code too). What we lock here is
        // that the action is under the /oauth2/{site}/ namespace —
        // if a template refactor filled the field with an unrelated
        // or cross-origin URL, POSTs would never reach the OAuth2
        // server and the user's grant would be silently lost.
        $clientName = 'ConsentPageRenderTest-action-' . bin2hex(random_bytes(3));
        $prep = AuthCodeFlow::renderConsentPageForUserRole(
            $this->baseUrl,
            $clientName,
            'openid user/Patient.read',
        );
        $this->clientsToRemove[] = $prep['client_id'];

        $this->assertStringContainsString(
            '/oauth2/',
            $prep['consent_action'],
            'Consent form action must stay under the /oauth2/ namespace. Got: '
                . $prep['consent_action']
        );
    }

    #[Test]
    public function testConsentPageForPatientRoleRendersIdenticalStructure(): void
    {
        // The patient-portal consent page shares the same template as
        // staff — same csrf input, same proceed button, same action
        // endpoint. Lock the parity so a future split-template refactor
        // that forgets to port a control over surfaces here.
        $fixture = $this->fixture;
        $this->assertIsArray($fixture);

        $clientName = 'ConsentPageRenderTest-patient-' . bin2hex(random_bytes(3));
        $prep = AuthCodeFlow::renderConsentPageForPatientRole(
            $this->baseUrl,
            $clientName,
            'openid api:port patient/patient.read',
            $fixture,
        );
        $this->clientsToRemove[] = $prep['client_id'];

        $crawler = new Crawler($prep['consent_html']);
        $this->assertGreaterThan(
            0,
            $crawler->filterXPath('//input[@type="hidden"][@name="csrf_token_form"]')->count(),
            'Patient-role consent page must also render csrf_token_form'
        );
        $this->assertGreaterThan(
            0,
            $crawler->filterXPath('//button[@type="submit"][@name="proceed"][@value="1"]')->count(),
            'Patient-role consent page must also render proceed button'
        );
        $this->assertStringContainsString(
            '/oauth2/',
            $prep['consent_action'],
            'Patient-role consent form must also POST under the /oauth2/ namespace'
        );
        $this->assertStringContainsStringIgnoringCase(
            'cancel',
            $prep['consent_html'],
            'Patient-role consent page must also expose a cancel/deny control'
        );
        $this->assertStringContainsString(
            $clientName,
            $prep['consent_html'],
            'Patient-role consent page must also display the registered client name'
        );
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
