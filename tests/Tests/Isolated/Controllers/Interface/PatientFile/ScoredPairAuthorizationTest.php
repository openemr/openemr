<?php

/**
 * The Merge Patients page skips its SSN/DOB safeguard only for a pair the duplicate report actually
 * scored against each other. These tests pin the key that authorization is built on.
 *
 * The case that matters: a report listing groups A/B and C/D authorises A+B and C+D, and nothing
 * else. Recording the individual pids instead would wrongly authorise A+C.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\PatientFile;

use OpenEMR\Controllers\Interface\PatientFile\ManageDuplicatePatientsController;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class ScoredPairAuthorizationTest extends TestCase
{
    /**
     * Merge and Keep and Merge and Discard submit the same charts in opposite roles, so the key
     * cannot depend on which one is the target.
     */
    #[Test]
    public function pairKeyIsOrderIndependent(): void
    {
        $this->assertSame(
            ManageDuplicatePatientsController::pairKey(7, 9),
            ManageDuplicatePatientsController::pairKey(9, 7)
        );
    }

    #[Test]
    public function differentPairsGetDifferentKeys(): void
    {
        $keys = [
            ManageDuplicatePatientsController::pairKey(7, 9),
            ManageDuplicatePatientsController::pairKey(7, 21),
            ManageDuplicatePatientsController::pairKey(9, 21),
        ];

        $this->assertSame($keys, array_values(array_unique($keys)));
    }

    /**
     * Keys must not collide through string concatenation: 1+12 and 11+2 are different pairs.
     */
    #[Test]
    public function keysDoNotCollideAcrossDigitBoundaries(): void
    {
        $this->assertNotSame(
            ManageDuplicatePatientsController::pairKey(1, 12),
            ManageDuplicatePatientsController::pairKey(11, 2)
        );
    }

    /**
     * The finding that prompted this: two charts from *different* groups must not be authorised
     * just because both appeared somewhere on the report.
     */
    #[Test]
    public function chartsFromDifferentGroupsAreNotAuthorisedTogether(): void
    {
        // A report showing group A(1)/B(2) and group C(3)/D(4).
        $scored = [
            ManageDuplicatePatientsController::pairKey(1, 2),
            ManageDuplicatePatientsController::pairKey(3, 4),
        ];

        $this->assertContains(ManageDuplicatePatientsController::pairKey(1, 2), $scored);
        $this->assertContains(ManageDuplicatePatientsController::pairKey(4, 3), $scored, 'either direction');

        $this->assertNotContains(
            ManageDuplicatePatientsController::pairKey(1, 3),
            $scored,
            'A and C were never compared with each other'
        );
        $this->assertNotContains(ManageDuplicatePatientsController::pairKey(2, 4), $scored);
        $this->assertNotContains(ManageDuplicatePatientsController::pairKey(1, 4), $scored);
    }

    /**
     * Two matches inside one group were scored against the group's primary, not against each other.
     */
    #[Test]
    public function twoMatchesFromTheSameGroupAreNotAuthorisedTogether(): void
    {
        // Group primary 10 with matches 11 and 12.
        $scored = [
            ManageDuplicatePatientsController::pairKey(10, 11),
            ManageDuplicatePatientsController::pairKey(10, 12),
        ];

        $this->assertNotContains(ManageDuplicatePatientsController::pairKey(11, 12), $scored);
    }
}
