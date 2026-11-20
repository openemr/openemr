<?php

/**
 * Isolated tests for the demo seed appointment planner: clinic slots,
 * statuses by time, written-out and generated Visit Lists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\DemoSeed;

use OpenEMR\DemoSeed\Import\ImportedPatient;
use OpenEMR\DemoSeed\Plan\AppointmentPlanner;
use OpenEMR\DemoSeed\Plan\PlannedAppointment;
use OpenEMR\DemoSeed\Plan\PlannedVisitList;
use OpenEMR\DemoSeed\Plan\PlannedVisitListEntry;
use OpenEMR\DemoSeed\PostalAddress;
use OpenEMR\DemoSeed\SeedFile\SeedFileParser;
use OpenEMR\DemoSeed\Synthea\SyntheaPatient;
use PHPUnit\Framework\TestCase;

final class AppointmentPlannerTest extends TestCase
{
    /** A Wednesday, mid-morning. */
    private const NOW = '2026-11-18 10:00:00';

    private const HEADER = <<<'YAML'
        version: 1
        seed: 42
        synthea: {count: 4, seed: 7, clinician_seed: 8, reference_date: 2026-11-18, years_of_history: 5, plain_names: true}
        facilities:
          - {key: main, name: Main, street: 1 Main St, city: Northampton, state: MA, postal_code: "01060"}
        providers:
          - {key: doc, username: doc1, first_name: Dana, last_name: Lee, npi: "1356789010", facility: main}
          - {key: rn, username: rn1, first_name: Jo, last_name: Park, npi: "1689012346", facility: main}

        YAML;

    private const ELDERS_AND_FAMILY = <<<'YAML'
        cases:
          - key: family
            patients:
              - {key: elder, pick: {age: 80+}}

        YAML;

    public function testClinicDrawsFromTheProvidersPanelFirst(): void
    {
        [$appointments] = $this->plan(<<<'YAML'
            appointments:
              days: 0..0
              clinics:
                - {provider: doc, facility: main, hours: "09:00-09:30", fill: 100}
              categories: {office_visit: 1}
            YAML, panels: [3 => 'doc', 4 => 'doc', 1 => 'rn']);

        $this->assertSame(['09:00', '09:15'], array_map(
            static fn (PlannedAppointment $appointment): string => $appointment->start->format('H:i'),
            $appointments,
        ));
        $this->assertSame([3, 4], $this->sorted(array_map(static fn (PlannedAppointment $appointment): int => $appointment->pid, $appointments)));
        $this->assertSame('doc', $appointments[0]->providerKey);
        $this->assertSame('main', $appointments[0]->facilityKey);
        $this->assertSame(15, $appointments[0]->durationMinutes);
    }

    public function testPatientGetsOneAppointmentADay(): void
    {
        [$appointments] = $this->plan(<<<'YAML'
            appointments:
              days: 0..0
              clinics:
                - {provider: doc, facility: main, hours: "09:00-11:00", fill: 100}
              categories: {office_visit: 1}
            YAML);

        $pids = array_map(static fn (PlannedAppointment $appointment): int => $appointment->pid, $appointments);
        $this->assertCount(4, $pids, 'four patients fill four of the eight slots');
        $this->assertSame([1, 2, 3, 4], $this->sorted($pids));
    }

    public function testLongerCategoryTakesWholeSlotsAndOnlyClinicDays(): void
    {
        [$appointments] = $this->plan(<<<'YAML'
            appointments:
              days: 0..6
              clinics:
                - {provider: doc, facility: main, weekdays: [Mon], hours: "09:00-10:00", fill: 100}
              categories: {new_patient: 1}
            YAML, categoryMinutes: ['new_patient' => 30]);

        $this->assertSame(
            [['2026-11-23 09:00', 30], ['2026-11-23 09:30', 30]],
            array_map(
                static fn (PlannedAppointment $appointment): array => [$appointment->start->format('Y-m-d H:i'), $appointment->durationMinutes],
                $appointments,
            ),
        );
    }

    public function testStatusFollowsTheTimeTheLoaderRuns(): void
    {
        [$appointments] = $this->plan(<<<'YAML'
            appointments:
              days: -1..1
              clinics:
                - {provider: doc, facility: main, hours: "09:00-09:15, 11:00-11:15", fill: 100}
              categories: {office_visit: 1}
              status:
                past: {">": 1}
                today: {"@": 1}
                future: {"-": 1}
            YAML);

        $this->assertSame(
            [
                '2026-11-17 09:00 >',
                '2026-11-17 11:00 >',
                '2026-11-18 09:00 @',   // started before 10:00
                '2026-11-18 11:00 -',   // later today
                '2026-11-19 09:00 -',
                '2026-11-19 11:00 -',
            ],
            array_map(
                static fn (PlannedAppointment $appointment): string => $appointment->start->format('Y-m-d H:i') . ' ' . $appointment->status,
                $appointments,
            ),
        );
    }

    public function testWrittenOutListKeepsOrderAndStopsAndMovesOffTheWeekend(): void
    {
        [, $lists] = $this->plan(<<<'YAML'
            appointments:
              days: 0..7
              visit_lists:
                - {provider: rn, facility: main, time: "08:00-16:00", patients: 2}
                - provider: rn
                  facility: main
                  day: 3
                  time: "08:00-12:00"
                  patients:
                    - {any: true, order: 5}
                    - {patient: elder, order: 2, stop: 4}
            YAML, casePatients: ['elder' => 2]);

        $monday = $this->listOn($lists, '2026-11-23');
        $this->assertSame('08:00', $monday->start->format('H:i'));
        $this->assertSame('12:00', $monday->end->format('H:i'));
        $this->assertCount(2, $monday->entries);
        [$elder, $any] = $monday->entries;
        $this->assertSame([2, 2, 4], [$elder->pid, $elder->sequence, $elder->stop]);
        $this->assertNotSame(2, $any->pid);
        $this->assertSame([5, null], [$any->sequence, $any->stop]);
        $this->assertSame('office_visit', $elder->category);

        // the rule makes the other weekdays' lists, and none on top of the written-out one
        $this->assertSame(
            ['2026-11-18', '2026-11-19', '2026-11-20', '2026-11-23', '2026-11-24', '2026-11-25'],
            array_map(static fn (PlannedVisitList $list): string => $list->start->format('Y-m-d'), $lists),
        );
    }

    public function testRuleDrawsPreferredPatientsAndGroupsStopsByAddress(): void
    {
        $shared = new PostalAddress('9 Pine Street', 'Quincy', 'MA', '02169');
        $patients = [
            $this->patient(1, '1950-01-01', $shared),
            $this->patient(2, '1990-01-01', new PostalAddress('2 Oak Street', 'Amherst', 'MA', '01002')),
            $this->patient(3, '1940-01-01', new PostalAddress('3 Ash Street', 'Boston', 'MA', '02116')),
            $this->patient(4, '1952-01-01', $shared),
        ];
        [, $lists] = $this->plan(<<<'YAML'
            appointments:
              days: 0..0
              visit_lists:
                - {provider: rn, facility: main, time: "08:00-16:00", patients: 3, prefer: {age: 65+}}
            YAML, patients: $patients);

        $this->assertCount(1, $lists);
        $entries = $lists[0]->entries;
        $this->assertSame([1, 3, 4], $this->sorted(array_map(static fn (PlannedVisitListEntry $entry): int => $entry->pid, $entries)));
        $this->assertSame([1, 2, 3], array_map(static fn (PlannedVisitListEntry $entry): int => $entry->sequence, $entries));
        $stops = [];
        foreach ($entries as $entry) {
            $this->assertNotNull($entry->stop, 'rule lists give every patient a stop');
            $stops[$entry->pid] = $entry->stop;
        }
        $this->assertSame($stops[1], $stops[4], 'patients at one address share a stop');
        $this->assertNotSame($stops[1], $stops[3]);
        $this->assertSame([1, 2], $this->sorted(array_values(array_unique($stops))));
    }

    public function testSamePlanEachTime(): void
    {
        $yaml = <<<'YAML'
            appointments:
              days: -7..21
              clinics:
                - {provider: doc, facility: main, hours: "08:30-12:00, 13:00-17:00", fill: 60}
              categories: {office_visit: 5, new_patient: 1}
              status:
                past: {">": 8, "?": 2}
                today: {"@": 1, "-": 1}
                future: {"-": 7, "x": 3}
              visit_lists:
                - {provider: rn, facility: main, weekdays: [Mon, Wed, Fri], time: "08:00-16:00", patients: 1-3}
            YAML;

        $this->assertEquals($this->plan($yaml), $this->plan($yaml));
    }

    /**
     * @param list<ImportedPatient>|null $patients
     * @param array<int, string> $panels
     * @param array<string, int> $casePatients
     * @param array<string, int> $categoryMinutes
     * @return array{list<PlannedAppointment>, list<PlannedVisitList>}
     */
    private function plan(
        string $appointmentsYaml,
        ?array $patients = null,
        array $panels = [],
        array $casePatients = [],
        array $categoryMinutes = [],
    ): array {
        $yaml = self::HEADER . ($casePatients === [] ? '' : self::ELDERS_AND_FAMILY) . $appointmentsYaml;
        $seedFile = (new SeedFileParser())->parse($yaml);
        $patients ??= [
            $this->patient(1, '1960-01-10', new PostalAddress('1 Elm Street', 'Northampton', 'MA', '01060')),
            $this->patient(2, '1940-05-05', new PostalAddress('2 Oak Street', 'Amherst', 'MA', '01002')),
            $this->patient(3, '1983-03-03', new PostalAddress('3 Ash Street', 'Boston', 'MA', '02116')),
            $this->patient(4, '2001-07-07', new PostalAddress('4 Fir Street', 'Quincy', 'MA', '02169')),
        ];
        $homes = [];
        foreach ($patients as $patient) {
            if ($patient->synthea->address !== null) {
                $homes[$patient->pid] = $patient->synthea->address;
            }
        }
        $planner = new AppointmentPlanner(
            $seedFile->appointments,
            $seedFile->seed,
            new \DateTimeImmutable(self::NOW, new \DateTimeZone('UTC')),
            $seedFile->synthea->referenceDate,
            $categoryMinutes,
        );
        return $planner->plan($patients, $panels, $casePatients, $homes);
    }

    private function patient(int $pid, string $birthDate, PostalAddress $address): ImportedPatient
    {
        $birth = \DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate, new \DateTimeZone('UTC'));
        $this->assertNotFalse($birth);
        return new ImportedPatient($pid, new SyntheaPatient(
            'synthea-' . $pid,
            'ccda-' . $pid . '.xml',
            'fhir-' . $pid . '.json',
            'First' . $pid,
            'Last' . $pid,
            $birth,
            'female',
            null,
            null,
            $address,
        ));
    }

    /**
     * @param list<PlannedVisitList> $lists
     */
    private function listOn(array $lists, string $date): PlannedVisitList
    {
        foreach ($lists as $list) {
            if ($list->start->format('Y-m-d') === $date) {
                return $list;
            }
        }
        $this->fail('no Visit List on ' . $date);
    }

    /**
     * @param list<int> $values
     * @return list<int>
     */
    private function sorted(array $values): array
    {
        sort($values);
        return $values;
    }
}
