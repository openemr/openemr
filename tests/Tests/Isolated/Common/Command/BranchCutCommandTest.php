<?php

/**
 * Tests the orchestration of openemr:branch-cut: option parsing,
 * side dispatching, and the order in which mutators are invoked.
 * Mutators themselves are exercised by their per-class tests.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use OpenEMR\Common\Command\BranchCutCommand;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('isolated')]
#[Group('release-prep')]
final class BranchCutCommandTest extends TestCase
{
    public function testRelSideDispatchesRelMutatorsInOrder(): void
    {
        $a = new BranchCutRecordingMutator('rel-a');
        $b = new BranchCutRecordingMutator('rel-b');
        $tester = $this->buildTester([$a, $b], []);
        $exit = $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::SUCCESS, $exit, $tester->getDisplay());
        self::assertSame(1, $a->callCount);
        self::assertSame(1, $b->callCount);
        self::assertLessThan($b->lastCalledAt, $a->lastCalledAt);
    }

    public function testMasterSideDispatchesMasterMutators(): void
    {
        $rel = new BranchCutRecordingMutator('rel');
        $master = new BranchCutRecordingMutator('master');
        $tester = $this->buildTester([$rel], [$master]);
        $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'master',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(0, $rel->callCount);
        self::assertSame(1, $master->callCount);
    }

    public function testRequiresTargetVersion(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        // requiredString throws InvalidArgumentException → propagates as
        // non-success exit; assert the command did not succeed.
        self::assertSame(Command::INVALID, $exit);
    }

    public function testRequiresRelBranch(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.2.0',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testRequiresPrevRelBranch(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'rel-820',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testRequiresSide(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'rel-810',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testInvalidSideReturnsInvalid(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'bogus',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testMalformedTargetVersionReturnsInvalid(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.2',
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testMalformedRelBranchReturnsInvalid(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'not-a-rel-branch',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testMalformedPrevRelBranchReturnsInvalid(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'master',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testTargetContextPassedToRelSideMutators(): void
    {
        $spy = new BranchCutContextRecordingMutator();
        $tester = $this->buildTester([$spy], []);
        $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertNotNull($spy->lastContext);
        self::assertSame(8, $spy->lastContext->major);
        self::assertSame(2, $spy->lastContext->minor);
        self::assertSame(0, $spy->lastContext->patch);
        self::assertSame('rel-820', $spy->lastContext->relBranch);
        self::assertSame('rel-810', $spy->lastContext->prevRelBranch);
    }

    public function testFilesChangedSummaryLineRendered(): void
    {
        $touched = new class implements MutatorInterface {
            public function name(): string
            {
                return 'touched-mutator';
            }
            public function apply(MutatorContext $context): MutatorResult
            {
                return new MutatorResult(['foo/bar.txt']);
            }
        };
        $tester = $this->buildTester([$touched], []);
        $tester->execute([
            '--target-version' => '8.2.0',
            '--rel-branch' => 'rel-820',
            '--prev-rel-branch' => 'rel-810',
            '--side' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        $display = $tester->getDisplay();
        self::assertStringContainsString('1 file(s) changed', $display);
        self::assertStringContainsString('side=rel', $display);
        self::assertStringContainsString('foo/bar.txt', $display);
    }

    /**
     * @param list<MutatorInterface> $relMutators
     * @param list<MutatorInterface> $masterMutators
     */
    private function buildTester(array $relMutators, array $masterMutators): CommandTester
    {
        $command = new BranchCutCommand($relMutators, $masterMutators);
        $app = new Application();
        $app->add($command);
        return new CommandTester($app->find('openemr:branch-cut'));
    }
}

final class BranchCutRecordingMutator implements MutatorInterface
{
    private static int $sequence = 0;

    public int $callCount = 0;
    public int $lastCalledAt = -1;

    public function __construct(private readonly string $label)
    {
    }

    public function name(): string
    {
        return $this->label;
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $this->callCount++;
        $this->lastCalledAt = self::$sequence++;
        return MutatorResult::noop();
    }
}

final class BranchCutContextRecordingMutator implements MutatorInterface
{
    public ?MutatorContext $lastContext = null;

    public function name(): string
    {
        return 'context-recording-mutator';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $this->lastContext = $context;
        return MutatorResult::noop();
    }
}
