<?php

/**
 * One `cases[]` entry: specific patients and everything they get, written
 * out in full.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class CaseSpec
{
    /**
     * @param non-empty-list<CasePatient> $patients
     * @param list<CaseLink> $links
     * @param array<string, list<TelecomEntry>> $telecom by patient key
     * @param array<string, list<AddressEntry>> $addresses by patient key
     * @param array<string, list<RelatedPersonEntry>> $relatedPeople by patient key
     * @param array<string, CaseInsurance> $insurance by patient key
     */
    public function __construct(
        public string $key,
        public ?string $note,
        public array $patients,
        public array $links,
        public array $telecom,
        public array $addresses,
        public array $relatedPeople,
        public array $insurance,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['key', 'note', 'patients', 'links', 'telecom', 'addresses', 'related_people', 'insurance']);

        $patients = [];
        $known = [];
        foreach ($map->mapList('patients') as $patientMap) {
            $patient = CasePatient::parse($patientMap, $lists);
            if (in_array($patient->key, $known, true)) {
                throw SeedFileException::at($patientMap->pathOf('key'), 'patient key "' . $patient->key . '" is used twice in this case');
            }
            foreach (['last_name' => $patient->lastNameSameAs, 'address' => $patient->addressSameAs] as $field => $sameAs) {
                if ($sameAs !== null && !in_array($sameAs, $known, true)) {
                    throw SeedFileException::at($patientMap->pathOf($field), 'same_as must name a patient listed earlier in this case');
                }
            }
            $known[] = $patient->key;
            $patients[] = $patient;
        }
        if ($patients === []) {
            throw SeedFileException::at($map->pathOf('patients'), 'a case needs at least one patient');
        }

        $links = [];
        foreach ($map->optionalMapList('links') as $linkMap) {
            $link = CaseLink::parse($linkMap, $lists);
            self::requireKnown($known, $link->from, $linkMap->pathOf('from'));
            self::requireKnown($known, $link->to, $linkMap->pathOf('to'));
            $links[] = $link;
        }

        return new self(
            $map->string('key'),
            $map->optionalString('note'),
            $patients,
            $links,
            self::byPatient(
                $map->optionalMap('telecom'),
                $known,
                static fn (MapReader $owner, string $key): array => TelecomEntry::parseList($owner, $key, $lists),
            ),
            self::byPatient(
                $map->optionalMap('addresses'),
                $known,
                static fn (MapReader $owner, string $key): array => array_map(
                    static fn (MapReader $entry): AddressEntry => AddressEntry::parse($entry, $lists),
                    $owner->mapList($key),
                ),
            ),
            self::byPatient(
                $map->optionalMap('related_people'),
                $known,
                static fn (MapReader $owner, string $key): array => array_map(
                    static fn (MapReader $entry): RelatedPersonEntry => RelatedPersonEntry::parse($entry, $lists),
                    $owner->mapList($key),
                ),
            ),
            self::insuranceByPatient($map->optionalMap('insurance'), $known),
        );
    }

    /**
     * @param list<string> $known
     * @return array<string, CaseInsurance>
     */
    private static function insuranceByPatient(?MapReader $map, array $known): array
    {
        if ($map === null) {
            return [];
        }
        $result = [];
        foreach ($map->keys() as $patientKey) {
            self::requireKnown($known, $patientKey, $map->pathOf($patientKey));
            $result[$patientKey] = CaseInsurance::parse($map->map($patientKey));
        }
        return $result;
    }

    /**
     * The patient key whose related people include the one with this key.
     */
    public function relatedPersonOwner(string $personKey): ?string
    {
        foreach ($this->relatedPeople as $patientKey => $people) {
            foreach ($people as $person) {
                if ($person->key === $personKey) {
                    return $patientKey;
                }
            }
        }
        return null;
    }

    /**
     * @template T
     * @param list<string> $known
     * @param callable(MapReader, string): list<T> $parseEntries
     * @return array<string, list<T>>
     */
    private static function byPatient(?MapReader $map, array $known, callable $parseEntries): array
    {
        if ($map === null) {
            return [];
        }
        $result = [];
        foreach ($map->keys() as $patientKey) {
            self::requireKnown($known, $patientKey, $map->pathOf($patientKey));
            $result[$patientKey] = $parseEntries($map, $patientKey);
        }
        return $result;
    }

    /**
     * @param list<string> $known
     */
    private static function requireKnown(array $known, string $key, string $path): void
    {
        if (!in_array($key, $known, true)) {
            throw SeedFileException::at($path, '"' . $key . '" is not a patient of this case');
        }
    }
}
