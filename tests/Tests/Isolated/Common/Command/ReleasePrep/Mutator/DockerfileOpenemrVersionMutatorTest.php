<?php

/**
 * Tests for DockerfileOpenemrVersionMutator: the rel-side flip of
 * `ARG OPENEMR_VERSION=master` to the freshly-cut rel branch name.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerfileOpenemrVersionMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('release-prep')]
final class DockerfileOpenemrVersionMutatorTest extends TestCase
{
    private const RELATIVE_PATH = 'docker/release/Dockerfile';

    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-dov-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir . '/docker/release', 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir');
        }
    }

    protected function tearDown(): void
    {
        $this->removeRecursive($this->tmpDir);
    }

    public function testFlipsMasterToRelBranch(): void
    {
        $this->write("# header line\nARG OPENEMR_VERSION=master\nFROM alpine AS base\n");
        $result = (new DockerfileOpenemrVersionMutator())->apply($this->context('rel-820'));
        self::assertTrue($result->changed());
        self::assertStringContainsString('ARG OPENEMR_VERSION=rel-820', $this->read());
        self::assertStringNotContainsString('ARG OPENEMR_VERSION=master', $this->read());
    }

    public function testIdempotentWhenAlreadyAtTarget(): void
    {
        $this->write("ARG OPENEMR_VERSION=rel-820\nFROM alpine AS base\n");
        $result = (new DockerfileOpenemrVersionMutator())->apply($this->context('rel-820'));
        self::assertFalse($result->changed());
    }

    public function testThrowsWhenSetToUnexpectedValue(): void
    {
        $this->write("ARG OPENEMR_VERSION=rel-810\nFROM alpine AS base\n");
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/expected "master".*"rel-820"/');
        (new DockerfileOpenemrVersionMutator())->apply($this->context('rel-820'));
    }

    public function testThrowsWhenNoArgLineAtAll(): void
    {
        $this->write("FROM alpine AS base\n");
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/no ARG OPENEMR_VERSION line/');
        (new DockerfileOpenemrVersionMutator())->apply($this->context('rel-820'));
    }

    public function testThrowsWhenRelBranchMissingFromContext(): void
    {
        $this->write("ARG OPENEMR_VERSION=master\n");
        $context = MutatorContext::fromVersionString($this->tmpDir, '8.2.0');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/--rel-branch/');
        (new DockerfileOpenemrVersionMutator())->apply($context);
    }

    public function testDoesNotTouchOtherArgLines(): void
    {
        $this->write(<<<DF
        ARG ALPINE_VERSION=3.23
        ARG OPENEMR_VERSION=master
        ARG PHP_VERSION=8.5
        DF);
        (new DockerfileOpenemrVersionMutator())->apply($this->context('rel-820'));
        $out = $this->read();
        self::assertStringContainsString('ARG ALPINE_VERSION=3.23', $out);
        self::assertStringContainsString('ARG PHP_VERSION=8.5', $out);
        self::assertStringContainsString('ARG OPENEMR_VERSION=rel-820', $out);
    }

    private function context(string $relBranch): MutatorContext
    {
        return MutatorContext::fromVersionString($this->tmpDir, '8.2.0', null, $relBranch, 'rel-810');
    }

    private function write(string $contents): void
    {
        $path = $this->tmpDir . '/' . self::RELATIVE_PATH;
        if (file_put_contents($path, $contents) === false) {
            throw new \RuntimeException('Cannot write Dockerfile');
        }
    }

    private function read(): string
    {
        $path = $this->tmpDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read Dockerfile');
        }
        return $contents;
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
