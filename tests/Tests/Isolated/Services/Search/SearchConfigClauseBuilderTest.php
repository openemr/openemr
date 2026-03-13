<?php

/**
 * SearchConfigClauseBuilder Isolated Test
 *
 * Tests the buildSortOrderClauseFromConfig method which generates SQL ORDER BY
 * clauses with column whitelist enforcement.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\SearchConfigClauseBuilder;
use OpenEMR\Services\Search\SearchFieldOrder;
use OpenEMR\Services\Search\SearchQueryConfig;
use PHPUnit\Framework\TestCase;

class SearchConfigClauseBuilderTest extends TestCase
{
    private const ALLOWED_COLUMNS = ['name', 'date', 'status', 'id'];

    public function testEmptyConfigReturnsEmptyString(): void
    {
        $config = new SearchQueryConfig();
        $result = SearchConfigClauseBuilder::buildSortOrderClauseFromConfig($config, self::ALLOWED_COLUMNS);
        $this->assertSame('', $result);
    }

    public function testSingleAscendingField(): void
    {
        $config = new SearchQueryConfig();
        $config->addSearchFieldOrder(new SearchFieldOrder('name', true));

        $result = SearchConfigClauseBuilder::buildSortOrderClauseFromConfig($config, self::ALLOWED_COLUMNS);
        $this->assertSame('ORDER BY name ASC', $result);
    }

    public function testSingleDescendingField(): void
    {
        $config = new SearchQueryConfig();
        $config->addSearchFieldOrder(new SearchFieldOrder('date', false));

        $result = SearchConfigClauseBuilder::buildSortOrderClauseFromConfig($config, self::ALLOWED_COLUMNS);
        $this->assertSame('ORDER BY date DESC', $result);
    }

    public function testMultipleFields(): void
    {
        $config = new SearchQueryConfig();
        $config->addSearchFieldOrder(new SearchFieldOrder('name', true));
        $config->addSearchFieldOrder(new SearchFieldOrder('date', false));
        $config->addSearchFieldOrder(new SearchFieldOrder('id', true));

        $result = SearchConfigClauseBuilder::buildSortOrderClauseFromConfig($config, self::ALLOWED_COLUMNS);
        $this->assertSame('ORDER BY name ASC, date DESC, id ASC', $result);
    }

    public function testDisallowedColumnIsFiltered(): void
    {
        $config = new SearchQueryConfig();
        $config->addSearchFieldOrder(new SearchFieldOrder('malicious_column', true));

        $result = SearchConfigClauseBuilder::buildSortOrderClauseFromConfig($config, self::ALLOWED_COLUMNS);
        $this->assertSame('', $result);
    }

    public function testMixOfAllowedAndDisallowedColumns(): void
    {
        $config = new SearchQueryConfig();
        $config->addSearchFieldOrder(new SearchFieldOrder('name', true));
        $config->addSearchFieldOrder(new SearchFieldOrder('DROP TABLE users', true));
        $config->addSearchFieldOrder(new SearchFieldOrder('date', false));

        $result = SearchConfigClauseBuilder::buildSortOrderClauseFromConfig($config, self::ALLOWED_COLUMNS);
        $this->assertSame('ORDER BY name ASC, date DESC', $result);
    }

    public function testEmptyAllowedColumnsFiltersEverything(): void
    {
        $config = new SearchQueryConfig();
        $config->addSearchFieldOrder(new SearchFieldOrder('name', true));

        $result = SearchConfigClauseBuilder::buildSortOrderClauseFromConfig($config, []);
        $this->assertSame('', $result);
    }
}
