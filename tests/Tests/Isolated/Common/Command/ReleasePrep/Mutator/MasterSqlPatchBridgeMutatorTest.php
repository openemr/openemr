<?php

/**
 * Tests for MasterSqlPatchBridgeMutator: renames the long-lived bridge
 * upgrade file so its "from" anchor reflects the just-shipped patch.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\Mutator\MasterSqlPatchBridgeMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('release-prep')]
final class MasterSqlPatchBridgeMutatorTest extends TestCase
{
    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-mspbm-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir . '/sql', 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir');
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testRenamesBridgeFromPrevPatchToNewPatch(): void
    {
        $oldContents = "-- header for 8_1_0-to-8_2_0\nINSERT INTO foo VALUES (1);\n";
        $this->writeSql('8_1_0-to-8_2_0_upgrade.sql', $oldContents);

        $result = (new MasterSqlPatchBridgeMutator())->apply($this->context('8.1.1', '8.1.0'));

        self::assertTrue($result->changed());
        self::assertFalse(is_file($this->tmpDir . '/sql/8_1_0-to-8_2_0_upgrade.sql'));
        self::assertTrue(is_file($this->tmpDir . '/sql/8_1_1-to-8_2_0_upgrade.sql'));
        self::assertSame(
            $oldContents,
            file_get_contents($this->tmpDir . '/sql/8_1_1-to-8_2_0_upgrade.sql'),
            'rename must preserve file contents exactly',
        );
        self::assertSame(
            ['sql/8_1_0-to-8_2_0_upgrade.sql', 'sql/8_1_1-to-8_2_0_upgrade.sql'],
            $result->changedFiles,
        );
    }

    public function testIdempotentWhenNewExistsAndOldGone(): void
    {
        $this->writeSql('8_1_1-to-8_2_0_upgrade.sql', "-- already renamed\n");

        $result = (new MasterSqlPatchBridgeMutator())->apply($this->context('8.1.1', '8.1.0'));

        self::assertFalse($result->changed());
        self::assertTrue(is_file($this->tmpDir . '/sql/8_1_1-to-8_2_0_upgrade.sql'));
    }

    public function testIdempotentOnSecondRun(): void
    {
        $this->writeSql('8_1_0-to-8_2_0_upgrade.sql', "-- header\n");
        $mutator = new MasterSqlPatchBridgeMutator();
        $mutator->apply($this->context('8.1.1', '8.1.0'));
        $second = $mutator->apply($this->context('8.1.1', '8.1.0'));
        self::assertFalse($second->changed());
    }

    public function testAmbiguousStateBothFilesExistThrows(): void
    {
        $this->writeSql('8_1_0-to-8_2_0_upgrade.sql', "-- old\n");
        $this->writeSql('8_1_1-to-8_2_0_upgrade.sql', "-- new\n");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/ambiguous/');
        (new MasterSqlPatchBridgeMutator())->apply($this->context('8.1.1', '8.1.0'));
    }

    public function testNeitherFileExistsThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Neither bridge file exists/');
        (new MasterSqlPatchBridgeMutator())->apply($this->context('8.1.1', '8.1.0'));
    }

    public function testRequiresFromVersion(): void
    {
        $this->writeSql('8_1_0-to-8_2_0_upgrade.sql', "-- header\n");
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.1.1', 'rel-810');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/--prev-version/');
        (new MasterSqlPatchBridgeMutator())->apply($context);
    }

    public function testHandlesSecondPatchOnSameMinor(): void
    {
        // rel-810 already shipped 8.1.1 (so bridge is at 8_1_1-to-8_2_0);
        // now patch-prep is 8.1.2 with prev=8.1.1.
        $this->writeSql('8_1_1-to-8_2_0_upgrade.sql', "-- accumulated SQL\nINSERT INTO bar VALUES (1);\n");

        $result = (new MasterSqlPatchBridgeMutator())->apply($this->context('8.1.2', '8.1.1'));

        self::assertTrue($result->changed());
        self::assertFalse(is_file($this->tmpDir . '/sql/8_1_1-to-8_2_0_upgrade.sql'));
        self::assertTrue(is_file($this->tmpDir . '/sql/8_1_2-to-8_2_0_upgrade.sql'));
    }

    public function testNextMinorAnchorMatchesTargetMinorPlusOne(): void
    {
        // target 8.2.1 (rel-820 patch-prep) → next minor = 8.3.0.
        $this->writeSql('8_2_0-to-8_3_0_upgrade.sql', "-- header\n");

        (new MasterSqlPatchBridgeMutator())->apply($this->context('8.2.1', '8.2.0'));

        self::assertFalse(is_file($this->tmpDir . '/sql/8_2_0-to-8_3_0_upgrade.sql'));
        self::assertTrue(is_file($this->tmpDir . '/sql/8_2_1-to-8_3_0_upgrade.sql'));
    }

    private function context(string $targetVersion, string $prevVersion): MutatorContext
    {
        return MutatorContext::fromVersionString(
            $this->tmpDir,
            $targetVersion,
            null,
            null,
            $prevVersion,
        );
    }

    private function writeSql(string $name, string $contents): void
    {
        $path = $this->tmpDir . '/sql/' . $name;
        if (file_put_contents($path, $contents) === false) {
            throw new \RuntimeException('Failed to write ' . $path);
        }
    }

    private function removeRecursive(string $path): void
    {
        if (!is_dir($path)) {
            if (is_file($path) || is_link($path)) {
                unlink($path);
            }
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var \SplFileInfo $entry */
        foreach ($iterator as $entry) {
            $p = $entry->getPathname();
            if ($entry->isDir() && !$entry->isLink()) {
                rmdir($p);
            } else {
                unlink($p);
            }
        }
        rmdir($path);
    }
}
