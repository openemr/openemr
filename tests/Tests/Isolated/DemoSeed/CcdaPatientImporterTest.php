<?php

/**
 * Isolated tests for the order of the demo seed import's database steps.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\DemoSeed;

use OpenEMR\DemoSeed\Import\CcdaPatientImporter;
use OpenEMR\DemoSeed\Import\ImportDatabase;
use OpenEMR\DemoSeed\Import\ImportException;
use OpenEMR\DemoSeed\Synthea\SyntheaPatient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

final class CcdaPatientImporterTest extends TestCase
{
    public function testHoldsTheLockFromBeforeTheAuditSettingsAreReadUntilTheyArePutBack(): void
    {
        $database = new RecordingImportDatabase();

        (new CcdaPatientImporter($database, '/openemr', 'default'))->importAll([]);

        $this->assertSame(['lock', 'disable', 'restore', 'uuids', 'unlock'], $database->calls);
    }

    public function testASecondRunOnTheSameDatabaseLeavesTheAuditSettingsAlone(): void
    {
        $database = new RecordingImportDatabase(locked: true);

        try {
            (new CcdaPatientImporter($database, '/openemr', 'default'))->importAll([]);
            $this->fail('The import should stop when another run holds the lock');
        } catch (ImportException $e) {
            $this->assertStringContainsString('Another run of the loader', $e->getMessage());
        }
        $this->assertSame(['lock'], $database->calls);
    }

    public function testReleasesTheLockWhenPuttingTheAuditSettingsBackFails(): void
    {
        $database = new RecordingImportDatabase(failRestore: true);

        try {
            (new CcdaPatientImporter($database, '/openemr', 'default'))->importAll([]);
            $this->fail('The restore failure should propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('restore failed', $e->getMessage());
        }
        $this->assertSame(['lock', 'disable', 'restore', 'uuids', 'unlock'], $database->calls);
    }

    public function testTakesTheNewPatientWithTheSyntheaIdAndIgnoresOneAddedMeanwhile(): void
    {
        // pid 6 was registered by someone else while the import ran.
        $database = new RecordingImportDatabase(maxPid: 4, pidsByReferrerId: ['synthea-1' => [5]]);

        $imported = $this->importer($database)->importAll([$this->patient('synthea-1')]);

        $this->assertSame(5, $imported[0]->pid);
        $this->assertSame('00000000-0000-4000-8000-000000000005', $imported[0]->uuid);
    }

    public function testStopsWhenTheImportMergedIntoAnExistingPatient(): void
    {
        $database = new RecordingImportDatabase(maxPid: 4, pidsByReferrerId: ['synthea-1' => [2]]);

        $this->expectException(ImportException::class);
        $this->expectExceptionMessage('merged Synthea patient synthea-1 into existing patient pid 2');

        $this->importer($database)->importAll([$this->patient('synthea-1')]);
    }

    public function testStopsWhenTheImportAddedNoPatient(): void
    {
        $database = new RecordingImportDatabase(maxPid: 4);

        $this->expectException(ImportException::class);
        $this->expectExceptionMessage('to add one patient with that id, found 0');

        $this->importer($database)->importAll([$this->patient('synthea-1')]);
    }

    /**
     * An importer whose child process is `true`, which succeeds without
     * importing anything; the recording database plays the import's part.
     */
    private function importer(RecordingImportDatabase $database): CcdaPatientImporter
    {
        $true = (new ExecutableFinder())->find('true');
        if ($true === null) {
            $this->markTestSkipped('No `true` executable to stand in for the import');
        }
        return new CcdaPatientImporter($database, sys_get_temp_dir(), 'default', $true);
    }

    private function patient(string $syntheaId): SyntheaPatient
    {
        return new SyntheaPatient(
            $syntheaId,
            $syntheaId . '.xml',
            $syntheaId . '.json',
            'Al',
            'Able',
            new \DateTimeImmutable('1960-01-10'),
            'male',
            'M',
            null,
        );
    }
}

/**
 * Records the importer's calls instead of touching a database.
 */
final class RecordingImportDatabase extends ImportDatabase
{
    /** @var list<string> */
    public array $calls = [];

    /**
     * @param array<string, list<int>> $pidsByReferrerId what the database
     *        holds after the import
     */
    public function __construct(
        private readonly bool $locked = false,
        private readonly bool $failRestore = false,
        private readonly int $maxPid = 0,
        private readonly array $pidsByReferrerId = [],
    ) {
    }

    public function maxPid(): int
    {
        return $this->maxPid;
    }

    public function pidsWithReferrerId(string $referrerId): array
    {
        return $this->pidsByReferrerId[$referrerId] ?? [];
    }

    public function assignPatientUuid(int $pid): string
    {
        return sprintf('00000000-0000-4000-8000-%012d', $pid);
    }

    public function lockImports(): void
    {
        $this->calls[] = 'lock';
        if ($this->locked) {
            throw new ImportException('Another run of the loader is importing into this database; wait for it to finish');
        }
    }

    public function unlockImports(): void
    {
        $this->calls[] = 'unlock';
    }

    public function disableAuditLog(): array
    {
        $this->calls[] = 'disable';
        return ['enable_auditlog' => '1'];
    }

    public function restoreAuditLog(array $previous): void
    {
        $this->calls[] = 'restore';
        if ($this->failRestore) {
            throw new \RuntimeException('restore failed');
        }
    }

    public function populateMissingUuids(): void
    {
        $this->calls[] = 'uuids';
    }
}
