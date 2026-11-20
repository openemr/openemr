<?php

/**
 * Works out everything the seed file adds to the imported patients: claims
 * patients for cases, then applies the rules to the rest. Writes nothing;
 * the same seed file and patients always give the same plan.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

use OpenEMR\DemoSeed\Import\ImportedPatient;
use OpenEMR\DemoSeed\PostalAddress;
use OpenEMR\DemoSeed\SeedFile\AddressEntry;
use OpenEMR\DemoSeed\SeedFile\CaseSpec;
use OpenEMR\DemoSeed\SeedFile\Gender;
use OpenEMR\DemoSeed\SeedFile\NamePools;
use OpenEMR\DemoSeed\SeedFile\RelatedPersonEntry;
use OpenEMR\DemoSeed\SeedFile\RelationFlags;
use OpenEMR\DemoSeed\SeedFile\SeedFile;
use OpenEMR\DemoSeed\SeedFile\TelecomEntry;

final class Planner
{
    /** FHIR v3 MaritalStatus code => list `marital` option id */
    private const MARITAL = [
        'M' => 'married',
        'S' => 'single',
        'D' => 'divorced',
        'W' => 'widowed',
        'L' => 'separated',
        'T' => 'domestic partner',
    ];
    /** patient_data keeps marital status in `status` */
    private const MARITAL_COLUMN = 'status';
    private const STREET_SUFFIXES = ['Street', 'Avenue', 'Road', 'Lane', 'Drive', 'Court', 'Way', 'Place'];
    /** telecom (system, use) => patient_data column the old screens read */
    private const PATIENT_PHONE_COLUMNS = [
        'phone|home' => 'phone_home',
        'phone|mobile' => 'phone_cell',
        'phone|work' => 'phone_biz',
    ];

    private readonly \DateTimeImmutable $today;
    private readonly \DateTimeImmutable $referenceDate;

    /** @var array<int, ImportedPatient> by pid */
    private array $patients = [];
    /** @var array<int, array<string, string>> */
    private array $fields = [];
    /** @var array<int, string> */
    private array $providerKeys = [];
    /** @var array<int, string> */
    private array $lastNames = [];
    /** @var array<int, PostalAddress> */
    private array $homes = [];
    /** @var array<string, int> */
    private array $casePatients = [];
    /** @var array<string, PlannedPerson> */
    private array $people = [];
    /** @var list<PlannedRelation> */
    private array $relations = [];
    /** @var list<PlannedTelecom> */
    private array $telecoms = [];
    /** @var list<PlannedAddress> */
    private array $addresses = [];
    /** @var array<string, int> */
    private array $ranks = [];
    /** @var array<int, int> */
    private array $priorities = [];
    private ?PhoneNumberPool $phones = null;
    private ?EmailPool $emails = null;

    /**
     * @param \DateTimeImmutable $now day offsets count from this day
     */
    public function __construct(private readonly SeedFile $seedFile, \DateTimeImmutable $now)
    {
        $this->today = $now->setTime(0, 0);
        $this->referenceDate = $seedFile->synthea->referenceDate;
        $contactData = $seedFile->contactData;
        if ($contactData !== null) {
            [$reservedPhones, $reservedEmails] = $this->reservedContactValues();
            $this->phones = new PhoneNumberPool($contactData->areaCodes, $reservedPhones);
            $this->emails = new EmailPool($contactData->emailDomain, $reservedEmails);
        }
    }

    /**
     * @param list<ImportedPatient> $imported in import order
     * @throws PlanException
     */
    public function plan(array $imported): Plan
    {
        foreach ($imported as $patient) {
            $this->startPatient($patient);
        }
        $this->claimCasePatients($imported);
        foreach ($this->seedFile->cases as $case) {
            $this->applyCase($case);
        }
        $this->assignCareTeam($imported);
        $claimed = array_flip($this->casePatients);
        foreach ($imported as $patient) {
            if (isset($claimed[$patient->pid])) {
                continue;
            }
            $this->applyRelatedPeopleRules($patient);
            $this->applyTelecomRules($patient);
            $this->applyAddressRules($patient);
        }
        $this->choosePrimaryTelecoms();
        $this->copyTelecomsToPatientColumns();

        $updates = [];
        foreach ($imported as $patient) {
            $fields = $this->fields[$patient->pid] ?? [];
            $providerKey = $this->providerKeys[$patient->pid] ?? null;
            if ($fields !== [] || $providerKey !== null) {
                $updates[] = new PatientUpdate($patient->pid, $providerKey, $fields);
            }
        }
        return new Plan(
            $this->seedFile->facilities,
            $this->seedFile->providers,
            $updates,
            array_values($this->people),
            $this->relations,
            $this->telecoms,
            $this->addresses,
            $this->casePatients,
        );
    }

    private function startPatient(ImportedPatient $patient): void
    {
        $pid = $patient->pid;
        $this->patients[$pid] = $patient;
        $this->lastNames[$pid] = $patient->synthea->lastName;
        $this->fields[$pid] = [];
        $marital = self::MARITAL[$patient->synthea->maritalStatus ?? ''] ?? null;
        if ($marital !== null) {
            $this->fields[$pid][self::MARITAL_COLUMN] = $marital;
        }
        $address = $patient->synthea->address;
        if ($address !== null) {
            $this->homes[$pid] = $address;
            // the CCDA import stores the state's name; the list wants its code
            $this->fields[$pid]['state'] = $address->state;
        }
    }

    /**
     * @param list<ImportedPatient> $imported
     */
    private function claimCasePatients(array $imported): void
    {
        $claimed = [];
        foreach ($this->seedFile->cases as $case) {
            foreach ($case->patients as $casePatient) {
                $match = null;
                foreach ($imported as $patient) {
                    if (!isset($claimed[$patient->pid]) && $casePatient->pick->matches($patient->synthea, $this->referenceDate)) {
                        $match = $patient;
                        break;
                    }
                }
                if ($match === null) {
                    throw new PlanException(sprintf(
                        'cases.%s: no unclaimed patient matches the pick for "%s"; raise synthea.count or loosen pick',
                        $case->key,
                        $casePatient->key,
                    ));
                }
                $claimed[$match->pid] = true;
                $this->casePatients[$casePatient->key] = $match->pid;
            }
        }
    }

    private function applyCase(CaseSpec $case): void
    {
        foreach ($case->patients as $casePatient) {
            $pid = $this->casePid($casePatient->key);
            if ($casePatient->maritalStatus !== null) {
                $this->fields[$pid][self::MARITAL_COLUMN] = $casePatient->maritalStatus;
            }
            if ($casePatient->providerKey !== null) {
                $this->providerKeys[$pid] = $casePatient->providerKey;
            }
            $lastName = $casePatient->lastName
                ?? ($casePatient->lastNameSameAs === null ? null : $this->lastNames[$this->casePid($casePatient->lastNameSameAs)]);
            if ($lastName !== null) {
                $this->lastNames[$pid] = $lastName;
                $this->fields[$pid]['lname'] = $lastName;
            }
            if ($casePatient->addressSameAs !== null) {
                $home = $this->homes[$this->casePid($casePatient->addressSameAs)] ?? null;
                if ($home !== null) {
                    $this->homes[$pid] = $home;
                    $this->fields[$pid]['street'] = $home->street;
                    $this->fields[$pid]['city'] = $home->city;
                    $this->fields[$pid]['state'] = $home->state;
                    $this->fields[$pid]['postal_code'] = $home->postalCode;
                }
            }
        }

        foreach ($case->links as $link) {
            $toPid = $this->casePid($link->to);
            $ref = 'patient:' . $toPid;
            if (!isset($this->people[$ref])) {
                $synthea = $this->patients[$toPid]->synthea;
                $this->people[$ref] = new PlannedPerson(
                    $ref,
                    $synthea->firstName,
                    $this->lastNames[$toPid],
                    self::sexOption($synthea->gender()),
                    $synthea->birthDate,
                    $toPid,
                );
            }
            $this->addRelation($this->casePid($link->from), $ref, $link->relationship, $link->role, $link->flags);
        }

        foreach ($case->relatedPeople as $patientKey => $entries) {
            foreach ($entries as $index => $entry) {
                $this->addCaseRelatedPerson($case, $patientKey, $index, $entry);
            }
        }
        foreach ($case->telecom as $patientKey => $entries) {
            foreach ($entries as $entry) {
                $this->addTelecomEntry(ContactOwner::patient($this->casePid($patientKey)), $entry);
            }
        }
        foreach ($case->addresses as $patientKey => $entries) {
            foreach ($entries as $entry) {
                $this->addAddressEntry(ContactOwner::patient($this->casePid($patientKey)), $entry);
            }
        }
    }

    private function addCaseRelatedPerson(CaseSpec $case, string $patientKey, int $index, RelatedPersonEntry $entry): void
    {
        $pid = $this->casePid($patientKey);
        $patient = $this->patients[$pid]->synthea;
        $ref = 'case:' . $case->key . ':' . $patientKey . ':' . ($entry->key ?? (string) $index);
        $dice = Dice::for($this->seedFile->seed, $ref);
        $kind = RelationshipKind::of($entry->relationship);
        $sex = RelationshipKind::sexOf($entry->relationship)
            ?? ($kind === RelationshipKind::Spouse ? self::sexOption(self::opposite($patient->gender())) : null)
            ?? $this->sexFromNamePools($entry->firstName);
        $this->people[$ref] = new PlannedPerson(
            $ref,
            $entry->firstName,
            $entry->lastName ?? $this->lastNames[$pid],
            $sex,
            $this->birthDateFor($kind, $patient->birthDate, $patient->ageOn($this->referenceDate), $dice),
            null,
        );
        $this->addRelation($pid, $ref, $entry->relationship, $entry->role, $entry->flags);

        $owner = ContactOwner::person($ref);
        $home = $this->homes[$pid] ?? null;
        if ($entry->sameAddress && $home !== null) {
            $this->addCurrentAddress($owner, $home);
        }
        if ($entry->address !== null) {
            $this->addCurrentAddress($owner, $entry->address);
        }
        foreach ($entry->telecom as $telecom) {
            $this->addTelecomEntry($owner, $telecom);
        }
    }

    /**
     * @param list<ImportedPatient> $imported
     */
    private function assignCareTeam(array $imported): void
    {
        $providers = $this->seedFile->rules->careTeamProviders;
        if ($providers === []) {
            return;
        }
        $turn = 0;
        foreach ($imported as $patient) {
            if (!isset($this->providerKeys[$patient->pid])) {
                $this->providerKeys[$patient->pid] = $providers[$turn % count($providers)];
                $turn++;
            }
        }
    }

    private function applyRelatedPeopleRules(ImportedPatient $patient): void
    {
        $synthea = $patient->synthea;
        $age = $synthea->ageOn($this->referenceDate);
        $parentSexes = [];
        foreach ($this->seedFile->rules->relatedPeople as $ruleIndex => $rule) {
            if (!$rule->when->matches($synthea, $this->referenceDate)) {
                continue;
            }
            $dice = Dice::for($this->seedFile->seed, $synthea->syntheaId, 'related', (string) $ruleIndex);
            if (!$dice->percent($rule->percent)) {
                continue;
            }
            $candidates = array_values(array_filter(
                $rule->relationships,
                static fn (string $relationship): bool => RelationshipKind::of($relationship)->fitsPatientAge($age),
            ));
            if ($candidates === []) {
                continue;
            }
            $relationship = $dice->pick($candidates);
            $kind = RelationshipKind::of($relationship);
            $sex = RelationshipKind::sexOf($relationship) ?? match ($kind) {
                RelationshipKind::Spouse => self::sexOption(self::opposite($synthea->gender())),
                RelationshipKind::Parent => $parentSexes === [] ? null : self::oppositeOption($parentSexes[count($parentSexes) - 1]),
                default => null,
            } ?? $dice->pick(['Female', 'Male']);
            if ($kind === RelationshipKind::Parent) {
                $parentSexes[] = $sex;
            }
            $names = $this->names();
            $firstName = $dice->pick($sex === 'Female' ? $names->female : $names->male);
            $lastName = $kind->sharesLastName() ? $this->lastNames[$patient->pid] : $dice->pick($names->last);
            $ref = 'rule:' . $patient->pid . ':' . $ruleIndex;
            $this->people[$ref] = new PlannedPerson(
                $ref,
                $firstName,
                $lastName,
                $sex,
                $this->birthDateFor($kind, $synthea->birthDate, $age, $dice),
                null,
            );
            $this->addRelation($patient->pid, $ref, $relationship, $rule->role, $rule->flags);

            $owner = ContactOwner::person($ref);
            $home = $this->homes[$patient->pid] ?? null;
            if ($rule->sameAddress && $home !== null) {
                $this->addCurrentAddress($owner, $home);
            }
            if ($dice->percent($this->seedFile->rules->relatedPersonMobilePercent)) {
                $this->addTelecom($owner, 'phone', 'mobile', $this->phones()->next($dice), false, $this->daysAgo($dice->between(30, 1800)));
            }
            if ($dice->percent($this->seedFile->rules->relatedPersonEmailPercent)) {
                $this->addTelecom($owner, 'email', 'home', $this->emails()->next($firstName, $lastName), false, $this->daysAgo($dice->between(30, 1800)));
            }
        }
    }

    private function applyTelecomRules(ImportedPatient $patient): void
    {
        $synthea = $patient->synthea;
        $owner = ContactOwner::patient($patient->pid);
        $mobile = null;
        foreach ($this->seedFile->rules->telecom as $ruleIndex => $rule) {
            if (!$rule->when->matches($synthea, $this->referenceDate)) {
                continue;
            }
            $dice = Dice::for($this->seedFile->seed, $synthea->syntheaId, 'telecom', (string) $ruleIndex);
            if (!$dice->percent($rule->percent)) {
                continue;
            }
            if ($rule->requiresMobile) {
                if ($mobile === null) {
                    continue;
                }
                $value = $mobile;
            } elseif ($rule->system === 'email') {
                $value = $this->emails()->next($synthea->firstName, $this->lastNames[$patient->pid]);
            } else {
                $value = $this->phones()->next($dice);
            }
            if ($rule->inactive === null) {
                if ($mobile === null && $rule->system === 'phone' && $rule->use === 'mobile') {
                    $mobile = $value;
                }
                $this->addTelecom($owner, $rule->system, $rule->use, $value, false, $this->daysAgo($dice->between(30, 1800)));
                continue;
            }
            $end = $this->daysAgo($dice->inRange($rule->inactive->endedDaysAgo));
            $this->addTelecom(
                $owner,
                $rule->system,
                $rule->use,
                $value,
                false,
                $end->modify('-' . $dice->between(365, 1500) . ' days'),
                $end,
                $rule->inactive->reason,
            );
        }
    }

    private function applyAddressRules(ImportedPatient $patient): void
    {
        $home = $this->homes[$patient->pid] ?? null;
        if ($home === null) {
            return;
        }
        foreach ($this->seedFile->rules->addresses as $ruleIndex => $rule) {
            if (!$rule->when->matches($patient->synthea, $this->referenceDate)) {
                continue;
            }
            $dice = Dice::for($this->seedFile->seed, $patient->synthea->syntheaId, 'address', (string) $ruleIndex);
            if (!$dice->percent($rule->percent)) {
                continue;
            }
            $street = $rule->poBox
                ? 'PO Box ' . $dice->between(100, 9999)
                : $dice->between(1, 999) . ' ' . $dice->pick($this->names()->last) . ' ' . $dice->pick(self::STREET_SUFFIXES);
            $end = $rule->endedDaysAgo === null ? null : $this->daysAgo($dice->inRange($rule->endedDaysAgo));
            $start = match (true) {
                $end !== null => $end->modify('-' . $dice->between(365, 2000) . ' days'),
                $rule->startedDaysAgo !== null => $this->daysAgo($dice->inRange($rule->startedDaysAgo)),
                default => $this->daysAgo($dice->between(30, 1800)),
            };
            $this->addresses[] = new PlannedAddress(
                ContactOwner::patient($patient->pid),
                $rule->type,
                $rule->use,
                $home->withStreet($street),
                false,
                $end === null,
                $start,
                $end,
                $rule->reason,
                $rule->note,
            );
        }
    }

    private function addRelation(int $ownerPid, string $personRef, string $relationship, string $role, RelationFlags $flags): void
    {
        $priority = ($this->priorities[$ownerPid] ?? 0) + 1;
        $this->priorities[$ownerPid] = $priority;
        $this->relations[] = new PlannedRelation($ownerPid, $personRef, $relationship, $role, $flags, $priority);
    }

    private function addTelecomEntry(ContactOwner $owner, TelecomEntry $entry): void
    {
        $end = null;
        $reason = null;
        if ($entry->inactive !== null) {
            $dice = Dice::for($this->seedFile->seed, $owner->key(), 'telecom', $entry->value);
            $end = $this->daysAgo($dice->inRange($entry->inactive->endedDaysAgo));
            $reason = $entry->inactive->reason;
        } elseif ($entry->endsInDays !== null) {
            $end = $this->today->modify('+' . $entry->endsInDays . ' days');
        }
        $start = match (true) {
            $entry->startedDaysAgo !== null => $this->daysAgo($entry->startedDaysAgo),
            $end !== null && $entry->inactive !== null => $end->modify('-365 days'),
            default => $this->today,
        };
        $this->addTelecom(
            $owner,
            $entry->system,
            $entry->use,
            $entry->value,
            $entry->primary,
            $start,
            $end,
            $reason,
            $entry->note,
            $entry->inactive === null,
        );
    }

    private function addTelecom(
        ContactOwner $owner,
        string $system,
        string $use,
        string $value,
        bool $primary,
        \DateTimeImmutable $start,
        ?\DateTimeImmutable $end = null,
        ?string $reason = null,
        ?string $note = null,
        ?bool $active = null,
    ): void {
        $rank = ($this->ranks[$owner->key()] ?? 0) + 1;
        $this->ranks[$owner->key()] = $rank;
        $this->telecoms[] = new PlannedTelecom(
            $owner,
            $system,
            $use,
            $value,
            $rank,
            $primary,
            $active ?? ($reason === null),
            $start,
            $end,
            $reason,
            $note,
        );
    }

    private function addAddressEntry(ContactOwner $owner, AddressEntry $entry): void
    {
        $end = $entry->endedDaysAgo === null ? null : $this->daysAgo($entry->endedDaysAgo);
        $start = match (true) {
            $entry->startedDaysAgo !== null => $this->daysAgo($entry->startedDaysAgo),
            $end !== null => $end->modify('-365 days'),
            default => $this->today,
        };
        $this->addresses[] = new PlannedAddress(
            $owner,
            $entry->type,
            $entry->use,
            $entry->address,
            false,
            $end === null,
            $start,
            $end,
            $entry->reason,
            $entry->note,
        );
    }

    private function addCurrentAddress(ContactOwner $owner, PostalAddress $address): void
    {
        $this->addresses[] = new PlannedAddress($owner, 'both', 'home', $address, true, true, $this->today, null, null, null);
    }

    /**
     * Where an owner has no primary among its active entries of a system,
     * the first active one is primary.
     */
    private function choosePrimaryTelecoms(): void
    {
        $hasPrimary = [];
        foreach ($this->telecoms as $telecom) {
            if ($telecom->primary && $telecom->active) {
                $hasPrimary[$telecom->owner->key() . '|' . $telecom->system] = true;
            }
        }
        foreach ($this->telecoms as $index => $telecom) {
            $group = $telecom->owner->key() . '|' . $telecom->system;
            if ($telecom->active && !isset($hasPrimary[$group])) {
                $this->telecoms[$index] = $telecom->withPrimary(true);
                $hasPrimary[$group] = true;
            }
        }
    }

    /**
     * The demographics screen and many reports still read the phone and
     * email columns of patient_data.
     */
    private function copyTelecomsToPatientColumns(): void
    {
        foreach ($this->telecoms as $telecom) {
            $pid = $telecom->owner->pid;
            if ($pid === null || !$telecom->active) {
                continue;
            }
            $column = $telecom->system === 'email'
                ? ($telecom->primary ? 'email' : null)
                : (self::PATIENT_PHONE_COLUMNS[$telecom->system . '|' . $telecom->use] ?? null);
            if ($column !== null && !isset($this->fields[$pid][$column])) {
                $this->fields[$pid][$column] = $telecom->value;
            }
        }
    }

    private function birthDateFor(RelationshipKind $kind, \DateTimeImmutable $patientBirth, int $patientAge, Dice $dice): ?\DateTimeImmutable
    {
        $years = match ($kind) {
            RelationshipKind::Spouse => $dice->between(-5, 5),
            RelationshipKind::Parent => -$dice->between(20, 40),
            RelationshipKind::Child => $dice->between(20, max(20, min(40, $patientAge - 18))),
            RelationshipKind::Sibling => $dice->between(-8, 8),
            RelationshipKind::OtherFamily, RelationshipKind::NotFamily => null,
        };
        if ($years === null) {
            return null;
        }
        $birth = $patientBirth->modify(sprintf('%+d years %+d days', $years, $dice->between(-180, 180)));
        $latest = $kind === RelationshipKind::Spouse ? $this->today->modify('-18 years') : $this->today->modify('-30 days');
        return $birth > $latest ? $latest->modify('-' . $dice->between(0, 365) . ' days') : $birth;
    }

    private function daysAgo(int $days): \DateTimeImmutable
    {
        return $this->today->modify('-' . $days . ' days');
    }

    private function casePid(string $patientKey): int
    {
        return $this->casePatients[$patientKey]
            ?? throw new PlanException('Unknown case patient key "' . $patientKey . '"');
    }

    private function names(): NamePools
    {
        return $this->seedFile->names ?? throw new PlanException('The seed file needs `names` to make up people and streets');
    }

    private function phones(): PhoneNumberPool
    {
        return $this->phones ?? throw new PlanException('The seed file needs `contact_data` to make up phone numbers');
    }

    private function emails(): EmailPool
    {
        return $this->emails ?? throw new PlanException('The seed file needs `contact_data` to make up email addresses');
    }

    /**
     * A first name found in only one of the name pools tells the sex.
     */
    private function sexFromNamePools(string $firstName): ?string
    {
        $names = $this->seedFile->names;
        if ($names === null) {
            return null;
        }
        $female = in_array($firstName, $names->female, true);
        $male = in_array($firstName, $names->male, true);
        return match (true) {
            $female && !$male => 'Female',
            $male && !$female => 'Male',
            default => null,
        };
    }

    private static function opposite(?Gender $gender): ?Gender
    {
        return match ($gender) {
            Gender::Male => Gender::Female,
            Gender::Female => Gender::Male,
            null => null,
        };
    }

    private static function oppositeOption(string $sex): ?string
    {
        return match ($sex) {
            'Male' => 'Female',
            'Female' => 'Male',
            default => null,
        };
    }

    private static function sexOption(?Gender $gender): ?string
    {
        return match ($gender) {
            Gender::Male => 'Male',
            Gender::Female => 'Female',
            null => null,
        };
    }

    /**
     * @return array{list<string>, list<string>} phone numbers and emails the
     *         seed file spells out, so made-up ones never collide with them
     */
    private function reservedContactValues(): array
    {
        $phones = [];
        $emails = [];
        foreach ($this->seedFile->facilities as $facility) {
            foreach ([$facility->phone, $facility->fax] as $number) {
                if ($number !== null) {
                    $phones[] = $number;
                }
            }
        }
        $entries = [];
        foreach ($this->seedFile->cases as $case) {
            foreach ($case->telecom as $list) {
                $entries = [...$entries, ...$list];
            }
            foreach ($case->relatedPeople as $people) {
                foreach ($people as $person) {
                    $entries = [...$entries, ...$person->telecom];
                }
            }
        }
        foreach ($entries as $entry) {
            if ($entry->system === 'email') {
                $emails[] = $entry->value;
            } else {
                $phones[] = $entry->value;
            }
        }
        return [$phones, $emails];
    }
}
