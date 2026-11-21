<?php

/**
 * Isolated tests for the demo seed file parser.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\DemoSeed;

use OpenEMR\DemoSeed\SeedFile\FixedVisitListEntry;
use OpenEMR\DemoSeed\SeedFile\Gender;
use OpenEMR\DemoSeed\SeedFile\SeedFileException;
use OpenEMR\DemoSeed\SeedFile\SeedFileParser;
use OpenEMR\DemoSeed\SeedFile\TimeWindow;
use OpenEMR\DemoSeed\SeedFile\Weekday;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SeedFileParserTest extends TestCase
{
    private const MINIMAL = <<<'YAML'
        version: 1
        seed: 42
        synthea:
          count: 5
          seed: 7
          clinician_seed: 8
          reference_date: 2026-11-19
          years_of_history: 5
          plain_names: true
        YAML;

    private const FACILITY = <<<'YAML'

        facilities:
          - {key: main, name: Main, street: 1 Main St, city: Northampton, state: MA, postal_code: "01060"}
        YAML;

    private const INSURANCE_COMPANIES = <<<'YAML'

        insurance_companies:
          - {key: bcbs, name: Blue Cross, type: 6, cms_id: BC001}
        YAML;

    private const INSURANCE = self::INSURANCE_COMPANIES . <<<'YAML'

        rules:
          insurance:
            plans:
              - when: {age: 18+}

        YAML;

    private const APPOINTMENTS = self::MINIMAL . self::FACILITY . <<<'YAML'

        providers:
          - {key: doc, username: doc1, first_name: Dana, last_name: Lee, npi: "1356789010", facility: main}
        appointments:
          days: -7..21
          clinics:
            - {provider: doc, facility: main, weekdays: [Mon, Wed], hours: "08:30-12:00, 13:00-17:00", fill: 70}
          categories: {office_visit: 3, new_patient: 1}
          status:
            past: {">": 1, "x": 0}
            today: {"@": 1}
            future: {"-": 7, "*": 3}
          visit_lists:
            - {provider: doc, facility: main, time: "08:00-16:00", patients: 5-9, prefer: {age: 65+}}
            - provider: doc
              facility: main
              day: 1
              time: "08:00-12:00"
              patient_category: home_visit
              patients:
                - {any: true, order: 2, stop: 1}
                - {any: true, order: 1}
        YAML;

    public function testParsesMinimalFile(): void
    {
        $seedFile = (new SeedFileParser())->parse(self::MINIMAL);

        $this->assertSame(42, $seedFile->seed);
        $this->assertSame(5, $seedFile->synthea->count);
        $this->assertSame(7, $seedFile->synthea->seed);
        $this->assertSame(8, $seedFile->synthea->clinicianSeed);
        $this->assertSame('2026-11-19', $seedFile->synthea->referenceDate->format('Y-m-d'));
        $this->assertSame(5, $seedFile->synthea->yearsOfHistory);
        $this->assertTrue($seedFile->synthea->plainNames);
        $this->assertNull($seedFile->synthea->state);
        $this->assertNull($seedFile->synthea->ages);
        $this->assertNull($seedFile->synthea->gender);
        $this->assertTrue($seedFile->appointments->isEmpty());
    }

    public function testParsesOptionalSyntheaFilters(): void
    {
        $yaml = self::MINIMAL . "\n  state: Massachusetts\n  city: Northampton\n  ages: 0-90\n  gender: F\n";

        $synthea = (new SeedFileParser())->parse($yaml)->synthea;

        $this->assertSame('Massachusetts', $synthea->state);
        $this->assertSame('Northampton', $synthea->city);
        $this->assertNotNull($synthea->ages);
        $this->assertSame(0, $synthea->ages->min);
        $this->assertSame(90, $synthea->ages->max);
        $this->assertSame(Gender::Female, $synthea->gender);
    }

    public function testAcceptsQuotedReferenceDate(): void
    {
        $yaml = str_replace('reference_date: 2026-11-19', 'reference_date: "2026-11-19"', self::MINIMAL);

        $synthea = (new SeedFileParser())->parse($yaml)->synthea;

        $this->assertSame('2026-11-19', $synthea->referenceDate->format('Y-m-d'));
    }

    public function testParsesAppointments(): void
    {
        $appointments = (new SeedFileParser())->parse(self::APPOINTMENTS)->appointments;

        $this->assertSame(-7, $appointments->days->from);
        $this->assertSame(21, $appointments->days->to);
        $this->assertSame([Weekday::Mon, Weekday::Tue, Weekday::Wed, Weekday::Thu, Weekday::Fri], $appointments->weekdays);

        $this->assertCount(1, $appointments->clinics);
        $clinic = $appointments->clinics[0];
        $this->assertSame('doc', $clinic->providerKey);
        $this->assertSame([Weekday::Mon, Weekday::Wed], $clinic->weekdays);
        $this->assertSame([[510, 720], [780, 1020]], array_map(
            static fn (TimeWindow $window): array => [$window->start, $window->end],
            $clinic->hours,
        ));
        $this->assertSame(70, $clinic->fill);
        $categories = $appointments->categories;
        $this->assertNotNull($categories);
        $this->assertSame(['office_visit', 'new_patient'], $categories->values);
        $this->assertSame([3, 1], $categories->weights);
        // a weight of 0 leaves the status out
        $this->assertSame(['>'], $appointments->status->past->values);
        $this->assertSame(['-', '*'], $appointments->status->future->values);

        $this->assertCount(1, $appointments->visitListRules);
        $rule = $appointments->visitListRules[0];
        $this->assertSame(5, $rule->minPatients);
        $this->assertSame(9, $rule->maxPatients);
        $this->assertSame('office_visit', $rule->patientCategory);
        $this->assertSame(65, $rule->prefer->age?->min);
        $this->assertNull($rule->weekdays);

        $this->assertCount(1, $appointments->fixedVisitLists);
        $list = $appointments->fixedVisitLists[0];
        $this->assertSame(1, $list->day);
        $this->assertSame('home_visit', $list->patientCategory);
        $this->assertSame('appointments.visit_lists[1]', $list->path);
        $this->assertSame([[null, 2, 1], [null, 1, null]], array_map(
            static fn (FixedVisitListEntry $entry): array => [$entry->patientKey, $entry->order, $entry->stop],
            $list->patients,
        ));

        $this->assertSame([
            'appointments.categories.office_visit' => 'office_visit',
            'appointments.categories.new_patient' => 'new_patient',
            'appointments.visit_lists[0].patient_category' => 'office_visit',
            'appointments.visit_lists[1].patient_category' => 'home_visit',
        ], $appointments->categoryReferences());
    }

    public function testCollectsAppointmentStatusesForListValidation(): void
    {
        $seedFile = (new SeedFileParser())->parse(self::APPOINTMENTS);

        $statuses = [];
        foreach ($seedFile->listReferences as $reference) {
            if ($reference->listId === 'apptstat') {
                $statuses[] = $reference->value;
            }
        }
        $this->assertSame(['>', '@', '-', '*'], $statuses);
    }

    #[DataProvider('invalidFileProvider')]
    public function testRejectsInvalidFile(string $yaml, string $expectedMessage): void
    {
        $this->expectException(SeedFileException::class);
        $this->expectExceptionMessage($expectedMessage);

        (new SeedFileParser())->parse($yaml);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidFileProvider(): array
    {
        return [
            'not yaml' => ["version: [1\n", 'not valid YAML'],
            'a list, not a map' => ["- 1\n- 2\n", '(file): expected a map'],
            'unknown top-level key' => [self::MINIMAL . "\npatients: 3\n", '(file): unknown key(s): patients'],
            'unknown synthea key' => [self::MINIMAL . "\n  import: ccda\n", 'synthea: unknown key(s): import'],
            'wrong version' => [str_replace('version: 1', 'version: 2', self::MINIMAL), 'version: this loader reads version 1'],
            'missing seed' => [str_replace("seed: 42\n", '', self::MINIMAL), 'seed: required'],
            'zero count' => [str_replace('count: 5', 'count: 0', self::MINIMAL), 'synthea.count: must be at least 1'],
            'count as text' => [str_replace('count: 5', 'count: five', self::MINIMAL), 'synthea.count: required, must be a whole number'],
            'negative history' => [str_replace('years_of_history: 5', 'years_of_history: -1', self::MINIMAL), 'synthea.years_of_history: must not be negative'],
            'plain names not boolean' => [str_replace('plain_names: true', 'plain_names: yes please', self::MINIMAL), 'synthea.plain_names: required, must be true or false'],
            'impossible date' => [str_replace('reference_date: 2026-11-19', 'reference_date: "2026-02-30"', self::MINIMAL), 'synthea.reference_date'],
            'city without state' => [self::MINIMAL . "\n  city: Northampton\n", 'synthea.city: needs synthea.state'],
            'bad gender' => [self::MINIMAL . "\n  gender: X\n", 'synthea.gender: must be M or F'],
            'bad ages' => [self::MINIMAL . "\n  ages: old\n", 'synthea.ages: expected an age range'],
            'reversed ages' => [self::MINIMAL . "\n  ages: 90-10\n", 'synthea.ages: the first age must not be larger'],
            'provider with bad npi' => [
                self::MINIMAL . self::FACILITY . "\nproviders:\n  - {key: a, username: auser, first_name: A, last_name: Bee, npi: \"1234567890\", facility: main}\n",
                'providers[0].npi',
            ],
            'provider at unknown facility' => [
                self::MINIMAL . self::FACILITY . "\nproviders:\n  - {key: a, username: auser, first_name: A, last_name: Bee, npi: \"1356789010\", facility: west}\n",
                'facility',
            ],
            'address rules without names' => [
                self::MINIMAL . "\nrules:\n  addresses:\n    - {percent: 10, add: {type: postal, use: home, po_box: true}}\n",
                'names: required when rules.addresses makes up streets',
            ],
            'policy with unknown company' => [
                self::MINIMAL . self::INSURANCE . "        primary: {from: [bcbs, aetna], subscriber: self}\n",
                'rules.insurance: no insurance company has key "aetna"',
            ],
            'secondary without primary' => [
                self::MINIMAL . self::INSURANCE . "        secondary: {from: [bcbs]}\n",
                'rules.insurance.plans[0]: a secondary or tertiary policy needs a primary',
            ],
            'self pay and a policy' => [
                self::MINIMAL . self::INSURANCE . "        self_pay: true\n        primary: {from: [bcbs]}\n",
                'give self_pay: true or a primary policy, not both',
            ],
            'unknown rule subscriber' => [
                self::MINIMAL . self::INSURANCE . "        primary: {from: [bcbs], subscriber: cousin}\n",
                'rules.insurance.plans[0].primary.subscriber: must be self, spouse or guardian',
            ],
            'bad policy number format' => [
                self::MINIMAL . self::INSURANCE . "        primary: {from: [bcbs]}\n    policy_numbers: {format: \"{CMS}-{X}\"}\n",
                'rules.insurance.policy_numbers.format: unknown part {X}',
            ],
            'case subscriber not a case patient' => [
                self::MINIMAL . self::INSURANCE_COMPANIES . "\ncases:\n  - key: c\n    patients:\n      - {key: a, pick: {age: 18+}}\n    insurance:\n      a: {primary: {company: bcbs, subscriber: {patient: b}}}\n",
                'cases[0].insurance.a.primary.subscriber: no case patient has key "b"',
            ],
            'case subscriber not a related person' => [
                self::MINIMAL . self::INSURANCE_COMPANIES . "\ncases:\n  - key: c\n    patients:\n      - {key: a, pick: {age: 18+}}\n    insurance:\n      a: {primary: {company: bcbs, subscriber: {related: dad}}}\n",
                'no related person in this case has key "dad"',
            ],
            'appointment days reversed' => [
                str_replace('days: -7..21', 'days: 21..-7', self::APPOINTMENTS),
                'appointments.days: expected a day like 3 or a range like "-7..21"',
            ],
            'unknown weekday' => [
                str_replace('weekdays: [Mon, Wed]', 'weekdays: [Mon, Funday]', self::APPOINTMENTS),
                'appointments.clinics[0].weekdays[1]: expected Mon',
            ],
            'overlapping clinic hours' => [
                str_replace('13:00-17:00', '11:00-17:00', self::APPOINTMENTS),
                'appointments.clinics[0].hours: time windows must be in order and not overlap',
            ],
            'clinic hours backwards' => [
                str_replace('08:30-12:00, 13:00-17:00', '12:00-08:30', self::APPOINTMENTS),
                'appointments.clinics[0].hours: expected a time window',
            ],
            'clinic fill over 100' => [
                str_replace('fill: 70', 'fill: 170', self::APPOINTMENTS),
                'appointments.clinics[0].fill: must be from 0 to 100',
            ],
            'clinic with unknown provider' => [
                str_replace('{provider: doc, facility: main, weekdays', '{provider: nobody, facility: main, weekdays', self::APPOINTMENTS),
                'appointments.clinics[0].provider: no provider has key "nobody"',
            ],
            'clinics without categories' => [
                str_replace('  categories: {office_visit: 3, new_patient: 1}' . "\n", '', self::APPOINTMENTS),
                'appointments.categories: required when there are clinics',
            ],
            'all category weights zero' => [
                str_replace('{office_visit: 3, new_patient: 1}', '{office_visit: 0}', self::APPOINTMENTS),
                'appointments.categories: needs at least one value with a weight above 0',
            ],
            'visit list patient count reversed' => [
                str_replace('patients: 5-9', 'patients: 9-5', self::APPOINTMENTS),
                'appointments.visit_lists[0].patients: expected a number of patients',
            ],
            'visit list at unknown facility' => [
                str_replace('{provider: doc, facility: main, time', '{provider: doc, facility: west, time', self::APPOINTMENTS),
                'appointments.visit_lists[0].facility: no facility has key "west"',
            ],
            'fixed list entry with patient and any' => [
                str_replace('{any: true, order: 1}', '{patient: elder, any: true, order: 1}', self::APPOINTMENTS),
                'appointments.visit_lists[1].patients[1]: give patient: <case patient key> or any: true',
            ],
            'fixed list entry not a case patient' => [
                str_replace('{any: true, order: 1}', '{patient: elder, order: 1}', self::APPOINTMENTS),
                'appointments.visit_lists[1].patients[1].patient: no case patient has key "elder"',
            ],
            'fixed list orders repeated' => [
                str_replace('order: 2, stop: 1', 'order: 1, stop: 1', self::APPOINTMENTS),
                'appointments.visit_lists[1].patients: each patient needs its own order',
            ],
            'fixed list stop zero' => [
                str_replace('stop: 1}', 'stop: 0}', self::APPOINTMENTS),
                'appointments.visit_lists[1].patients[0].stop: must be at least 1',
            ],
            'same_as a later patient' => [
                self::MINIMAL . "\ncases:\n  - key: c\n    patients:\n      - {key: a, pick: {age: 18+}, last_name: {same_as: b}}\n      - {key: b, pick: {age: 18+}}\n",
                'same_as',
            ],
        ];
    }

    public function testRejectsMissingFile(): void
    {
        $this->expectException(SeedFileException::class);
        $this->expectExceptionMessage('Seed file not found');

        (new SeedFileParser())->parseFile(__DIR__ . '/does-not-exist.yaml');
    }
}
