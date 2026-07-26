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

        // Step 3: select the from-version and submit. Use direct
        // element manipulation for the same reason Phase 4c-1's
        // InstallWizardUiTest does: submitForm() silently drops POSTs
        // when the values array references DOM-missing fields, and
        // button-click-with-nested-icon-markup sometimes doesn't
        // fire. findElement + JS-driven change event + form-element
        // .submit() bypasses both quirks.
        $select = $client->findElement(WebDriverBy::name('form_old_version'));
        // The select's <option> uses `value='<version>'`; find and click.
        $client->findElement(WebDriverBy::cssSelector(
            'select[name="form_old_version"] option[value="' . $fromVersion . '"]',
        ))->click();
        // Confirm the selection took (browser sync).
        self::assertSame(
            $fromVersion,
            $select->getAttribute('value'),
            'form_old_version select should reflect the clicked option value',
        );
        // The sql_upgrade.php form has no `id` attribute; find via CSS.
        $client->findElement(WebDriverBy::cssSelector('form[action="sql_upgrade.php"]'))->submit();

        // Step 4: assert the migration ran without ERROR / FAILED
        // markers in the response body. Mirrors InstallWizardUiTest's
        // state 3 sanity check. sql_upgrade.php's success path prints
        // green "success" indicators for each migration file it
        // processes; the failure path prints "ERROR" or "FAILED" in
        // red. Absence of both is the "no migration failed" signal.
        //
        // Wait for the body to contain "Upgrade" (the response title
        // or heading text after the form re-renders) so we're not
        // asserting on the still-visible form-submission page.
        try {
            $client->waitForElementToContain('body', 'Upgrade', 60);
        } catch (TimeoutException) {
            self::fail(
                "sql_upgrade.php post-submit response did not contain 'Upgrade' within 60s. "
                . "Current URL: {$client->getCurrentURL()}. "
                . "The migration script may have hung, crashed, or returned an unexpected shape.",
            );
        }
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
