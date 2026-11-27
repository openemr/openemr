<?php

/**
 * Imports Synthea CCDA files as new patients, one at a time, and records
 * which pid each Synthea patient became.
 *
 * Each file goes through the existing `openemr:ccda-newpatient-import`
 * command in its own process, as `import_ccda.php` does in development mode;
 * the import keeps a lot of state and can call exit(), so it isn't run
 * in-process. Like that script, the audit log is off during the import and
 * missing UUIDs are filled in at the end.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Import;

use OpenEMR\DemoSeed\Synthea\SyntheaPatient;
use Symfony\Component\Process\Process;

final readonly class CcdaPatientImporter
{
    public function __construct(
        private ImportDatabase $database,
        private string $openemrPath,
        private string $site,
        private string $phpBinary = PHP_BINARY,
    ) {
    }

    /**
     * @param list<SyntheaPatient> $patients
     * @param ?callable(ImportedPatient, int, int): void $onImported called after each
     *        patient with the patient, its 1-based position and the total, so
     *        the caller can record it before the next import can fail
     * @return list<ImportedPatient>
     */
    public function importAll(array $patients, ?callable $onImported = null): array
    {
        // Held from before the audit settings are read until they are put
        // back, so a run on the same database with another work directory
        // can't read them while they are off and restore them off.
        $this->database->lockImports();
        try {
            return $this->importAllLocked($patients, $onImported);
        } finally {
            $this->database->unlockImports();
        }
    }

    /**
     * @param list<SyntheaPatient> $patients
     * @param ?callable(ImportedPatient, int, int): void $onImported
     * @return list<ImportedPatient>
     */
    private function importAllLocked(array $patients, ?callable $onImported): array
    {
        $imported = [];
        $previousAuditSettings = $this->database->disableAuditLog();
        try {
            foreach ($patients as $index => $patient) {
                $importedPatient = $this->importOne($patient);
                $imported[] = $importedPatient;
                if ($onImported !== null) {
                    $onImported($importedPatient, $index + 1, count($patients));
                }
            }
        } finally {
            try {
                $this->database->restoreAuditLog($previousAuditSettings);
            } finally {
                // Also after a failure, so the patients already imported are
                // complete for --reuse-import.
                $this->database->populateMissingUuids();
            }
        }
        return $imported;
    }

    private function importOne(SyntheaPatient $patient): ImportedPatient
    {
        $maxPidBefore = $this->database->maxPid();

        $process = new Process(
            [
                $this->phpBinary,
                rtrim($this->openemrPath, '/') . '/bin/console',
                'openemr:ccda-newpatient-import',
                '--site=' . $this->site,
                '--document=' . $patient->ccdaFile,
            ],
            $this->openemrPath,
            null,
            null,
            null,
        );
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ImportException(sprintf(
                'CCDA import failed for Synthea patient %s (exit code %d)',
                $patient->syntheaId,
                $process->getExitCode() ?? -1,
            ));
        }

        $pid = $this->importedPid($patient, $maxPidBefore);
        try {
            $uuid = $this->database->assignPatientUuid($pid);
        } catch (\Throwable $e) {
            // The patient is in the database but can't go in the manifest.
            throw new ImportException(sprintf(
                'Synthea patient %s was imported as pid %d, but could not be given a UUID, so it is not in the'
                . ' patient manifest and --reuse-import will leave it out; remove it or reset the database',
                $patient->syntheaId,
                $pid,
            ), 0, $e);
        }
        return new ImportedPatient($pid, $uuid, $patient);
    }

    /**
     * The command doesn't report the pid, so find the new patient by the
     * referrerID the import gives it: Synthea's patient id. A patient someone
     * else adds meanwhile has another one.
     */
    private function importedPid(SyntheaPatient $patient, int $maxPidBefore): int
    {
        $pids = $this->database->pidsWithReferrerId($patient->syntheaId);
        $newPids = array_values(array_filter($pids, static fn (int $pid): bool => $pid > $maxPidBefore));
        if (count($newPids) === 1) {
            return $newPids[0];
        }
        if ($newPids === [] && $pids !== []) {
            // The import merges a document into an existing patient with the
            // same referrerID and no encounters, instead of adding one.
            throw new ImportException(sprintf(
                'The CCDA import merged Synthea patient %s into existing patient pid %d instead of adding a'
                . ' patient; remove that patient or reset the database',
                $patient->syntheaId,
                $pids[count($pids) - 1],
            ));
        }
        throw new ImportException(sprintf(
            'Expected the CCDA import of Synthea patient %s to add one patient with that id, found %d',
            $patient->syntheaId,
            count($newPids),
        ));
    }
}
