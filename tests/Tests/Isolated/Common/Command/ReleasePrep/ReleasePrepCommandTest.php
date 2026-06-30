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

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use OpenEMR\Common\Command\ReleasePrepCommand;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
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
            '--project-dir' => sys_get_temp_dir(),
        ]);
        self::assertSame(0, $rel->callCount);
        self::assertSame(1, $master->callCount);
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

    public function testInvalidImageDigestReturnsInvalid(): void
    {
        $tester = $this->buildTester([], []);
        $exit = $tester->execute([
            '--target-version' => '8.1.0',
            '--scope' => 'rel',
            '--project-dir' => sys_get_temp_dir(),
            '--image-digest' => 'not-a-digest',
        ]);
        self::assertSame(Command::INVALID, $exit);
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
