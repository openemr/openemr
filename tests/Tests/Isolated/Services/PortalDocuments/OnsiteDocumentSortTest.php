<?php

/**
 * Request input can only pick a sort column from the fixed list; it never becomes SQL. This
 * replaces OnsiteDocumentCriteriaSqlInjectionTest, which covered the Phreeze criteria builder
 * the list used to go through.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PortalDocuments;

use OpenEMR\Services\PortalDocuments\OnsiteDocumentSort;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OnsiteDocumentSortTest extends TestCase
{
    public function testAnEmptySortIsTheDenialReason(): void
    {
        $this->assertSame(OnsiteDocumentSort::DenialReason, OnsiteDocumentSort::fromRequest(''));
    }

    public function testTheOldPropertyNamesStillWork(): void
    {
        $this->assertSame('doc_type', OnsiteDocumentSort::fromRequest('DocType')->column());
        $this->assertSame('review_date', OnsiteDocumentSort::fromRequest('ReviewDate')->column());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedProvider(): array
    {
        return [
            'column name' => ['doc_type'],
            'sql' => ['DocType; DROP TABLE onsite_documents'],
            'direction' => ['DocType desc'],
            'subquery' => ['(SELECT 1)'],
            'old bitwise filter' => ['Id_BitwiseAnd'],
            'random order' => ['?'],
            'blob column' => ['FullDocument'],
        ];
    }

    #[DataProvider('refusedProvider')]
    public function testAnythingElseIsRefused(string $orderBy): void
    {
        $this->expectException(\InvalidArgumentException::class);
        OnsiteDocumentSort::fromRequest($orderBy);
    }
}
