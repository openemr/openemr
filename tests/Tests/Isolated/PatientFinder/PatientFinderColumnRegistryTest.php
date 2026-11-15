<?php

/**
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
use PHPUnit\Framework\TestCase;

class PatientFinderColumnRegistryTest extends TestCase
{
    private function registry(string ...$keys): PatientFinderColumnRegistry
    {
        $registry = new PatientFinderColumnRegistry();
        foreach ($keys as $key) {
            $registry->add(PatientFinderColumn::fromTable($key, ucfirst($key)));
        }
        return $registry;
    }

    public function testAddBeforeInsertsAheadOfTheTarget(): void
    {
        $registry = $this->registry('name', 'dob');
        $registry->addBefore('dob', PatientFinderColumn::fromTable('ihi', 'IHI'));

        $this->assertSame(['name', 'ihi', 'dob'], $registry->keys());
    }

    public function testAddBeforeUnknownTargetAppends(): void
    {
        $registry = $this->registry('name', 'dob');
        $registry->addBefore('nope', PatientFinderColumn::fromTable('ihi', 'IHI'));

        $this->assertSame(['name', 'dob', 'ihi'], $registry->keys());
    }

    public function testAddBeforeMovesAnExistingColumn(): void
    {
        $registry = $this->registry('name', 'dob', 'ihi');
        $registry->addBefore('name', PatientFinderColumn::fromTable('ihi', 'IHI'));

        $this->assertSame(['ihi', 'name', 'dob'], $registry->keys());
    }

    public function testAddBeforeItselfKeepsTheColumnInPlace(): void
    {
        // Regression: moving a column before itself must not silently drop it.
        $registry = $this->registry('name', 'ihi', 'dob');
        $replacement = PatientFinderColumn::fromTable('ihi', 'IHI Updated');
        $registry->addBefore('ihi', $replacement);

        $this->assertSame(['name', 'ihi', 'dob'], $registry->keys());
        $this->assertSame('IHI Updated', $registry->get('ihi')?->title);
    }
}
