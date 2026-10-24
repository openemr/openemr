<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Patient;

use OpenEMR\Services\Patient\PatientMergeRequest;
use OpenEMR\Services\Patient\PatientMergeResult;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class PatientMergeResultTest extends TestCase
{
    #[Test]
    public function completedCarriesStepsAndNoError(): void
    {
        $result = PatientMergeResult::completed(['moved a document', 'Merge complete.']);

        $this->assertTrue($result->successful);
        $this->assertSame(['moved a document', 'Merge complete.'], $result->steps);
        $this->assertNull($result->errorMessage);
    }

    /**
     * A merge that stops partway still has to report what it already did, otherwise an operator
     * cannot tell whether the charts were left half-merged.
     */
    #[Test]
    public function failedKeepsTheStepsCompletedBeforeTheAbort(): void
    {
        $result = PatientMergeResult::failed(
            ['Changing patient ID for document scan.pdf'],
            'Target and source DOB do not match'
        );

        $this->assertFalse($result->successful);
        $this->assertSame(['Changing patient ID for document scan.pdf'], $result->steps);
        $this->assertSame('Target and source DOB do not match', $result->errorMessage);
    }

    #[Test]
    public function failedBeforeAnyWorkReportsNoSteps(): void
    {
        $result = PatientMergeResult::failed([], 'Target and source pid may not be the same!');

        $this->assertFalse($result->successful);
        $this->assertSame([], $result->steps);
        $this->assertSame('Target and source pid may not be the same!', $result->errorMessage);
    }

    #[Test]
    public function requestDefaultsToEnforcingTheIdentityChecks(): void
    {
        $request = new PatientMergeRequest(targetPid: 4, sourcePid: 9);

        $this->assertSame(4, $request->targetPid);
        $this->assertSame(9, $request->sourcePid);
        $this->assertFalse($request->skipIdentityChecks);
    }
}
