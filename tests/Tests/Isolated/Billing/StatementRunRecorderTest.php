<?php

/**
 * Isolated tests for statement run recording policy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\StatementRunRecorder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class StatementRunRecorderTest extends TestCase
{
    public function testNonEmptyStatementCountsWhenEmailDeliveryFails(): void
    {
        $this->assertTrue(StatementRunRecorder::shouldRecord('Rendered statement content', false, false));
    }

    public function testEmptyStatementDoesNotCount(): void
    {
        $this->assertFalse(StatementRunRecorder::shouldRecord('', false, false));
    }

    public function testWithoutUpdateDoesNotCount(): void
    {
        $this->assertFalse(StatementRunRecorder::shouldRecord('Rendered statement content', true, false));
    }

    public function testDebugRunDoesNotCount(): void
    {
        $this->assertFalse(StatementRunRecorder::shouldRecord('Rendered statement content', false, true));
    }

    public function testDuePatientStatementsRequirePositiveNetBalance(): void
    {
        $this->assertFalse(StatementRunRecorder::hasPositiveDuePatientBalance('-0.01'));
        $this->assertFalse(StatementRunRecorder::hasPositiveDuePatientBalance('0.00'));
        $this->assertTrue(StatementRunRecorder::hasPositiveDuePatientBalance('0.01'));
    }
}
