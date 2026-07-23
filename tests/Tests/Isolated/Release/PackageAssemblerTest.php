<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\PackageAssembler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

final class PackageAssemblerTest extends TestCase
{
    private string $tmpDir = '';

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/openemr-package-assembler-' . bin2hex(random_bytes(8));
        if (!mkdir($this->tmpDir, 0700, true)) {
            throw new \RuntimeException('Failed to create tmp dir: ' . $this->tmpDir);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            (new Process(['rm', '-rf', $this->tmpDir]))->run();
        }
    }

    public function testMissingOpenemrDirReturnsError(): void
    {
        $output = new BufferedOutput();
        $assembler = new PackageAssembler('8.1.0', $this->tmpDir . '/does-not-exist', $this->tmpDir, $output);

        self::assertSame(1, $assembler->assemble());
        self::assertStringContainsString('OpenEMR directory not found', $output->fetch());
    }

    public function testMissingBuildXmlReturnsError(): void
    {
        $output = new BufferedOutput();
        $assembler = new PackageAssembler('8.1.0', $this->tmpDir, $this->tmpDir, $output);

        self::assertSame(1, $assembler->assemble());
        self::assertStringContainsString('build.xml not found', $output->fetch());
    }

    public function testDirtyCheckoutReturnsError(): void
    {
        $repo = $this->tmpDir . '/openemr';
        mkdir($repo, 0700, true);
        $this->git($repo, ['init', '-q']);
        $this->git($repo, ['config', 'user.email', 'test@example.com']);
        $this->git($repo, ['config', 'user.name', 'Test']);
        file_put_contents("{$repo}/build.xml", "<project/>\n");
        $this->git($repo, ['add', 'build.xml']);
        $this->git($repo, ['commit', '-qm', 'init']);
        // Leave an uncommitted change, mimicking an uncommitted version bump.
        file_put_contents("{$repo}/build.xml", "<project name=\"dirty\"/>\n");

        $output = new BufferedOutput();
        $assembler = new PackageAssembler('8.1.0', $repo, $this->tmpDir . '/out', $output);

        self::assertSame(1, $assembler->assemble());
        self::assertStringContainsString('dirty checkout', $output->fetch());
    }

    /**
     * @param list<string> $args
     */
    private function git(string $cwd, array $args): void
    {
        (new Process(['git', ...$args], $cwd))->mustRun();
    }
}
