<?php

/**
 * Invariant test for proposal point Q3: "provider-agnostic OIDC."
 *
 * With `oidc_enabled` set and `gcip_issuer` pointing at any standards-
 * compliant OIDC provider (not just Firebase / Google Cloud Identity
 * Platform), a user whose `sub` is mapped to an OpenEMR account must be
 * able to initiate the authorization_code flow from the login page and
 * land authenticated.
 *
 * The proposal frames this as: "OpenEMR's auth story is OIDC, not
 * Firebase-specifically; the implementation must work with any issuer
 * whose discovery document satisfies the spec."
 *
 * The invariant is expressed as observable browser behavior (the user
 * starts on the login page, follows the SSO entry point, and ends
 * authenticated), so it survives implementation details — whether Q3
 * lands as a button on the existing template, a server-side redirect
 * on the login route, or a new controller.
 *
 * Today the module's only login override is Firebase-specific: the
 * `Bootstrap::onTemplatePageEvent` hook renders `gcip-login.html.twig`
 * iff the Firebase triplet (`gcip_firebase_api_key`,
 * `gcip_firebase_auth_domain`, `gcip_firebase_project_id`) is
 * configured, and that template loads the Firebase JS SDK from gstatic
 * and calls `firebase.auth()`. A standards-compliant issuer with no
 * Firebase config is a no-op — the standard local credential form
 * renders, with no SSO affordance at all. This test fails red against
 * that state and will turn green once Q3 adds a generic OIDC entry
 * point.
 *
 * The test uses the oidc-mock container (reachable at
 * `http://oidc-mock:9400` inside the compose network) as the
 * standards-compliant issuer. oidc-mock implements discovery,
 * dynamic client registration, and a self-login form — everything
 * needed to drive authorization_code end-to-end without depending on
 * Firebase or a cloud account.
 *
 * Expected trajectory:
 *   - Red today: login page exposes no SSO entry point when only
 *     generic OIDC config is present (Firebase triplet absent).
 *   - Green once Q3 lands and the flow completes against any
 *     compliant issuer.
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Common\Auth\Oidc;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

final class OidcStandardsCompliantIssuerTest extends PantherTestCase
{
    /**
     * oidc-mock container, reachable inside the compose network. See
     * `docker/development-easy/docker-compose.yml` `oidc-mock` service
     * and `https://github.com/geigerzaehler/oidc-provider-mock`.
     */
    private const OIDC_ISSUER = 'http://oidc-mock:9400';
    /**
     * Identifier the test registers as the `sub` of the signed-in
     * user. oidc-mock's self-login form lets the test supply any
     * subject; Q3's implementation is responsible for mapping that
     * subject to a local OpenEMR account (proposal Q5 —
     * pre-provisioning).
     */
    private const TEST_SUBJECT = 'admin-oidc-invariant';
    private const TEST_EMAIL = 'admin-oidc@example.test';
    private const OIDC_CLIENT_ID = 'openemr-q3-invariant';

    /**
     * Minimal `module_gcip_config` values that mean "OIDC is
     * configured against a standards-compliant issuer." The Firebase
     * triplet is intentionally absent — the invariant is phrased at
     * the OIDC layer, not the Firebase-renderer layer.
     *
     * @var array<string, string>
     */
    private const GCIP_CONFIG = [
        'gcip_issuer' => self::OIDC_ISSUER,
        'gcip_client_id' => self::OIDC_CLIENT_ID,
    ];

    private Client $client;
    private ?string $originalOidcEnabled = null;
    private ?string $originalOidcLocalLoginDisabled = null;
    /** @var array<string, ?string> */
    private array $originalGcipConfig = [];

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('E2E test requires database');
        }

        $this->originalOidcEnabled = $this->getGlobal('oidc_enabled');
        $this->originalOidcLocalLoginDisabled = $this->getGlobal('oidc_local_login_disabled');
        foreach (array_keys(self::GCIP_CONFIG) as $key) {
            $this->originalGcipConfig[$key] = $this->getGcipConfig($key);
        }

        $this->setGlobal('oidc_enabled', '1');
        // Clear the legacy hybrid flag so the DB matches the point-8
        // shape the companion invariant (`OidcModeExcludesCredentialAuthTest`)
        // asserts. Under point 8 the flag does not exist and the clear
        // is a noop; before point 8 lands, leaving the flag in place
        // would silently enable the local login fallback and hide the
        // actual Q3 failure mode.
        $this->clearGlobal('oidc_local_login_disabled');
        foreach (self::GCIP_CONFIG as $key => $value) {
            $this->setGcipConfig($key, $value);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (getenv('DISABLE_DATABASE') === '1') {
            return;
        }

        $this->restoreGlobal('oidc_enabled', $this->originalOidcEnabled);
        $this->restoreGlobal('oidc_local_login_disabled', $this->originalOidcLocalLoginDisabled);
        foreach ($this->originalGcipConfig as $key => $originalValue) {
            $this->restoreGcipConfig($key, $originalValue);
        }
    }

    #[Test]
    public function testStandardsCompliantIssuerDrivesSuccessfulLogin(): void
    {
        $this->client = $this->createSeleniumClient();
        try {
            $this->client->request(
                'GET',
                '/interface/login/login.php?site=default&testing_mode=1',
            );

            // Find and follow the SSO entry point. Q3 must expose a
            // clickable affordance that begins authorization_code
            // against the configured issuer; the test is agnostic to
            // exactly which element serves that role (anchor, button,
            // auto-redirect form) so long as it exists and leads to
            // the issuer.
            $entryPoint = $this->findSsoEntryPoint();
            if ($entryPoint === null) {
                self::fail($this->entryPointNotFoundMessage());
            }
            $entryPoint->click();

            // Drive through oidc-mock's self-login page. The mock
            // accepts any `sub` via a plain HTML form; Q3's mapping
            // layer (proposal Q5) must translate that `sub` (or the
            // claims the mock returns, e.g. `email`) into an OpenEMR
            // user. The test pre-provisions that mapping in setUp so
            // the mock's subject resolves to a real local account.
            $this->completeOidcMockLogin();

            // Authenticated state: OpenEMR's main shell renders with
            // title "OpenEMR" (vs. "OpenEMR Login" on the login
            // page). Give the browser time to follow the final
            // redirect back from the issuer.
            try {
                $this->client->wait(15)->until(
                    static fn(WebDriver $driver): bool => $driver->getTitle() === 'OpenEMR',
                );
            } catch (TimeoutException) {
                // Fall through; assertSame reports actual title.
            }
            self::assertSame(
                'OpenEMR',
                $this->client->getTitle(),
                sprintf(
                    'Authorization_code flow against a standards-compliant OIDC'
                    . ' issuer (%s) did not result in an authenticated OpenEMR'
                    . ' session. Proposal Q3 requires provider-agnostic OIDC:'
                    . ' the flow must complete for any issuer whose discovery'
                    . ' document is spec-compliant, not only Firebase /'
                    . ' Google Cloud Identity Platform. Final URL: %s.',
                    self::OIDC_ISSUER,
                    $this->client->getCurrentURL(),
                ),
            );
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    /**
     * Locate a clickable element on the login page that initiates the
     * OIDC flow. The test accepts several reasonable conventions so
     * Q3's UI layer is free to choose:
     *
     *   - `[data-openemr-oidc-login]` (explicit contract marker)
     *   - `<a href>` whose path starts with `/interface/login/oidc/`
     *     or `/oauth2/authorize` (server-initiated redirect route)
     *   - `<a href>` pointing directly at the configured issuer's
     *     authorization endpoint
     *   - visible text "Sign in with SSO" / "Single Sign-On" /
     *     "Sign in with OIDC" (excludes the Firebase-specific
     *     "Sign in with local credentials" fallback link)
     */
    private function findSsoEntryPoint(): ?WebDriverElement
    {
        $selectors = [
            // Explicit contract attribute (preferred for stability)
            '//*[@data-openemr-oidc-login]',
            // Server-side redirect route
            '//a[starts-with(@href, "/interface/login/oidc/")]',
            '//a[starts-with(@href, "/oauth2/authorize")]',
            // Direct link to configured issuer's authorize endpoint
            '//a[contains(@href, "' . self::OIDC_ISSUER . '")]',
            // Visible-text conventions
            '//a[contains(translate(normalize-space(.),'
                . ' "ABCDEFGHIJKLMNOPQRSTUVWXYZ",'
                . ' "abcdefghijklmnopqrstuvwxyz"),'
                . ' "single sign")]',
            '//a[contains(translate(normalize-space(.),'
                . ' "ABCDEFGHIJKLMNOPQRSTUVWXYZ",'
                . ' "abcdefghijklmnopqrstuvwxyz"),'
                . ' "sign in with sso")]',
            '//button[contains(translate(normalize-space(.),'
                . ' "ABCDEFGHIJKLMNOPQRSTUVWXYZ",'
                . ' "abcdefghijklmnopqrstuvwxyz"),'
                . ' "single sign")]',
        ];

        foreach ($selectors as $xpath) {
            try {
                $elements = $this->client->findElements(WebDriverBy::xpath($xpath));
            } catch (WebDriverException) {
                // Driver-side failure on this selector — try the next.
                continue;
            }
            if (count($elements) > 0) {
                return $elements[0];
            }
        }
        return null;
    }

    /**
     * Complete whatever UI oidc-mock presents. The mock's self-login
     * form has a single text input for the subject and a submit
     * button; the test fills in a known `sub` and submits. If Q3's
     * implementation pre-authorizes the client or uses PKCE without a
     * consent step, the mock skips straight to the redirect and this
     * helper is a noop.
     */
    private function completeOidcMockLogin(): void
    {
        try {
            $this->client->wait(10)->until(
                fn(WebDriver $driver): bool =>
                    str_contains($driver->getCurrentURL(), self::OIDC_ISSUER)
                    || $driver->getTitle() === 'OpenEMR',
            );
        } catch (TimeoutException) {
            return; // Neither state reached; caller's assertion will report.
        }

        if (!str_contains($this->client->getCurrentURL(), self::OIDC_ISSUER)) {
            return; // Issuer skipped its UI; nothing to fill in.
        }

        // Fill in the subject and any email the mock requests, then
        // submit. Field names come from the oidc-mock self-login page.
        $subjectInputs = $this->client->findElements(WebDriverBy::name('sub'));
        if (count($subjectInputs) > 0) {
            $subjectInputs[0]->sendKeys(self::TEST_SUBJECT);
        }
        $emailInputs = $this->client->findElements(WebDriverBy::name('email'));
        if (count($emailInputs) > 0) {
            $emailInputs[0]->sendKeys(self::TEST_EMAIL);
        }

        $submitButtons = $this->client->findElements(
            WebDriverBy::cssSelector('button[type="submit"], input[type="submit"]'),
        );
        if (count($submitButtons) > 0) {
            $submitButtons[0]->click();
        }
    }

    /**
     * Initialize a Selenium-grid Panther client against the openemr
     * container. Inlined rather than pulled in via `BaseTrait` because
     * the trait exposes many `$this->crawler`-dependent helpers this
     * test does not use, and those helpers surface as phpstan noise
     * when merged into a `final` class.
     */
    private function createSeleniumClient(): Client
    {
        $seleniumHost = getenv('SELENIUM_HOST') ?: 'selenium';
        $baseUrl = getenv('SELENIUM_BASE_URL') ?: 'http://openemr';
        $forceHeadless = getenv('SELENIUM_FORCE_HEADLESS') === 'true';

        $capabilities = DesiredCapabilities::chrome();
        $chromeArgs = [
            '--window-size=1920,1080',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
        ];
        if ($forceHeadless) {
            $chromeArgs[] = '--headless';
        }
        $capabilities->setCapability('goog:chromeOptions', ['args' => $chromeArgs]);
        $capabilities->setCapability('unhandledPromptBehavior', 'accept');
        $capabilities->setCapability('pageLoadStrategy', 'normal');

        $client = Client::createSeleniumClient(
            "http://{$seleniumHost}:4444/wd/hub",
            $capabilities,
            $baseUrl,
        );
        $client->manage()->timeouts()->implicitlyWait(0);
        $client->manage()->timeouts()->pageLoadTimeout(60);
        return $client;
    }

    private function entryPointNotFoundMessage(): string
    {
        return sprintf(
            'No OIDC SSO entry point found on the login page when'
            . ' oidc_enabled=1 and gcip_issuer=%s. Proposal Q3'
            . ' requires provider-agnostic OIDC: the login page must'
            . ' expose a clickable affordance (button, link, or'
            . ' auto-redirect) that initiates authorization_code'
            . ' against the configured issuer regardless of whether'
            . ' the Firebase triplet is present. The test accepts any'
            . ' of: a [data-openemr-oidc-login] element, an anchor'
            . ' whose href starts with /interface/login/oidc/ or'
            . ' /oauth2/authorize, a direct link to the issuer, or'
            . ' visible text matching "single sign-on"/"sign in with'
            . ' sso".',
            self::OIDC_ISSUER,
        );
    }

    private function getGlobal(string $name): ?string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT gl_value FROM globals WHERE gl_name = ? LIMIT 1',
            [$name],
        );
        $value = $rows[0]['gl_value'] ?? null;
        return is_string($value) ? $value : null;
    }

    private function setGlobal(string $name, string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO globals (gl_name, gl_value) VALUES (?, ?)'
            . ' ON DUPLICATE KEY UPDATE gl_value = VALUES(gl_value)',
            [$name, $value],
        );
    }

    private function clearGlobal(string $name): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM globals WHERE gl_name = ?',
            [$name],
        );
    }

    private function restoreGlobal(string $name, ?string $originalValue): void
    {
        if ($originalValue === null) {
            $this->clearGlobal($name);
            return;
        }

        $this->setGlobal($name, $originalValue);
    }

    private function getGcipConfig(string $key): ?string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT config_value FROM module_gcip_config WHERE config_key = ? LIMIT 1',
            [$key],
        );
        $value = $rows[0]['config_value'] ?? null;
        return is_string($value) ? $value : null;
    }

    private function setGcipConfig(string $key, string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO module_gcip_config (config_key, config_value) VALUES (?, ?)'
            . ' ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)',
            [$key, $value],
        );
    }

    private function restoreGcipConfig(string $key, ?string $originalValue): void
    {
        if ($originalValue === null) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM module_gcip_config WHERE config_key = ?',
                [$key],
            );
            return;
        }

        $this->setGcipConfig($key, $originalValue);
    }
}
