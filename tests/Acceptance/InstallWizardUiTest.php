<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance;

use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use OpenEMR\Tests\Acceptance\Support\BrowserSession;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;

/**
 * Browser-driven walk of setup.php's install wizard.
 *
 * Fires only against the tarball artifact (not docker) — the docker
 * artifact auto-installs via env-var-driven auto_configure.php, so
 * real docker users NEVER see the wizard. Tarball users always do.
 *
 * Prerequisite: the acceptance-package stack must be booted WITHOUT
 * running install-helper.php, so the artifact serves setup.php on `/`
 * instead of redirecting to the login page. Achieved by
 * `boot-package.sh --skip-install-helper <version>`; the workflow's
 * `wizard-install` matrix scenario passes that flag.
 *
 * Walks setup.php's state machine 0 → 1 → 2 → 3 (the "install
 * happens here" step), asserting each state's expected heading
 * renders and the final state 3 reports success. States 4-7 are
 * informational pages (PHP config, web server config, theme select,
 * final credentials display) that don't affect whether the artifact
 * is actually installed — verifying `/` now redirects to the login
 * page is the definitive "install completed" signal for user-facing
 * behavior, so we do that instead of walking the final decorative
 * pages.
 *
 * Failure modes caught that Phase 3's install-helper.php CLI path
 * cannot:
 *   - setup.php state-machine bugs (wrong state transitions, session
 *     cookie handling breaks)
 *   - CSRF token generation/validation regressions in the wizard
 *   - Form field name changes that break the state-2 DB config POST
 *   - Wizard's per-step form rendering (missing required fields,
 *     Bootstrap layout breakage that hides inputs)
 *   - PHP fatal errors partway through a state handler
 */
#[Group('wizard-install')]
final class InstallWizardUiTest extends TestCase
{
    private ?Client $client = null;

    protected function tearDown(): void
    {
        if ($this->client !== null) {
            $this->client->quit();
            $this->client = null;
        }
    }

    public function testAdminCanWalkSetupWizardEndToEnd(): void
    {
        // Local $client references let phpstan narrow the type across
        // the whole method body; $this->client is nullable (for
        // tearDown's release semantics) and property accesses would
        // otherwise trip method.nonObject at every call.
        $client = BrowserSession::create();
        $this->client = $client;

        // State 0: pre-install page. GET /setup.php should render the
        // "Pre Install - Checking File and Directory Permissions"
        // heading and expose a form that advances to state=1.
        $client->request('GET', '/setup.php');
        $this->assertCurrentHeadingContains(
            $client,
            'Pre Install',
            'setup.php state 0 should render the "Pre Install" heading — a different heading means the wizard entry point is broken or the artifact somehow completed install already',
        );

        // State 0 → 1: submit the "Proceed to Step 1" form. Panther's
        // submitForm auto-fills all hidden inputs (state + site +
        // csrf_token_form) from the template.
        $client->submitForm('Proceed to Step 1');
        $this->assertCurrentHeadingContains(
            $client,
            'Step 1',
            'After POST state=1, the response should show "Step 1 - Select Database Setup". A "Not authorized" message means CSRF or session-state validation regressed',
        );

        // State 1 → 2: "Have setup create the database" radio (inst=1)
        // is the default-checked option; submit the form to advance.
        $client->submitForm('Proceed to Step 2');
        $this->assertCurrentHeadingContains(
            $client,
            'Step 2',
            'After POST state=2, the response should show "Step 2 - Database and OpenEMR Initial User Setup Details"',
        );

        // State 2 → 3: fill the DB + admin-user form. Values match
        // install-helper.php's Installer::quick_install() call so the
        // wizard-driven install has identical shape to the CLI one
        // Phase 3 uses.
        //
        // loginhost=% (wildcard) — same fix as install-helper.php's
        // loginhost setting: openemr container talks to mysql from a
        // container-network IP, not 'localhost' from mysql's
        // perspective, so the DB user needs '%' host to match.
        // Fill the form via findElement + sendKeys, then click submit
        // directly. Panther's submitForm() helper silently drops the
        // POST when the value array contains a field the DOM lacks; the
        // symptom is "no network request fired and the crawler stays
        // on the old page." Explicit field-by-field manipulation avoids
        // that silent-failure mode.
        //
        // Field values match install-helper.php's Installer::quick_install()
        // call so the wizard-driven install has identical shape to the
        // CLI one Phase 3 uses. loginhost=% (wildcard) — same fix as
        // install-helper.php's loginhost setting: openemr container
        // talks to mysql from a container-network IP, not 'localhost'
        // from mysql's perspective, so the DB user needs '%' host to
        // match.
        // Fields that need override from the template's defaults:
        //   * server + loginhost — container-networking overrides
        //   * pass + rootpass + iuserpass — password inputs default
        //     to empty string in the form (required by validation)
        //   * iuser — template defaults to a randomly-generated
        //     username; force to "admin" to match the CLI-install
        //     credentials Phase 1's tests already assert against
        // Fields with sensible template defaults left untouched:
        //   * port (3306), dbname (openemr), login (openemr),
        //     root (root), collate, iuname (Administrator),
        //     iufname (Administrator), igroup (Default)
        $fields = [
            'server' => 'mysql',
            'loginhost' => '%',
            'pass' => 'openemr',
            'rootpass' => 'root',
            'iuser' => 'admin',
            'iuserpass' => 'pass',
        ];
        foreach ($fields as $name => $value) {
            $el = $client->findElement(WebDriverBy::name($name));
            $el->clear();
            $el->sendKeys($value);
        }
        // Submit the form directly via its id. Clicking the button
        // element does NOT reliably fire the submit in this Panther +
        // ChromeDriver combo (observed 0 POSTs in access logs after
        // clicks that returned success). Form's ->submit() bypasses
        // whatever button-click quirk is at play.
        $client->findElement(WebDriverBy::id('myform'))->submit();

        // State 3: the actual install work. Success rendering shows
        // "Step 3 - Creating Database and First User" plus a series
        // of green "success" indicators for each install step.
        $this->assertCurrentHeadingContains(
            $client,
            'Step 3',
            'After POST state=3, the response should show "Step 3 - Creating Database and First User"',
        );
        $state3Body = $client->getCrawler()->filter('body')->text();
        self::assertStringNotContainsString(
            'ERROR',
            $state3Body,
            'State 3 response should not contain ERROR — install failed midway',
        );
        self::assertStringNotContainsString(
            'FAILED',
            $state3Body,
            'State 3 response should not contain FAILED — one of the install substeps did not complete',
        );

        // Definitive post-install signal: GET / should now redirect to
        // the login page (same assertion Phase 1's InstallTest makes
        // against a Phase-3-installed artifact). If the wizard's state
        // 3 reported success but `/` still shows setup.php or errors,
        // the install didn't actually take effect — this catches that.
        $client->request('GET', '/');
        // Assert BOTH the current URL redirected away from `/` to the
        // login route AND the page title matches "OpenEMR Login". Title
        // alone would pass if a root handler ever rendered a login-page
        // template inline at `/` without redirecting; asserting the URL
        // change is the "redirect actually happened" proof.
        self::assertStringContainsString(
            '/interface/login/login.php',
            $client->getCurrentURL(),
            'GET / after wizard install should redirect to /interface/login/login.php — staying at / means the install did not actually take effect (sqlconf.php $config still 0)',
        );
        self::assertStringContainsString(
            'OpenEMR Login',
            $client->getTitle(),
            'Post-redirect page title should be "OpenEMR Login" — a different title after the redirect target check passes means the login route rendered something unexpected',
        );
    }

    /**
     * Wait for the current page's first h3 to render, then assert its
     * text contains the expected fragment. Every wizard state's page
     * starts with an <h3> that names the step (e.g. "Step 2 - Database
     * and OpenEMR Initial User Setup Details"), so a settled h3 is the
     * "wizard advanced to expected state" signal.
     *
     * Panther's post-submit crawler snapshot can race the actual page
     * load — filter('h3')->text() will throw "current node list is
     * empty" if called before the new DOM commits. `waitFor('h3', 15)`
     * blocks until the element appears, up to 15s.
     */
    private function assertCurrentHeadingContains(Client $client, string $expected, string $failureMessage): void
    {
        // Wait until an h3 containing the expected text appears — NOT
        // just "any h3." Multi-step wizard pages all have h3s, and the
        // previous page's h3 stays in the DOM/crawler snapshot for a
        // beat after a POST-triggered navigation completes; asserting
        // "any h3" would race and pass on the stale value.
        try {
            $client->waitForElementToContain('h3', $expected, 20);
            return;
        } catch (TimeoutException) {
            // Fall through to the diagnostic dump below. Any further
            // exception (driver died between the timeout and now) is
            // let propagate — the test's already failing anyway, and a
            // driver-death exception is still a signal.
        }

        $url = $client->getCurrentURL();
        $headings = $client->getCrawler()->filter('h3');
        $currentHeading = $headings->count() > 0 ? $headings->first()->text() : '(no h3 in current DOM)';

        // Look for h1/h3 first (the wizard's step markers). Fall back
        // to a body-content snippet so a validation-error page
        // (typically shows the error message inline, no h3) is
        // visible in the failure output.
        $source = $client->getWebDriver()->getPageSource();
        if (preg_match_all('/<h[13][^>]*>(.*?)<\/h[13]>/s', $source, $m)) {
            $snippet = 'headings: ' . implode(' || ', array_map(trim(...), $m[1]));
        } elseif (preg_match('/<body[^>]*>(.*)$/s', $source, $bm)) {
            $bodyClean = (string) preg_replace('/<style[^>]*>.*?<\/style>/s', '', $bm[1]);
            $bodyClean = (string) preg_replace('/<script[^>]*>.*?<\/script>/s', '', $bodyClean);
            $snippet = 'no h1/h3; body (styles stripped): ' . substr($bodyClean, 0, 1200);
        } else {
            $snippet = 'no h1/h3 AND no <body>: ' . substr($source, 0, 1200);
        }

        self::fail(
            "{$failureMessage}\nExpected h3 to contain: {$expected}\n"
            . "Current URL: {$url}\n"
            . "Actual h3 (first): {$currentHeading}\n"
            . "Page source snippet:\n{$snippet}",
        );
    }
}
