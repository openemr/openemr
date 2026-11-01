<?php

/**
 * A procedure order's reason description: saved from the field the reason
 * row posts, shown again when the order is opened, and kept by a save that
 * changes nothing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\ProcedureOrder;

use DOMDocument;
use DOMElement;
use DOMXPath;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\ReasonStatusCodes;
use PHPUnit\Framework\TestCase;

final class ProcedureOrderReasonDescriptionTest extends TestCase
{
    private const MARKER = 'test-fixture-procedure-reason-description';
    private const REASON_CODE = 'ICD10:J45.909';
    private const REASON_TEXT = 'Unspecified asthma, uncomplicated';

    private int $orderId = 0;
    private int $procedureTypeId = 0;

    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 4) . '/interface/forms/procedure_order/procedure_order_save_functions.php';
    }

    protected function setUp(): void
    {
        $this->procedureTypeId = QueryUtils::sqlInsert(
            "INSERT INTO procedure_type (name, procedure_code, procedure_type, notes) VALUES (?, 'TPRD1', 'ord', ?)",
            ['Reason description test', self::MARKER]
        );
        $this->orderId = QueryUtils::sqlInsert(
            'INSERT INTO procedure_order (patient_id, clinical_hx) VALUES (0, ?)',
            [self::MARKER]
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order_code WHERE procedure_order_id = ?', [$this->orderId]);
        QueryUtils::sqlStatementThrowException('DELETE FROM procedure_order WHERE clinical_hx = ?', [self::MARKER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM procedure_type WHERE notes = ?', [self::MARKER]);
        $this->assertSame([], QueryUtils::fetchRecords(
            'SELECT procedure_order_id FROM procedure_order_code WHERE procedure_order_id = ?'
            . ' UNION ALL SELECT procedure_order_id FROM procedure_order WHERE clinical_hx = ?'
            . ' UNION ALL SELECT procedure_type_id FROM procedure_type WHERE notes = ?',
            [$this->orderId, self::MARKER, self::MARKER]
        ));
    }

    public function testTheDescriptionSurvivesSavingOpeningAndSavingUnchanged(): void
    {
        // A reason code picked on a new line: the reason row posts its description.
        saveProcedureOrderCodes($this->orderId, $this->post('', self::REASON_CODE, self::REASON_TEXT));
        $row = $this->savedRow();
        $this->assertSame(self::REASON_TEXT, $row['reason_description']);

        // Opened again, the reason row shows the description and carries it in the field it posts.
        $xpath = new DOMXPath($this->parse($this->renderReasonRow($row)));
        $posted = self::element($xpath, '//input[@name="form_proc_reason_code_text[0]"]')->getAttribute('value');
        $this->assertSame(self::REASON_TEXT, $posted);
        $shown = self::element($xpath, '//p[contains(@class, "code-selector-text-display")]');
        $this->assertSame(self::REASON_TEXT, trim($shown->textContent));
        $this->assertStringNotContainsString('d-none', $shown->getAttribute('class'));

        // Saved with nothing changed, the description stays.
        saveProcedureOrderCodes($this->orderId, $this->post(self::seq($row), self::REASON_CODE, $posted));
        $this->assertSame(self::REASON_TEXT, $this->savedRow()['reason_description']);
    }

    public function testClearingTheReasonCodeStillClearsTheDescription(): void
    {
        saveProcedureOrderCodes($this->orderId, $this->post('', self::REASON_CODE, self::REASON_TEXT));
        $seq = self::seq($this->savedRow());

        saveProcedureOrderCodes($this->orderId, $this->post($seq, '', self::REASON_TEXT));
        $this->assertNull($this->savedRow()['reason_description']);
    }

    public function testTheReasonRowShowsEverySavedField(): void
    {
        $xpath = new DOMXPath($this->parse($this->renderReasonRow([
            'reason_code' => self::REASON_CODE,
            'reason_description' => self::REASON_TEXT,
            'reason_status' => 'pending',
            'reason_date_low' => '2026-01-02 03:04:00',
            'reason_date_high' => null,
        ])));

        $this->assertStringNotContainsString('d-none', self::element($xpath, '//tr')->getAttribute('class'));
        $code = self::element($xpath, '//input[@name="form_proc_reason_code[0]"]');
        $this->assertSame(self::REASON_CODE, $code->getAttribute('value'));
        // Picked from the code popup only, so the code can't be typed over and saved with another code's description.
        $this->assertSame('this.blur()', $code->getAttribute('onfocus'));
        $this->assertSame('pending', self::element($xpath, '//select[@name="form_proc_reason_status[0]"]/option[@selected]')->getAttribute('value'));
        $this->assertSame('2026-01-02 03:04:00', self::element($xpath, '//input[@name="form_proc_reason_date_low[0]"]')->getAttribute('value'));
        $this->assertSame('', self::element($xpath, '//input[@name="form_proc_reason_date_high[0]"]')->getAttribute('value'));
    }

    public function testANewLineHasAnEmptyHiddenReasonRow(): void
    {
        // The order form includes the row for its new-line template with $oprow left false.
        $xpath = new DOMXPath($this->parse($this->renderReasonRow(false)));

        $this->assertStringContainsString('d-none', self::element($xpath, '//tr')->getAttribute('class'));
        $this->assertSame('', self::element($xpath, '//input[@name="form_proc_reason_code[0]"]')->getAttribute('value'));
        $this->assertSame('', self::element($xpath, '//input[@name="form_proc_reason_code_text[0]"]')->getAttribute('value'));
        $this->assertStringContainsString('d-none', self::element($xpath, '//p[contains(@class, "code-selector-text-display")]')->getAttribute('class'));
        $this->assertSame('', self::element($xpath, '//select[@name="form_proc_reason_status[0]"]/option[@selected]')->getAttribute('value'));
    }

    /**
     * The fields the order form posts for one line.
     *
     * @return array<string, list<string>>
     */
    private function post(string $seq, string $reasonCode, string $reasonText): array
    {
        return [
            'form_proc_type' => [(string) $this->procedureTypeId],
            'form_proc_order_seq' => [$seq],
            'form_proc_type_diag' => [''],
            'form_proc_order_title' => ['Procedure'],
            'form_transport' => [''],
            'form_procedure_type' => ['laboratory_test'],
            'form_proc_reason_code' => [$reasonCode],
            'form_proc_reason_code_text' => [$reasonText],
            'form_proc_reason_status' => [''],
            'form_proc_reason_date_low' => [''],
            'form_proc_reason_date_high' => [''],
        ];
    }

    /**
     * The line as the order form reads it back.
     *
     * @return array<mixed>
     */
    private function savedRow(): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT procedure_order_seq, reason_code, reason_description, reason_status, reason_date_low, reason_date_high'
            . ' FROM procedure_order_code WHERE procedure_order_id = ?',
            [$this->orderId]
        );
        $this->assertCount(1, $rows);
        return $rows[0];
    }

    /**
     * @param array<mixed> $row
     */
    private static function seq(array $row): string
    {
        $seq = $row['procedure_order_seq'] ?? null;
        if (!is_int($seq) && !is_string($seq)) {
            self::fail('The line has no procedure_order_seq');
        }
        return (string) $seq;
    }

    private static function element(DOMXPath $xpath, string $query): DOMElement
    {
        $nodes = $xpath->query($query);
        $node = $nodes === false ? null : $nodes->item(0);
        self::assertInstanceOf(DOMElement::class, $node, $query);
        return $node;
    }

    /**
     * The reason row as the order form includes it, with the status list it passes.
     *
     * @param array<mixed>|false $oprow
     */
    private function renderReasonRow(array|false $oprow): string
    {
        $i = 0;
        $reasonCodeStatii = ReasonStatusCodes::getCodesWithDescriptions();
        ob_start();
        include dirname(__DIR__, 4) . '/interface/forms/procedure_order/templates/procedure_reason_row.php';
        return (string) ob_get_clean();
    }

    private function parse(string $html): DOMDocument
    {
        $doc = new DOMDocument();
        $doc->loadHTML('<!DOCTYPE html><html><body><table>' . $html . '</table></body></html>', LIBXML_NOERROR);
        return $doc;
    }
}
