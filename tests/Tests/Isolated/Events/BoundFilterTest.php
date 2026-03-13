<?php

/**
 * Isolated BoundFilter Test
 *
 * Tests the BoundFilter value object for SQL WHERE clause building.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Events;

use OpenEMR\Events\BoundFilter;
use PHPUnit\Framework\TestCase;

class BoundFilterTest extends TestCase
{
    public function testDefaultFilterClauseIsOne(): void
    {
        $filter = new BoundFilter();
        $this->assertSame('1', $filter->getFilterClause());
    }

    public function testDefaultBoundValuesIsEmptyArray(): void
    {
        $filter = new BoundFilter();
        $this->assertSame([], $filter->getBoundValues());
    }

    public function testSetFilterClause(): void
    {
        $filter = new BoundFilter();
        $filter->setFilterClause('status = ?');
        $this->assertSame('status = ?', $filter->getFilterClause());
    }

    public function testSetBoundValues(): void
    {
        $filter = new BoundFilter();
        $filter->setBoundValues(['active', 'pending']);
        $this->assertSame(['active', 'pending'], $filter->getBoundValues());
    }

    public function testAddBoundValueString(): void
    {
        $filter = new BoundFilter();
        $filter->addBoundValue('value1');
        $filter->addBoundValue('value2');
        $this->assertSame(['value1', 'value2'], $filter->getBoundValues());
    }

    public function testAddBoundValueInteger(): void
    {
        $filter = new BoundFilter();
        $filter->addBoundValue(123);
        $filter->addBoundValue(456);
        $this->assertSame([123, 456], $filter->getBoundValues());
    }

    public function testAddBoundValueMixedTypes(): void
    {
        $filter = new BoundFilter();
        $filter->addBoundValue('string');
        $filter->addBoundValue(42);
        $filter->addBoundValue('another');
        $this->assertSame(['string', 42, 'another'], $filter->getBoundValues());
    }

    public function testSetBoundValuesOverwritesPrevious(): void
    {
        $filter = new BoundFilter();
        $filter->addBoundValue('first');
        $filter->setBoundValues(['replaced']);
        $this->assertSame(['replaced'], $filter->getBoundValues());
    }

    public function testCompleteFilterScenario(): void
    {
        $filter = new BoundFilter();
        $filter->setFilterClause('status = ? AND type = ?');
        $filter->addBoundValue('active');
        $filter->addBoundValue('primary');

        $this->assertSame('status = ? AND type = ?', $filter->getFilterClause());
        $this->assertSame(['active', 'primary'], $filter->getBoundValues());
    }

    public function testEmptyFilterClause(): void
    {
        $filter = new BoundFilter();
        $filter->setFilterClause('');
        $this->assertSame('', $filter->getFilterClause());
    }

    public function testComplexFilterClause(): void
    {
        $filter = new BoundFilter();
        $clause = '(status = ? OR status = ?) AND created_at > ? AND deleted = ?';
        $filter->setFilterClause($clause);
        $filter->setBoundValues(['active', 'pending', '2024-01-01', 0]);

        $this->assertSame($clause, $filter->getFilterClause());
        $this->assertCount(4, $filter->getBoundValues());
    }
}
