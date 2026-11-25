<?php

/**
 * Works out everything the seed file adds to the imported patients: claims
 * patients for cases, then applies the rules to the rest, insurance
 * after the people (its subscribers can be the people made before), then
 * appointments (AppointmentPlanner). Writes nothing. The same seed file,
 * patients and day give the same plan (only today's appointment statuses
 * also depend on the time).
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
use OpenEMR\DemoSeed\SeedFile\InsuranceCompanySpec;
use OpenEMR\DemoSeed\SeedFile\InsuranceOption;
use OpenEMR\DemoSeed\SeedFile\NamePools;
use OpenEMR\DemoSeed\SeedFile\PolicyType;
use OpenEMR\DemoSeed\SeedFile\RelatedPersonEntry;
use OpenEMR\DemoSeed\SeedFile\RelationFlags;
use OpenEMR\DemoSeed\SeedFile\SeedFile;
use OpenEMR\DemoSeed\SeedFile\Subscriber;
use OpenEMR\DemoSeed\SeedFile\SubscriberKind;
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
    /** @var list<PlannedPolicy> */
    private array $policies = [];
    private ?PhoneNumberPool $phones = null;
    private ?EmailPool $emails = null;

    /**
     * @param \DateTimeImmutable $now day offsets count from this day
     * @param array<string, int> $categoryMinutes appointment length by
     *        calendar category (pc_constant_id)
     */
    public function __construct(
        private readonly SeedFile $seedFile,
        private readonly \DateTimeImmutable $now,
        private readonly array $categoryMinutes = [],
    ) {
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
        foreach ($this->seedFile->cases as $case) {
            $this->applyCaseInsurance($case);
        }
        foreach ($imported as $patient) {
            if (!isset($claimed[$patient->pid])) {
                $this->applyInsuranceRules($patient);
            }
        }

        [$appointments, $visitLists] = (new AppointmentPlanner(
            $this->seedFile->appointments,
            $this->seedFile->seed,
            $this->now,
            $this->referenceDate,
            $this->categoryMinutes,
        ))->plan($imported, $this->providerKeys, $this->casePatients, $this->homes);

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
            $this->seedFile->insuranceCompanies,
            $this->policies,
            $appointments,
            $visitLists,
        );
    }

    private function startPatient(ImportedPatient $patient): void
    {
        $pid = $patient->pid;
        $this->patients[$pid] = $patient;
        $this->lastNames[$pid] = $patient->synthea->lastName;
        $this->fields[$pid] = [];
        if ($patient->synthea->middleName !== null) {
            // the CCDA import puts the middle name into fname
            $this->fields[$pid]['fname'] = $patient->synthea->firstName;
            $this->fields[$pid]['mname'] = $patient->synthea->middleName;
        }
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
        $ref = $entry->key !== null
            ? self::namedCasePersonRef($case->key, $patientKey, $entry->key)
            : 'case:' . $case->key . ':' . $patientKey . ':index:' . $index;
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
                RelationshipKind::Child, RelationshipKind::Sibling, RelationshipKind::Grandparent,
                RelationshipKind::OtherFamily, RelationshipKind::NotFamily => null,
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

    private function applyCaseInsurance(CaseSpec $case): void
    {
        foreach ($case->insurance as $patientKey => $insurance) {
            $pid = $this->casePid($patientKey);
            $dice = Dice::for($this->seedFile->seed, 'insurance', $case->key, $patientKey);
            $subscribers = [];
            foreach ($insurance->policies as $policy) {
                $subscribers[$policy->type->value] = $this->caseSubscriber($case, $pid, $policy->subscriber);
            }
            $lastEnd = [];
            foreach ($insurance->ended as $ended) {
                $end = $this->daysAgo($dice->inRange($ended->endedDaysAgo));
                $start = $this->notBeforeBirth($pid, $end->modify('-' . $dice->between(365, 1500) . ' days'));
                if ($start >= $end) {
                    throw new PlanException(sprintf('cases.%s: %s\'s ended %s policy would end before they were born', $case->key, $patientKey, $ended->type->value));
                }
                $company = $this->seedFile->insuranceCompany($ended->companyKey);
                $this->policies[] = new PlannedPolicy(
                    $pid,
                    $ended->type,
                    $company->key,
                    $ended->policyNumber ?? $this->policyNumber($company, $dice),
                    null,
                    $subscribers[$ended->type->value] ?? $this->patientSubscriber($pid, 'self'),
                    null,
                    $start,
                    $end,
                );
                $previous = $lastEnd[$ended->type->value] ?? null;
                $lastEnd[$ended->type->value] = $previous === null ? $end : max($previous, $end);
            }
            foreach ($insurance->policies as $policy) {
                $company = $this->seedFile->insuranceCompany($policy->companyKey);
                $previousEnd = $lastEnd[$policy->type->value] ?? null;
                $this->policies[] = new PlannedPolicy(
                    $pid,
                    $policy->type,
                    $company->key,
                    $policy->policyNumber ?? $this->policyNumber($company, $dice),
                    $policy->groupNumber,
                    $this->caseSubscriber($case, $pid, $policy->subscriber),
                    self::copay($policy->copay),
                    $previousEnd?->modify('+1 day') ?? $this->notBeforeBirth($pid, $this->daysAgo($dice->between(30, 1800))),
                    null,
                );
            }
        }
    }

    private function caseSubscriber(CaseSpec $case, int $pid, Subscriber $subscriber): PlannedSubscriber
    {
        $key = $subscriber->key ?? '';
        return match ($subscriber->kind) {
            SubscriberKind::Self => $this->patientSubscriber($pid, 'self'),
            SubscriberKind::Patient => $this->patientSubscriber(
                $this->casePid($key),
                $this->subscriberRelationship($pid, 'patient:' . $this->casePid($key)),
            ),
            SubscriberKind::Related => $this->personSubscriber(
                $pid,
                self::namedCasePersonRef(
                    $case->key,
                    $case->relatedPersonOwner($key) ?? throw new PlanException('cases.' . $case->key . ': no related person has key "' . $key . '"'),
                    $key,
                ),
            ),
            SubscriberKind::Spouse, SubscriberKind::Guardian => throw new PlanException(
                'cases.' . $case->key . ': name the subscriber with {patient: key} or {related: key}',
            ),
        };
    }

    /**
     * The first plan set that fits the patient, and has a choice whose
     * spouse or guardian the patient has, decides; none = self pay.
     */
    private function applyInsuranceRules(ImportedPatient $patient): void
    {
        $pid = $patient->pid;
        // Only people who can be a subscriber count, so an option that needs
        // one is passed over rather than failing later.
        $available = [
            SubscriberKind::Spouse->name => $this->relatedRef(
                $pid,
                fn (PlannedRelation $relation): bool => RelationshipKind::of($relation->relationship) === RelationshipKind::Spouse
                    && $this->canSubscribe($relation),
            ),
            SubscriberKind::Guardian->name => $this->relatedRef(
                $pid,
                fn (PlannedRelation $relation): bool => $relation->role === 'GUARD' && $this->canSubscribe($relation),
            )
                ?? $this->relatedRef(
                    $pid,
                    fn (PlannedRelation $relation): bool => RelationshipKind::of($relation->relationship) === RelationshipKind::Parent
                        && $this->canSubscribe($relation),
                ),
        ];
        $dice = Dice::for($this->seedFile->seed, $patient->synthea->syntheaId, 'insurance');
        foreach ($this->seedFile->rules->insurance->plans as $plan) {
            if (!$plan->when->matches($patient->synthea, $this->referenceDate)) {
                continue;
            }
            $options = array_values(array_filter(
                $plan->options,
                static function (InsuranceOption $option) use ($available): bool {
                    foreach ($option->needs as $kind) {
                        if (($available[$kind->name] ?? null) === null) {
                            return false;
                        }
                    }
                    return true;
                },
            ));
            if ($options === []) {
                continue;
            }
            $option = $options[$dice->weighted(array_map(static fn (InsuranceOption $option): int => max(1, $option->weight), $options))];
            if (!$option->selfPay) {
                $this->addRulePolicies($pid, $option, $available, $dice);
            }
            return;
        }
    }

    /**
     * @param array<string, ?string> $available person refs by SubscriberKind name
     */
    private function addRulePolicies(int $pid, InsuranceOption $option, array $available, Dice $dice): void
    {
        $rules = $this->seedFile->rules->insurance;
        foreach ($option->policies as $typeValue => $rule) {
            $type = PolicyType::from($typeValue);
            if (!$dice->percent($rule->percent)) {
                if ($type === PolicyType::Primary) {
                    return;
                }
                continue;
            }
            $company = $this->seedFile->insuranceCompany($dice->pick($rule->companyKeys));
            $kind = $rule->subscriber->kind;
            $subscriber = $kind === SubscriberKind::Self
                ? $this->patientSubscriber($pid, 'self')
                : $this->personSubscriber($pid, $available[$kind->name] ?? throw new PlanException('No ' . strtolower($kind->name) . ' for patient ' . $pid));
            $groupNumber = $rules->groupNumbers !== null && $dice->percent($rules->groupPercent)
                ? $rules->groupNumbers->render($company->numberPrefix(), $dice->between(...))
                : null;
            $start = $this->notBeforeBirth($pid, $this->daysAgo($dice->between(30, 1800)));
            if ($type === PolicyType::Primary && $dice->percent($rules->endedPercent)) {
                $end = $this->daysAgo($dice->inRange($rules->endedDaysAgo));
                $endedStart = $this->notBeforeBirth($pid, $end->modify('-' . $dice->between(365, 1500) . ' days'));
                if ($endedStart < $end) {
                    $others = array_values(array_diff($rule->companyKeys, [$company->key]));
                    $endedCompany = $others === [] ? $company : $this->seedFile->insuranceCompany($dice->pick($others));
                    $this->policies[] = new PlannedPolicy(
                        $pid,
                        $type,
                        $endedCompany->key,
                        $this->policyNumber($endedCompany, $dice),
                        null,
                        $subscriber,
                        self::copay($rule->copay),
                        $endedStart,
                        $end,
                    );
                    $start = $end->modify('+1 day');
                }
            }
            $this->policies[] = new PlannedPolicy(
                $pid,
                $type,
                $company->key,
                $this->policyNumber($company, $dice),
                $groupNumber,
                $subscriber,
                self::copay($rule->copay),
                $start,
                null,
            );
        }
    }

    /**
     * @param callable(PlannedRelation): bool $test
     */
    private function relatedRef(int $pid, callable $test): ?string
    {
        foreach ($this->relations as $relation) {
            if ($relation->ownerPid === $pid && $test($relation)) {
                return $relation->personRef;
            }
        }
        return null;
    }

    /**
     * Whether personSubscriber() can make a subscriber of this person: a
     * patient, or someone with a birth date and a sex.
     */
    private function canSubscribe(PlannedRelation $relation): bool
    {
        $person = $this->people[$relation->personRef] ?? null;
        if ($person === null) {
            return false;
        }
        if ($person->linkedPid !== null) {
            return isset($this->homes[$person->linkedPid]);
        }
        return $person->birthDate !== null
            && $person->sex !== null
            && $this->subscriberAddress($relation->ownerPid, $relation->personRef) !== null;
    }

    /**
     * A related person's address for their policy: their own current one,
     * or else the patient's home.
     */
    private function subscriberAddress(int $pid, string $ref): ?PostalAddress
    {
        foreach ($this->addresses as $planned) {
            if ($planned->owner->personRef === $ref && $planned->primary && $planned->active) {
                return $planned->address;
            }
        }
        return $this->homes[$pid] ?? null;
    }

    /**
     * The reference of a case's related person with a key. Kept apart from
     * the unnamed ones (by index), so a key such as "1" can't take another
     * person's place.
     */
    private static function namedCasePersonRef(string $caseKey, string $patientKey, string $personKey): string
    {
        return 'case:' . $caseKey . ':' . $patientKey . ':key:' . $personKey;
    }

    private function patientSubscriber(int $subscriberPid, string $relationship): PlannedSubscriber
    {
        $synthea = $this->patients[$subscriberPid]->synthea;
        $fields = $this->fields[$subscriberPid] ?? [];
        return new PlannedSubscriber(
            $relationship,
            $synthea->firstName,
            $synthea->middleName,
            $this->lastNames[$subscriberPid],
            $synthea->birthDate,
            self::sexOption($synthea->gender())
                ?? throw new PlanException('Patient ' . $subscriberPid . ' has no sex to put on their insurance'),
            $this->homes[$subscriberPid]
                ?? throw new PlanException('Patient ' . $subscriberPid . ' has no address to put on their insurance'),
            $fields['phone_home'] ?? $fields['phone_cell'] ?? null,
        );
    }

    /**
     * A related person as subscriber. A person who is a patient takes the
     * patient's details; anyone else without an address of their own has
     * the patient's.
     */
    private function personSubscriber(int $pid, string $ref): PlannedSubscriber
    {
        $person = $this->people[$ref] ?? throw new PlanException('Unknown person ' . $ref);
        $relationship = $this->subscriberRelationship($pid, $ref);
        if ($person->linkedPid !== null) {
            return $this->patientSubscriber($person->linkedPid, $relationship);
        }
        $phone = null;
        foreach ($this->telecoms as $telecom) {
            if ($telecom->owner->personRef === $ref && $telecom->system === 'phone' && $telecom->primary && $telecom->active) {
                $phone = $telecom->value;
                break;
            }
        }
        $what = sprintf('%s %s, subscriber for patient %d,', $person->firstName, $person->lastName, $pid);
        return new PlannedSubscriber(
            $relationship,
            $person->firstName,
            null,
            $person->lastName,
            $person->birthDate ?? throw new PlanException($what . ' has no birth date; give a relationship that implies one'),
            $person->sex ?? throw new PlanException($what . ' has no sex; give a first name from the name pools'),
            $this->subscriberAddress($pid, $ref) ?? throw new PlanException($what . ' has no address'),
            $phone,
        );
    }

    /**
     * The patient's relation to the subscriber, as list `sub_relation` has it.
     */
    private function subscriberRelationship(int $pid, string $personRef): string
    {
        foreach ($this->relations as $relation) {
            if ($relation->ownerPid === $pid && $relation->personRef === $personRef) {
                return match (RelationshipKind::of($relation->relationship)) {
                    RelationshipKind::Spouse => 'spouse',
                    RelationshipKind::Parent => 'child',
                    RelationshipKind::Child, RelationshipKind::Sibling, RelationshipKind::Grandparent,
                    RelationshipKind::OtherFamily, RelationshipKind::NotFamily => 'other',
                };
            }
        }
        return 'other';
    }

    private function policyNumber(InsuranceCompanySpec $company, Dice $dice): string
    {
        return $this->seedFile->rules->insurance->policyNumbers->render($company->numberPrefix(), $dice->between(...));
    }

    private function notBeforeBirth(int $pid, \DateTimeImmutable $date): \DateTimeImmutable
    {
        return max($date, $this->patients[$pid]->synthea->birthDate);
    }

    private static function copay(?int $dollars): ?string
    {
        return $dollars === null ? null : $dollars . '.00';
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
            RelationshipKind::Grandparent => -$dice->between(45, 65),
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
