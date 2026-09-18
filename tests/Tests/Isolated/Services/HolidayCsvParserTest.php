<?php

/**
 * Isolated tests for HolidayCsvParser.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\HolidayCsvParser;
use OpenEMR\Services\HolidayRow;
use OpenEMR\Services\InvalidHolidayCsvException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class HolidayCsvParserTest extends TestCase
{
    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        // xl() pass-through so error messages are predictable.
        $GLOBALS['disable_translation'] = true;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['disable_translation']);
        foreach ($this->tempFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->tempFiles = [];
    }

    public function testEmptyFileThrows(): void
    {
        $this->expectException(InvalidHolidayCsvException::class);
        $this->expectExceptionMessage('CSV file is empty');
        $this->parseString('');
    }

    public function testOnlyHeaderRowThrows(): void
    {
        $this->expectException(InvalidHolidayCsvException::class);
        $this->expectExceptionMessage('CSV file is empty');
        $this->parseString("date,description\n");
    }

    public function testHeaderPlusDataRowYieldsRow(): void
    {
        $rows = $this->parseString(<<<'CSV'
        date,description
        2026-12-25,Christmas Day
        CSV);
        self::assertCount(1, $rows);
        self::assertSame('Christmas Day', $rows[0]->description);
        self::assertSame('2026-12-25', $rows[0]->dateForStorage());
    }

    public function testNoHeaderJustDataYields(): void
    {
        $rows = $this->parseString("2026-12-25,Christmas Day\n");
        self::assertCount(1, $rows);
        self::assertSame('Christmas Day', $rows[0]->description);
    }

    public function testInvalidDateThrowsWithRowNumber(): void
    {
        try {
            $this->parseString("2026-12-25,Christmas\nnot-a-date,Other\n");
            self::fail('Expected InvalidHolidayCsvException'); // @codeCoverageIgnore
        } catch (InvalidHolidayCsvException $e) {
            self::assertSame(2, $e->rowNumber);
            self::assertStringContainsString('Invalid date format', $e->getMessage());
        }
    }

    public function testMissingDescriptionThrows(): void
    {
        try {
            $this->parseString("2026-12-25\n");
            self::fail('Expected InvalidHolidayCsvException'); // @codeCoverageIgnore
        } catch (InvalidHolidayCsvException $e) {
            self::assertSame(1, $e->rowNumber);
            self::assertStringContainsString('date and description', $e->getMessage());
        }
    }

    public function testLeadingBlankRowsBeforeHeaderStillRecognizesHeader(): void
    {
        $rows = $this->parseString(<<<'CSV'

        ,

        date,description
        2026-12-25,Christmas
        CSV);
        self::assertCount(1, $rows);
        self::assertSame('Christmas', $rows[0]->description);
    }

    public function testSlashFormatDateAccepted(): void
    {
        $rows = $this->parseString("2026/12/25,Christmas\n");
        self::assertCount(1, $rows);
        self::assertSame('2026-12-25', $rows[0]->dateForStorage());
    }

    public function testMultipleRowsAllYielded(): void
    {
        $rows = $this->parseString(<<<'CSV'
        date,description
        2026-05-23,Saturday holiday
        2026-05-24,Sunday holiday
        2026-12-25,Christmas
        CSV);
        self::assertCount(3, $rows);
        self::assertSame(['2026-05-23', '2026-05-24', '2026-12-25'], array_map(
            static fn(HolidayRow $r): string => $r->dateForStorage(),
            $rows,
        ));
    }

    public function testUnreadableFileThrows(): void
    {
        try {
            iterator_to_array(
                (new HolidayCsvParser())->parse('/nonexistent/path/holidays.csv'),
                false,
            );
            self::fail('Expected InvalidHolidayCsvException'); // @codeCoverageIgnore
        } catch (InvalidHolidayCsvException $e) {
            self::assertSame('Unable to read uploaded file', $e->getMessage());
            self::assertNotNull($e->getPrevious());
        }
    }

    public function testInvalidLeapDayRejected(): void
    {
        // 2025 is not a leap year — 2025-02-29 must fail.
        try {
            $this->parseString("2025-02-29,Bogus\n");
            self::fail('Expected InvalidHolidayCsvException'); // @codeCoverageIgnore
        } catch (InvalidHolidayCsvException $e) {
            self::assertStringContainsString('Invalid date format', $e->getMessage());
        }
    }

    /**
     * @return list<HolidayRow>
     */
    private function parseString(string $csv): array
    {
        $path = tempnam(sys_get_temp_dir(), 'holiday-csv-test-');
        if ($path === false) {
            self::fail('tempnam failed'); // @codeCoverageIgnore
        }
        file_put_contents($path, $csv);
        $this->tempFiles[] = $path;
        $parser = new HolidayCsvParser();
        return iterator_to_array($parser->parse($path), false);
    }
}
