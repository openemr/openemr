<?php

/**
 * E2e test: the checkout receipt (pos_checkout.php?enc=) lists lines whose
 * values arrive as null.
 *
 * The receipt's line functions take typed parameters, and these lines handed
 * them null, so the receipt stopped with "An error has occurred":
 *  - a product sale whose product an administrator deleted (the drug editor
 *    deletes the product but keeps its sales): the name is null;
 *  - a line the Eye Exam form coded with no price on file: its fee is null.
 * The IPPF receipt (Specific Application: IPPF) also passed its running totals,
 * an array, to a string parameter, so it stopped at its first line; and a line
 * with no charge category, an adjustment with no posting date, a line marked
 * billed in the Billing Manager (no bill date) and a tax that is no longer an
 * active tax rate each arrive as null; its totals are checked against the lines.
 * The IPPF page also never loaded FeeSheet, which its product lines call, so a
 * receipt with a product sale stopped before reaching them.
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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class CheckoutReceiptTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-checkout-receipt';
    private const VISIT_DATE = '2025-04-09';
    private const PRODUCT_NAME = 'E2E Receipt Product';

    private int $pid = 0;
    private int $encounter = 0;
    private bool $ippfSet = false;
    private ?string $savedApplication = null;

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testReceiptListsASaleOfADeletedProduct(): void
    {
        try {
            $this->insertVisit();
            $this->addBillingLine('CPT4', '99213', 'Office visit', '100.00', 1);
            // Sold, then the product deleted as the drug editor's Delete does (its sales are kept).
            $drugId = QueryUtils::sqlInsert('INSERT INTO drugs (name, active) VALUES (?, 1)', [self::PRODUCT_NAME]);
            QueryUtils::sqlInsert(
                "INSERT INTO drug_sales (drug_id, inventory_id, pid, encounter, sale_date, quantity, fee, trans_type)"
                . " VALUES (?, 0, ?, ?, ?, 1, '15.00', 1)",
                [$drugId, $this->pid, $this->encounter, self::VISIT_DATE]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM drugs WHERE drug_id = ?', [$drugId]);

            $this->receiptPage();
            $rows = $this->receiptRows();
            $this->assertContains(['', '15.00', '1', '15.00'], array_map(self::lastFour(...), $rows), "The deleted product's sale is listed, unnamed");
            $this->assertContains(['Office visit', '100.00', '1', '100.00'], array_map(self::lastFour(...), $rows));
            $this->assertTotalCharges('115.00', $rows);
        } finally {
            $this->quitAndCleanUp();
        }
    }

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testReceiptListsALineCodedWithNoPrice(): void
    {
        try {
            $this->insertVisit();
            $this->addBillingLine('CPT4', '99213', 'Office visit', '100.00', 1);
            // As the Eye Exam form's coding saves a code it finds no price for: no fee and no units.
            $this->addBillingLine('ICD10', 'H52.4', 'Presbyopia', null, null);

            $this->receiptPage();
            $rows = $this->receiptRows();
            $this->assertContains(['Presbyopia', '0.00', '1', '0.00'], array_map(self::lastFour(...), $rows), 'The unpriced line is listed at 0.00');
            $this->assertContains(['Office visit', '100.00', '1', '100.00'], array_map(self::lastFour(...), $rows));
            $this->assertTotalCharges('100.00', $rows);
        } finally {
            $this->quitAndCleanUp();
        }
    }

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testIppfReceiptListsItsLines(): void
    {
        try {
            $this->insertVisit();
            $this->useIppf();
            // A product sale marked billed with no bill date, as the normal checkout's Save leaves one;
            // like every line here it has no charge category, so the category's title is null.
            $drugId = QueryUtils::sqlInsert('INSERT INTO drugs (name, active) VALUES (?, 1)', [self::PRODUCT_NAME]);
            QueryUtils::sqlInsert(
                "INSERT INTO drug_sales (drug_id, inventory_id, pid, encounter, sale_date, quantity, fee, billed, trans_type)"
                . " VALUES (?, 0, ?, ?, ?, 1, '15.00', 1, 1)",
                [$drugId, $this->pid, $this->encounter, self::VISIT_DATE]
            );
            $this->addBillingLine('CPT4', '99213', 'Office visit', '100.00', 1);
            // Marked billed in the Billing Manager, which leaves no bill date.
            $billed = $this->addBillingLine('CPT4', '99000', 'Specimen handling', '5.00', 1);
            QueryUtils::sqlStatementThrowException('UPDATE billing SET billed = 1 WHERE id = ?', [$billed]);
            // As the Eye Exam form's coding saves a procedure code it finds no codes row for.
            $this->addBillingLine('CPT4', '92060', 'Sensorimotor exam', null, null);
            // A tax charged under a rate that is no longer an active tax rate.
            $this->addBillingLine('TAX', 'e2e-old-tax', 'Old tax', '2.00', 1);
            // An adjustment posted from Posting Payments, which leaves no posting date.
            QueryUtils::sqlInsert(
                "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user,"
                . " session_id, memo, pay_amount, adj_amount, modified_time, follow_up, follow_up_note, account_code)"
                . " VALUES (?, ?, 1, 'CPT4', '99213', '', 0, ?, 1, 0, 'E2E discount', '0.00', '10.00', NOW(), '', '', 'PA')",
                [$this->pid, $this->encounter, self::VISIT_DATE . ' 10:00:00']
            );

            $page = $this->receiptPage();
            foreach ([self::PRODUCT_NAME, 'Office visit', 'Specimen handling', 'Sensorimotor exam', 'Pt Item Adjustment'] as $text) {
                $this->assertStringContainsString($text, $page);
            }
            // Five lines (15.00, 100.00, 5.00, 0.00 and the 10.00 adjustment): quantity, price,
            // adjustment column (line adjustments are on by default) and total; then the
            // unmatched 2.00 tax line, after the Sub-Total, makes Total Charges 112.00.
            $rows = $this->receiptRows();
            $this->assertContains(['Sub-Total', '5', '120.00', '', '10.00', '110.00'], $rows);
            $this->assertTotalCharges('112.00', $rows);
        } finally {
            $this->quitAndCleanUp();
        }
    }

    /**
     * The receipt with details, as its Show Details button opens it, after the
     * check that it didn't stop. Text only; receiptRows() reads its table.
     */
    private function receiptPage(): string
    {
        $this->base();
        $this->login(LoginTestData::username, LoginTestData::password);
        $this->client->request('GET', '/interface/patient_file/pos_checkout.php?details=1&ptid=' . $this->pid . '&enc=' . $this->encounter);
        $page = $this->client->executeScript('return document.body ? document.body.textContent : "";');
        $this->assertIsString($page);
        // Core\ErrorHandler appends this to a page that has already started when an exception escapes.
        $this->assertFalse(str_contains($page, 'An error has occurred'), 'The receipt stopped: the page says "An error has occurred."');
        return $page;
    }

    /**
     * @return list<list<string>>
     */
    private function receiptRows(): array
    {
        $rows = $this->client->executeScript(
            'return Array.from(document.querySelectorAll("table tr"))'
            . '.map(r => Array.from(r.cells).map(c => c.textContent.replace(/\s+/g, " ").trim()));'
        );
        $this->assertIsArray($rows);
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
     * @param list<string> $row
     * @return list<string>
     */
    private static function lastFour(array $row): array
    {
        return array_slice($row, -4);
    }

    /**
     * @param list<list<string>> $rows
     */
    private function assertTotalCharges(string $amount, array $rows): void
    {
        foreach ($rows as $row) {
            if (in_array('Total Charges', $row, true)) {
                $this->assertStringContainsString($amount, $row[count($row) - 1]);
                return;
            }
        }
        $this->fail('The receipt has no Total Charges line');
    }

    private function useIppf(): void
    {
        $value = QueryUtils::fetchSingleValue("SELECT gl_value FROM globals WHERE gl_name = 'specific_application' AND gl_index = 0", 'gl_value');
        $this->savedApplication = is_string($value) ? $value : null;
        $this->ippfSet = true;
        QueryUtils::sqlStatementThrowException("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('specific_application', 0, '2')");
    }

    private function insertVisit(): void
    {
        $this->cleanUpFixtureData();
        $this->pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        $this->encounter = $this->toInt(QueryUtils::generateId());
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$this->pid, self::FIXTURE_MARKER, 'Rae', 'Receipttest', '1980-01-01', 'Female']
        );
        QueryUtils::sqlInsert(
            'INSERT INTO form_encounter (date, reason, pid, encounter, provider_id, facility_id) VALUES (?, ?, ?, ?, 1, 3)',
            [self::VISIT_DATE . ' 09:00:00', self::FIXTURE_MARKER, $this->pid, $this->encounter]
        );
    }

    private function addBillingLine(string $codeType, string $code, string $text, ?string $fee, ?int $units): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO billing (date, code_type, code, pid, provider_id, encounter, code_text, billed, activity, units, fee)"
            . " VALUES (?, ?, ?, ?, 1, ?, ?, 0, 1, ?, ?)",
            [self::VISIT_DATE . ' 09:00:00', $codeType, $code, $this->pid, $this->encounter, $text, $units, $fee]
        );
    }

    private function quitAndCleanUp(): void
    {
        try {
            if (isset($this->client)) {
                $this->client->quit();
            }
        } finally {
            $this->cleanUpFixtureData();
        }
    }

    private function cleanUpFixtureData(): void
    {
        if ($this->ippfSet) {
            if ($this->savedApplication === null) {
                QueryUtils::sqlStatementThrowException("DELETE FROM globals WHERE gl_name = 'specific_application' AND gl_index = 0");
            } else {
                QueryUtils::sqlStatementThrowException(
                    "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('specific_application', 0, ?)",
                    [$this->savedApplication]
                );
            }
            $this->ippfSet = false;
            $this->savedApplication = null;
        }
        $pids = QueryUtils::fetchTableColumn('SELECT pid FROM patient_data WHERE pubpid = ?', 'pid', [self::FIXTURE_MARKER]);
        foreach ($pids as $pid) {
            foreach (['ar_activity', 'billing', 'drug_sales'] as $table) {
                QueryUtils::sqlStatementThrowException('DELETE FROM ' . $table . ' WHERE pid = ?', [$pid]);
            }
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM drugs WHERE name = ?', [self::PRODUCT_NAME]);
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
