<?php

/**
 * E2e tests for the Portal Lockout Tracker admin unblock UI.
 *
 * Exercises the full happy path introduced in #14288:
 *   1. seed patient_access_onsite with a non-zero portal_fail_counter
 *      (via PortalPatientFixtureManager),
 *   2. navigate to the Portal Lockout Tracker report,
 *   3. submit the filter form and verify the seeded row is rendered,
 *   4. click Reset Counter and verify the DB row is zeroed and the
 *      timestamp is nulled.
 *
 * The chain covers interface/reports/portal_lockout_tracker.php,
 * library/ajax/login_counter_ip_tracker.php's resetPortalAccountCounter
 * handler, and AuthUtils::resetPortalAccountFailedCounter in one flow.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Panther\PantherTestCase;

class PortalLockoutTrackerTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const REPORT_IFRAME = "//*[@id='framesDisplay']//iframe[@name='rep']";

    private $crawler;

    private ?PortalPatientFixtureManager $portalFixtures = null;

    protected function tearDown(): void
    {
        $this->portalFixtures?->removePortalPatientFixtures();
        parent::tearDown();
    }

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testResetPortalCounterClearsDbRow(): void
    {
        $fixture = $this->seedPortalRowWithFailedCounter(7);

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->goToMainMenuLink('Reports||Services||Portal Lockout Tracker');
            $this->assertActiveTab('Portal Lockout Tracker');

            $this->switchToIFrame(self::REPORT_IFRAME);
            $this->submitReportForm();

            $crawler = $this->client->refreshCrawler();
            $pageText = $crawler->filterXPath('//body')->text();
            $this->assertStringContainsString(
                $fixture['portal_login_username'],
                $pageText,
                'Seeded portal_login_username must render in the report table'
            );
            $this->assertStringContainsString(
                '7',
                $pageText,
                'Seeded portal_fail_counter must render in the row'
            );

            // Click the row's Reset Counter button. Only one row matches the
            // seeded username, and its button lives in the fail-counter cell.
            $this->client->executeScript(
                'document.querySelector("#portal-fail-counter-'
                . addslashes($fixture['portal_login_username'])
                . ' button").click();'
            );

            // The reset is fire-and-forget from the browser side; wait for
            // the DB write to land rather than polling the DOM (the
            // fetch/DOM update sequence is covered by the unit tests, this
            // one verifies the backend actually zeroes the counter).
            $this->waitForCounterToClear($fixture['portal_login_username']);

            $this->assertSame(
                0,
                $this->readPortalCounter($fixture['portal_login_username']),
                'Clicking Reset must zero patient_access_onsite.portal_fail_counter'
            );
            $this->assertNull(
                $this->readPortalLastFail($fixture['portal_login_username']),
                'Clicking Reset must null patient_access_onsite.portal_last_fail'
            );
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    // -------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------

    /**
     * @return array{pid: int, pubpid: non-falsy-string, portal_username: string, portal_login_username: string, plain_password: string, email: string}
     */
    private function seedPortalRowWithFailedCounter(int $counter): array
    {
        $this->portalFixtures ??= new PortalPatientFixtureManager();
        $fixture = $this->portalFixtures->installPortalPatient(
            portalLoginUsername: 'e2e-portal-lockout-' . Uuid::uuid4()->toString(),
            plainPassword: 'IrrelevantPassword1!'
        );
        QueryUtils::sqlStatementThrowException(
            "UPDATE `patient_access_onsite` SET `portal_fail_counter` = ?, "
                . "`portal_last_fail` = NOW() WHERE BINARY `portal_login_username` = ?",
            [$counter, $fixture['portal_login_username']]
        );
        return $fixture;
    }

    private function submitReportForm(): void
    {
        $this->client->executeScript(
            'document.getElementById("form_refresh").value = "true";'
            . 'document.getElementById("theform").submit();'
        );
        $this->client->waitFor('#report_results');
    }

    private function waitForCounterToClear(string $username): void
    {
        // Poll the DB directly; the fetch call from the browser is
        // asynchronous, so we cannot rely on the click returning after the
        // server-side UPDATE has landed.
        $deadline = microtime(true) + 5.0;
        while (microtime(true) < $deadline) {
            if ($this->readPortalCounter($username) === 0) {
                return;
            }
            usleep(100_000);
        }
    }

    private function readPortalCounter(string $username): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT portal_fail_counter FROM patient_access_onsite WHERE BINARY portal_login_username = ?",
            [$username]
        );
        $value = $row['portal_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readPortalLastFail(string $username): ?string
    {
        $row = QueryUtils::querySingleRow(
            "SELECT portal_last_fail FROM patient_access_onsite WHERE BINARY portal_login_username = ?",
            [$username]
        );
        $value = $row['portal_last_fail'] ?? null;
        return is_string($value) ? $value : null;
    }
}
