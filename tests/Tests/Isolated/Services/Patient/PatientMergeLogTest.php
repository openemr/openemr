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

use OpenEMR\Services\Patient\PatientMergeLog;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class PatientMergeLogTest extends TestCase
{
    #[Test]
    public function startsEmpty(): void
    {
        $log = new PatientMergeLog();

        $this->assertTrue($log->isEmpty());
        $this->assertSame([], $log->getSteps());
    }

    #[Test]
    public function preservesInsertionOrder(): void
    {
        $log = new PatientMergeLog();
        $log->add('first');
        $log->add('second');
        $log->add('third');

        $this->assertFalse($log->isEmpty());
        $this->assertSame(['first', 'second', 'third'], $log->getSteps());
    }

    /**
     * A merge repeats the same statement across many tables, so identical steps must all survive --
     * the operator counts them to see how much work was done.
     */
    #[Test]
    public function keepsDuplicateSteps(): void
    {
        $log = new PatientMergeLog();
        $log->add('DELETE FROM `history_data` WHERE `pid` = ? (1)');
        $log->add('DELETE FROM `history_data` WHERE `pid` = ? (1)');

        $this->assertCount(2, $log->getSteps());
    }

    #[Test]
    public function stepsAreNotAffectedByLaterAdditions(): void
    {
        $log = new PatientMergeLog();
        $log->add('first');
        $captured = $log->getSteps();
        $log->add('second');

        $this->assertSame(['first'], $captured);
    }
}
