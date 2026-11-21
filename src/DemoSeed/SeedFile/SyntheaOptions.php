<?php

/**
 * The `synthea` section of a seed file: how to run Synthea.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class SyntheaOptions
{
    public function __construct(
        public int $count,
        public int $seed,
        public int $clinicianSeed,
        public \DateTimeImmutable $referenceDate,
        public int $yearsOfHistory,
        public bool $plainNames,
        public ?string $state = null,
        public ?string $city = null,
        public ?AgeRange $ages = null,
        public ?Gender $gender = null,
    ) {
        if ($count < 1) {
            throw new \DomainException('Synthea count must be at least 1');
        }
        if ($yearsOfHistory < 0) {
            throw new \DomainException('Synthea years of history must not be negative');
        }
        if ($city !== null && $state === null) {
            throw new \DomainException('Synthea city needs a state');
        }
    }

    public function withCount(int $count): self
    {
        return new self(
            $count,
            $this->seed,
            $this->clinicianSeed,
            $this->referenceDate,
            $this->yearsOfHistory,
            $this->plainNames,
            $this->state,
            $this->city,
            $this->ages,
            $this->gender,
        );
    }

    /**
     * Synthea command-line arguments (after `java -jar <jar>`).
     *
     * Always exports both CCDA (imported) and FHIR (read for what the CCDA
     * lacks, such as marital status and payer), and only living patients.
     *
     * @return list<string>
     */
    public function toArguments(string $outputDirectory): array
    {
        $arguments = [
            '-p', (string) $this->count,
            '-s', (string) $this->seed,
            '-cs', (string) $this->clinicianSeed,
            '-r', $this->referenceDate->format('Ymd'),
        ];
        if ($this->ages !== null) {
            array_push($arguments, '-a', $this->ages->toString());
        }
        if ($this->gender !== null) {
            array_push($arguments, '-g', $this->gender->value);
        }
        array_push(
            $arguments,
            '--exporter.baseDirectory=' . rtrim($outputDirectory, '/') . '/',
            '--exporter.years_of_history=' . $this->yearsOfHistory,
            '--exporter.ccda.export=true',
            '--exporter.fhir.export=true',
            '--exporter.hospital.fhir.export=false',
            '--exporter.practitioner.fhir.export=false',
            '--exporter.metadata.export=false',
            '--generate.only_alive_patients=true',
            '--generate.append_numbers_to_person_names=' . ($this->plainNames ? 'false' : 'true'),
        );
        // Synthea takes state and city as trailing positional arguments.
        if ($this->state !== null) {
            $arguments[] = $this->state;
            if ($this->city !== null) {
                $arguments[] = $this->city;
            }
        }
        return $arguments;
    }
}
