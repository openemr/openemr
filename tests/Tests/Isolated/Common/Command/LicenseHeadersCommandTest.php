<?php

/**
 * Tests openemr:license-headers end to end against a throwaway project
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Command\LicenseHeadersCommand;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;

#[Group('isolated')]
final class LicenseHeadersCommandTest extends TestCase
{
    private const POLICY = <<<'YAML'
        standard: GPL
        exempt:
        - reason: Vendored
          paths: [vendored/*]
        YAML;

    private Filesystem $filesystem;
    private string $projectDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->projectDir = Path::join(sys_get_temp_dir(), uniqid('license-headers-', true));
        $this->write('.license-headers.yaml', self::POLICY);
        $this->write('good.php', "<?php\n/** @license GPL */\n");
        $this->write('bad.js', "/** @author A */\n");
        $this->write('vendored/lib.php', "<?php\n");
        $this->write('README.md', "# Not scanned\n");
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->projectDir);
    }

    public function testFailsOnFindingsInTheGivenFiles(): void
    {
        $tester = $this->tester();
        $exit = $tester->execute(['paths' => ['good.php', './bad.js', 'vendored/lib.php', 'README.md', 'gone.php'], '--project-dir' => $this->projectDir]);
        self::assertSame(Command::FAILURE, $exit);
        $display = $tester->getDisplay();
        self::assertStringContainsString('bad.js:1:1: error - The file header has no @license tag. [missing-license]', $display);
        self::assertStringNotContainsString('good.php:', $display);
        self::assertStringContainsString('3 files scanned: 1 with findings, 1 exempt.', $display);
    }

    public function testPassesWhenEveryFileComplies(): void
    {
        $tester = $this->tester();
        $exit = $tester->execute(['paths' => [Path::join($this->projectDir, 'good.php')], '--project-dir' => $this->projectDir]);
        self::assertSame(Command::SUCCESS, $exit, $tester->getDisplay());
        self::assertStringContainsString('1 files scanned: 0 with findings, 0 exempt.', $tester->getDisplay());
    }

    public function testReportOnlyWritesReportsAndSucceeds(): void
    {
        $sarif = Path::join($this->projectDir, 'out/scan.sarif');
        $markdown = Path::join($this->projectDir, 'out/summary.md');
        $tester = $this->tester();
        $exit = $tester->execute([
            'paths' => ['good.php', 'bad.js'],
            '--project-dir' => $this->projectDir,
            '--report-only' => true,
            '--sarif' => $sarif,
            '--markdown' => $markdown,
        ]);
        self::assertSame(Command::SUCCESS, $exit);
        self::assertStringContainsString('"ruleId": "missing-license"', $this->filesystem->readFile($sarif));
        self::assertStringContainsString('| Compliant | 1 (50.0%) |', $this->filesystem->readFile($markdown));
    }

    public function testAllAndChangedSinceReadFromGit(): void
    {
        $this->git('init', '--quiet');
        $this->git('add', 'good.php', '.license-headers.yaml');
        $this->git('commit', '--quiet', '-m', 'base');
        $this->git('branch', 'base');
        $this->git('add', 'bad.js', 'vendored/lib.php');
        $this->git('commit', '--quiet', '-m', 'more');

        $tester = $this->tester();
        $tester->execute(['--all' => true, '--project-dir' => $this->projectDir, '--report-only' => true]);
        self::assertStringContainsString('3 files scanned: 1 with findings, 1 exempt.', $tester->getDisplay());

        $tester->execute(['--changed-since' => 'base', '--project-dir' => $this->projectDir]);
        self::assertStringContainsString('2 files scanned: 1 with findings, 1 exempt.', $tester->getDisplay());
        self::assertSame(Command::FAILURE, $tester->getStatusCode());
    }

    public function testRequiresExactlyOneWayToChooseFiles(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Give exactly one of');
        $this->tester()->execute(['paths' => ['good.php'], '--all' => true, '--project-dir' => $this->projectDir]);
    }

    public function testRequiresSomeWayToChooseFiles(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->tester()->execute(['--project-dir' => $this->projectDir]);
    }

    public function testRejectsEmptyOptionValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('--config needs a value.');
        $this->tester()->execute(['paths' => ['good.php'], '--project-dir' => $this->projectDir, '--config' => '']);
    }

    private function tester(): CommandTester
    {
        return new CommandTester(new LicenseHeadersCommand(new FrozenClock(new \DateTimeImmutable('2026-06-01'))));
    }

    private function write(string $path, string $contents): void
    {
        $this->filesystem->dumpFile(Path::join($this->projectDir, $path), $contents);
    }

    private function git(string ...$arguments): void
    {
        $command = ['git', '-c', 'user.name=Test', '-c', 'user.email=test@example.com', '-c', 'commit.gpgsign=false', ...$arguments];
        (new Process($command, $this->projectDir))->mustRun();
    }
}
