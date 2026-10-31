<?php

/**
 * Isolated PatientFinderRequest Test
 *
 * Tests the parsing of the (legacy DataTables) Patient Finder request from a raw
 * GET array into the typed PatientFinderRequest value object.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Services\PatientFinder\PatientFinderRequest;
use PHPUnit\Framework\TestCase;

class PatientFinderRequestTest extends TestCase
{
    public function testParsesBasicPagingAndEcho(): void
    {
        $req = PatientFinderRequest::fromArray([
            'sEcho' => '7',
            'iDisplayStart' => '20',
            'iDisplayLength' => '10',
            'sColumns' => 'name,DOB,sex',
        ]);

        $this->assertSame(7, $req->echo);
        $this->assertSame(20, $req->displayStart);
        $this->assertSame(10, $req->displayLength);
        $this->assertTrue($req->hasPaging());
        $this->assertSame(['name', 'DOB', 'sex'], $req->columnKeys);
    }

    public function testMissingPagingBecomesNegativeOneAndHasPagingFalse(): void
    {
        $req = PatientFinderRequest::fromArray(['sColumns' => 'name']);

        $this->assertSame(-1, $req->displayStart);
        $this->assertSame(-1, $req->displayLength);
        $this->assertFalse($req->hasPaging());
    }

    public function testNonNumericPagingFallsBackToNegativeOne(): void
    {
        $req = PatientFinderRequest::fromArray([
            'iDisplayStart' => 'not-a-number',
            'iDisplayLength' => '',
        ]);

        $this->assertSame(-1, $req->displayStart);
        $this->assertSame(-1, $req->displayLength);
    }

    public function testGlobalSearchIsTrimmed(): void
    {
        $req = PatientFinderRequest::fromArray(['sSearch' => '  smith  ']);

        $this->assertSame('smith', $req->globalSearch);
        $this->assertTrue($req->hasGlobalSearch());
        $this->assertFalse($req->anySearch);
    }

    public function testEmptyGlobalSearch(): void
    {
        $req = PatientFinderRequest::fromArray(['sSearch' => '   ']);

        $this->assertSame('', $req->globalSearch);
        $this->assertFalse($req->hasGlobalSearch());
    }

    public function testExactSearchToggle(): void
    {
        $exact = PatientFinderRequest::fromArray(['searchType' => 'true']);
        $like = PatientFinderRequest::fromArray(['searchType' => 'false']);

        $this->assertTrue($exact->exactSearch);
        $this->assertFalse($like->exactSearch);
    }

    public function testSearchAnyActivatesOnlyWhenSearchBoxEmpty(): void
    {
        $any = PatientFinderRequest::fromArray(['search_any' => 'jones', 'sSearch' => '']);
        $this->assertTrue($any->anySearch);
        $this->assertSame('jones', $any->globalSearch);

        // When the normal search box is also filled, search_any is NOT in effect
        // and the normal term wins.
        $both = PatientFinderRequest::fromArray(['search_any' => 'jones', 'sSearch' => 'smith']);
        $this->assertFalse($both->anySearch);
        $this->assertSame('smith', $both->globalSearch);
    }

    public function testSortOnlyIncludesSortableColumns(): void
    {
        $req = PatientFinderRequest::fromArray([
            'iSortCol_0' => '0',
            'iSortingCols' => '2',
            'iSortCol_1' => '3',
            'sSortDir_0' => 'asc',
            'sSortDir_1' => 'desc',
            'bSortable_0' => 'true',
            'bSortable_3' => 'false', // not sortable → dropped
        ]);

        $this->assertSame([['index' => 0, 'dir' => 'ASC']], $req->sortColumns);
    }

    public function testSortDirectionNormalisedAndDefaulted(): void
    {
        $req = PatientFinderRequest::fromArray([
            'iSortCol_0' => '2',
            'iSortingCols' => '1',
            'sSortDir_0' => 'weird',
            'bSortable_2' => 'true',
        ]);

        $this->assertSame([['index' => 2, 'dir' => 'ASC']], $req->sortColumns);
    }

    public function testNoSortWhenNoSortColumnPresent(): void
    {
        $req = PatientFinderRequest::fromArray(['iSortingCols' => '1']);
        $this->assertSame([], $req->sortColumns);
    }

    public function testColumnSearchesKeyedByIndex(): void
    {
        $req = PatientFinderRequest::fromArray([
            'sColumns' => 'name,DOB,sex',
            'bSearchable_0' => 'true',
            'sSearch_0' => 'smith',
            'bSearchable_1' => 'true',
            'sSearch_1' => '', // empty term → dropped
            'bSearchable_2' => 'false', // not searchable → dropped
            'sSearch_2' => 'male',
        ]);

        $this->assertSame([0 => 'smith'], $req->columnSearches);
    }

    public function testEmptyColumnsYieldsEmptyKeys(): void
    {
        $req = PatientFinderRequest::fromArray([]);
        $this->assertSame([], $req->columnKeys);
        $this->assertSame([], $req->columnSearches);
        $this->assertSame([], $req->sortColumns);
    }
}
