<?php

/**
 * Tests the orchestration of openemr:release-prep: option parsing,
 * scope dispatching, and the order in which mutators are invoked.
 * Mutators themselves are exercised by MutatorTest.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep;

use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerComposeProductionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\GlobalsIncMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\OpenApiVersionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SwaggerRegenMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\VersionPhpMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use OpenEMR\Common\Command\ReleasePrepCommand;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('isolated')]
#[Group('release-prep')]
final class ReleasePrepCommandTest extends TestCase
{
    public function testRelScopeDispatchesRelMutatorsInOrder(): void
    {
        $a = new RecordingMutator('rel-mutator-A');
        $b = new RecordingMutator('rel-mutator-B');
        $tester = $this->buildTester([$a, $b], []);
        $exit = $tester->execute([
            '--target-version' => '8.1.0',
            '--scope' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::SUCCESS, $exit, $tester->getDisplay());
        self::assertSame(1, $a->callCount);
        self::assertSame(1, $b->callCount);
        self::assertLessThan($b->lastCalledAt, $a->lastCalledAt);
    }

    public function testMasterScopeDispatchesMasterMutators(): void
    {
        $rel = new RecordingMutator('rel-mutator');
        $master = new RecordingMutator('master-mutator');
        $tester = $this->buildTester([$rel], [$master]);
        $tester->execute([
            '--target-version' => '8.1.1',
            '--scope' => 'master',
            '--rel-branch' => 'rel-810',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(0, $rel->callCount);
        self::assertSame(1, $master->callCount);
    }

    public function testMasterScopeRequiresRelBranch(): void
    {
        $master = new RecordingMutator('master-mutator');
        $tester = $this->buildTester([], [$master]);
        $exit = $tester->execute([
            '--target-version' => '8.1.1',
            '--scope' => 'master',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
        self::assertSame(0, $master->callCount);
        self::assertStringContainsString('--rel-branch', $tester->getDisplay());
    }

    public function testRelBranchIsPlumbedToMutatorContext(): void
    {
        $contextSpy = new ContextRecordingMutator();
        $tester = $this->buildTester([], [$contextSpy]);
        $tester->execute([
            '--target-version' => '8.1.1',
            '--scope' => 'master',
            '--rel-branch' => 'rel-810',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertNotNull($contextSpy->lastContext);
        self::assertSame('rel-810', $contextSpy->lastContext->relBranch);
        self::assertSame('v8_1_1', $contextSpy->lastContext->tagName());
    }

    public function testInvalidScopeReturnsInvalidExitCode(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.1.0',
            '--scope' => 'bogus',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testMalformedVersionReturnsInvalid(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.1',
            '--scope' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(Command::INVALID, $exit);
    }

    public function testDefaultRelMutatorListExcludesGlobalsIncMutator(): void
    {
        // The `allow_debug_language` flip is owned by branch-cut's rel-side
        // mutators, not release-prep. Include it here and release-prep
        // hides the "did branch-cut merge?" question behind idempotency.
        // See PR #12725 for the rel-820 exercise that surfaced this.
        $default = $this->buildDefaultRelMutators();
        $classes = array_map(static fn (MutatorInterface $m): string => $m::class, $default);
        self::assertNotContains(
            GlobalsIncMutator::class,
            $classes,
            'GlobalsIncMutator must not appear in the release-prep rel-side default list',
        );
        // Positive assertions: the mutators that ARE the rel-side list.
        self::assertContains(VersionPhpMutator::class, $classes);
        self::assertContains(DockerComposeProductionMutator::class, $classes);
        self::assertContains(OpenApiVersionMutator::class, $classes);
        self::assertContains(SwaggerRegenMutator::class, $classes);
    }

    /**
     * @param list<MutatorInterface> $relMutators
     * @param list<MutatorInterface> $masterMutators
     */
    private function buildTester(array $relMutators, array $masterMutators): CommandTester
    {
        $command = new ReleasePrepCommand($relMutators, $masterMutators);
        $app = new Application();
        $app->addCommand($command);
        return new CommandTester($app->find('openemr:release-prep'));
    }

    /**
     * Introspect ReleasePrepCommand's private buildDefaultRelMutators()
     * via reflection so this test guards the actual production list
     * regardless of internal encapsulation choices.
     *
     * @return list<MutatorInterface>
     */
    private function buildDefaultRelMutators(): array
    {
        $command = new ReleasePrepCommand();
        $reflect = new ReflectionClass(ReleasePrepCommand::class);
        $method = $reflect->getMethod('buildDefaultRelMutators');
        $result = $method->invoke($command);
        self::assertIsArray($result);
        /** @var list<MutatorInterface> $result */
        return $result;
    }
}

final class RecordingMutator implements MutatorInterface
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

final class ContextRecordingMutator implements MutatorInterface
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
