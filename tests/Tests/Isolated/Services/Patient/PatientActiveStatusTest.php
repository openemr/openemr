<?php

/**
 * Isolated tests for PatientActiveStatus: how a patient_data write settles
 * active, inactive_reason and inactive_date.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Patient;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Services\Patient\PatientActiveStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PatientActiveStatusTest extends TestCase
{
    private const TODAY = '2026-11-21';

    private PatientActiveStatus $status;

    protected function setUp(): void
    {
        $this->status = new PatientActiveStatus(
            new FrozenClock(new DateTimeImmutable(self::TODAY . 'T12:00:00', new DateTimeZone('UTC')))
        );
    }

    public function testWriteWithoutStatusColumnsIsUnchanged(): void
    {
        $changes = ['fname' => 'Ann', 'lname' => 'Lee'];

        $this->assertSame($changes, $this->status->apply($changes, ['active' => '0', 'inactive_reason' => 'moved']));
    }

    public function testCheckedCheckboxMakesThePatientActiveAndClearsTheReasonAndDate(): void
    {
        $result = $this->status->apply(
            ['active' => 'Yes', 'inactive_reason' => 'moved', 'inactive_date' => '2026-01-02'],
            ['active' => '0', 'inactive_reason' => 'moved', 'inactive_date' => '2026-01-02']
        );

        $this->assertSame(1, $result['active']);
        $this->assertSame('', $result['inactive_reason']);
        $this->assertNull($result['inactive_date']);
    }

    public function testUncheckedCheckboxMakesThePatientInactiveAsOfToday(): void
    {
        $result = $this->status->apply(
            ['active' => '', 'inactive_reason' => 'moved', 'inactive_date' => ''],
            ['active' => '1', 'inactive_reason' => '', 'inactive_date' => null]
        );

        $this->assertSame(0, $result['active']);
        $this->assertSame('moved', $result['inactive_reason']);
        $this->assertSame(self::TODAY, $result['inactive_date']);
    }

    public function testInactiveDateGivenByTheUserIsKept(): void
    {
        $result = $this->status->apply(['active' => '', 'inactive_date' => '2026-03-04'], ['active' => '1']);

        $this->assertSame('2026-03-04', $result['inactive_date']);
    }

    public function testAlreadyInactivePatientKeepsTheStoredReasonAndDate(): void
    {
        $result = $this->status->apply(
            ['active' => 0],
            ['active' => '0', 'inactive_reason' => 'transferred', 'inactive_date' => '2025-12-01']
        );

        $this->assertSame('transferred', $result['inactive_reason']);
        $this->assertSame('2025-12-01', $result['inactive_date']);
    }

    public function testPatientGoingInactiveDoesNotInheritAStaleDate(): void
    {
        $result = $this->status->apply(['active' => 0], ['active' => '1', 'inactive_date' => '2020-01-01']);

        $this->assertSame(self::TODAY, $result['inactive_date']);
    }

    public function testEnteringADeceasedDateMakesThePatientInactive(): void
    {
        $result = $this->status->apply(
            ['active' => 'Yes', 'deceased_date' => '2026-11-15 00:00:00', 'inactive_reason' => '', 'inactive_date' => ''],
            ['active' => '1', 'deceased_date' => null]
        );

        $this->assertSame(0, $result['active']);
        $this->assertSame(PatientActiveStatus::DECEASED_REASON, $result['inactive_reason']);
        $this->assertSame('2026-11-15', $result['inactive_date']);
    }

    public function testDeceasedPatientKeepsAReasonAndDateTheUserChose(): void
    {
        $result = $this->status->apply(
            ['deceased_date' => '2026-11-15', 'inactive_reason' => 'other', 'inactive_date' => '2026-11-16'],
            ['active' => '1']
        );

        $this->assertSame('other', $result['inactive_reason']);
        $this->assertSame('2026-11-16', $result['inactive_date']);
    }

    public function testStoredDeceasedDateKeepsThePatientInactive(): void
    {
        $result = $this->status->apply(
            ['active' => 'Yes'],
            ['active' => '0', 'deceased_date' => '2026-11-15 00:00:00', 'inactive_reason' => 'deceased', 'inactive_date' => '2026-11-15']
        );

        $this->assertSame(0, $result['active']);
        $this->assertSame('deceased', $result['inactive_reason']);
        $this->assertSame('2026-11-15', $result['inactive_date']);
    }

    public function testClearingTheDeceasedDateLetsThePatientBeActiveAgain(): void
    {
        $result = $this->status->apply(
            ['active' => 'Yes', 'deceased_date' => ''],
            ['active' => '0', 'deceased_date' => '2026-11-15 00:00:00', 'inactive_reason' => 'deceased']
        );

        $this->assertSame(1, $result['active']);
        $this->assertSame('', $result['inactive_reason']);
    }

    public function testNewPatientWithoutAnActiveValueIsActive(): void
    {
        $result = $this->status->apply(['inactive_reason' => ''], []);

        $this->assertSame(1, $result['active']);
    }

    public function testInvalidInactiveDateFallsBackToToday(): void
    {
        $result = $this->status->apply(['active' => 0, 'inactive_date' => '2026-02-31'], ['active' => '1']);

        $this->assertSame(self::TODAY, $result['inactive_date']);
    }

    #[DataProvider('toBoolProvider')]
    public function testToBool(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, PatientActiveStatus::toBool($value));
    }

    /**
     * @return array<string, array{mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function toBoolProvider(): array
    {
        return [
            'checkbox Yes' => ['Yes', true],
            'translated Yes' => ['Sí', true],
            'stored 1' => ['1', true],
            'int 1' => [1, true],
            'bool true' => [true, true],
            'string true' => ['true', true],
            'unchecked' => ['', false],
            'stored 0' => ['0', false],
            'int 0' => [0, false],
            'bool false' => [false, false],
            'string false' => ['false', false],
            'null' => [null, false],
        ];
    }
}
