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
 * Browser-driven walk of sql_upgrade.php's version-selector form.
 *
 * Fires only against the tarball artifact (Phase 3's package
 * workflow, in the wizard-upgrade matrix scenario). Docker skips
 * the upgrade wizard entirely because docker's auto-upgrade path
 * runs fsupgrade-<N>.sh + sql_upgrade.php CLI on container restart
 * — no wizard interaction. Tarball users always see this wizard.
 *
 * Prerequisite: the acceptance-package stack has been booted with
 * from-version installed and swapped to to-version's filesystem
 * WITHOUT running the CLI sql_upgrade — the classic "user just
 * extracted new tarball, hasn't run the upgrade script yet" state.
 * Achieved by `upgrade-package.sh --skip-sql-upgrade <from> <to>`;
 * the workflow's `wizard-upgrade` matrix scenario passes that flag.
 *
 * Walks the wizard:
 *   1. Load /sql_upgrade.php — assert the version-selector form
 *      renders (has form_old_version select + form_submit button)
 *   2. Verify the from-version is available in the dropdown options
 *   3. Select the from-version and submit
 *   4. Assert the response includes "success" indicators or does
 *      not include ERROR / FAILED text (mirrors InstallWizardUiTest's
 *      state 3 assertion pattern)
 *   5. Verify GET / redirects to the login page — a stronger signal
 *      that the migration took effect than parsing wizard output
 *
 * Failure modes caught that Phase 3's CLI `sql_upgrade.php --from=<v>`
 * cannot:
 *   - sql_upgrade.php form-rendering regressions (missing options,
 *     button label changes, form action URL changes)
 *   - CSRF or session validation regressions on the upgrade form
 *   - JS regressions that block the submit
 *   - Post-migration login-page-redirect regressions (schema left in
 *     an intermediate state that breaks the auth flow)
 */
#[Group('wizard-upgrade')]
final class UpgradeWizardUiTest extends TestCase
{
    private ?Client $client = null;

    protected function tearDown(): void
    {
        if ($this->client !== null) {
            $this->client->quit();
            $this->client = null;
        }
    }

    public function testAdminCanWalkSqlUpgradeWizardEndToEnd(): void
    {
        // The FROM_VERSION env var propagates the same value that
        // upgrade-package.sh received. If it's not set we default to
        // 8.2.0 which is the current wizard-upgrade scenario default
        // and the only version with a shipped tarball at this time.
        $fromVersion = getenv('FROM_VERSION') ?: '8.2.0';

        // Local $client references let phpstan narrow the type across
        // the whole method body; $this->client is nullable (tearDown
        // release semantics) and property accesses would otherwise
        // trip method.nonObject at every call.
        $client = BrowserSession::create();
        $this->client = $client;

        // Step 1: load /sql_upgrade.php. On a fresh-installed but
        // pre-migration artifact this renders the version-selector
        // form. If instead we get a "Not Authorized" or blank page,
        // the upgrade-package.sh --skip-sql-upgrade step somehow
        // completed the migration OR the artifact isn't in the
        // expected pre-migration state.
        $client->request('GET', '/sql_upgrade.php');
        try {
            $client->waitFor('select[name="form_old_version"]', 20);
        } catch (TimeoutException) {
            $source = $client->getWebDriver()->getPageSource();
            $snippet = substr($source, 0, 1200);
            self::fail(
                "sql_upgrade.php did not render the form_old_version select within 20s. "
                . "Current URL: {$client->getCurrentURL()}. "
                . "Title: {$client->getTitle()}. "
                . "Page source snippet:\n{$snippet}",
            );
        }

        // Step 2: verify from-version is present in the dropdown. If
        // it's missing, users upgrading from that release literally
        // couldn't select it — the wizard would be usable but
        // dishonestly incomplete.
        $optionValues = [];
        foreach ($client->findElements(WebDriverBy::cssSelector('select[name="form_old_version"] option')) as $option) {
            $optionValues[] = (string) $option->getAttribute('value');
        }
        self::assertContains(
            $fromVersion,
            $optionValues,
            "sql_upgrade.php dropdown must include from-version {$fromVersion} — dropdown options seen: "
            . implode(', ', $optionValues),
        );

        // Step 3: select the from-version and submit. Options via
        // JS-driven .value + change event (Panther/ChromeDriver's
        // findElement(option)->click() doesn't reliably trigger the
        // select's change handler in a headless context), then click
        // the plain submit button — sql_upgrade.php's button has no
        // nested icon markup so the Phase 4c-1 button-click quirk
        // doesn't apply here (verified against setup.php's
        // Create-DB-and-User button in InstallWizardUiTest, which
        // DID have that quirk because it wrapped an <i class="fas">).
        $client->executeScript(
            'const s = document.querySelector("select[name=\"form_old_version\"]");'
            . 's.value = arguments[0];'
            . 's.dispatchEvent(new Event("change", {bubbles: true}));',
            [$fromVersion],
        );
        self::assertSame(
            $fromVersion,
            $client->findElement(WebDriverBy::name('form_old_version'))->getAttribute('value'),
            'form_old_version select should reflect the JS-assigned value',
        );
        $client->findElement(WebDriverBy::cssSelector('button[name="form_submit"]'))->click();

        // Step 4: assert the migration ran through completion. Wait
        // for the definitive "Database and Access Control upgrade
        // finished." marker — sql_upgrade.php emits this only at the
        // end of the post-submit handler (line 563), immediately
        // before exit(). Choosing this specific string (rather than
        // e.g. "Upgrade" which is present on the initial form's
        // "Upgrade Database" button, or "Updating UUIDs" which fires
        // partway through) means the wait only settles once the
        // migration has run all the way through, not on any of the
        // in-flight or pre-submit states.
        //
        // 120s timeout — real migrations can take a few minutes on
        // large-jump upgrades (the sql_upgrade.php form itself warns
        // "several minutes to several hours" for pre-5.0.0 upgrades).
        // Give it enough headroom for the tests' typical single-minor
        // jumps.
        try {
            $client->waitForElementToContain(
                'body',
                'Database and Access Control upgrade finished.',
                120,
            );
        } catch (TimeoutException) {
            self::fail(
                "sql_upgrade.php never emitted its completion marker "
                . "('Database and Access Control upgrade finished.') within 120s. "
                . "Current URL: {$client->getCurrentURL()}. "
                . "The migration script may have hung, crashed, or returned an unexpected shape.",
            );
        }
        // Also assert no ERROR / FAILED markers in the body — the
        // completion string could theoretically appear alongside a
        // partial-failure banner. Mirrors InstallWizardUiTest's
        // state 3 sanity check pattern.
        $body = $client->getCrawler()->filter('body')->text();
        self::assertStringNotContainsString(
            'ERROR',
            $body,
            'sql_upgrade.php response should not contain ERROR — a migration step failed',
        );
        self::assertStringNotContainsString(
            'FAILED',
            $body,
            'sql_upgrade.php response should not contain FAILED — a migration step reported failure',
        );

        // Step 5: definitive post-migration signal — GET / redirects
        // to the login page. Same assertion pattern InstallWizardUiTest
        // uses. If sql_upgrade.php reported success but the app is
        // wedged (schema in an intermediate state), this catches that.
        $client->request('GET', '/');
        self::assertStringContainsString(
            '/interface/login/login.php',
            $client->getCurrentURL(),
            'GET / after wizard upgrade should redirect to /interface/login/login.php — staying at / means the migration did not fully take effect',
        );
        self::assertStringContainsString(
            'OpenEMR Login',
            $client->getTitle(),
            'Post-redirect page title should be "OpenEMR Login"',
        );
    }
}
