<?php

/**
 * E2e test: Reports > Inventory > Activity runs.
 *
 * inventoryActivityLineItem() took the warehouse as an int, but warehouse ids
 * are list option ids ('onsite', '' for none) and the report's closing totals
 * pass '~', so every Submit stopped with "An error has occurred". Its
 * warehouse name, sale date and invoice number come through LEFT JOINs and
 * are null for a lot in no warehouse and a lot with no sale. The fixture is
 * one product with a lot On Site that sold 2 to a patient and a lot in no
 * warehouse with nothing sold, destroyed the same day.
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

class InventoryActivityReportTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;

    private const FIXTURE_MARKER = 'e2e-inventory-activity';
    private const PRODUCT_NAME = 'E2E Activity Product';
    private const SALE_DATE = '2025-04-02';

    private int $drugId = 0;
    private int $pid = 0;
    private int $encounter = 0;

    #[Test]
    #[Depends('testLoginAuthorized')]
    public function testActivityReportListsLotsInAndOutOfWarehouses(): void
    {
        try {
            $this->insertFixtureData();
            $this->base();
            $this->login(LoginTestData::username, LoginTestData::password);
            $this->client->request('GET', '/interface/reports/inventory_activity.php');
            $this->client->waitFor('#report_parameters', 30);

            // By product, with details, for the fixture's product and the sale's day.
            $leaving = $this->client->findElement(WebDriverBy::tagName('body'));
            $this->client->executeScript(
                'const f = document.forms[0];'
                . ' f.form_by.value = "p";'
                . ' f.form_from_date.value = arguments[0];'
                . ' f.form_to_date.value = arguments[0];'
                . ' f.form_product.value = arguments[1];'
                . ' f.form_details.checked = true;'
                . ' f.form_action.value = "submit";'
                . ' f.submit();',
                [self::SALE_DATE, (string) $this->drugId]
            );
            $this->client->wait(30)->until(
                WebDriverExpectedCondition::stalenessOf($leaving),
                'The report page did not reload within 30 seconds.'
            );
            $this->client->waitFor('#report_results', 30);

            $page = $this->client->executeScript('return document.body.textContent;');
            $this->assertIsString($page);
            $this->assertFalse(str_contains($page, 'An error has occurred'), 'The report stopped: the page says "An error has occurred."');

            $rows = $this->reportRows();
            $this->assertContains(
                [self::SALE_DATE, $this->pid . '.' . $this->encounter, '-2'],
                array_map(static fn(array $row): array => [$row[2] ?? '', $row[3] ?? '', $row[5] ?? ''], $rows),
                'The sale is listed with its date, invoice and quantity'
            );
            $this->assertContains(
                [self::SALE_DATE, 'Destroyed', '-5'],
                array_map(static fn(array $row): array => [$row[2] ?? '', $row[3] ?? '', $row[9] ?? ''], $rows),
                'The lot destroyed in no warehouse is listed as an adjustment'
            );
            $labels = array_merge(...array_map(static fn(array $row): array => array_slice($row, 0, 2), $rows));
            $this->assertContains('Total for On Site', $labels, 'The On Site lot is totalled under its warehouse');
            $this->assertContains('Total for None', $labels, 'The lot in no warehouse is totalled under None');
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
     * The result table's body rows, each a list of its cells' text.
     *
     * @return list<list<string>>
     */
    private function reportRows(): array
    {
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

    private function insertFixtureData(): void
    {
        $this->cleanUpFixtureData();

        $this->pid = $this->toInt(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(pid), 0) + 1 AS pid FROM patient_data', 'pid'));
        $this->encounter = $this->toInt(QueryUtils::generateId());
        QueryUtils::sqlInsert(
            'INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$this->pid, self::FIXTURE_MARKER, 'Ivy', 'Activitytest', '1980-01-01', 'Female']
        );
        QueryUtils::sqlInsert(
            'INSERT INTO form_encounter (date, reason, pid, encounter, provider_id) VALUES (?, ?, ?, ?, 1)',
            [self::SALE_DATE . ' 09:00:00', self::FIXTURE_MARKER, $this->pid, $this->encounter]
        );
        $this->drugId = QueryUtils::sqlInsert('INSERT INTO drugs (name, active) VALUES (?, 1)', [self::PRODUCT_NAME]);
        // A lot On Site with 8 left after selling 2, and a lot in no warehouse ('') with 5,
        // no sale, destroyed that day.
        $onSiteLot = QueryUtils::sqlInsert(
            "INSERT INTO drug_inventory (drug_id, lot_number, on_hand, warehouse_id) VALUES (?, 'E2E-LOT-1', 8, 'onsite')",
            [$this->drugId]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO drug_inventory (drug_id, lot_number, on_hand, warehouse_id, destroy_date) VALUES (?, 'E2E-LOT-2', 5, '', ?)",
            [$this->drugId, self::SALE_DATE]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO drug_sales (drug_id, inventory_id, pid, encounter, sale_date, quantity, fee, trans_type)"
            . " VALUES (?, ?, ?, ?, ?, 2, '20.00', 1)",
            [$this->drugId, $onSiteLot, $this->pid, $this->encounter, self::SALE_DATE]
        );
    }

    private function cleanUpFixtureData(): void
    {
        $drugIds = QueryUtils::fetchTableColumn('SELECT drug_id FROM drugs WHERE name = ?', 'drug_id', [self::PRODUCT_NAME]);
        foreach ($drugIds as $drugId) {
            QueryUtils::sqlStatementThrowException('DELETE FROM drug_sales WHERE drug_id = ?', [$drugId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM drug_inventory WHERE drug_id = ?', [$drugId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM drugs WHERE drug_id = ?', [$drugId]);
        }
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
