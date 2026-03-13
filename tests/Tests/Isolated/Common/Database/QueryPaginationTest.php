<?php

/**
 * Isolated QueryPagination Test
 *
 * Tests pagination logic for database queries.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Database;

use OpenEMR\Common\Database\QueryPagination;
use PHPUnit\Framework\TestCase;

class QueryPaginationTest extends TestCase
{
    public function testDefaultConstructorValues(): void
    {
        $pagination = new QueryPagination();
        $this->assertSame(QueryPagination::DEFAULT_LIMIT, $pagination->getLimit());
        $this->assertSame(0, $pagination->getCurrentOffsetId());
        $this->assertSame(0, $pagination->getTotalCount());
        $this->assertFalse($pagination->hasMoreData());
    }

    public function testConstructorWithLimitAndOffset(): void
    {
        $pagination = new QueryPagination(10, 20);
        $this->assertSame(10, $pagination->getLimit());
        $this->assertSame(20, $pagination->getCurrentOffsetId());
    }

    public function testLimitCannotExceedMaxLimit(): void
    {
        $pagination = new QueryPagination(500);
        $this->assertSame(QueryPagination::MAX_LIMIT, $pagination->getLimit());
    }

    public function testNegativeOffsetThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Offset Id must be greater than or equal to 0');
        new QueryPagination(10, -1);
    }

    public function testSetLimitNegativeThrowsException(): void
    {
        $pagination = new QueryPagination();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be greater than or equal to 0');
        $pagination->setLimit(-1);
    }

    public function testSetLimit(): void
    {
        $pagination = new QueryPagination();
        $pagination->setLimit(50);
        $this->assertSame(50, $pagination->getLimit());
    }

    public function testSetCurrentOffsetId(): void
    {
        $pagination = new QueryPagination();
        $pagination->setCurrentOffsetId(100);
        $this->assertSame(100, $pagination->getCurrentOffsetId());
    }

    public function testSetCurrentOffsetIdWithString(): void
    {
        $pagination = new QueryPagination();
        $pagination->setCurrentOffsetId('abc123');
        $this->assertSame('abc123', $pagination->getCurrentOffsetId());
    }

    public function testSetTotalCount(): void
    {
        $pagination = new QueryPagination();
        $pagination->setTotalCount(1000);
        $this->assertSame(1000, $pagination->getTotalCount());
    }

    public function testSetTotalCountNull(): void
    {
        $pagination = new QueryPagination();
        $pagination->setTotalCount(100);
        $pagination->setTotalCount(null);
        $this->assertNull($pagination->getTotalCount());
    }

    public function testHasMoreData(): void
    {
        $pagination = new QueryPagination();
        $this->assertFalse($pagination->hasMoreData());

        $pagination->setHasMoreData(true);
        $this->assertTrue($pagination->hasMoreData());

        $pagination->setHasMoreData(false);
        $this->assertFalse($pagination->hasMoreData());
    }

    public function testGetNextOffsetId(): void
    {
        $pagination = new QueryPagination(10, 0);
        $this->assertSame(10, $pagination->getNextOffsetId());

        $pagination = new QueryPagination(10, 20);
        $this->assertSame(30, $pagination->getNextOffsetId());
    }

    public function testSearchUri(): void
    {
        $pagination = new QueryPagination();
        $this->assertSame('', $pagination->getSearchUri());

        $pagination->setSearchUri('/api/patient?name=Smith');
        $this->assertSame('/api/patient?name=Smith', $pagination->getSearchUri());
    }

    public function testCopy(): void
    {
        $pagination = new QueryPagination(10, 20);
        $pagination->setTotalCount(100);
        $pagination->setHasMoreData(true);
        $pagination->setSearchUri('/api/test');

        $copy = $pagination->copy();

        $this->assertNotSame($pagination, $copy);
        $this->assertSame($pagination->getLimit(), $copy->getLimit());
        $this->assertSame($pagination->getCurrentOffsetId(), $copy->getCurrentOffsetId());
        $this->assertSame($pagination->getTotalCount(), $copy->getTotalCount());
        $this->assertSame($pagination->hasMoreData(), $copy->hasMoreData());
        $this->assertSame($pagination->getSearchUri(), $copy->getSearchUri());
    }

    public function testGetLinksFirstPageNoMore(): void
    {
        $pagination = new QueryPagination(10, 0);
        $pagination->setSearchUri('/api/patient');

        $links = $pagination->getLinks();
        $this->assertIsArray($links);

        $this->assertArrayHasKey('first', $links);
        $this->assertIsString($links['first']);
        $this->assertStringContainsString('_offset=0', $links['first']);
        $this->assertStringContainsString('_count=10', $links['first']);
        $this->assertArrayNotHasKey('previous', $links);
        $this->assertArrayNotHasKey('next', $links);
    }

    public function testGetLinksMiddlePageWithMore(): void
    {
        $pagination = new QueryPagination(10, 20);
        $pagination->setSearchUri('/api/patient');
        $pagination->setHasMoreData(true);

        $links = $pagination->getLinks();
        $this->assertIsArray($links);

        $this->assertArrayHasKey('first', $links);
        $this->assertArrayHasKey('previous', $links);
        $this->assertArrayHasKey('next', $links);

        $this->assertIsString($links['first']);
        $this->assertIsString($links['previous']);
        $this->assertIsString($links['next']);
        $this->assertStringContainsString('_offset=0', $links['first']);
        $this->assertStringContainsString('_offset=10', $links['previous']);
        $this->assertStringContainsString('_offset=30', $links['next']);
    }

    public function testGetLinksLastPage(): void
    {
        $pagination = new QueryPagination(10, 40);
        $pagination->setSearchUri('/api/patient');
        $pagination->setHasMoreData(false);

        $links = $pagination->getLinks();
        $this->assertIsArray($links);

        $this->assertArrayHasKey('first', $links);
        $this->assertArrayHasKey('previous', $links);
        $this->assertArrayNotHasKey('next', $links);
    }

    public function testJsonSerialize(): void
    {
        $pagination = new QueryPagination(10, 20);
        $pagination->setSearchUri('/api/patient');
        $pagination->setHasMoreData(true);

        $json = json_encode($pagination);
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);

        $this->assertArrayHasKey('first', $decoded);
        $this->assertArrayHasKey('previous', $decoded);
        $this->assertArrayHasKey('next', $decoded);
    }

    public function testZeroLimitAllowed(): void
    {
        $pagination = new QueryPagination(0);
        $this->assertSame(0, $pagination->getLimit());
    }

    public function testPreviousOffsetNeverNegative(): void
    {
        // When at offset 5 with limit 10, previous should be 0, not -5
        $pagination = new QueryPagination(10, 5);
        $pagination->setSearchUri('/api/test');

        $links = $pagination->getLinks();
        $this->assertIsArray($links);
        $this->assertArrayHasKey('previous', $links);
        $this->assertIsString($links['previous']);
        $this->assertStringContainsString('_offset=0', $links['previous']);
    }
}
