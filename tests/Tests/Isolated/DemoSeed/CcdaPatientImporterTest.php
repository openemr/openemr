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
use PHPUnit\Framework\TestCase;

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
}

/**
 * Records the importer's calls instead of touching a database.
 */
final class RecordingImportDatabase extends ImportDatabase
{
    /** @var list<string> */
    public array $calls = [];

    public function __construct(private readonly bool $locked = false, private readonly bool $failRestore = false)
    {
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
