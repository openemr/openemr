<?php

/**
 * Parses a seed file (YAML) into a SeedFile.
 *
 * Everything is checked before the loader writes anything: unknown keys,
 * wrong types and out-of-range values are errors that name their path in
 * the file. Sections that a later version of the loader will load are
 * accepted and listed in SeedFile::$sectionsNotLoaded.
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

    /** Sections this loader version reads. */
    private const LOADED_SECTIONS = ['version', 'seed', 'synthea'];

    /** Sections recognized but not loaded yet (later steps). */
    private const PENDING_SECTIONS = [
        'names',
        'facilities',
        'providers',
        'insurance_companies',
        'rules',
        'cases',
        'appointments',
    ];

    private const SYNTHEA_KEYS = [
        'count',
        'seed',
        'clinician_seed',
        'reference_date',
        'years_of_history',
        'plain_names',
        'state',
        'city',
        'ages',
        'gender',
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
        $root = $this->requireMap($data, '(file)');
        $this->rejectUnknownKeys($root, [...self::LOADED_SECTIONS, ...self::PENDING_SECTIONS], '(file)');

        $version = $this->requireInt($root, 'version', '');
        if ($version !== self::SUPPORTED_VERSION) {
            throw SeedFileException::at('version', 'this loader reads version ' . self::SUPPORTED_VERSION);
        }

        $sectionsNotLoaded = array_values(array_filter(
            self::PENDING_SECTIONS,
            static fn (string $section): bool => array_key_exists($section, $root),
        ));

        return new SeedFile(
            $this->requireInt($root, 'seed', ''),
            $this->parseSynthea($this->requireMap($root['synthea'] ?? null, 'synthea')),
            $sectionsNotLoaded,
        );
    }

    /**
     * @param array<string, mixed> $section
     */
    private function parseSynthea(array $section): SyntheaOptions
    {
        $path = 'synthea';
        $this->rejectUnknownKeys($section, self::SYNTHEA_KEYS, $path);

        $count = $this->requireInt($section, 'count', $path);
        if ($count < 1) {
            throw SeedFileException::at('synthea.count', 'must be at least 1');
        }
        $yearsOfHistory = $this->requireInt($section, 'years_of_history', $path);
        if ($yearsOfHistory < 0) {
            throw SeedFileException::at('synthea.years_of_history', 'must not be negative');
        }

        $state = $this->optionalString($section, 'state', $path);
        $city = $this->optionalString($section, 'city', $path);
        if ($city !== null && $state === null) {
            throw SeedFileException::at('synthea.city', 'needs synthea.state as well');
        }

        $ages = $this->optionalString($section, 'ages', $path);
        $gender = $this->optionalString($section, 'gender', $path);
        $parsedGender = null;
        if ($gender !== null) {
            $parsedGender = Gender::tryFrom($gender)
                ?? throw SeedFileException::at('synthea.gender', 'must be M or F');
        }

        return new SyntheaOptions(
            count: $count,
            seed: $this->requireInt($section, 'seed', $path),
            clinicianSeed: $this->requireInt($section, 'clinician_seed', $path),
            referenceDate: $this->requireDate($section, 'reference_date', $path),
            yearsOfHistory: $yearsOfHistory,
            plainNames: $this->requireBool($section, 'plain_names', $path),
            state: $state,
            city: $city,
            ages: $ages === null ? null : AgeRange::fromString($ages, 'synthea.ages'),
            gender: $parsedGender,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requireMap(mixed $value, string $path): array
    {
        if (!is_array($value) || ($value !== [] && array_is_list($value))) {
            throw SeedFileException::at($path, 'expected a map of keys and values');
        }
        $map = [];
        foreach ($value as $key => $item) {
            if (!is_string($key)) {
                throw SeedFileException::at($path, 'keys must be names, found ' . $key);
            }
            $map[$key] = $item;
        }
        return $map;
    }

    /**
     * @param array<string, mixed> $map
     * @param list<string> $allowed
     */
    private function rejectUnknownKeys(array $map, array $allowed, string $path): void
    {
        $unknown = array_diff(array_keys($map), $allowed);
        if ($unknown !== []) {
            throw SeedFileException::at($path, 'unknown key(s): ' . implode(', ', $unknown));
        }
    }

    /**
     * @param array<string, mixed> $map
     */
    private function requireInt(array $map, string $key, string $path): int
    {
        $value = $map[$key] ?? null;
        if (!is_int($value)) {
            throw SeedFileException::at($this->join($path, $key), 'required, must be a whole number');
        }
        return $value;
    }

    /**
     * @param array<string, mixed> $map
     */
    private function requireBool(array $map, string $key, string $path): bool
    {
        $value = $map[$key] ?? null;
        if (!is_bool($value)) {
            throw SeedFileException::at($this->join($path, $key), 'required, must be true or false');
        }
        return $value;
    }

    /**
     * @param array<string, mixed> $map
     */
    private function optionalString(array $map, string $key, string $path): ?string
    {
        if (!array_key_exists($key, $map) || $map[$key] === null) {
            return null;
        }
        $value = $map[$key];
        if (!is_string($value) || trim($value) === '') {
            throw SeedFileException::at($this->join($path, $key), 'must be text');
        }
        return trim($value);
    }

    /**
     * Accepts a YAML date (2026-11-19) or the same date in quotes.
     *
     * @param array<string, mixed> $map
     */
    private function requireDate(array $map, string $key, string $path): \DateTimeImmutable
    {
        $value = $map[$key] ?? null;
        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value)->setTime(0, 0);
        }
        if (is_string($value)) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('UTC'));
            if ($date !== false && $date->format('Y-m-d') === $value) {
                return $date;
            }
        }
        throw SeedFileException::at($this->join($path, $key), 'required, must be a date like 2026-11-19');
    }

    private function join(string $path, string $key): string
    {
        return $path === '' ? $key : $path . '.' . $key;
    }
}
