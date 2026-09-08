<?php

/**
 * Isolated tests for RXCUIImportService's RXNCONSO.RRF parsing.
 *
 * The row filters asserted here are the ones the legacy interface/super/load_codes.php applied, so
 * these tests double as a regression guard on that behaviour surviving the move into src/.
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
use OpenEMR\Services\CodeTypes\Importer\RXCUIImportService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ZipArchive;

#[Group('isolated')]
class RXCUIImportServiceTest extends TestCase
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

    public function testCodeTypeIsRxcui(): void
    {
        $this->assertSame('RXCUI', (new RXCUIImportService())->getCodeType());
    }

    public function testPrescribableRxnormRowsAreImportedFromARawRrfFile(): void
    {
        $path = $this->writeTempFile(
            $this->rrfLine('1191', 'RXNORM', 'aspirin', '4096')
            . $this->rrfLine('161', 'RXNORM', 'acetaminophen', '4096')
        );

        $this->assertSame(
            [
                ['code' => '1191', 'code_text' => 'aspirin', 'code_text_short' => ''],
                ['code' => '161', 'code_text' => 'acetaminophen', 'code_text_short' => ''],
            ],
            iterator_to_array((new RXCUIImportService())->readRecords($path), false)
        );
    }

    public function testRowsFromOtherVocabulariesAreSkipped(): void
    {
        $path = $this->writeTempFile(
            $this->rrfLine('1191', 'RXNORM', 'aspirin', '4096')
            . $this->rrfLine('9999', 'MSH', 'from another source', '4096')
        );

        $records = iterator_to_array((new RXCUIImportService())->readRecords($path), false);

        $this->assertSame(['1191'], array_column($records, 'code'));
    }

    public function testRowsOutsideThePrescribableContentViewAreSkipped(): void
    {
        $path = $this->writeTempFile(
            $this->rrfLine('1191', 'RXNORM', 'aspirin', '4096')
            . $this->rrfLine('9999', 'RXNORM', 'not prescribable', '')
        );

        $records = iterator_to_array((new RXCUIImportService())->readRecords($path), false);

        $this->assertSame(['1191'], array_column($records, 'code'));
    }

    public function testTruncatedRowsAreSkipped(): void
    {
        $path = $this->writeTempFile(
            "1191|ENG|P|L0000001|\n"
            . $this->rrfLine('161', 'RXNORM', 'acetaminophen', '4096')
        );

        $records = iterator_to_array((new RXCUIImportService())->readRecords($path), false);

        $this->assertSame(['161'], array_column($records, 'code'));
    }

    public function testRepeatedCodesAreEmittedOnlyOnce(): void
    {
        $path = $this->writeTempFile(
            $this->rrfLine('1191', 'RXNORM', 'aspirin', '4096')
            . $this->rrfLine('1191', 'RXNORM', 'aspirin (duplicate row)', '4096')
        );

        $records = iterator_to_array((new RXCUIImportService())->readRecords($path), false);

        $this->assertCount(1, $records);
        $this->assertSame('aspirin', $records[0]['code_text']);
    }

    public function testRrfIsReadFromInsideAReleaseZip(): void
    {
        $path = $this->writeZip([
            'rrf/RXNCONSO.RRF' => $this->rrfLine('1191', 'RXNORM', 'aspirin', '4096'),
            'rrf/RXNSAT.RRF' => "unrelated\n",
        ]);

        $this->assertSame(
            [['code' => '1191', 'code_text' => 'aspirin', 'code_text_short' => '']],
            iterator_to_array((new RXCUIImportService())->readRecords($path), false)
        );
    }

    public function testZipWithoutTheConsoTableThrowsInsteadOfExiting(): void
    {
        $path = $this->writeZip(['rrf/RXNSAT.RRF' => "unrelated\n"]);

        $this->expectException(CodeImportException::class);
        iterator_to_array((new RXCUIImportService())->readRecords($path), false);
    }

    public function testMissingFileThrows(): void
    {
        $this->expectException(CodeImportException::class);
        iterator_to_array(
            (new RXCUIImportService())->readRecords(sys_get_temp_dir() . '/no-such-file-' . uniqid() . '.rrf'),
            false
        );
    }

    /**
     * Build one RXNCONSO.RRF row. Only the columns the importer reads are populated; the trailing
     * delimiter matches the real files, which end every row with a pipe.
     */
    private function rrfLine(string $rxcui, string $sab, string $str, string $cvf): string
    {
        $columns = array_fill(0, 18, '');
        $columns[0] = $rxcui;
        $columns[11] = $sab;
        $columns[14] = $str;
        $columns[17] = $cvf;
        return implode('|', $columns) . "|\n";
    }

    private function writeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'oe-rxcui-');
        self::assertIsString($path);
        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;
        return $path;
    }

    /**
     * @param array<string, string> $entries
     */
    private function writeZip(array $entries): string
    {
        $path = $this->writeTempFile('');
        $zip = new ZipArchive();
        self::assertTrue($zip->open($path, ZipArchive::OVERWRITE) === true);
        foreach ($entries as $name => $contents) {
            $zip->addFromString($name, $contents);
        }
        $zip->close();
        return $path;
    }
}
