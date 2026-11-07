<?php

/**
 * E2e test: Reports > Financial > Pat Ledger names an insurer payment's payer
 * from the ledger's patient, not from the chart open in the session.
 *
 * The fixture is two patients, each with their own primary insurer. The
 * first one's chart is opened; the ledger is then run for the second, whose
 * visit has an insurer payment. The payment's payer must be the second
 * patient's insurer.
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
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsPatientOpenTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class PatientLedgerPayerTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-ledger-payer';
    private const VISIT_DATE = '2025-04-17';
    private const CHART_FNAME = 'Ann';
    private const CHART_LNAME = 'Ledgerchart';
    private const REPORT_FNAME = 'Ben';
    private const REPORT_LNAME = 'Ledgerreport';
    private const CHART_INSURER = 'E2E Ledger Chart Plan';
    private const REPORT_INSURER = 'E2E Ledger Report Plan';

    /**
     * XPath to the report iframe. Reports open in a frame named 'rep'.
     */
    private const REPORT_IFRAME = "//*[@id='framesDisplay']//iframe[@name='rep']";

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testInsurerPaymentIsNamedFromTheLedgersPatient(): void
    {
        try {
            $reportPid = $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->openChart(self::CHART_FNAME, self::CHART_LNAME);

            $this->goToMainMenuLink('Reports||Financial||Pat Ledger');
            $this->assertActiveTab('Patient Ledger by Date');
            $this->switchToIFrame(self::REPORT_IFRAME);
            $this->submitLedger($reportPid, self::REPORT_LNAME . ', ' . self::REPORT_FNAME);

            $payers = $this->paymentPayers();
            $this->assertSame(
                [self::VISIT_DATE . ' / ' . self::REPORT_INSURER],
                $payers,
                "The insurer payment is named from the ledger's patient's insurance, not the open chart's"
            );
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

    /**
     * Opens a patient's chart through the search box, as PatientOpenTrait does.
     */
    private function openChart(string $firstname, string $lastname): void
    {
        $this->client->waitFor(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $searchForm = $this->crawler->filterXPath(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_FORM_PATIENTOPEN_TRAIT)->form();
        $searchForm['anySearchBox'] = $lastname;
        $this->client->waitFor(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath(XpathsConstantsPatientOpenTrait::ANYSEARCHBOX_CLICK_PATIENTOPEN_TRAIT)->click();

        $this->client->waitFor(XpathsConstants::PATIENT_FINDER_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_FINDER_IFRAME);
        $link = '//a[text()="' . $lastname . ', ' . $firstname . '"]';
        $this->client->waitFor($link);
        $this->crawler = $this->client->refreshCrawler();
        $this->crawler->filterXPath($link)->click();

        $this->client->switchTo()->defaultContent();
        $this->client->waitFor(XpathsConstants::PATIENT_IFRAME);
        $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
        $this->client->waitFor('//*[text()="Medical Record Dashboard - ' . $firstname . ' ' . $lastname . '"]');
    }

    private function submitLedger(int $pid, string $name): void
    {
        // The page being left; once it is stale the next one has replaced it.
        $leaving = $this->client->findElement(WebDriverBy::tagName('body'));
        $this->client->executeScript(
            'const f = document.getElementById("theform");'
            . ' f.form_pid.value = arguments[0];'
            . ' f.form_patient.value = arguments[1];'
            . ' f.form_from_date.value = arguments[2];'
            . ' f.form_to_date.value = arguments[2];'
            . ' f.form_refresh.value = "true";'
            . ' f.submit();',
            [(string) $pid, $name, self::VISIT_DATE]
        );
        $this->client->wait(30)->until(
            WebDriverExpectedCondition::stalenessOf($leaving),
            'The ledger did not reload within 30 seconds.'
        );
        $this->client->waitFor('#report_results', 30);
        $this->crawler = $this->client->refreshCrawler();
    }

    /**
     * The "date / payer" cell of each payment row, dated by the fixture's visit.
     *
     * @return list<string>
     */
    private function paymentPayers(): array
    {
        // Core\ErrorHandler appends this to a page that has already started when an exception escapes.
        $page = $this->client->executeScript('return document.body.textContent;');
        $this->assertIsString($page);
        $this->assertFalse(str_contains($page, 'An error has occurred'), 'The ledger stopped: the page says "An error has occurred."');
        $cells = $this->client->executeScript(
            'return Array.from(document.querySelectorAll("#report_results td"))'
            . '.map(c => c.textContent.replace(/\s+/g, " ").trim());'
        );
        $this->assertIsArray($cells);
        $payers = [];
        foreach ($cells as $cell) {
            $this->assertIsString($cell);
            if (str_starts_with($cell, self::VISIT_DATE . ' / ')) {
                $payers[] = $cell;
            }
        }
        return $payers;
    }

    private function insertFixtureData(): int
    {
        $this->cleanUpFixtureData();

        $chartPid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        $reportPid = $chartPid + 1;
        $chartInsurer = $this->insertInsurer(self::CHART_INSURER);
        $reportInsurer = $this->insertInsurer(self::REPORT_INSURER);
        $this->insertPatient($chartPid, self::CHART_FNAME, self::CHART_LNAME, $chartInsurer);
        $this->insertPatient($reportPid, self::REPORT_FNAME, self::REPORT_LNAME, $reportInsurer);

        $encounter = $this->toInt(QueryUtils::generateId());
        $formId = QueryUtils::sqlInsert(
            'INSERT INTO form_encounter (date, reason, pid, encounter, provider_id) VALUES (?, ?, ?, ?, 1)',
            [self::VISIT_DATE . ' 09:00:00', self::FIXTURE_MARKER, $reportPid, $encounter]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, formdir)"
            . " VALUES (?, ?, 'New Patient Encounter', ?, ?, 'admin', 'Default', 1, 'newpatient')",
            [self::VISIT_DATE . ' 09:00:00', $encounter, $formId, $reportPid]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO billing (date, code_type, code, pid, provider_id, encounter, code_text, billed, activity, units, fee, payer_id)"
            . " VALUES (?, 'CPT4', '99213', ?, 1, ?, ?, 1, 1, 1, '100.00', ?)",
            [self::VISIT_DATE . ' 09:00:00', $reportPid, $encounter, self::FIXTURE_MARKER, $reportInsurer]
        );
        // The primary insurer's payment on the visit, as posting from an EOB records it.
        $sessionId = QueryUtils::sqlInsert(
            "INSERT INTO ar_session (payer_id, user_id, reference, check_date, deposit_date, pay_total, modified_time,"
            . " global_amount, payment_type, description, adjustment_code, post_to_date, patient_id, payment_method)"
            . " VALUES (?, 1, 'E2E-LEDGER-EOB', ?, ?, '60.00', NOW(), 0, 'insurance', ?, 'insurance_payment', ?, 0, 'check_payment')",
            [$reportInsurer, self::VISIT_DATE, self::VISIT_DATE, self::FIXTURE_MARKER, self::VISIT_DATE]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user,"
            . " session_id, memo, pay_amount, adj_amount, modified_time, follow_up, follow_up_note, account_code)"
            . " VALUES (?, ?, 1, 'CPT4', '99213', '', 1, ?, 1, ?, '', '60.00', '0.00', NOW(), '', '', 'IPP')",
            [$reportPid, $encounter, self::VISIT_DATE . ' 10:00:00', $sessionId]
        );

        return $reportPid;
    }

    private function insertInsurer(string $name): int
    {
        $id = $this->toInt(QueryUtils::generateId());
        QueryUtils::sqlInsert('INSERT INTO insurance_companies (id, name) VALUES (?, ?)', [$id, $name]);
        return $id;
    }

    private function insertPatient(int $pid, string $fname, string $lname, int $insurer): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$pid, self::FIXTURE_MARKER, $fname, $lname, '1980-01-01', 'Female']
        );
        QueryUtils::sqlInsert(
            "INSERT INTO insurance_data (type, provider, plan_name, policy_number, date, pid, subscriber_lname, subscriber_fname,"
            . " subscriber_relationship) VALUES ('primary', ?, ?, ?, '2025-01-01', ?, ?, ?, 'self')",
            [$insurer, self::FIXTURE_MARKER, 'E2E-' . $pid, $pid, $lname, $fname]
        );
    }

    private function cleanUpFixtureData(): void
    {
        $pids = QueryUtils::fetchTableColumn('SELECT pid FROM patient_data WHERE pubpid = ?', 'pid', [self::FIXTURE_MARKER]);
        foreach ($pids as $pid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException("DELETE FROM forms WHERE pid = ? AND formdir = 'newpatient'", [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [$pid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_session WHERE description = ?', [self::FIXTURE_MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE reason = ?', [self::FIXTURE_MARKER]);
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM insurance_companies WHERE name IN (?, ?)',
            [self::CHART_INSURER, self::REPORT_INSURER]
        );
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
