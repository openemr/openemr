<?php

/**
 * Writes the list of imported patients (pid and Synthea facts) as JSON, so
 * the mapping can be inspected and later steps can reuse an import.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Import;

use Symfony\Component\Filesystem\Filesystem;

final readonly class PatientManifest
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    /**
     * @param list<ImportedPatient> $patients
     */
    public function write(string $path, array $patients): void
    {
        $rows = array_map(
            static fn (ImportedPatient $patient): array => ['pid' => $patient->pid, ...$patient->synthea->toArray()],
            $patients,
        );
        $this->filesystem->dumpFile(
            $path,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
        );
    }
}
