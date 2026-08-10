<?php

/**
 * Isolated tests for the parsing half of BaseCodeTypeImporter.
 *
 * readRecords() is deliberately separate from import() so the file handling and column mapping can
 * be exercised without a database.
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
use OpenEMR\Services\CodeTypes\Importer\BaseCodeTypeImporter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class BaseCodeTypeImporterTest extends TestCase
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

    public function testDefaultMappingReadsCodeDescriptionShortDescription(): void
    {
        $importer = new TestCommaImporter();
        $path = $this->writeTempFile("A01,Long description,Short\nA02,Another one,Other\n");

        $this->assertSame(
            [
                ['code' => 'A01', 'code_text' => 'Long description', 'code_text_short' => 'Short'],
                ['code' => 'A02', 'code_text' => 'Another one', 'code_text_short' => 'Other'],
            ],
            iterator_to_array($importer->readRecords($path), false)
        );
    }

    public function testAlternateDelimiterAndRepeatedColumnMapping(): void
    {
        // This is the shape the Ireland module's CH07 importer uses: '%' delimited, with the short
        // description mapped onto the same column as the long one.
        $importer = new TestPercentImporter();
        $path = $this->writeTempFile("Z01%Health category one\nZ02%Health category two\n");

        $this->assertSame(
            [
                ['code' => 'Z01', 'code_text' => 'Health category one', 'code_text_short' => 'Health category one'],
                ['code' => 'Z02', 'code_text' => 'Health category two', 'code_text_short' => 'Health category two'],
            ],
            iterator_to_array($importer->readRecords($path), false)
        );
    }

    public function testBlankAndCodelessRowsAreSkipped(): void
    {
        $importer = new TestCommaImporter();
        $path = $this->writeTempFile("A01,First,Short\n\n,No code here,Short\nA02,Second,Short\n");

        $records = iterator_to_array($importer->readRecords($path), false);

        $this->assertCount(2, $records);
        $this->assertSame(['A01', 'A02'], array_column($records, 'code'));
    }

    public function testMissingTrailingColumnsBecomeEmptyStrings(): void
    {
        $importer = new TestCommaImporter();
        $path = $this->writeTempFile("A01,Only a long description\nA02\n");

        $this->assertSame(
            [
                ['code' => 'A01', 'code_text' => 'Only a long description', 'code_text_short' => ''],
                ['code' => 'A02', 'code_text' => '', 'code_text_short' => ''],
            ],
            iterator_to_array($importer->readRecords($path), false)
        );
    }

    public function testFileHandleIsClosedAfterFullIteration(): void
    {
        $importer = new TestCommaImporter();
        $path = $this->writeTempFile("A01,First,Short\n");

        iterator_to_array($importer->readRecords($path), false);

        $this->assertNotNull($importer->openedHandle);
        $this->assertFalse(is_resource($importer->openedHandle));
    }

    public function testFileHandleIsClosedWhenIterationStopsEarly(): void
    {
        $importer = new TestCommaImporter();
        $path = $this->writeTempFile("A01,First,Short\nA02,Second,Short\nA03,Third,Short\n");

        foreach ($importer->readRecords($path) as $record) {
            $this->assertSame('A01', $record['code']);
            break;
        }
        // Dropping the last reference destroys the generator, which runs its finally block.
        gc_collect_cycles();

        $this->assertNotNull($importer->openedHandle);
        $this->assertFalse(is_resource($importer->openedHandle));
    }

    public function testUnreadableFileThrowsCodeImportException(): void
    {
        $importer = new TestCommaImporter();

        $this->expectException(CodeImportException::class);
        iterator_to_array($importer->readRecords(sys_get_temp_dir() . '/does-not-exist-' . uniqid() . '.csv'), false);
    }

    public function testReplaceModeValidatesTheFileBeforeDeletingAnything(): void
    {
        // readRecords() is a generator, so nothing is parsed until it is advanced. import() must
        // prime it before the DELETE that replace mode performs, otherwise an unreadable upload
        // wipes an existing code set and inserts nothing.
        //
        // The code-type lookup is stubbed so the first real database access left in the path is
        // that DELETE. There is no database in an isolated test, so if the ordering regressed the
        // failure would come from the DELETE and carry the generic wrapper message. Asserting on
        // the file-level message is what pins the ordering.
        $importer = new TestImporterWithStubbedCodeType();
        $missing = sys_get_temp_dir() . '/does-not-exist-' . uniqid() . '.csv';

        $this->expectException(CodeImportException::class);
        $this->expectExceptionMessage('File does not exist or is not readable');
        $importer->import($missing, true);
    }

    public function testImportNormalizesUnexpectedFailuresIntoCodeImportException(): void
    {
        // resolveCodeTypeId() reaches for a database that an isolated test does not have. Whatever
        // that throws, callers must only ever have to catch CodeImportException.
        $importer = new TestCommaImporter();

        $this->expectException(CodeImportException::class);
        $importer->import($this->writeTempFile("A01,First,Short\n"), false);
    }

    private function writeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'oe-code-import-');
        self::assertIsString($path);
        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;
        return $path;
    }
}

/**
 * Minimal importer exercising the inherited defaults, with the opened handle exposed so tests can
 * assert it was closed.
 */
class TestCommaImporter extends BaseCodeTypeImporter
{
    /** @var resource|null */
    public mixed $openedHandle = null;

    public function getCodeType(): string
    {
        return 'TEST-COMMA';
    }

    public function openFile(string $filePath): mixed
    {
        return $this->openedHandle = parent::openFile($filePath);
    }
}

/**
 * Stubs out the only database access that precedes the replace-mode DELETE, so a test can pin the
 * order of "parse the file" against "delete the existing code set".
 */
class TestImporterWithStubbedCodeType extends BaseCodeTypeImporter
{
    public function getCodeType(): string
    {
        return 'TEST-STUBBED';
    }

    protected function resolveCodeTypeId(string $codeTypeKey): int
    {
        return 1;
    }
}

class TestPercentImporter extends BaseCodeTypeImporter
{
    public function getCodeType(): string
    {
        return 'TEST-PERCENT';
    }

    public function getDelimiter(): string
    {
        return '%';
    }

    public function getMappingForFile(mixed $file): array
    {
        return ['code' => 0, 'code_text' => 1, 'code_text_short' => 1];
    }
}
