<?php

/**
 * Isolated PatientFinderDetailService Test
 *
 * Checks that an expanded finder row reads only the fields the Patient Finder
 * Detail layout shows, builds combined columns from their SQL, applies the
 * finder's patient filter, finds nothing for a bad or hidden pid, and renders the
 * layout with the values it read.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Events\PatientFinder\PatientFinderFilterEvent;
use OpenEMR\Services\PatientFinder\PatientFinderAppointments;
use OpenEMR\Services\PatientFinder\PatientFinderBuiltInColumns;
use OpenEMR\Services\PatientFinder\PatientFinderDetailService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PatientFinderDetailServiceTest extends TestCase
{
    /** @var list<array{string, array<mixed>}> */
    private array $queries = [];

    /**
     * @param list<string>       $layoutFields the detail layout's field ids; "id:34" gives a field
     *                                         that data type instead of text
     * @param list<array<mixed>> $patientRows  what the patient query returns
     */
    private function service(
        array $layoutFields,
        array $patientRows,
        ?EventDispatcher $dispatcher = null,
        bool $appointmentAccess = true
    ): PatientFinderDetailService {
        $fetch = function (string $sql, array $binds) use ($layoutFields, $patientRows): array {
            $this->queries[] = [$sql, $binds];
            if (str_contains($sql, 'FROM layout_options')) {
                return array_map(static function (string $field): array {
                    [$id, $type] = array_pad(explode(':', $field, 2), 2, '2');
                    return ['field_id' => $id, 'data_type' => $type];
                }, $layoutFields);
            }
            return $patientRows;
        };
        return new PatientFinderDetailService(
            $dispatcher ?? new EventDispatcher(),
            $fetch,
            static fn(): array => ['pid', 'sex', 'DOB', 'ss'],
            new PatientFinderBuiltInColumns(),
            static fn(string $section, string $value): bool => $appointmentAccess,
            static fn(string $layoutId, array $values): string
                => $layoutId . ':' . json_encode($values, JSON_THROW_ON_ERROR),
            new PatientFinderAppointments(
                static fn(string $sql, array $binds): array => [
                    ['pc_eid' => 1, 'pc_eventDate' => '2026-12-01', 'pc_startTime' => '09:30:00', 'pc_recurrtype' => 0],
                ],
                static fn(string $from, string $to, int $eid): array => [],
                new FrozenClock(new DateTimeImmutable('2026-11-13 12:00:00'))
            )
        );
    }

    /**
     * Only fields the layout shows are read, unknown ids are dropped, and values come back unescaped.
     */
    public function testReadsOnlyTheLayoutFieldsAndReturnsThemRaw(): void
    {
        $service = $this->service(
            ['sex', 'address_full', 'no_such_field', 'bad`id'],
            [['pid' => 7, 'sex' => 'Female', 'address_full' => '1 Main St, Tampa, FL  33601 & Co']]
        );

        $values = $service->values(7);

        $this->assertSame(['sex' => 'Female', 'address_full' => '1 Main St, Tampa, FL  33601 & Co'], $values);
        [$sql, $binds] = end($this->queries) ?: ['', []];
        $this->assertStringStartsWith('SELECT patient_data.pid, patient_data.`sex`, CONCAT_WS(', $sql);
        $this->assertStringContainsString(' AS `address_full` FROM patient_data', $sql);
        $this->assertStringNotContainsString('no_such_field', $sql);
        $this->assertStringNotContainsString('bad', $sql);
        $this->assertStringNotContainsString('`ss`', $sql, 'a field the layout does not show is never read');
        $this->assertSame([7], $binds);
    }

    /**
     * A patient the finder filter hides is not found here either.
     */
    public function testAppliesTheFinderPatientFilter(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            PatientFinderFilterEvent::EVENT_HANDLE,
            static function (PatientFinderFilterEvent $event): void {
                $event->getBoundFilter()->setFilterClause('patient_data.pid NOT IN (?)');
                $event->getBoundFilter()->setBoundValues([42]);
            }
        );
        $service = $this->service(['sex'], [], $dispatcher);

        $this->assertNull($service->values(42), 'a patient the finder hides is not found');
        [$sql, $binds] = end($this->queries) ?: ['', []];
        $this->assertStringContainsString('WHERE patient_data.pid = ? AND (patient_data.pid NOT IN (?)) LIMIT 1', $sql);
        $this->assertSame([42, 42], $binds);
    }

    /**
     * The HTML is the detail layout rendered with the patient's values.
     */
    public function testHtmlRendersTheDetailLayoutWithThePatientsValues(): void
    {
        $service = $this->service(['sex'], [['pid' => 7, 'sex' => 'Female']]);

        $this->assertSame('PTFINDER:{"sex":"Female"}', $service->html(7));
    }

    /**
     * A patient that is not found has no HTML, so the endpoint can answer 404.
     */
    public function testHtmlIsNullForAPatientNotFound(): void
    {
        $service = $this->service(['sex'], []);

        $this->assertNull($service->html(7));
        $this->assertNull($service->html(0));
    }

    /**
     * A pid that is not positive runs no query.
     */
    public function testNonPositivePidIsNotLookedUp(): void
    {
        $service = $this->service(['sex'], [['pid' => 1, 'sex' => 'Male']]);

        $this->assertNull($service->values(0));
        $this->assertNull($service->values(-3));
        $this->assertSame([], $this->queries);
    }

    /**
     * Rows offer to expand only when the layout has fields.
     */
    public function testEnabledOnlyWhenTheLayoutShowsSomething(): void
    {
        $this->assertTrue($this->service(['sex'], [])->isEnabled());
        $this->assertFalse($this->service([], [])->isEnabled());
    }

    /**
     * An appointment field in the layout is not read for a user without patients/appt.
     */
    public function testAppointmentFieldNeedsTheAppointmentAcl(): void
    {
        $denied = $this->service(['sex', 'next_appointment'], [['pid' => 7, 'sex' => 'Male']], null, false)->values(7);
        $allowed = $this->service(['sex', 'next_appointment'], [['pid' => 7, 'sex' => 'Male']])->values(7);

        $this->assertSame(['sex' => 'Male'], $denied);
        $this->assertSame(['sex' => 'Male', 'next_appointment' => '2026-12-01 09:30'], $allowed);
        $this->assertStringNotContainsString('next_appointment', $this->lastSql(), 'worked out after the query, not in it');
    }

    /**
     * A NationNotes field, which the renderer prints unescaped, is never read; nor is a field
     * whose id a NationNotes row shares, since the renderer looks values up by id.
     */
    public function testNationNotesFieldsAreNeverRead(): void
    {
        $this->service(['sex', 'ss:34', 'DOB', 'DOB:34', 'sex'], [['pid' => 7, 'sex' => 'Male']])->values(7);
        $sql = $this->lastSql();

        $this->assertStringContainsString('patient_data.`sex`', $sql);
        $this->assertSame(1, substr_count($sql, 'patient_data.`sex`'), 'a repeated id is read once');
        $this->assertStringNotContainsString('`ss`', $sql);
        $this->assertStringNotContainsString('`DOB`', $sql, 'an id shared with a NationNotes row is not read');
    }

    /**
     * A layout whose only active fields are NationNotes does not offer to expand rows.
     */
    public function testOnlyNationNotesFieldsLeaveTheDetailOff(): void
    {
        $this->assertFalse($this->service(['ss:34'], [])->isEnabled());
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sessionPatientFieldProvider(): array
    {
        return [
            'allergies' => ['allergies:24'],
            'previous names' => ['previous_names:52'],
            'address list' => ['addresses:54'],
            'telecom list' => ['telecoms:55'],
            'relation list' => ['relations:56'],
        ];
    }

    /**
     * A row the renderer fills from the open chart's patient would show that patient's data
     * under the expanded one, so a layout with one is never shown, nor its patient read.
     */
    #[DataProvider('sessionPatientFieldProvider')]
    public function testALayoutReadingTheSessionPatientIsNeverShown(string $field): void
    {
        $service = $this->service(['sex', $field], [['pid' => 7, 'sex' => 'Female']]);

        $this->assertFalse($service->isEnabled());
        $this->assertNull($service->html(7));
        $this->assertStringNotContainsString('FROM patient_data', $this->lastSql());
    }

    /**
     * The SQL of the last query the service ran.
     */
    private function lastSql(): string
    {
        $last = end($this->queries);
        return $last === false ? '' : $last[0];
    }
}
