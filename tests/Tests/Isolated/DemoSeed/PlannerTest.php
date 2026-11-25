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
use OpenEMR\DemoSeed\Plan\PlannedAddress;
use OpenEMR\DemoSeed\Plan\PlannedPerson;
use OpenEMR\DemoSeed\Plan\PlannedPolicy;
use OpenEMR\DemoSeed\Plan\PlannedRelation;
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
        insurance_companies:
          - {key: medicare, name: Medicare, type: 2, cms_id: MA001}
          - {key: medigap, name: Medigap, type: 17, cms_id: AA001}
          - {key: medicaid, name: MassHealth, type: 3, cms_id: MA002}
          - {key: bcbs, name: Blue Cross, type: 6, cms_id: BC001}
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
          insurance:
            plans:
              - when: {age: 65+}
                primary: {from: [medicare], subscriber: self, copay: 0}
                secondary: {from: [medigap], subscriber: self}
              - when: {age: 18-64}
                choose:
                  - {primary: {from: [bcbs], subscriber: spouse}}
              - when: {age: 18-64, synthea_payer: [Medicaid]}
                primary: {from: [medicaid], subscriber: self}
            ended: {percent: 100, ended_days_ago: 60-90}
            policy_numbers: {format: "{CMS}{8 digits}"}
            group_numbers: {percent: 100, format: "GRP{5 digits}"}
        cases:
          - key: couple
            patients:
              - {key: wife, pick: {sex: Female, age: 30-50}, provider: ortiz}
              - {key: husband, pick: {sex: Male, age: 30-50}, last_name: {same_as: wife}, address: {same_as: wife}}
            links:
              - {from: wife, to: husband, relationship: SPS, role: NOK}
              - {from: husband, to: wife, relationship: SPS, role: NOK}
            telecom:
              wife: [{system: phone, use: mobile, value: (413) 555-0110}]
            insurance:
              wife:
                primary: {company: bcbs, policy: BC0019900001, group: GRP10001, subscriber: self, copay: 25}
                ended: [{type: primary, company: medicaid, ended_days_ago: 400}]
              husband: {primary: {company: bcbs, policy: BC0019900001, group: GRP10001, subscriber: {patient: wife}, copay: 25}}
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
        $relations = array_values(array_filter($plan->relations, static fn (PlannedRelation $r): bool => $r->ownerPid === 2));
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
            static fn (PlannedAddress $a): bool => $a->owner->personRef === 'rule:1:0',
        ));
        $this->assertCount(1, $homes);
        $this->assertSame('1 Elm Street', $homes[0]->address->street);
        $this->assertTrue($homes[0]->primary);
    }

    public function testRelationshipsThatDontFitTheAgeAreSkipped(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        // pid 4 is 25: no adult child, so the second rule picks a friend
        $relations = array_values(array_filter($plan->relations, static fn (PlannedRelation $r): bool => $r->ownerPid === 4));
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

        $boxes = array_values(array_filter($plan->addresses, static fn (PlannedAddress $a): bool => $a->owner->pid === 1));
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

    public function testCasePolicyNamesAnotherPatientAsSubscriber(): void
    {
        $policies = $this->policiesOf($this->plan(self::SEED_FILE), 3);

        $this->assertCount(1, $policies);
        $policy = $policies[0];
        $this->assertSame('bcbs', $policy->companyKey);
        $this->assertSame('BC0019900001', $policy->policyNumber);
        $this->assertSame('GRP10001', $policy->groupNumber);
        $this->assertSame('25.00', $policy->copay);
        $this->assertNull($policy->end);
        $this->assertSame('spouse', $policy->subscriber->relationship);
        $this->assertSame(['Bea', 'Ann', 'Zed'], [$policy->subscriber->firstName, $policy->subscriber->middleName, $policy->subscriber->lastName]);
        $this->assertSame('2 Oak Street', $policy->subscriber->address->street);
        $this->assertSame('(413) 555-0110', $policy->subscriber->phone);
    }

    public function testEndedPolicyEndsTheDayBeforeTheCurrentOne(): void
    {
        [$ended, $current] = $this->policiesOf($this->plan(self::SEED_FILE), 2);

        $this->assertSame('medicaid', $ended->companyKey);
        $this->assertMatchesRegularExpression('/^MA002\d{8}$/', $ended->policyNumber);
        $this->assertSame('2025-10-16', $ended->end?->format('Y-m-d'));
        $this->assertLessThan($ended->end, $ended->start);
        $this->assertSame('2025-10-17', $current->start->format('Y-m-d'));
        $this->assertSame('self', $current->subscriber->relationship);
    }

    public function testRulesGivePrimaryWithEndedPolicyAndSecondary(): void
    {
        $policies = $this->policiesOf($this->plan(self::SEED_FILE), 1);

        $this->assertSame(
            ['primary|medicare|ended', 'primary|medicare|current', 'secondary|medigap|current'],
            array_map(
                static fn (PlannedPolicy $p): string => $p->type->value . '|' . $p->companyKey . '|' . ($p->end === null ? 'current' : 'ended'),
                $policies,
            ),
        );
        $this->assertSame($policies[0]->end?->modify('+1 day')->format('Y-m-d'), $policies[1]->start->format('Y-m-d'));
        $this->assertMatchesRegularExpression('/^MA001\d{8}$/', $policies[1]->policyNumber);
        $this->assertMatchesRegularExpression('/^GRP\d{5}$/', $policies[1]->groupNumber ?? '');
        $this->assertSame('0.00', $policies[1]->copay);
        $this->assertNull($policies[2]->copay);
        $this->assertSame(['self', 'Al', 'Able', 'Male'], [
            $policies[1]->subscriber->relationship,
            $policies[1]->subscriber->firstName,
            $policies[1]->subscriber->lastName,
            $policies[1]->subscriber->sex,
        ]);
    }

    public function testSkipsAChoiceNeedingASpouseAndMatchesThePayer(): void
    {
        $policies = $this->policiesOf($this->plan(self::SEED_FILE), 4);

        $this->assertSame(['medicaid', 'medicaid'], array_map(static fn (PlannedPolicy $p): string => $p->companyKey, $policies));
        $this->assertSame('Dee', $policies[1]->subscriber->firstName);
    }

    public function testMiddleNameMovesOutOfFirstName(): void
    {
        $plan = $this->plan(self::SEED_FILE);

        $this->assertSame(['Bea', 'Ann'], [$this->update($plan, 2)->fields['fname'], $this->update($plan, 2)->fields['mname']]);
        $this->assertArrayNotHasKey('fname', $this->update($plan, 1)->fields);
    }

    public function testNamedAndUnnamedRelatedPeopleStayApart(): void
    {
        // The unnamed second person used to get the same reference as the
        // one with key "1" and take its place.
        $yaml = str_replace(
            "    insurance:\n      wife:\n        primary: {company: bcbs, policy: BC0019900001, group: GRP10001, subscriber: self, copay: 25}",
            "    related_people:\n      wife:\n"
                . "        - {key: \"1\", first_name: Ana, relationship: MTH, role: NOK}\n"
                . "        - {first_name: Ben, relationship: FTH, role: ECON}\n"
                . "    insurance:\n      wife:\n        primary: {company: bcbs, policy: BC0019900001, group: GRP10001, subscriber: {related: \"1\"}, copay: 25}",
            self::SEED_FILE,
        );
        $plan = $this->plan($yaml);

        $this->assertSame('Ana', $this->person($plan, 'case:couple:wife:key:1')->firstName);
        $this->assertSame('Ben', $this->person($plan, 'case:couple:wife:index:1')->firstName);
        $people = array_map(
            fn (PlannedRelation $relation): string => $this->person($plan, $relation->personRef)->firstName,
            array_values(array_filter($plan->relations, static fn (PlannedRelation $relation): bool => $relation->ownerPid === 2)),
        );
        $this->assertSame(['Cal', 'Ana', 'Ben'], $people);
        $this->assertSame('Ana', $this->policiesOf($plan, 2)[1]->subscriber->firstName);
    }

    public function testKeysWithColonsCanNotMakeAnotherPersonsReference(): void
    {
        $yaml = str_replace(
            "    insurance:\n      wife:\n        primary: {company: bcbs, policy: BC0019900001, group: GRP10001, subscriber: self, copay: 25}",
            "    related_people:\n      wife:\n"
                . "        - {key: \"x:index:0\", first_name: Ana, relationship: MTH, role: NOK}\n"
                . "        - {first_name: Ben, relationship: FTH, role: ECON}\n"
                . "    insurance:\n      wife:\n        primary: {company: bcbs, policy: BC0019900001, group: GRP10001, subscriber: self, copay: 25}",
            self::SEED_FILE,
        );
        $plan = $this->plan($yaml);

        $this->assertSame('Ana', $this->person($plan, 'case:couple:wife:key:x%3Aindex%3A0')->firstName);
        $this->assertSame('Ben', $this->person($plan, 'case:couple:wife:index:1')->firstName);
    }

    public function testSelfSubscriberOptionIsPassedOverWithoutAHome(): void
    {
        [$first, $second, $third] = $this->patients();
        $patients = [$first, $second, $third, $this->patient(4, 'Dee', 'Fox', '2001-07-07', 'female', null, null, payer: 'Medicaid')];

        // Medicaid has the patient as subscriber, which needs a home address,
        // so it is passed over instead of stopping the plan.
        $this->assertSame([], $this->policiesOf($this->plan(self::SEED_FILE, $patients), 4));
    }

    public function testZeroPercentSelfPolicyDoesNotRuleOutAnOption(): void
    {
        [$first, $second, $third] = $this->patients();
        $patients = [
            $first,
            $second,
            $third,
            $this->patient(4, 'Dee', 'Fox', '2001-07-07', 'unknown', null, new PostalAddress('4 Fir Street', 'Quincy', 'MA', '02169')),
        ];
        $yaml = str_replace(
            '- {primary: {from: [bcbs], subscriber: guardian}}',
            '- {primary: {from: [bcbs], subscriber: guardian}, secondary: {from: [medigap], subscriber: self, percent: 0}}',
            $this->withGuardianRule('GRMTH'),
        );

        // Pid 4 has no sex to subscribe with, but the secondary policy that
        // would need it is never made, so the guardian's policy still is.
        $companies = array_map(static fn (PlannedPolicy $p): string => $p->companyKey, $this->policiesOf($this->plan($yaml, $patients), 4));
        $this->assertContains('bcbs', $companies);
        $this->assertNotContains('medigap', $companies);
    }

    public function testGrandparentGuardianCanBeTheSubscriber(): void
    {
        $plan = $this->plan($this->withGuardianRule('GRMTH'));

        // pid 4 is 25, so a grandparent fits; it gets a birth date and a sex
        // (the first policy is the ended one every patient gets here)
        [, $current] = $this->policiesOf($plan, 4);
        $this->assertSame('bcbs', $current->companyKey);
        $this->assertNull($current->end);
        $this->assertSame('Ana', $current->subscriber->firstName);
        $this->assertSame('Female', $current->subscriber->sex);
        $this->assertLessThan(new \DateTimeImmutable('1960-01-01'), $current->subscriber->birthDate);
    }

    public function testGuardianWhoCanNotBeASubscriberIsPassedOver(): void
    {
        $plan = $this->plan($this->withGuardianRule('FRND'));

        // A friend has no birth date, so the option that needs a guardian is
        // skipped and the next plan set (Medicaid) applies.
        $this->assertSame(
            ['medicaid', 'medicaid'],
            array_map(static fn (PlannedPolicy $p): string => $p->companyKey, $this->policiesOf($plan, 4)),
        );
    }

    public function testGuardianWithNoAddressIsPassedOver(): void
    {
        [$first, $second, $third] = $this->patients();
        $patients = [$first, $second, $third, $this->patient(4, 'Dee', 'Fox', '2001-07-07', 'female', null, null)];

        $plan = $this->plan($this->withGuardianRule('GRMTH'), $patients);

        // The grandparent has no address of their own and the patient has no
        // home, so the guardian option is skipped rather than stopping the
        // plan, and no other plan set fits.
        $this->assertSame([], $this->policiesOf($plan, 4));
    }

    /**
     * The seed file, with adults getting a guardian of this relationship and
     * 18-64s choosing Blue Cross with the guardian as subscriber.
     */
    private function withGuardianRule(string $relationship): string
    {
        return str_replace(
            [
                'add: {relationship: [CHILD, FRND], role: ECON}',
                '- {primary: {from: [bcbs], subscriber: spouse}}',
            ],
            [
                'add: {relationship: [' . $relationship . '], role: GUARD}',
                '- {primary: {from: [bcbs], subscriber: guardian}}',
            ],
            self::SEED_FILE,
        );
    }

    /**
     * @param ?list<ImportedPatient> $patients
     */
    private function plan(string $yaml, ?array $patients = null): Plan
    {
        $seedFile = (new SeedFileParser())->parse($yaml);
        return (new Planner($seedFile, new \DateTimeImmutable(self::NOW)))->plan($patients ?? $this->patients());
    }

    /**
     * @return list<ImportedPatient>
     */
    private function patients(): array
    {
        return [
            $this->patient(1, 'Al', 'Able', '1960-01-10', 'male', 'M', new PostalAddress('1 Elm Street', 'Northampton', 'MA', '01060')),
            $this->patient(2, 'Bea', 'Zed', '1985-05-05', 'female', 'M', new PostalAddress('2 Oak Street', 'Amherst', 'MA', '01002'), middleName: 'Ann'),
            $this->patient(3, 'Cal', 'Young', '1983-03-03', 'male', 'M', new PostalAddress('3 Ash Street', 'Boston', 'MA', '02116')),
            $this->patient(4, 'Dee', 'Fox', '2001-07-07', 'female', null, new PostalAddress('4 Fir Street', 'Quincy', 'MA', '02169'), payer: 'Medicaid'),
        ];
    }

    private function patient(
        int $pid,
        string $firstName,
        string $lastName,
        string $birthDate,
        string $sex,
        ?string $marital,
        ?PostalAddress $address,
        ?string $payer = null,
        ?string $middleName = null,
    ): ImportedPatient {
        $birth = \DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate, new \DateTimeZone('UTC'));
        $this->assertNotFalse($birth);
        return new ImportedPatient($pid, sprintf('00000000-0000-4000-8000-%012d', $pid), new SyntheaPatient(
            'synthea-' . $pid,
            'ccda-' . $pid . '.xml',
            'fhir-' . $pid . '.json',
            $firstName,
            $lastName,
            $birth,
            $sex,
            $marital,
            $payer,
            $address,
            $middleName,
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
     * @return list<PlannedPolicy>
     */
    private function policiesOf(Plan $plan, int $pid): array
    {
        return array_values(array_filter($plan->policies, static fn (PlannedPolicy $p): bool => $p->pid === $pid));
    }

    /**
     * @return list<PlannedTelecom>
     */
    private function telecomsOf(Plan $plan, int $pid): array
    {
        return array_values(array_filter($plan->telecoms, static fn (PlannedTelecom $t): bool => $t->owner->pid === $pid));
    }
}
