<?php

/**
 * E2e test: Reports > Financial > Pmt Method (Receipts Summary) with a co-pay
 * in the billing table, as the Charges panel's Copay screen records one.
 *
 * The co-pay query never selected `code_text`, so the co-pay line passed null
 * to receiptsByMethodLineItem()'s string `$memo` and the page stopped with
 * "An error has occurred" for any dates that included the co-pay. The fixture
 * is one encounter with that co-pay (10.00, cash) and a patient check
 * (20.00, reference E2E-CHK-1) on the same day.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class ReceiptsByMethodReportTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-receipts-copay';
    private const VISIT_DATE = '2025-03-14';
    private const CHECK_REFERENCE = 'E2E-CHK-1';

    /**
     * XPath to the report iframe. Reports open in a frame named 'rep'.
     */
    private const REPORT_IFRAME = "//*[@id='framesDisplay']//iframe[@name='rep']";

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testReportRunsOverACopayFromTheBillingTable(): void
    {
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->goToMainMenuLink('Reports||Financial||Pmt Method');
            $this->assertActiveTab('Receipts Summary');
            $this->switchToIFrame(self::REPORT_IFRAME);

            // Report by Payment Method, with details.
            $this->submitReport('2', true);
            $rows = $this->reportRows();
            $this->assertContains(
                ['Co-Pay', '', self::VISIT_DATE, '', '10.00'],
                $this->methodRefDatePayments($rows),
                'The co-pay is listed under Co-Pay, with no reference, dated by its visit'
            );
            $this->assertContains(
                ['Check Payment', self::CHECK_REFERENCE, self::VISIT_DATE, '', '20.00'],
                $this->methodRefDatePayments($rows),
                'The check is listed as before, with its reference'
            );
            $this->assertContains(['Total for Co-Pay', '', '10.00'], $rows);
            $this->assertSame(['Grand Total', '', '30.00'], $rows[count($rows) - 1], 'The report reaches its Grand Total');

            // Report by Payer, summary only: the co-pay counts as the patient's.
            $this->submitReport('1', false);
            $rows = $this->reportRows();
            $this->assertContains(['Patient', '', '10.00'], $rows);
            $this->assertContains(['Personal pay', '', '20.00'], $rows);
            $this->assertSame(['Grand Total', '', '30.00'], $rows[count($rows) - 1], 'The report reaches its Grand Total');
        } finally {
            try {
                if (isset($this->client)) {
                    $this->client->quit();
                }
            } finally {
                $this->cleanUpFixtureData();
            }
        }
    }

    private function submitReport(string $reportBy, bool $details): void
    {
        // The page being left; once it is stale the next one has replaced it.
        $leaving = $this->client->findElement(WebDriverBy::tagName('body'));
        $this->client->executeScript(
            'const f = document.getElementById("theform");'
            . ' f.form_report_by.value = arguments[0];'
            . ' f.form_use_edate.value = "0";'
            . ' f.form_details.checked = arguments[1];'
            . ' f.form_from_date.value = arguments[2];'
            . ' f.form_to_date.value = arguments[2];'
            . ' f.form_refresh.value = "true";'
            . ' f.submit();',
            [$reportBy, $details, self::VISIT_DATE]
        );
        $this->client->wait(30)->until(
            WebDriverExpectedCondition::stalenessOf($leaving),
            'The report page did not reload within 30 seconds.'
        );
        $this->client->waitFor('#report_results', 30);
        $this->crawler = $this->client->refreshCrawler();
    }

    /**
     * The result table's rows, each a list of its cells' text.
     *
     * @return list<list<string>>
     */
    private function reportRows(): array
    {
        // Core\ErrorHandler appends this to a page that has already started when an exception escapes.
        $page = $this->client->executeScript('return document.body.textContent;');
        $this->assertIsString($page);
        $this->assertFalse(str_contains($page, 'An error has occurred'), 'The report stopped: the page says "An error has occurred."');
        $rows = $this->client->executeScript(
            'return Array.from(document.querySelectorAll("#mymaintable tbody tr"))'
            . '.map(r => Array.from(r.cells).map(c => c.textContent.replace(/\s+/g, " ").trim()));'
        );
        $this->assertIsArray($rows);
        $this->assertNotEmpty($rows, 'The report lists rows');
        $out = [];
        foreach ($rows as $row) {
            $this->assertIsArray($row);
            $cells = [];
            foreach ($row as $cell) {
                $this->assertIsString($cell);
                $cells[] = $cell;
            }
            $out[] = $cells;
        }
        return $out;
    }

    /**
     * Detail rows cut down to method, reference, date, adjustments and payments.
     *
     * @param list<list<string>> $rows
     * @return list<list<string>>
     */
    private function methodRefDatePayments(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (count($row) === 10) {
                $out[] = [$row[0], $row[1], $row[2], $row[8], $row[9]];
            }
        }
        return $out;
    }

    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        $pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        $encounter = $this->toInt(QueryUtils::generateId());
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$pid, self::FIXTURE_MARKER, 'Rex', 'Copaytest', '1980-01-01', 'Male']
        );
        $formId = QueryUtils::sqlInsert(
            'INSERT INTO form_encounter (date, reason, pid, encounter, provider_id) VALUES (?, ?, ?, ?, 1)',
            [self::VISIT_DATE . ' 09:00:00', self::FIXTURE_MARKER, $pid, $encounter]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, formdir)"
            . " VALUES (?, ?, 'New Patient Encounter', ?, ?, 'admin', 'Default', 1, 'newpatient')",
            [self::VISIT_DATE . ' 09:00:00', $encounter, $formId, $pid]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO billing (date, code_type, code, pid, provider_id, encounter, code_text, billed, activity, units, fee)"
            . " VALUES (?, 'CPT4', '99213', ?, 1, ?, ?, 0, 1, 1, '100.00')",
            [self::VISIT_DATE . ' 09:00:00', $pid, $encounter, self::FIXTURE_MARKER]
        );
        // The co-pay as the Charges panel's Copay screen records it: the amount as the code, the method of payment as the text.
        QueryUtils::sqlInsert(
            "INSERT INTO billing (date, code_type, code, pid, provider_id, encounter, code_text, billed, activity, units, fee)"
            . " VALUES (?, 'COPAY', '10.00', ?, 1, ?, 'cash', 0, 1, 1, '-10.00')",
            [self::VISIT_DATE . ' 09:00:00', $pid, $encounter]
        );
        $sessionId = QueryUtils::sqlInsert(
            "INSERT INTO ar_session (payer_id, user_id, reference, check_date, deposit_date, pay_total, modified_time,"
            . " global_amount, payment_type, description, adjustment_code, post_to_date, patient_id, payment_method)"
            . " VALUES (0, 1, ?, ?, ?, '20.00', NOW(), 0, 'patient', ?, 'patient_payment', ?, ?, 'check_payment')",
            [self::CHECK_REFERENCE, self::VISIT_DATE, self::VISIT_DATE, self::FIXTURE_MARKER, self::VISIT_DATE, $pid]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user,"
            . " session_id, memo, pay_amount, adj_amount, modified_time, follow_up, follow_up_note, account_code)"
            . " VALUES (?, ?, 1, 'CPT4', '99213', '', 0, ?, 1, ?, '', '20.00', '0.00', NOW(), '', '', 'PP')",
            [$pid, $encounter, self::VISIT_DATE . ' 10:00:00', $sessionId]
        );
    }

    private function cleanUpFixtureData(): void
    {
        $pids = QueryUtils::fetchTableColumn('SELECT pid FROM patient_data WHERE pubpid = ?', 'pid', [self::FIXTURE_MARKER]);
        foreach ($pids as $pid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM forms WHERE pid = ? AND formdir = 'newpatient'", [$pid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_session WHERE description = ?', [self::FIXTURE_MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE reason = ?', [self::FIXTURE_MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pubpid = ?', [self::FIXTURE_MARKER]);
    }

    private function toInt(mixed $value): int
    {
        if (!is_numeric($value)) {
            $this->fail('Expected a number, got ' . get_debug_type($value));
        }
        return (int) $value;
    }
}
