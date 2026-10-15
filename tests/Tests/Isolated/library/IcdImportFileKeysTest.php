<?php

/**
 * Isolated tests for icd_import_file_keys(), which picks the files an ICD-10
 * release loads and which table each one replaces.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/standard_tables_capture.inc.php';

final class IcdImportFileKeysTest extends TestCase
{
    private const KEYS = ['icd10pcs_codes_', 'icd10cm_order_'];

    /**
     * @param list<string> $filenames
     * @param list<array{filename: string, key: string}> $expected
     */
    #[DataProvider('releaseProvider')]
    public function testPairsImportableFilesWithKeys(array $filenames, array $expected): void
    {
        $this->assertSame($expected, icd_import_file_keys($filenames, self::KEYS));
    }

    /**
     * @return array<string, array{list<string>, list<array{filename: string, key: string}>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function releaseProvider(): array
    {
        return [
            'annual release loads both tables' => [
                ['.', '..', 'icd10cm_order_2027.txt', 'icd10pcs_codes_2027.txt'],
                [
                    ['filename' => 'icd10cm_order_2027.txt', 'key' => 'icd10cm_order_'],
                    ['filename' => 'icd10pcs_codes_2027.txt', 'key' => 'icd10pcs_codes_'],
                ],
            ],
            'CM zip alone loads only diagnoses' => [
                [
                    'Code Descriptions',
                    'icd10OrderFiles.pdf',
                    'icd10cm_codes_2026.txt',
                    'icd10cm_codes_addenda_2026.txt',
                    'icd10cm_order_2026.txt',
                    'icd10cm_order_addenda_2026.txt',
                ],
                [['filename' => 'icd10cm_order_2026.txt', 'key' => 'icd10cm_order_']],
            ],
            'upper-case names still match' => [
                ['ICD10PCS_CODES_2027.TXT'],
                [['filename' => 'ICD10PCS_CODES_2027.TXT', 'key' => 'icd10pcs_codes_']],
            ],
            'PCS addenda is skipped' => [
                ['codes_addenda_2027.txt', 'icd10pcs_codes_addenda_2027.txt'],
                [],
            ],
            'non-text file named like a key is skipped' => [
                ['icd10cm_order_2027.pdf'],
                [],
            ],
        ];
    }
}
