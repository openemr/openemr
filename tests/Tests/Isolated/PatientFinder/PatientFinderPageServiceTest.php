<?php

/**
 * Isolated PatientFinderPageService Test
 *
 * Checks that the finder page's column view (headers, per-column search inputs,
 * DataTables columns/order JSON, column count) is built from the shared registry,
 * including module columns and the Name column's link renderer.
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
    private function service(): PatientFinderPageService
    {
        // Identity helpers so assertions read against the raw strings.
        return new PatientFinderPageService(
            static fn(string $s): string => $s,
            static fn(string $s): string => $s,
            static fn(string $s): string => $s
        );
    }

    public function testBuildsColumnViewFromRegistry(): void
    {
        $registry = new PatientFinderColumnRegistry();
        $registry->add(PatientFinderColumn::fromTable('name', 'Full Name'));
        $registry->add(PatientFinderColumn::fromTable('sex', 'Sex', defaultSortDir: 'desc'));
        $registry->add(PatientFinderColumn::enriched('ihi', 'IHI'));

        $view = $this->service()->buildColumnView($registry);

        $this->assertSame(3, $view['colcount']);
        $this->assertSame(
            '{"sName": "name", "mRender": wrapInLink}, {"sName": "sex"}, {"sName": "ihi"}',
            $view['coljson']
        );
        $this->assertSame('["0", "asc"], ["1", "desc"], ["2", "asc"]', $view['orderjson']);

        $this->assertStringContainsString('<th>Full Name</th>', $view['header']);
        $this->assertStringContainsString('<th>Sex</th>', $view['header']);
        $this->assertStringContainsString('<th>IHI</th>', $view['header']);

        // The name column's placeholder is "Search by Name", not "Full Name".
        $this->assertStringContainsString("placeholder='Search by Name'", $view['header0']);
        $this->assertStringContainsString("placeholder='Search by Sex'", $view['header0']);
    }

    public function testEmptyRegistryYieldsEmptyView(): void
    {
        $view = $this->service()->buildColumnView(new PatientFinderColumnRegistry());

        $this->assertSame(0, $view['colcount']);
        $this->assertSame('', $view['coljson']);
        $this->assertSame('', $view['orderjson']);
        $this->assertSame('', $view['header']);
    }
}
