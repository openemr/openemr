<?php

/**
 * Loads small ICD-10 releases through icd_import() and checks which code sets
 * end up active. A mid-year ICD-10-CM release must not retire the active
 * ICD-10-PCS set.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Common\StandardTables;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @phpstan-type IcdTable 'icd10_dx_order_code'|'icd10_pcs_order_code'
 */
final class IcdImportTest extends TestCase
{
    private Filesystem $filesystem;
    private string $tempDir;
    private string $previousTempFilesDir;
    private int $previousDxRevision;
    private int $previousPcsRevision;
    /** @var list<int> */
    private array $previousActiveDxRevisions;
    /** @var list<int> */
    private array $previousActivePcsRevisions;

    /**
     * @codeCoverageIgnore PHPUnit runs setUpBeforeClass before coverage instrumentation starts.
     */
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../../library/standard_tables_capture.inc.php';
    }

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->tempDir = $this->filesystem->tempnam(sys_get_temp_dir(), 'icd-import-');
        $this->filesystem->remove($this->tempDir);
        $this->filesystem->mkdir($this->tempDir . '/ICD10');

        $bag = OEGlobalsBag::getInstance();
        $this->previousTempFilesDir = $bag->getString('temporary_files_dir');
        $bag->set('temporary_files_dir', $this->tempDir);

        $this->previousDxRevision = self::maxRevision('icd10_dx_order_code');
        $this->previousPcsRevision = self::maxRevision('icd10_pcs_order_code');
        $this->previousActiveDxRevisions = self::activeRevisions('icd10_dx_order_code');
        $this->previousActivePcsRevisions = self::activeRevisions('icd10_pcs_order_code');
    }

    protected function tearDown(): void
    {
        self::restore('icd10_dx_order_code', $this->previousDxRevision, $this->previousActiveDxRevisions);
        self::restore('icd10_pcs_order_code', $this->previousPcsRevision, $this->previousActivePcsRevisions);
        OEGlobalsBag::getInstance()->set('temporary_files_dir', $this->previousTempFilesDir);
        $this->filesystem->remove($this->tempDir);
    }

    public function testMidYearCmOnlyReleaseKeepsActivePcsSet(): void
    {
        $this->seedActiveRevisions();
        $this->stageRelease([
            'icd10cm_order_2026.txt' => self::dxLine(1, 'A00', '0', 'Cholera') . self::dxLine(2, 'A001', '1', 'Cholera due to Vibrio cholerae 01, biovar eltor'),
            'icd10cm_order_addenda_2026.txt' => self::dxLine(3, 'Z9999', '1', 'Addenda rows are not codes'),
            'icd10OrderFiles.pdf' => 'not a code file',
        ]);

        icd_import('ICD10');

        $newDxRevision = $this->previousDxRevision + 2;
        $this->assertSame(
            [['code' => 'A00', 'revision' => $newDxRevision], ['code' => 'A001', 'revision' => $newDxRevision]],
            self::activeCodes('icd10_dx_order_code'),
        );
        $this->assertSame(
            [['code' => '0016070', 'revision' => $this->previousPcsRevision + 1]],
            self::activeCodes('icd10_pcs_order_code'),
        );
        $this->assertSame(
            'A00.1',
            QueryUtils::fetchSingleValue(
                'SELECT TRIM(formatted_dx_code) AS formatted FROM icd10_dx_order_code WHERE revision = ? AND dx_code = ?',
                'formatted',
                [$newDxRevision, 'A001'],
            ),
        );
    }

    public function testAnnualReleaseReplacesBothSets(): void
    {
        $this->seedActiveRevisions();
        $this->stageRelease([
            'icd10cm_order_2027.txt' => self::dxLine(1, 'C7831', '1', 'Secondary malignant neoplasm of right large intestine'),
            'ICD10PCS_CODES_2027.TXT' => '0016071 Bypass Cerebral Ventricle to Nasopharynx with Autologous Tissue Substitute, Open Approach' . PHP_EOL,
        ]);

        icd_import('ICD10');

        $this->assertSame(
            [['code' => 'C7831', 'revision' => $this->previousDxRevision + 2]],
            self::activeCodes('icd10_dx_order_code'),
        );
        $this->assertSame(
            [['code' => '0016071', 'revision' => $this->previousPcsRevision + 2]],
            self::activeCodes('icd10_pcs_order_code'),
        );
    }

    /**
     * Stand in for the release a site already has loaded: one active revision per table.
     */
    private function seedActiveRevisions(): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE icd10_dx_order_code SET active = 0',
        );
        QueryUtils::sqlStatementThrowException(
            'UPDATE icd10_pcs_order_code SET active = 0',
        );
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO icd10_dx_order_code (dx_code, formatted_dx_code, valid_for_coding, short_desc, long_desc, active, revision) VALUES (?, ?, ?, ?, ?, 1, ?)',
            ['A000', 'A00.0', '1', 'Cholera d/t vib cholerae', 'Cholera due to Vibrio cholerae 01, biovar cholerae', $this->previousDxRevision + 1],
        );
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO icd10_pcs_order_code (pcs_code, long_desc, active, revision) VALUES (?, ?, 1, ?)',
            ['0016070', 'Bypass Cerebral Ventricle to Nasopharynx with Autologous Tissue Substitute, Open Approach', $this->previousPcsRevision + 1],
        );
    }

    /**
     * @param array<string, string> $files filename => contents
     */
    private function stageRelease(array $files): void
    {
        foreach ($files as $filename => $contents) {
            $this->filesystem->dumpFile($this->tempDir . '/ICD10/' . $filename, $contents);
        }
    }

    /**
     * Format one record of the CMS ICD-10-CM order file.
     */
    private static function dxLine(int $order, string $code, string $validForCoding, string $description): string
    {
        return sprintf('%05d %-7s %s %-60s %s', $order, $code, $validForCoding, $description, $description) . PHP_EOL;
    }

    /**
     * @param IcdTable $table
     * @return list<array{code: string, revision: int}>
     */
    private static function activeCodes(string $table): array
    {
        $sql = match ($table) {
            'icd10_dx_order_code' => 'SELECT TRIM(dx_code) AS code, revision FROM icd10_dx_order_code WHERE active = 1 ORDER BY dx_code',
            'icd10_pcs_order_code' => 'SELECT TRIM(pcs_code) AS code, revision FROM icd10_pcs_order_code WHERE active = 1 ORDER BY pcs_code',
        };
        $codes = [];
        foreach (QueryUtils::fetchRecords($sql) as $row) {
            self::assertIsString($row['code']);
            self::assertIsNumeric($row['revision']);
            $codes[] = ['code' => $row['code'], 'revision' => (int) $row['revision']];
        }
        return $codes;
    }

    /**
     * @param IcdTable $table
     */
    private static function maxRevision(string $table): int
    {
        $sql = match ($table) {
            'icd10_dx_order_code' => 'SELECT COALESCE(MAX(revision), 0) AS revision FROM icd10_dx_order_code',
            'icd10_pcs_order_code' => 'SELECT COALESCE(MAX(revision), 0) AS revision FROM icd10_pcs_order_code',
        };
        $revision = QueryUtils::fetchSingleValue($sql, 'revision');
        self::assertIsNumeric($revision);
        return (int) $revision;
    }

    /**
     * @param IcdTable $table
     * @return list<int>
     */
    private static function activeRevisions(string $table): array
    {
        $sql = match ($table) {
            'icd10_dx_order_code' => 'SELECT DISTINCT revision FROM icd10_dx_order_code WHERE active = 1',
            'icd10_pcs_order_code' => 'SELECT DISTINCT revision FROM icd10_pcs_order_code WHERE active = 1',
        };
        $revisions = [];
        foreach (QueryUtils::fetchRecords($sql) as $row) {
            self::assertIsNumeric($row['revision']);
            $revisions[] = (int) $row['revision'];
        }
        return $revisions;
    }

    /**
     * Drop the revisions a test added and reactivate the ones that were active before it.
     *
     * @param IcdTable $table
     * @param list<int> $activeRevisions
     */
    private static function restore(string $table, int $maxRevision, array $activeRevisions): void
    {
        [$delete, $deactivate, $activate] = match ($table) {
            'icd10_dx_order_code' => [
                'DELETE FROM icd10_dx_order_code WHERE revision > ?',
                'UPDATE icd10_dx_order_code SET active = 0',
                'UPDATE icd10_dx_order_code SET active = 1 WHERE revision = ?',
            ],
            'icd10_pcs_order_code' => [
                'DELETE FROM icd10_pcs_order_code WHERE revision > ?',
                'UPDATE icd10_pcs_order_code SET active = 0',
                'UPDATE icd10_pcs_order_code SET active = 1 WHERE revision = ?',
            ],
        };
        QueryUtils::sqlStatementThrowException($delete, [$maxRevision]);
        QueryUtils::sqlStatementThrowException($deactivate);
        foreach ($activeRevisions as $revision) {
            QueryUtils::sqlStatementThrowException($activate, [$revision]);
        }
    }
}
