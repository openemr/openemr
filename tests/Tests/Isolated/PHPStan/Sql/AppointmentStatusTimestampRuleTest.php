<?php

/**
 * Tests for AppointmentStatusTimestampRule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\Sql;

use OpenEMR\PHPStan\Rules\Sql\AppointmentStatusTimestampRule;
use OpenEMR\PHPStan\Rules\Sql\SqlSinkResolver;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<AppointmentStatusTimestampRule>
 */
final class AppointmentStatusTimestampRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new AppointmentStatusTimestampRule(new SqlSinkResolver());
    }

    public function testFlagsStatusUpdateMissingPcTime(): void
    {
        $this->analyse(
            [__DIR__ . '/data/appointment_status_missing_pc_time.php'],
            [[
                'Appointment status updates must also set pc_time. '
                . 'Use AppointmentService::persistAppointmentStatus().',
                6,
            ]],
        );
    }

    public function testAllowsStatusUpdateThatSetsPcTime(): void
    {
        $this->analyse(
            [__DIR__ . '/data/appointment_status_with_pc_time.php'],
            [],
        );
    }

    public function testIgnoresInsertsThatListPcApptstatus(): void
    {
        $this->analyse(
            [__DIR__ . '/data/appointment_status_insert.php'],
            [],
        );
    }

    public function testFlagsStatusInSetWhenPcTimeIsOnlyInWhere(): void
    {
        $this->analyse(
            [__DIR__ . '/data/appointment_status_pc_time_only_in_where.php'],
            [[
                'Appointment status updates must also set pc_time. '
                . 'Use AppointmentService::persistAppointmentStatus().',
                6,
            ]],
        );
    }

    public function testIgnoresPcApptstatusOnlyInWhere(): void
    {
        $this->analyse(
            [__DIR__ . '/data/appointment_status_in_where_only.php'],
            [],
        );
    }
}
