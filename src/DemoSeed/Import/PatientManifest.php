<?php

/**
 * Writes the list of imported patients (pid and Synthea facts) as JSON, so
 * the mapping can be inspected and later steps can reuse an import.
 *
 * The manifest names the site it was written for, so it isn't reused for
 * another site.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Import;

use OpenEMR\DemoSeed\Synthea\SyntheaException;
use OpenEMR\DemoSeed\Synthea\SyntheaPatient;
use Symfony\Component\Filesystem\Filesystem;

final readonly class PatientManifest
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    /**
     * @param list<ImportedPatient> $patients
     */
    public function write(string $path, string $site, array $patients): void
    {
        $rows = array_map(
            static fn (ImportedPatient $patient): array => ['pid' => $patient->pid, 'uuid' => $patient->uuid, ...$patient->synthea->toArray()],
            $patients,
        );
        $this->filesystem->dumpFile(
            $path,
            json_encode(
                ['site' => $site, 'patients' => $rows],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            ) . "\n",
        );
    }

    /**
     * Reads a manifest written for $site and checks, before anything is
     * written, that each pid is still the patient imported there, e.g. that
     * the database wasn't reset since.
     *
     * @param callable(int): ?string $uuidOfPid the UUID of the patient with
     *        that pid in the database, or null if there is none
     * @return list<ImportedPatient>
     */
    public function readForReuse(string $path, string $site, callable $uuidOfPid): array
    {
        $patients = $this->read($path, $site);
        foreach ($patients as $patient) {
            if ($uuidOfPid($patient->pid) !== $patient->uuid) {
                throw new ImportException(sprintf(
                    'pid %d is not the patient %s says was imported there (%s %s, UUID %s);'
                    . ' was the database reset since that import?',
                    $patient->pid,
                    $path,
                    $patient->synthea->firstName,
                    $patient->synthea->lastName,
                    $patient->uuid,
                ));
            }
        }
        return $patients;
    }

    /**
     * Reads a manifest written for $site.
     *
     * @return list<ImportedPatient>
     */
    public function read(string $path, string $site): array
    {
        if (!is_file($path)) {
            throw new ImportException('No patient manifest at ' . $path . '; run the loader once without --reuse-import');
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new ImportException('Could not read the patient manifest at ' . $path);
        }
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ImportException('The patient manifest is not valid JSON: ' . $path, 0, $e);
        }
        if (!is_array($decoded) || array_is_list($decoded)) {
            throw new ImportException(
                'The patient manifest at ' . $path . ' is from an older loader that did not record the site'
                . ' or patient UUIDs; reset the database and run the loader without --reuse-import',
            );
        }
        $manifestSite = $decoded['site'] ?? null;
        if ($manifestSite !== $site) {
            throw new ImportException(sprintf(
                'The patient manifest at %s was written for site "%s", not "%s"',
                $path,
                is_string($manifestSite) ? $manifestSite : '?',
                $site,
            ));
        }
        $rows = $decoded['patients'] ?? null;
        if (!is_array($rows) || !array_is_list($rows)) {
            throw new ImportException('The patient manifest has no list of patients: ' . $path);
        }
        $patients = [];
        foreach ($rows as $position => $row) {
            $pid = is_array($row) ? ($row['pid'] ?? null) : null;
            $uuid = is_array($row) ? ($row['uuid'] ?? null) : null;
            if (!is_array($row) || !is_int($pid) || !is_string($uuid)) {
                throw new ImportException('A patient manifest row has no pid or uuid: ' . $path);
            }
            try {
                $patients[] = new ImportedPatient($pid, $uuid, SyntheaPatient::fromArray($row));
            } catch (\DomainException | SyntheaException $e) {
                throw new ImportException(
                    sprintf('Patient %d in the manifest at %s is not valid', $position + 1, $path),
                    0,
                    $e,
                );
            }
        }
        return $patients;
    }
}
