<?php

/**
 * Isolated eRx SQL Strict Mode Test
 *
 * Verifies that eRx_xml.php SQL queries do not compare DATETIME columns
 * against empty strings, which fails under MySQL strict mode.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\eRx;

use PHPUnit\Framework\TestCase;

class ErxSqlStrictModeTest extends TestCase
{
    private string $sourceCode;

    protected function setUp(): void
    {
        $filePath = dirname(__DIR__, 3) . '/../interface/eRx_xml.php';
        $this->assertTrue(file_exists($filePath), 'eRx_xml.php must exist');
        $this->sourceCode = file_get_contents($filePath);
    }

    /**
     * Verify no SQL compares enddate to empty string.
     *
     * Comparing a DATETIME column to '' fails under MySQL 5.7+ strict mode.
     * Only NULL and '0000-00-00' checks should be used for "empty" dates.
     *
     * @see https://github.com/openemr/openemr/issues/10834
     */
    public function testNoEmptyStringDatetimeComparison(): void
    {
        // Match patterns like: enddate = '' or enddate = "" (in SQL context)
        $pattern = '/enddate\s*=\s*[\'"][\'"]/' ;

        $this->assertDoesNotMatchRegularExpression(
            $pattern,
            $this->sourceCode,
            'eRx_xml.php must not compare DATETIME enddate column to empty string — '
            . 'use IS NULL or = \'0000-00-00\' instead (MySQL strict mode compatibility)'
        );
    }

    /**
     * Verify enddate NULL checks are present for active medication filtering.
     */
    public function testEnddateNullCheckPresent(): void
    {
        $this->assertStringContainsString(
            'enddate is null',
            $this->sourceCode,
            'eRx_xml.php should check for NULL enddate values'
        );
    }
}
