<?php

/**
 * E2e test: the printed fee sheet (Popups > Superbill, fill=1) lists the
 * patient's insurers with a comma between two of them, and none before
 * the first.
 *
 * The fixture is two patients: one whose only policy is a secondary, and
 * one with a primary and a secondary.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstants;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsPatientOpenTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class PrintedFeeSheetInsuranceTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-fee-sheet-insurance';
    private const FNAME = 'Cy';
    private const SECONDARY_ONLY_LNAME = 'Feesheetsecondary';
    private const BOTH_LNAME = 'Feesheetboth';
    private const PRIMARY_INSURER = 'E2E Fee Sheet Primary';
    private const SECONDARY_INSURER = 'E2E Fee Sheet Secondary';

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testInsurersAreSeparatedWithoutALeadingComma(): void
    {
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);

            $this->openChart(self::FNAME, self::SECONDARY_ONLY_LNAME);
            $this->assertSame(
                ['Insurance: ' . self::SECONDARY_INSURER],
                $this->printedInsuranceLines(),
                'A sole secondary is listed with no comma before it'
            );

            $this->openChart(self::FNAME, self::BOTH_LNAME);
            $this->assertSame(
                ['Insurance: ' . self::PRIMARY_INSURER . ', ' . self::SECONDARY_INSURER],
                $this->printedInsuranceLines(),
                'A primary and a secondary are separated by a comma'
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
        $this->client->switchTo()->defaultContent();
    }

    /**
     * The printed fee sheet for the open chart, as Popups > Superbill opens
     * it (here in the chart's frame, so the main window stays open): the
     * distinct "Insurance:" lines of its pages.
     *
     * @return list<string>
     */
    private function printedInsuranceLines(): array
    {
        $this->switchToIFrame(XpathsConstants::PATIENT_IFRAME);
        $this->client->executeScript(
            'window.location.href = arguments[0];',
            ['../../patient_file/printed_fee_sheet.php?fill=1']
        );
        $this->client->waitFor('#printbutton', 30);
        $page = $this->client->executeScript('return document.body.textContent;');
        $this->assertIsString($page);
        $this->assertFalse(str_contains($page, 'An error has occurred'), 'The fee sheet stopped: the page says "An error has occurred."');
        $cells = $this->client->executeScript(
            'return Array.from(document.querySelectorAll("td.fshead"))'
            . '.map(c => c.textContent.replace(/\s+/g, " ").trim())'
            . '.filter(t => t.startsWith("Insurance:"));'
        );
        $this->assertIsArray($cells);
        $this->assertNotEmpty($cells, 'The fee sheet has an Insurance line');
        $lines = [];
        foreach ($cells as $cell) {
            $this->assertIsString($cell);
            $lines[] = $cell;
        }
        $this->client->switchTo()->defaultContent();
        return array_values(array_unique($lines));
    }

    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        $pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        $primary = $this->insertInsurer(self::PRIMARY_INSURER);
        $secondary = $this->insertInsurer(self::SECONDARY_INSURER);

        $this->insertPatient($pid, self::SECONDARY_ONLY_LNAME);
        $this->insertPolicy($pid, 'secondary', $secondary, self::SECONDARY_ONLY_LNAME);

        $this->insertPatient($pid + 1, self::BOTH_LNAME);
        $this->insertPolicy($pid + 1, 'primary', $primary, self::BOTH_LNAME);
        $this->insertPolicy($pid + 1, 'secondary', $secondary, self::BOTH_LNAME);
    }

    private function insertInsurer(string $name): int
    {
        $id = $this->toInt(QueryUtils::generateId());
        QueryUtils::sqlInsert('INSERT INTO insurance_companies (id, name) VALUES (?, ?)', [$id, $name]);
        return $id;
    }

    private function insertPatient(int $pid, string $lname): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$pid, self::FIXTURE_MARKER, self::FNAME, $lname, '1980-01-01', 'Male']
        );
    }

    private function insertPolicy(int $pid, string $type, int $insurer, string $lname): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO insurance_data (type, provider, plan_name, policy_number, date, pid, subscriber_lname, subscriber_fname,"
            . " subscriber_relationship) VALUES (?, ?, ?, ?, '2025-01-01', ?, ?, ?, 'self')",
            [$type, $insurer, self::FIXTURE_MARKER, 'E2E-' . $pid . '-' . $type, $pid, $lname, self::FNAME]
        );
    }

    private function cleanUpFixtureData(): void
    {
        $pids = QueryUtils::fetchTableColumn('SELECT pid FROM patient_data WHERE pubpid = ?', 'pid', [self::FIXTURE_MARKER]);
        foreach ($pids as $pid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [$pid]);
        }
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM insurance_companies WHERE name IN (?, ?)',
            [self::PRIMARY_INSURER, self::SECONDARY_INSURER]
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
