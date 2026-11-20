<?php

/**
 * Parses a seed file (YAML) into a SeedFile.
 *
 * Everything that can be checked without the database is checked here,
 * before the loader writes anything: unknown keys, wrong types, values out
 * of range, and keys that don't resolve (a provider's facility, a case's
 * provider, a policy's company or subscriber). List values are collected in SeedFile::$listReferences for
 * ListValidator to check against the database; calendar categories and
 * insurance types are checked by the command.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class SeedFileParser
{
    private const SUPPORTED_VERSION = 1;

    private const SECTIONS = [
        'version', 'seed', 'synthea', 'contact_data', 'names', 'facilities', 'providers', 'insurance_companies',
        'rules', 'cases', 'appointments',
    ];

    private const SYNTHEA_KEYS = [
        'count', 'seed', 'clinician_seed', 'reference_date', 'years_of_history', 'plain_names',
        'state', 'city', 'ages', 'gender',
    ];

    public function parseFile(string $path): SeedFile
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new SeedFileException('Seed file not found or not readable: ' . $path);
        }
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new SeedFileException('Could not read seed file: ' . $path);
        }
        return $this->parse($contents);
    }

    public function parse(string $yaml): SeedFile
    {
        try {
            $data = Yaml::parse($yaml, Yaml::PARSE_DATETIME | Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (ParseException $e) {
            throw new SeedFileException('The seed file is not valid YAML', 0, $e);
        }
        $root = MapReader::of($data, '');
        $root->rejectUnknownKeys(self::SECTIONS);

        if ($root->int('version') !== self::SUPPORTED_VERSION) {
            throw SeedFileException::at('version', 'this loader reads version ' . self::SUPPORTED_VERSION);
        }

        $lists = new ListReferenceCollector();

        $rulesMap = $root->optionalMap('rules');
        $rules = $rulesMap === null ? Rules::none() : Rules::parse($rulesMap, $lists);

        $caseMaps = $root->optionalMapList('cases');
        $cases = [];
        foreach ($caseMaps as $caseMap) {
            $cases[] = CaseSpec::parse($caseMap, $lists);
        }

        $appointmentsMap = $root->optionalMap('appointments');
        $appointments = $appointmentsMap === null ? AppointmentsSpec::none() : AppointmentsSpec::parse($appointmentsMap, $lists);

        $contactData = $root->optionalMap('contact_data');
        $names = $root->optionalMap('names');

        $seedFile = new SeedFile(
            seed: $root->int('seed'),
            synthea: $this->parseSynthea($root->map('synthea')),
            contactData: $contactData === null ? null : ContactDataSettings::parse($contactData),
            names: $names === null ? null : NamePools::parse($names),
            facilities: array_map(
                static fn (MapReader $map): FacilitySpec => FacilitySpec::parse($map, $lists),
                $root->optionalMapList('facilities'),
            ),
            providers: array_map(
                ProviderSpec::parse(...),
                $root->optionalMapList('providers'),
            ),
            insuranceCompanies: array_map(
                InsuranceCompanySpec::parse(...),
                $root->optionalMapList('insurance_companies'),
            ),
            rules: $rules,
            cases: $cases,
            appointments: $appointments,
            listReferences: $lists->all(),
        );
        $this->checkCrossReferences($seedFile);
        return $seedFile;
    }

    private function parseSynthea(MapReader $section): SyntheaOptions
    {
        $section->rejectUnknownKeys(self::SYNTHEA_KEYS);

        $count = $section->int('count');
        if ($count < 1) {
            throw SeedFileException::at('synthea.count', 'must be at least 1');
        }
        $yearsOfHistory = $section->int('years_of_history');
        if ($yearsOfHistory < 0) {
            throw SeedFileException::at('synthea.years_of_history', 'must not be negative');
        }

        $state = $section->optionalString('state');
        $city = $section->optionalString('city');
        if ($city !== null && $state === null) {
            throw SeedFileException::at('synthea.city', 'needs synthea.state as well');
        }

        $ages = $section->optionalString('ages');
        $gender = $section->optionalString('gender');
        $parsedGender = null;
        if ($gender !== null) {
            $parsedGender = Gender::tryFrom($gender)
                ?? throw SeedFileException::at('synthea.gender', 'must be M or F');
        }

        return new SyntheaOptions(
            count: $count,
            seed: $section->int('seed'),
            clinicianSeed: $section->int('clinician_seed'),
            referenceDate: $section->date('reference_date'),
            yearsOfHistory: $yearsOfHistory,
            plainNames: $section->bool('plain_names'),
            state: $state,
            city: $city,
            ages: $ages === null ? null : AgeRange::fromString($ages, 'synthea.ages'),
            gender: $parsedGender,
        );
    }

    private function checkCrossReferences(SeedFile $seedFile): void
    {
        $facilityKeys = $this->uniqueKeys(
            array_map(static fn (FacilitySpec $facility): string => $facility->key, $seedFile->facilities),
            'facilities',
        );
        $providerKeys = $this->uniqueKeys(
            array_map(static fn (ProviderSpec $provider): string => $provider->key, $seedFile->providers),
            'providers',
        );
        $this->uniqueKeys(
            array_map(static fn (ProviderSpec $provider): string => strtolower($provider->username), $seedFile->providers),
            'providers (username)',
        );
        foreach ($seedFile->providers as $index => $provider) {
            if (!in_array($provider->facilityKey, $facilityKeys, true)) {
                throw SeedFileException::at('providers[' . $index . '].facility', 'no facility has key "' . $provider->facilityKey . '"');
            }
        }
        foreach ($seedFile->rules->careTeamProviders as $index => $key) {
            if (!in_array($key, $providerKeys, true)) {
                throw SeedFileException::at('rules.care_team.providers[' . $index . ']', 'no provider has key "' . $key . '"');
            }
        }

        $this->uniqueKeys(array_map(static fn (CaseSpec $case): string => $case->key, $seedFile->cases), 'cases');
        $patientKeys = [];
        $personKeys = [];
        foreach ($seedFile->cases as $caseIndex => $case) {
            foreach ($case->patients as $patientIndex => $patient) {
                $patientKeys[] = $patient->key;
                $path = 'cases[' . $caseIndex . '].patients[' . $patientIndex . ']';
                if ($patient->providerKey !== null && !in_array($patient->providerKey, $providerKeys, true)) {
                    throw SeedFileException::at($path . '.provider', 'no provider has key "' . $patient->providerKey . '"');
                }
            }
            foreach ($case->relatedPeople as $people) {
                foreach ($people as $person) {
                    if ($person->key !== null) {
                        $personKeys[] = $person->key;
                    }
                }
            }
        }
        $this->uniqueKeys($patientKeys, 'cases (patient keys)');
        $this->uniqueKeys([...$patientKeys, ...$personKeys], 'cases (patient and related person keys)');
        $this->checkInsuranceReferences($seedFile, $patientKeys);
        $this->checkAppointmentReferences($seedFile->appointments, $providerKeys, $facilityKeys, $patientKeys);

        if ($seedFile->rules->relatedPeople !== [] && $seedFile->names === null) {
            throw SeedFileException::at('names', 'required when rules.related_people adds people');
        }
        if ($seedFile->rules->addresses !== [] && $seedFile->names === null) {
            throw SeedFileException::at('names', 'required when rules.addresses makes up streets');
        }
        $needsContactData = $seedFile->rules->telecom !== []
            || $seedFile->rules->relatedPersonMobilePercent > 0
            || $seedFile->rules->relatedPersonEmailPercent > 0;
        if ($needsContactData && $seedFile->contactData === null) {
            throw SeedFileException::at('contact_data', 'required when rules make up phone numbers or emails');
        }
    }

    /**
     * @param list<string> $patientKeys every case patient key
     */
    private function checkInsuranceReferences(SeedFile $seedFile, array $patientKeys): void
    {
        $companyKeys = $this->uniqueKeys(
            array_map(static fn (InsuranceCompanySpec $company): string => $company->key, $seedFile->insuranceCompanies),
            'insurance_companies',
        );
        $requireCompany = static function (string $key, string $path) use ($companyKeys): void {
            if (!in_array($key, $companyKeys, true)) {
                throw SeedFileException::at($path, 'no insurance company has key "' . $key . '"');
            }
        };
        foreach ($seedFile->rules->insurance->companyKeys() as $key) {
            $requireCompany($key, 'rules.insurance');
        }
        foreach ($seedFile->cases as $caseIndex => $case) {
            foreach ($case->insurance as $patientKey => $insurance) {
                $path = 'cases[' . $caseIndex . '].insurance.' . $patientKey;
                foreach ($insurance->policies as $policy) {
                    $policyPath = $path . '.' . $policy->type->value;
                    $requireCompany($policy->companyKey, $policyPath . '.company');
                    $subscriber = $policy->subscriber;
                    if ($subscriber->kind === SubscriberKind::Patient) {
                        if (!in_array($subscriber->key, $patientKeys, true)) {
                            throw SeedFileException::at($policyPath . '.subscriber', 'no case patient has key "' . $subscriber->key . '"');
                        }
                        if ($subscriber->key === $patientKey) {
                            throw SeedFileException::at($policyPath . '.subscriber', 'use self for the patient themselves');
                        }
                    }
                    if ($subscriber->kind === SubscriberKind::Related && $case->relatedPersonOwner($subscriber->key ?? '') === null) {
                        throw SeedFileException::at($policyPath . '.subscriber', 'no related person in this case has key "' . $subscriber->key . '"');
                    }
                }
                foreach ($insurance->ended as $index => $ended) {
                    $requireCompany($ended->companyKey, $path . '.ended[' . $index . '].company');
                }
            }
        }
    }

    /**
     * @param list<string> $providerKeys
     * @param list<string> $facilityKeys
     * @param list<string> $patientKeys every case patient key
     */
    private function checkAppointmentReferences(AppointmentsSpec $appointments, array $providerKeys, array $facilityKeys, array $patientKeys): void
    {
        $requireKeys = static function (string $path, string $providerKey, string $facilityKey) use ($providerKeys, $facilityKeys): void {
            if (!in_array($providerKey, $providerKeys, true)) {
                throw SeedFileException::at($path . '.provider', 'no provider has key "' . $providerKey . '"');
            }
            if (!in_array($facilityKey, $facilityKeys, true)) {
                throw SeedFileException::at($path . '.facility', 'no facility has key "' . $facilityKey . '"');
            }
        };
        foreach ($appointments->clinics as $clinic) {
            $requireKeys($clinic->path, $clinic->providerKey, $clinic->facilityKey);
        }
        foreach ($appointments->visitListRules as $rule) {
            $requireKeys($rule->path, $rule->providerKey, $rule->facilityKey);
        }
        foreach ($appointments->fixedVisitLists as $list) {
            $requireKeys($list->path, $list->providerKey, $list->facilityKey);
            $listed = [];
            foreach ($list->patients as $index => $entry) {
                if ($entry->patientKey === null) {
                    continue;
                }
                $path = $list->path . '.patients[' . $index . '].patient';
                if (!in_array($entry->patientKey, $patientKeys, true)) {
                    throw SeedFileException::at($path, 'no case patient has key "' . $entry->patientKey . '"');
                }
                if (isset($listed[$entry->patientKey])) {
                    throw SeedFileException::at($path, '"' . $entry->patientKey . '" is on this list already');
                }
                $listed[$entry->patientKey] = true;
            }
        }
    }

    /**
     * @param list<string> $keys
     * @return list<string>
     */
    private function uniqueKeys(array $keys, string $path): array
    {
        $counts = array_count_values($keys);
        foreach ($counts as $key => $count) {
            if ($count > 1) {
                throw SeedFileException::at($path, 'key "' . $key . '" is used more than once');
            }
        }
        return $keys;
    }
}
