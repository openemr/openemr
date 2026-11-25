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
        $imported = [];
        $previousAuditSettings = $this->database->disableAuditLog();
        try {
            foreach ($patients as $index => $patient) {
                $maxAuditBefore = $this->database->maxAuditMasterId();
                try {
                    $importedPatient = $this->importOne($patient);
                    $imported[] = $importedPatient;
                    if ($onImported !== null) {
                        $onImported($importedPatient, $index + 1, count($patients));
                    }
                } finally {
                    // After the patient is recorded, so a failure here can't
                    // leave an imported patient out of the manifest; also
                    // after a failed import, which stages rows too.
                    $this->database->deleteAuditStagingAbove($maxAuditBefore);
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

        $newPids = $this->database->pidsAbove($maxPidBefore);
        if (count($newPids) !== 1) {
            throw new ImportException(sprintf(
                'Expected the CCDA import of Synthea patient %s to create one patient, found %d',
                $patient->syntheaId,
                count($newPids),
            ));
        }
        try {
            $uuid = $this->database->assignPatientUuid($newPids[0]);
        } catch (\Throwable $e) {
            // The patient is in the database but can't go in the manifest.
            throw new ImportException(sprintf(
                'Synthea patient %s was imported as pid %d, but could not be given a UUID, so it is not in the'
                . ' patient manifest and --reuse-import will leave it out; remove it or reset the database',
                $patient->syntheaId,
                $newPids[0],
            ), 0, $e);
        }
        return new ImportedPatient($newPids[0], $uuid, $patient);
    }
}
