<?php

/**
 * Isolated tests for the demo seed planner: case picks, rules and
 * reproducibility.
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
use OpenEMR\DemoSeed\Plan\PatientUpdate;
use OpenEMR\DemoSeed\Plan\Plan;
use OpenEMR\DemoSeed\Plan\PlanException;
use OpenEMR\DemoSeed\Plan\PlannedPerson;
use OpenEMR\DemoSeed\Plan\PlannedTelecom;
use OpenEMR\DemoSeed\Plan\Planner;
use OpenEMR\DemoSeed\PostalAddress;
use OpenEMR\DemoSeed\SeedFile\SeedFileParser;
use OpenEMR\DemoSeed\Synthea\SyntheaPatient;
use PHPUnit\Framework\TestCase;

final class PlannerTest extends TestCase
{
    private const SEED_FILE = <<<'YAML'
        version: 1
        seed: 42
        synthea:
          count: 4
          seed: 7
          clinician_seed: 8
          reference_date: 2026-11-19
          years_of_history: 5
          plain_names: true
        contact_data:
          area_codes: [413]
          email_domain: example.com
        names:
          female: [Ana]
          male: [Ben]
          last: [Chen]
        facilities:
          - {key: main, name: Main, street: 1 Main St, city: Northampton, state: MA, postal_code: "01060", phone: (413) 555-0100}
        providers:
          - {key: lee, username: dlee, first_name: Dana, last_name: Lee, npi: "1356789010", facility: main}
          - {key: ortiz, username: rortiz, first_name: Rafael, last_name: Ortiz, npi: "1467890129", facility: main}
        rules:
          care_team:
            providers: [lee, ortiz]
          related_people:
            people:
              - when: {age: 18+, synthea_marital: [M]}
                percent: 100
                add: {relationship: SPS, role: NOK, emergency_contact: true, same_address: true}
              - when: {age: 18+}
                percent: 100
                add: {relationship: [CHILD, FRND], role: ECON}
            contact: {mobile: 100, email: 0}
          telecom:
            - {system: phone, use: mobile, percent: 100}
            - {system: sms, use: mobile, percent: 100, requires: mobile}
            - {system: phone, use: old, percent: 100, inactive: {ended_days_ago: 30-60, reason: Disconnected}}
          addresses:
            - percent: 100
              add: {type: postal, use: home, po_box: true}
        cases:
          - key: couple
            patients:
              - {key: wife, pick: {sex: Female, age: 30-50}, provider: ortiz}
              - {key: husband, pick: {sex: Male, age: 30-50}, last_name: {same_as: wife}, address: {same_as: wife}}
            links:
              - {from: wife, to: husband, relationship: SPS, role: NOK}
            telecom:
              wife: [{system: phone, use: mobile, value: (413) 555-0110}]
        YAML;

    private const NOW = '2026-11-20 14:30:00';

    public function testClaimsTheFirstMatchingPatientsForCases(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        // pid 1 is a man over 50, so the wife is pid 2 and the husband pid 3
        $this->assertSame(['wife' => 2, 'husband' => 3], $plan->casePatients);
    }

    public function testCaseOverridesLastNameAndAddress(): void
    {
        $husband = $this->update($this->plan(self::SEED_FILE), 3);

        $this->assertSame('Zed', $husband->fields['lname']);
        $this->assertSame('2 Oak Street', $husband->fields['street']);
        $this->assertSame('Amherst', $husband->fields['city']);
        $this->assertSame('01002', $husband->fields['postal_code']);
    }

    public function testFillsMaritalStatusAndStateCode(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        $this->assertSame('married', $this->update($plan, 1)->fields['status']);
        $this->assertSame('MA', $this->update($plan, 1)->fields['state']);
        $this->assertArrayNotHasKey('status', $this->update($plan, 4)->fields);
    }

    public function testCareTeamTakesTurnsAndKeepsCaseProviders(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        $this->assertSame('lee', $this->update($plan, 1)->providerKey);
        $this->assertSame('ortiz', $this->update($plan, 2)->providerKey);
        $this->assertSame('ortiz', $this->update($plan, 3)->providerKey);
        $this->assertSame('lee', $this->update($plan, 4)->providerKey);
    }

    public function testCaseLinkMakesAPersonForTheOtherPatient(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        $person = $this->person($plan, 'patient:3');
        $this->assertSame(3, $person->linkedPid);
        $this->assertSame('Zed', $person->lastName);
        $this->assertSame('Male', $person->sex);
        $relations = array_values(array_filter($plan->relations, static fn ($r): bool => $r->ownerPid === 2));
        $this->assertCount(1, $relations);
        $this->assertSame('patient:3', $relations[0]->personRef);
    }

    public function testRulesSkipCasePatients(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        $this->assertSame([], $this->telecomsOf($plan, 3));
        $this->assertSame(['phone|mobile|(413) 555-0110'], array_map(
            static fn (PlannedTelecom $t): string => $t->system . '|' . $t->use . '|' . $t->value,
            $this->telecomsOf($plan, 2),
        ));
    }

    public function testSpouseIsTheOppositeSexAndSharesNameAndAddress(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        $spouse = $this->person($plan, 'rule:1:0');
        $this->assertSame('Female', $spouse->sex);
        $this->assertSame('Ana', $spouse->firstName);
        $this->assertSame('Able', $spouse->lastName);
        $homes = array_values(array_filter(
            $plan->addresses,
            static fn ($a): bool => $a->owner->personRef === 'rule:1:0',
        ));
        $this->assertCount(1, $homes);
        $this->assertSame('1 Elm Street', $homes[0]->address->street);
        $this->assertTrue($homes[0]->primary);
    }

    public function testRelationshipsThatDontFitTheAgeAreSkipped(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        // pid 4 is 25: no adult child, so the second rule picks a friend
        $relations = array_values(array_filter($plan->relations, static fn ($r): bool => $r->ownerPid === 4));
        $this->assertCount(1, $relations);
        $this->assertSame('FRND', $relations[0]->relationship);
        $this->assertSame('Chen', $this->person($plan, $relations[0]->personRef)->lastName);
        $this->assertNull($this->person($plan, $relations[0]->personRef)->birthDate);
    }

    public function testSmsReusesTheMobileNumberAndOldPhoneIsInactive(): void
    {
        $telecoms = $this->telecomsOf($this->plan(self::SEED_FILE), 1);

        $this->assertCount(3, $telecoms);
        [$mobile, $sms, $old] = $telecoms;
        $this->assertSame($mobile->value, $sms->value);
        $this->assertMatchesRegularExpression('/^\(413\) 555-01\d\d$/', $mobile->value);
        $this->assertNotSame('(413) 555-0100', $mobile->value);
        $this->assertNotSame('(413) 555-0110', $mobile->value);
        $this->assertSame([1, 2, 3], [$mobile->rank, $sms->rank, $old->rank]);
        $this->assertTrue($mobile->primary && $mobile->active);
        $this->assertTrue($sms->primary);
        $this->assertFalse($old->active || $old->primary);
        $this->assertSame('Disconnected', $old->reason);
        $this->assertNotNull($old->periodEnd);
        $this->assertLessThan($old->periodEnd, $old->periodStart);
    }

    public function testMobileFillsThePatientCellColumn(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        $mobile = $this->telecomsOf($plan, 1)[0]->value;
        $this->assertSame($mobile, $this->update($plan, 1)->fields['phone_cell']);
        $this->assertSame('(413) 555-0110', $this->update($plan, 2)->fields['phone_cell']);
    }

    public function testPoBoxKeepsTheCityOfHome(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        $boxes = array_values(array_filter($plan->addresses, static fn ($a): bool => $a->owner->pid === 1));
        $this->assertCount(1, $boxes);
        $this->assertMatchesRegularExpression('/^PO Box \d+$/', $boxes[0]->address->street);
        $this->assertSame('Northampton', $boxes[0]->address->city);
        $this->assertFalse($boxes[0]->primary);
    }

    public function testSamePatientsAndSeedGiveTheSamePlan(): void
    {
        $this->assertEquals($this->plan(self::SEED_FILE), $this->plan(self::SEED_FILE));
    }

    public function testFailsWhenNoPatientMatchesACasePick(): void
    {
        $this->expectException(PlanException::class);
        $this->expectExceptionMessage('cases.couple: no unclaimed patient matches the pick for "husband"');

        $this->plan(str_replace('sex: Male, age: 30-50', 'sex: Male, age: 90+', self::SEED_FILE));
    }

    private function plan(string $yaml): Plan
    {
        $seedFile = (new SeedFileParser())->parse($yaml);
        return (new Planner($seedFile, new \DateTimeImmutable(self::NOW)))->plan($this->patients());
    }

    /**
     * @return list<ImportedPatient>
     */
    private function patients(): array
    {
        return [
            $this->patient(1, 'Al', 'Able', '1960-01-10', 'male', 'M', new PostalAddress('1 Elm Street', 'Northampton', 'MA', '01060')),
            $this->patient(2, 'Bea', 'Zed', '1985-05-05', 'female', 'M', new PostalAddress('2 Oak Street', 'Amherst', 'MA', '01002')),
            $this->patient(3, 'Cal', 'Young', '1983-03-03', 'male', 'M', new PostalAddress('3 Ash Street', 'Boston', 'MA', '02116')),
            $this->patient(4, 'Dee', 'Fox', '2001-07-07', 'female', null, new PostalAddress('4 Fir Street', 'Quincy', 'MA', '02169')),
        ];
    }

    private function patient(
        int $pid,
        string $firstName,
        string $lastName,
        string $birthDate,
        string $sex,
        ?string $marital,
        PostalAddress $address,
    ): ImportedPatient {
        $birth = \DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate, new \DateTimeZone('UTC'));
        $this->assertNotFalse($birth);
        return new ImportedPatient($pid, new SyntheaPatient(
            'synthea-' . $pid,
            'ccda-' . $pid . '.xml',
            'fhir-' . $pid . '.json',
            $firstName,
            $lastName,
            $birth,
            $sex,
            $marital,
            null,
            $address,
        ));
    }

    private function update(Plan $plan, int $pid): PatientUpdate
    {
        foreach ($plan->patientUpdates as $update) {
            if ($update->pid === $pid) {
                return $update;
            }
        }
        $this->fail('No update for pid ' . $pid);
    }

    private function person(Plan $plan, string $ref): PlannedPerson
    {
        foreach ($plan->people as $person) {
            if ($person->ref === $ref) {
                return $person;
            }
        }
        $this->fail('No person ' . $ref);
    }

    /**
     * @return list<PlannedTelecom>
     */
    private function telecomsOf(Plan $plan, int $pid): array
    {
        return array_values(array_filter($plan->telecoms, static fn (PlannedTelecom $t): bool => $t->owner->pid === $pid));
    }
}
