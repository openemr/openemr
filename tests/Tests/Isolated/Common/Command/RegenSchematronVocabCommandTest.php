<?php

/**
 * RegenSchematronVocabCommand isolated test.
 *
 * Covers the failure paths the command exists to guarantee: preflight before any
 * write, atomic temp-then-rename swaps, and restoring the prior .sch when the
 * vocab.php swap fails after the .sch has already been replaced.
 *
 * Failures are induced with the filesystem rather than mocks - a destination
 * path occupied by a non-empty directory makes file_put_contents() and rename()
 * fail deterministically for any user, including root, which matters because
 * the isolated suite may run as root inside the container.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use FilesystemIterator;
use OpenEMR\Common\Command\RegenSchematronVocabCommand;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class RegenSchematronVocabCommandTest extends TestCase
{
    /** Schema types and their .sch filenames, mirroring the command's TARGETS. */
    private const TARGETS = [
        'ccda' => 'Consolidation.sch',
        'qrda1' => '2022_CMS_QRDA_I.sch',
        'qrda3' => '2022_CMS_QRDA_Category_III.sch',
    ];

    private const OID = '2.16.840.1.113883.1.11.1';

    private string $workDir;
    private string $sourceRoot;
    private string $outputRoot;

    protected function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . '/oe-regen-vocab-' . bin2hex(random_bytes(6));
        $this->sourceRoot = $this->workDir . '/source';
        $this->outputRoot = $this->workDir . '/schemas';
        self::assertTrue(mkdir($this->outputRoot, 0777, true));
        $this->writeCompleteSourceTree();
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->workDir);
    }

    public function testWritesSchAndVocabForEveryTarget(): void
    {
        $tester = $this->runCommand();

        self::assertSame(Command::SUCCESS, $tester->getStatusCode(), $tester->getDisplay());

        foreach (self::TARGETS as $type => $schFile) {
            $schDest = "$this->outputRoot/$type/$schFile";
            $vocabDest = "$this->outputRoot/$type/vocab.php";
            self::assertFileExists($schDest, "$type: .sch not written");
            self::assertFileExists($vocabDest, "$type: vocab.php not written");
            self::assertSame(
                $this->schematronFixture(),
                file_get_contents($schDest),
                "$type: .sch content does not match source"
            );

            $vocab = require $vocabDest;
            self::assertIsArray($vocab, "$type: vocab.php did not return an array");
            self::assertSame(['completed', 'active'], $vocab[self::OID] ?? null, "$type: OID values not resolved");
        }

        // No temp files survive a successful run.
        self::assertSame([], $this->globTemps(), 'temp files left behind after success');
    }

    public function testPreflightFailsBeforeWritingAnything(): void
    {
        unlink("$this->sourceRoot/schematron/qrda3/voc.xml");

        $tester = $this->runCommand();

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('Missing required source files', $tester->getDisplay());

        // ccda sorts first in TARGETS - had preflight not run, it would already be written.
        self::assertFileDoesNotExist("$this->outputRoot/ccda/Consolidation.sch");
        self::assertFileDoesNotExist("$this->outputRoot/ccda/vocab.php");
    }

    public function testTempWriteFailureLeavesDestinationUntouched(): void
    {
        $this->seedPriorPair('ccda');
        // Occupy the .sch temp path with a non-empty directory so the write fails.
        $this->makeNonEmptyDir("$this->outputRoot/ccda/Consolidation.sch.tmp");

        $tester = $this->runCommand();

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('failed to write temp files', $tester->getDisplay());
        self::assertSame('PRIOR SCH', file_get_contents("$this->outputRoot/ccda/Consolidation.sch"));
        self::assertSame('PRIOR VOCAB', file_get_contents("$this->outputRoot/ccda/vocab.php"));
    }

    public function testSchSwapFailureRemovesBothTempFiles(): void
    {
        // Occupy the .sch destination with a non-empty directory so rename() fails.
        $this->makeNonEmptyDir("$this->outputRoot/ccda/Consolidation.sch");

        $tester = $this->runCommand();

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('failed to swap in new .sch', $tester->getDisplay());
        self::assertSame([], $this->globTemps(), 'temp files left behind after failed .sch swap');
    }

    public function testVocabSwapFailureRestoresPriorSch(): void
    {
        $this->seedPriorPair('ccda');
        // Replace the prior vocab.php with a non-empty directory so its rename() fails
        // after the .sch has already been swapped in.
        unlink("$this->outputRoot/ccda/vocab.php");
        $this->makeNonEmptyDir("$this->outputRoot/ccda/vocab.php");

        $tester = $this->runCommand();

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertStringContainsString('prior .sch restored', $tester->getDisplay());
        self::assertSame(
            'PRIOR SCH',
            file_get_contents("$this->outputRoot/ccda/Consolidation.sch"),
            'prior .sch was not restored after the vocab swap failed'
        );
        self::assertSame([], $this->globTemps(), 'temp files left behind after failed vocab swap');
    }

    public function testVocabSwapFailureWithNoPriorSchLeavesNoOrphan(): void
    {
        self::assertTrue(mkdir("$this->outputRoot/ccda", 0777, true));
        $this->makeNonEmptyDir("$this->outputRoot/ccda/vocab.php");

        $tester = $this->runCommand();

        self::assertSame(Command::FAILURE, $tester->getStatusCode());
        self::assertFileDoesNotExist(
            "$this->outputRoot/ccda/Consolidation.sch",
            'a .sch with no matching vocab.php was left behind'
        );
        self::assertSame([], $this->globTemps(), 'temp files left behind after failed vocab swap');
    }

    private function runCommand(): CommandTester
    {
        $tester = new CommandTester(new RegenSchematronVocabCommand($this->outputRoot));
        $tester->execute(['source-root' => $this->sourceRoot]);
        return $tester;
    }

    /**
     * Populate a complete, valid source tree for all three targets.
     */
    private function writeCompleteSourceTree(): void
    {
        foreach (self::TARGETS as $type => $schFile) {
            $dir = "$this->sourceRoot/schematron/$type";
            self::assertTrue(mkdir($dir, 0777, true));
            self::assertNotFalse(file_put_contents("$dir/$schFile", $this->schematronFixture()));
            self::assertNotFalse(file_put_contents("$dir/voc.xml", $this->vocabularyFixture()));
        }
    }

    /**
     * Put a known .sch + vocab.php pair in place so restore behavior is observable.
     */
    private function seedPriorPair(string $type): void
    {
        $dir = "$this->outputRoot/$type";
        self::assertTrue(mkdir($dir, 0777, true));
        self::assertNotFalse(file_put_contents("$dir/" . self::TARGETS[$type], 'PRIOR SCH'));
        self::assertNotFalse(file_put_contents("$dir/vocab.php", 'PRIOR VOCAB'));
    }

    /**
     * A non-empty directory at a file path makes both file_put_contents() and
     * rename() fail regardless of the running user's privileges.
     */
    private function makeNonEmptyDir(string $path): void
    {
        self::assertTrue(is_dir(dirname($path)) || mkdir(dirname($path), 0777, true));
        self::assertTrue(mkdir($path, 0777));
        self::assertNotFalse(file_put_contents("$path/occupied", 'x'));
    }

    /**
     * @return list<string>
     */
    private function globTemps(): array
    {
        $found = [];
        foreach (array_keys(self::TARGETS) as $type) {
            foreach (glob("$this->outputRoot/$type/*.tmp") ?: [] as $path) {
                $found[] = $path;
            }
        }
        return $found;
    }

    private function schematronFixture(): string
    {
        $oid = self::OID;
        return <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <schema xmlns="http://purl.oclc.org/dsdl/schematron">
              <pattern id="p-1">
                <rule context="//test">
                  <assert test="document('voc.xml')/voc:systems/voc:system[@valueSetOid='$oid']/voc:code">SHALL be in the value set</assert>
                </rule>
              </pattern>
            </schema>
            XML;
    }

    private function vocabularyFixture(): string
    {
        $oid = self::OID;
        return <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <systems xmlns="http://www.lantanagroup.com/voc">
              <system valueSetOid="$oid" valueSetName="Test Status">
                <code value="completed"/>
                <code value="active"/>
              </system>
            </systems>
            XML;
    }

    private function removeTree(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            /** @var \SplFileInfo $item */
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }
        @rmdir($path);
    }
}
