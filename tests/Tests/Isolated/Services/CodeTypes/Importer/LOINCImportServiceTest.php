<?php

/**
 * Isolated tests for LOINCImportService's header-driven column mapping.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2026 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\CodeTypes\Importer;

use OpenEMR\Services\CodeTypes\CodeImportException;
use OpenEMR\Services\CodeTypes\Importer\LOINCImportService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class LOINCImportServiceTest extends TestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];
    }

    public function testCodeTypeIsLoinc(): void
    {
        $this->assertSame('LOINC', (new LOINCImportService())->getCodeType());
    }

    public function testColumnsAreLocatedByHeaderNameNotPosition(): void
    {
        // Columns deliberately out of order and interleaved with columns we do not import.
        $path = $this->writeTempFile(
            "COMPONENT,SHORTNAME,LOINC_NUM,CLASS,LONG_COMMON_NAME\n"
            . "Glucose,Glucose SerPl-mCnc,2345-7,CHEM,\"Glucose [Mass/volume] in Serum or Plasma\"\n"
            . "Sodium,Sodium SerPl-sCnc,2951-2,CHEM,\"Sodium [Moles/volume] in Serum or Plasma\"\n"
        );

        $this->assertSame(
            [
                [
                    'code' => '2345-7',
                    'code_text' => 'Glucose [Mass/volume] in Serum or Plasma',
                    'code_text_short' => 'Glucose SerPl-mCnc',
                ],
                [
                    'code' => '2951-2',
                    'code_text' => 'Sodium [Moles/volume] in Serum or Plasma',
                    'code_text_short' => 'Sodium SerPl-sCnc',
                ],
            ],
            iterator_to_array((new LOINCImportService())->readRecords($path), false)
        );
    }

    public function testHeaderCellsAreNormalizedBeforeMatching(): void
    {
        // Regenstrief ships the file with a UTF-8 BOM, which becomes part of the first header cell.
        $path = $this->writeTempFile(
            "\u{FEFF}LOINC_NUM, LONG_COMMON_NAME ,shortname\n"
            . "2345-7,Glucose,Gluc\n"
        );

        $this->assertSame(
            [['code' => '2345-7', 'code_text' => 'Glucose', 'code_text_short' => 'Gluc']],
            iterator_to_array((new LOINCImportService())->readRecords($path), false)
        );
    }

    public function testHeaderOnlyFileYieldsNoRecords(): void
    {
        $path = $this->writeTempFile("LOINC_NUM,LONG_COMMON_NAME,SHORTNAME\n");

        $this->assertSame([], iterator_to_array((new LOINCImportService())->readRecords($path), false));
    }

    public function testMissingRequiredColumnThrows(): void
    {
        $path = $this->writeTempFile("LOINC_NUM,LONG_COMMON_NAME\n2345-7,Glucose\n");

        $this->expectException(CodeImportException::class);
        iterator_to_array((new LOINCImportService())->readRecords($path), false);
    }

    public function testEmptyFileThrows(): void
    {
        $path = $this->writeTempFile('');

        $this->expectException(CodeImportException::class);
        iterator_to_array((new LOINCImportService())->readRecords($path), false);
    }

    private function writeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'oe-loinc-');
        self::assertIsString($path);
        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;
        return $path;
    }
}
