<?php

/**
 * CSV rows and date of birth formatting for the appointments report.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Reports\Appointments;

use OpenEMR\Reports\Appointments\AppointmentsReportFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class AppointmentsReportFormatterTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function appointment(): array
    {
        return [
            'ulname' => 'Stone',
            'ufname' => 'Fred',
            'umname' => 'Q',
            'pc_eventDate' => '2026-11-20',
            'pc_startTime' => '09:15:00',
            'fname' => 'Wilma',
            'lname' => 'Slate',
            'address1' => '1 Main St',
            'address2' => 'Apt 2',
            'DOB' => '1960-02-03',
            'pc_catname' => 'Office Visit',
            'pc_apptstatus' => '@',
        ];
    }

    private static function formatter(bool $showAddress): AppointmentsReportFormatter
    {
        return new AppointmentsReportFormatter(
            $showAddress,
            static fn(string $category): string => 'cat:' . $category,
            static fn(string $status): string => 'status:' . $status,
        );
    }

    public function testCsvRowWithAddress(): void
    {
        $rows = self::formatter(true)->csvRows([self::appointment()]);

        $this->assertSame([[
            'Provider' => 'Stone,Fred Q',
            'Date' => '2026-11-20',
            'Time' => '09:15:00',
            'Patient' => 'Wilma Slate',
            'Address' => '1 Main St, Apt 2',
            'DOB' => '1960-02-03',
            'Type' => 'cat:Office Visit',
            'Status' => 'status:@',
        ]], $rows);
    }

    public function testCsvRowLeavesAddressOutWhenNotShown(): void
    {
        $rows = self::formatter(false)->csvRows([self::appointment()]);

        $this->assertSame(
            ['Provider', 'Date', 'Time', 'Patient', 'DOB', 'Type', 'Status'],
            array_keys($rows[0])
        );
    }

    public function testCsvRowForAvailableSlotHasEmptyPatientFields(): void
    {
        $slot = ['ulname' => 'Stone', 'ufname' => 'Fred', 'umname' => null, 'pc_eventDate' => '2026-11-20'];

        $rows = self::formatter(true)->csvRows([$slot]);

        $this->assertSame([[
            'Provider' => 'Stone,Fred ',
            'Date' => '2026-11-20',
            'Time' => '',
            'Patient' => ' ',
            'Address' => '',
            'DOB' => '',
            'Type' => 'cat:',
            'Status' => 'status:',
        ]], $rows);
    }

    public function testCsvRowsKeepsAppointmentOrder(): void
    {
        $second = ['pc_startTime' => '10:00:00'] + self::appointment();

        $rows = self::formatter(false)->csvRows([self::appointment(), $second]);

        $this->assertSame(['09:15:00', '10:00:00'], array_column($rows, 'Time'));
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function addressProvider(): array
    {
        return [
            'both lines' => [['address1' => '1 Main St', 'address2' => 'Apt 2'], '1 Main St, Apt 2'],
            'first line only' => [['address1' => '1 Main St', 'address2' => ''], '1 Main St'],
            'null second line' => [['address1' => '1 Main St', 'address2' => null], '1 Main St'],
            'no address' => [[], ''],
        ];
    }

    /**
     * @param array<string, mixed> $appointment
     */
    #[DataProvider('addressProvider')]
    public function testAddress(array $appointment, string $expected): void
    {
        $this->assertSame($expected, AppointmentsReportFormatter::address($appointment));
    }

    /**
     * @return array<string, array{array<string, mixed>, int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dobProvider(): array
    {
        return [
            'ISO format' => [['DOB' => '1960-02-03'], 0, '1960-02-03'],
            'US format' => [['DOB' => '1960-02-03'], 1, '02/03/1960'],
            'international format' => [['DOB' => '1960-02-03'], 2, '03/02/1960'],
            'available slot has no DOB' => [[], 1, ''],
            'null DOB' => [['DOB' => null], 1, ''],
            'empty DOB' => [['DOB' => ''], 1, ''],
        ];
    }

    /**
     * @param array<string, mixed> $appointment
     */
    #[DataProvider('dobProvider')]
    public function testDisplayDob(array $appointment, int $dateDisplayFormat, string $expected): void
    {
        $globals = new ParameterBag(['date_display_format' => $dateDisplayFormat]);

        $this->assertSame($expected, AppointmentsReportFormatter::displayDob($appointment, $globals));
    }
}
