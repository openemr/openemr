<?php

/**
 * E2e tests for the Financial Summary by Service Code report.
 *
 * Verifies the report renders correctly, displays expected data from
 * fixture billing records, and applies the Important Codes filter.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class SvcCodeFinancialReportTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private $crawler;

    /**
     * Encounter number for test fixtures, chosen to avoid collisions.
     */
    private const TEST_ENCOUNTER = 888888001;
    private const TEST_PID = 1;
    private const TEST_DATE = '2026-01-15';
    private const TEST_CODE_IMPORTANT = 'E2ETST01';
    private const TEST_CODE_NORMAL = 'E2ETST02';

    /**
     * XPath to the report iframe. Reports open in a frame named 'rep'.
     */
    private const REPORT_IFRAME = "//*[@id='framesDisplay']//iframe[@name='rep']";

    /**
     * Report page navigates, loads, and shows the empty state message.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testReportPageLoads(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->goToMainMenuLink('Reports||Financial||Financial Summary by Service Code');
            $this->assertActiveTab('Financial Summary by Service Code');
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    /**
     * Submit the report with no data and verify "No matches found" message.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testReportEmptySubmit(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->goToMainMenuLink('Reports||Financial||Financial Summary by Service Code');
            $this->assertActiveTab('Financial Summary by Service Code');

            $this->switchToIFrame(self::REPORT_IFRAME);
            $this->submitReportForm('2020-01-01', '2020-01-02');

            $this->crawler = $this->client->refreshCrawler();
            $pageText = $this->crawler->filterXPath('//body')->text();
            $this->assertStringContainsString('No matches found', $pageText);
        } catch (\Throwable $e) {
            $this->client->quit();
            throw $e;
        }
        $this->client->quit();
    }

    /**
     * Insert fixture data, submit the report, and verify the table shows
     * correct totals.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testReportShowsCorrectTotals(): void
    {
        $this->insertFixtureData();

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->goToMainMenuLink('Reports||Financial||Financial Summary by Service Code');
            $this->assertActiveTab('Financial Summary by Service Code');

            $this->switchToIFrame(self::REPORT_IFRAME);
            $this->submitReportForm(self::TEST_DATE, self::TEST_DATE);

            $this->crawler = $this->client->refreshCrawler();

            // Verify the report table exists and contains our test code
            $tableText = $this->crawler->filterXPath('//table[@id="mymaintable"]')->text();
            $this->assertStringContainsString(self::TEST_CODE_IMPORTANT, $tableText, 'Important code should appear in results');
            $this->assertStringContainsString(self::TEST_CODE_NORMAL, $tableText, 'Normal code should appear in results');

            // Verify specific values for the important code row
            $this->assertStringContainsString('250.00', $tableText, 'Billed amount should be 250.00');
            $this->assertStringContainsString('180.00', $tableText, 'Paid amount should be 180.00');
            $this->assertStringContainsString('Grand Total', $tableText, 'Grand Total row should exist');
        } catch (\Throwable $e) {
            $this->client->quit();
            $this->cleanUpFixtureData();
            throw $e;
        }
        $this->client->quit();
        $this->cleanUpFixtureData();
    }

    /**
     * Verify that the Important Codes checkbox filters results to only
     * codes with financial_reporting=1.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testImportantCodesFilter(): void
    {
        $this->insertFixtureData();

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->goToMainMenuLink('Reports||Financial||Financial Summary by Service Code');
            $this->assertActiveTab('Financial Summary by Service Code');

            $this->switchToIFrame(self::REPORT_IFRAME);

            // Check the Important Codes checkbox before submitting
            $checkbox = $this->crawler->filterXPath('//input[@name="form_details"]');
            if (!$checkbox->getElement(0)->isSelected()) {
                $checkbox->getElement(0)->click();
            }

            $this->submitReportForm(self::TEST_DATE, self::TEST_DATE);

            $this->crawler = $this->client->refreshCrawler();

            $tableText = $this->crawler->filterXPath('//table[@id="mymaintable"]')->text();
            // Important code (financial_reporting=1) should appear
            $this->assertStringContainsString(self::TEST_CODE_IMPORTANT, $tableText, 'Important code should appear with filter');
            // Normal code (financial_reporting=0) should NOT appear
            $this->assertStringNotContainsString(self::TEST_CODE_NORMAL, $tableText, 'Normal code should be filtered out');
        } catch (\Throwable $e) {
            $this->client->quit();
            $this->cleanUpFixtureData();
            throw $e;
        }
        $this->client->quit();
        $this->cleanUpFixtureData();
    }

    /**
     * Verify that duplicate modifier rows in the codes table do NOT
     * inflate report totals. This is the core bug fix.
     */
    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testDuplicateModifiersDoNotInflateTotals(): void
    {
        $this->insertFixtureData();
        // Add extra modifier variants that would inflate totals with the old query
        $this->insertCode(self::TEST_CODE_IMPORTANT, 1, '26', 0);
        $this->insertCode(self::TEST_CODE_IMPORTANT, 1, 'TC', 0);

        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->goToMainMenuLink('Reports||Financial||Financial Summary by Service Code');
            $this->assertActiveTab('Financial Summary by Service Code');

            $this->switchToIFrame(self::REPORT_IFRAME);
            $this->submitReportForm(self::TEST_DATE, self::TEST_DATE);

            $this->crawler = $this->client->refreshCrawler();

            // Extract rows from the report table
            $rows = $this->crawler->filterXPath('//table[@id="mymaintable"]//tr[td]');
            $found = false;
            for ($i = 0; $i < $rows->count(); $i++) {
                $cells = $rows->eq($i)->filterXPath('.//td');
                $code = trim($cells->eq(0)->text());
                if ($code === self::TEST_CODE_IMPORTANT) {
                    $found = true;
                    $billed = trim($cells->eq(2)->text());
                    $paid = trim($cells->eq(3)->text());
                    // Without the fix these would be tripled (3 modifier rows x original values)
                    $this->assertSame('250.00', $billed, 'Billed should be 250.00, not inflated by duplicate modifiers');
                    $this->assertSame('180.00', $paid, 'Paid should be 180.00, not inflated by duplicate modifiers');
                    break;
                }
            }
            $this->assertTrue($found, 'Expected to find code ' . self::TEST_CODE_IMPORTANT . ' in results');
        } catch (\Throwable $e) {
            $this->client->quit();
            $this->cleanUpFixtureData();
            throw $e;
        }
        $this->client->quit();
        $this->cleanUpFixtureData();
    }

    // -------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------

    private function submitReportForm(string $fromDate, string $toDate): void
    {
        // Set date fields via WebDriver (visible text inputs)
        $fromInput = $this->crawler->filter('#form_from_date')->getElement(0);
        $fromInput->clear();
        $fromInput->sendKeys($fromDate);
        $toInput = $this->crawler->filter('#form_to_date')->getElement(0);
        $toInput->clear();
        $toInput->sendKeys($toDate);

        // Set hidden fields and submit via JavaScript since WebDriver
        // cannot sendKeys to hidden inputs
        $this->client->executeScript(
            'document.getElementById("form_refresh").value = "true";'
            . 'document.getElementById("form_csvexport").value = "";'
            . 'document.getElementById("theform").submit();'
        );
        $this->client->waitFor('body');
        $this->crawler = $this->client->refreshCrawler();
    }

    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        QueryUtils::sqlInsert("INSERT INTO facility (name) VALUES (?)", ['e2e-test-svc-report']);
        /** @var array{id: int}|false $row */
        $row = QueryUtils::querySingleRow("SELECT id FROM facility WHERE name = ?", ['e2e-test-svc-report']);
        $facilityId = $row ? (int) $row['id'] : 0;

        QueryUtils::sqlInsert(
            "INSERT INTO form_encounter (pid, encounter, date, facility_id, reason) VALUES (?, ?, ?, ?, ?)",
            [self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_DATE . ' 12:00:00', $facilityId, 'e2e-test-svc-code-report']
        );

        // Important code: financial_reporting=1, units=2, fee=250, paid=180, adj=30
        QueryUtils::sqlInsert(
            "INSERT INTO billing (pid, encounter, code, code_type, units, fee, provider_id, activity) VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
            [self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_IMPORTANT, 'CPT4', 2, 250.00, 1]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, payer_type, post_time, post_user, session_id, pay_amount, adj_amount, modified_time, follow_up, account_code) VALUES (?, ?, 1, '', ?, 0, NOW(), 1, 0, ?, ?, NOW(), '', '')",
            [self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_IMPORTANT, 180.00, 30.00]
        );
        $this->insertCode(self::TEST_CODE_IMPORTANT, 1, '', 1);

        // Normal code: financial_reporting=0, units=1, fee=75, paid=60, adj=10
        QueryUtils::sqlInsert(
            "INSERT INTO billing (pid, encounter, code, code_type, units, fee, provider_id, activity) VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
            [self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_NORMAL, 'CPT4', 1, 75.00, 1]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, payer_type, post_time, post_user, session_id, pay_amount, adj_amount, modified_time, follow_up, account_code) VALUES (?, ?, 2, '', ?, 0, NOW(), 1, 0, ?, ?, NOW(), '', '')",
            [self::TEST_PID, self::TEST_ENCOUNTER, self::TEST_CODE_NORMAL, 60.00, 10.00]
        );
        $this->insertCode(self::TEST_CODE_NORMAL, 1, '', 0);
    }

    private function insertCode(string $code, int $codeType, string $modifier, int $financialReporting): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO codes (code, code_type, modifier, financial_reporting, code_text) VALUES (?, ?, ?, ?, ?)",
            [$code, $codeType, $modifier, $financialReporting, 'e2e-test-svc-code-report']
        );
    }

    private function cleanUpFixtureData(): void
    {
        QueryUtils::fetchRecordsNoLog("DELETE FROM ar_activity WHERE pid = ? AND encounter = ?", [self::TEST_PID, self::TEST_ENCOUNTER]);
        QueryUtils::fetchRecordsNoLog("DELETE FROM billing WHERE pid = ? AND encounter = ?", [self::TEST_PID, self::TEST_ENCOUNTER]);
        QueryUtils::fetchRecordsNoLog("DELETE FROM form_encounter WHERE reason = 'e2e-test-svc-code-report'", []);
        QueryUtils::fetchRecordsNoLog("DELETE FROM codes WHERE code_text = 'e2e-test-svc-code-report'", []);
        QueryUtils::fetchRecordsNoLog("DELETE FROM facility WHERE name = 'e2e-test-svc-report'", []);
    }
}
