<?php

/**
 * SearchQueryConfig Isolated Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\SearchFieldOrder;
use OpenEMR\Services\Search\SearchQueryConfig;
use PHPUnit\Framework\TestCase;

class SearchQueryConfigTest extends TestCase
{
    /**
     * Assert and return typed search field orders from a config.
     *
     * @return list<SearchFieldOrder>
     */
    private function getTypedOrders(SearchQueryConfig $config): array
    {
        $orders = $config->getSearchFieldOrders();
        $this->assertContainsOnlyInstancesOf(SearchFieldOrder::class, $orders);
        /** @var list<SearchFieldOrder> $orders */
        return $orders;
    }

    /**
     * Call createConfigFromQueryParams and assert the return type.
     *
     * @param array<string, mixed> $params
     */
    private function createConfig(array $params): SearchQueryConfig
    {
        $config = SearchQueryConfig::createConfigFromQueryParams($params);
        $this->assertInstanceOf(SearchQueryConfig::class, $config);
        return $config;
    }

    /**
     * Call createFhirConfigFromSearchParams and assert the return type.
     *
     * @param array<string, mixed> $params
     */
    private function createFhirConfig(array $params): SearchQueryConfig
    {
        $config = SearchQueryConfig::createFhirConfigFromSearchParams($params);
        $this->assertInstanceOf(SearchQueryConfig::class, $config);
        return $config;
    }

    // =========================================================================
    // Constructor defaults
    // =========================================================================

    public function testDefaultPaginationIsZero(): void
    {
        $config = new SearchQueryConfig();
        $this->assertSame(0, $config->getPagination()->getLimit());
    }

    public function testDefaultSearchFieldOrdersIsEmpty(): void
    {
        $config = new SearchQueryConfig();
        $this->assertSame([], $config->getSearchFieldOrders());
    }

    // =========================================================================
    // addSearchFieldOrder
    // =========================================================================

    public function testAddSearchFieldOrderAccumulatesOrders(): void
    {
        $config = new SearchQueryConfig();
        $config->addSearchFieldOrder(new SearchFieldOrder('name', true));
        $config->addSearchFieldOrder(new SearchFieldOrder('date', false));

        $orders = $this->getTypedOrders($config);
        $this->assertCount(2, $orders);
        $this->assertSame('name', $orders[0]->getField());
        $this->assertTrue($orders[0]->isAscending());
        $this->assertSame('date', $orders[1]->getField());
        $this->assertFalse($orders[1]->isAscending());
    }

    // =========================================================================
    // createConfigFromQueryParams — pagination
    // =========================================================================

    public function testCreateConfigFromQueryParamsWithMaxresults(): void
    {
        $config = $this->createConfig([
            '_maxresults' => '50',
            '_offset' => '10',
        ]);

        $this->assertSame(50, $config->getPagination()->getLimit());
        $this->assertSame(10, $config->getPagination()->getCurrentOffsetId());
    }

    public function testCreateConfigFromQueryParamsWithLimit(): void
    {
        $config = $this->createConfig(['_limit' => '25']);
        $this->assertSame(25, $config->getPagination()->getLimit());
    }

    public function testCreateConfigFromQueryParamsMaxresultsTakesPrecedenceOverLimit(): void
    {
        $config = $this->createConfig([
            '_maxresults' => '100',
            '_limit' => '25',
        ]);

        $this->assertSame(100, $config->getPagination()->getLimit());
    }

    public function testCreateConfigFromQueryParamsDefaultsToZeroPagination(): void
    {
        $config = $this->createConfig([]);

        $this->assertSame(0, $config->getPagination()->getLimit());
        $this->assertSame(0, $config->getPagination()->getCurrentOffsetId());
    }

    // =========================================================================
    // createConfigFromQueryParams — sort
    // =========================================================================

    public function testCreateConfigFromQueryParamsWithAscendingSort(): void
    {
        $config = $this->createConfig(['_sort' => 'name']);

        $orders = $this->getTypedOrders($config);
        $this->assertCount(1, $orders);
        $this->assertSame('name', $orders[0]->getField());
        $this->assertTrue($orders[0]->isAscending());
    }

    public function testCreateConfigFromQueryParamsWithDescendingSort(): void
    {
        $config = $this->createConfig(['_sort' => '-date']);

        $orders = $this->getTypedOrders($config);
        $this->assertCount(1, $orders);
        $this->assertSame('date', $orders[0]->getField());
        $this->assertFalse($orders[0]->isAscending());
    }

    public function testCreateConfigFromQueryParamsWithMultipleSortFields(): void
    {
        $config = $this->createConfig(['_sort' => 'name,-date,status']);

        $orders = $this->getTypedOrders($config);
        $this->assertCount(3, $orders);

        $this->assertSame('name', $orders[0]->getField());
        $this->assertTrue($orders[0]->isAscending());

        $this->assertSame('date', $orders[1]->getField());
        $this->assertFalse($orders[1]->isAscending());

        $this->assertSame('status', $orders[2]->getField());
        $this->assertTrue($orders[2]->isAscending());
    }

    public function testCreateConfigFromQueryParamsWithNoSort(): void
    {
        $config = $this->createConfig(['_maxresults' => '10']);
        $this->assertSame([], $config->getSearchFieldOrders());
    }

    // =========================================================================
    // createFhirConfigFromSearchParams
    // =========================================================================

    public function testCreateFhirConfigFromSearchParamsWithCountAndOffset(): void
    {
        $config = $this->createFhirConfig([
            '_count' => '20',
            '_offset' => '5',
        ]);

        $this->assertSame(20, $config->getPagination()->getLimit());
        $this->assertSame(5, $config->getPagination()->getCurrentOffsetId());
    }

    public function testCreateFhirConfigFromSearchParamsDefaultsToZero(): void
    {
        $config = $this->createFhirConfig([]);

        $this->assertSame(0, $config->getPagination()->getLimit());
        $this->assertSame(0, $config->getPagination()->getCurrentOffsetId());
    }

    public function testCreateFhirConfigFromSearchParamsWithSortOrders(): void
    {
        $order1 = new SearchFieldOrder('family', true);
        $order2 = new SearchFieldOrder('birthdate', false);

        $config = $this->createFhirConfig(['_sort' => [$order1, $order2]]);

        $orders = $this->getTypedOrders($config);
        $this->assertCount(2, $orders);
        $this->assertSame('family', $orders[0]->getField());
        $this->assertSame('birthdate', $orders[1]->getField());
    }

    public function testCreateFhirConfigFromSearchParamsIgnoresNonSearchFieldOrderItems(): void
    {
        $order = new SearchFieldOrder('name', true);

        $config = $this->createFhirConfig([
            '_sort' => [$order, 'not-a-search-field-order', 42],
        ]);

        $orders = $this->getTypedOrders($config);
        $this->assertCount(1, $orders);
        $this->assertSame('name', $orders[0]->getField());
    }
}
