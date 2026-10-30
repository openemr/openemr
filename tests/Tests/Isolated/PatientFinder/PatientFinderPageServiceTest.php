<?php

/**
 * Isolated PatientFinderPageService Test
 *
 * Checks that the finder page's column data (key, title, initial sort direction,
 * link flag) is derived from the shared registry in order, including module
 * columns and the Name column's link flag. No HTML is produced here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistry;
use OpenEMR\Services\PatientFinder\PatientFinderPageService;
use PHPUnit\Framework\TestCase;

class PatientFinderPageServiceTest extends TestCase
{
    public function testBuildsColumnDataFromRegistry(): void
    {
        $registry = new PatientFinderColumnRegistry();
        $registry->add(PatientFinderColumn::fromTable('name', 'Full Name'));
        $registry->add(PatientFinderColumn::fromTable('sex', 'Sex', defaultSortDir: 'desc'));
        $registry->add(PatientFinderColumn::enriched('ihi', 'IHI'));

        $columns = (new PatientFinderPageService())->getColumns($registry);

        $this->assertSame(
            [
                ['key' => 'name', 'title' => 'Full Name', 'sortDir' => 'asc', 'link' => true],
                ['key' => 'sex', 'title' => 'Sex', 'sortDir' => 'desc', 'link' => false],
                ['key' => 'ihi', 'title' => 'IHI', 'sortDir' => 'asc', 'link' => false],
            ],
            $columns
        );
    }

    public function testEmptyRegistryYieldsNoColumns(): void
    {
        $columns = (new PatientFinderPageService())->getColumns(new PatientFinderColumnRegistry());
        $this->assertSame([], $columns);
    }
}
