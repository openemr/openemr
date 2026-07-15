<?php

/**
 * Regression tests for the physical exam diagnosis editor.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Forms\PhysicalExam;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Forms\PhysicalExam\DiagnosisHelper;
use PHPUnit\Framework\TestCase;

class EditDiagnosesTest extends TestCase
{
    private const LINE_ID = 'OE12030A';

    protected function setUp(): void
    {
        parent::setUp();

        QueryUtils::sqlStatementThrowException(
            "CREATE TABLE IF NOT EXISTS form_physical_exam_diagnoses (
                line_id char(8) NOT NULL,
                ordering int(11) NOT NULL DEFAULT 0,
                diagnosis varchar(255) NOT NULL DEFAULT '',
                KEY (line_id, ordering)
            ) ENGINE=InnoDB"
        );

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'admin');
        $session->set('userauthorized', 1);
        $session->set('csrf_private_key', 'physical-exam-diagnosis-test');

        QueryUtils::sqlStatementThrowException(
            "DELETE FROM form_physical_exam_diagnoses WHERE line_id = ?",
            [self::LINE_ID]
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM form_physical_exam_diagnoses WHERE line_id = ?",
            [self::LINE_ID]
        );

        $_GET = [];
        $_POST = [];
        $_REQUEST = [];

        parent::tearDown();
    }

    public function testRenderAndSaveRoundTripPreservesPersistedDiagnosisOrdering(): void
    {
        $this->insertDiagnosis(10, 'First diagnosis');
        $this->insertDiagnosis(20, 'Second diagnosis');
        $this->insertDiagnosis(30, 'Third diagnosis');

        $output = $this->renderEditor();

        $this->assertStringContainsString("name='form_ordering[1]' value='10'", $output);
        $this->assertStringContainsString("name='form_ordering[2]' value='20'", $output);
        $this->assertStringContainsString("name='form_ordering[3]' value='30'", $output);
        $this->assertStringContainsString("name='form_ordering[4]' value='4'", $output);
        $this->assertStringContainsString("name='form_ordering[8]' value='8'", $output);

        DiagnosisHelper::save(
            self::LINE_ID,
            $this->extractInputValues($output, 'form_diagnosis'),
            $this->extractInputValues($output, 'form_ordering')
        );

        $this->assertSame(
            [
                ['ordering' => 10, 'diagnosis' => 'First diagnosis'],
                ['ordering' => 20, 'diagnosis' => 'Second diagnosis'],
                ['ordering' => 30, 'diagnosis' => 'Third diagnosis'],
            ],
            $this->fetchDiagnoses()
        );
    }

    public function testSaveFallsBackToRowNumberForInvalidOrdering(): void
    {
        DiagnosisHelper::save(
            self::LINE_ID,
            [
                1 => 'First diagnosis',
                2 => 'Second diagnosis',
            ],
            [
                1 => 'not numeric',
                2 => [],
            ]
        );

        $this->assertSame(
            [
                ['ordering' => 1, 'diagnosis' => 'First diagnosis'],
                ['ordering' => 2, 'diagnosis' => 'Second diagnosis'],
            ],
            $this->fetchDiagnoses()
        );
    }

    public function testNormalizeLineIdRejectsMalformedValues(): void
    {
        $this->assertSame(self::LINE_ID, DiagnosisHelper::normalizeLineId(self::LINE_ID));
        $this->assertSame('0', DiagnosisHelper::normalizeLineId('0'));
        $this->assertSame('42', DiagnosisHelper::normalizeLineId(42));

        $this->assertNull(DiagnosisHelper::normalizeLineId(null));
        $this->assertNull(DiagnosisHelper::normalizeLineId([]));
        $this->assertNull(DiagnosisHelper::normalizeLineId(''));
        $this->assertNull(DiagnosisHelper::normalizeLineId('   '));
        $this->assertNull(DiagnosisHelper::normalizeLineId(false));
    }

    private function renderEditor(): string
    {
        $_GET['lineid'] = self::LINE_ID;
        $_POST['form_save'] = '';
        $_REQUEST['lineid'] = self::LINE_ID;

        $bufferLevel = ob_get_level();
        ob_start();
        try {
            require dirname(__DIR__, 5) . '/interface/forms/physical_exam/edit_diagnoses.php';
            $output = (string) ob_get_clean();
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }

        return $output;
    }

    /**
     * @return array<int, string>
     */
    private function extractInputValues(string $output, string $inputName): array
    {
        preg_match_all(
            "/name='" . preg_quote($inputName, '/') . "\\[(\\d+)]' value='([^']*)'/",
            $output,
            $matches,
            PREG_SET_ORDER
        );

        $values = [];
        foreach ($matches as $match) {
            $values[(int) $match[1]] = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5);
        }

        return $values;
    }

    private function insertDiagnosis(int $ordering, string $diagnosis): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_physical_exam_diagnoses (line_id, ordering, diagnosis) VALUES (?, ?, ?)",
            [self::LINE_ID, $ordering, $diagnosis]
        );
    }

    /**
     * @return list<array{ordering: int, diagnosis: string}>
     */
    private function fetchDiagnoses(): array
    {
        $result = QueryUtils::fetchRecords(
            "SELECT ordering, diagnosis FROM form_physical_exam_diagnoses WHERE line_id = ? ORDER BY ordering, diagnosis",
            [self::LINE_ID]
        );

        $rows = [];
        foreach ($result as $row) {
            /** @var array{ordering: int|string, diagnosis: string} $row */
            $rows[] = [
                'ordering' => (int) $row['ordering'],
                'diagnosis' => $row['diagnosis'],
            ];
        }

        return $rows;
    }
}
