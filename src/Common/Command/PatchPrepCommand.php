<?php

/**
 * Patch-prep conductor command: applies the mechanical edits triggered
 * when a maintainer pushes a $v_patch increment to version.php on a
 * rel-* branch (e.g., rel-810 going 8.1.0 → 8.1.1-dev).
 *
 * SIBLING to `openemr:branch-cut` and `openemr:release-prep`. Workstream
 * 3 Phase B in the release-mechanism migration.
 *
 * Side selection:
 *   --side=rel    Applied to the rel branch itself, post-bump (version.php
 *                 has already moved to <new $v_patch>-dev). Scaffolds the
 *                 docker upgrade machinery + a new SQL patch upgrade
 *                 skeleton (X_Y_(P-1)-to-X_Y_P_upgrade.sql).
 *   --side=master Applied to a fresh checkout of master. Same docker
 *                 upgrade scaffold (cross-branch sync requirement) +
 *                 same new SQL patch upgrade skeleton + master-side
 *                 bridge file rename (X_Y_(P-1)-to-X_(Y+1)_0 →
 *                 X_Y_P-to-X_(Y+1)_0) + release-targets.yml edits.
 *
 * Mutators are idempotent — running the command twice on the same input
 * produces no diff. The patch-prep workflow may re-run after PR review
 * fixes; non-idempotent mutators would generate churn.
 *
 * From-version override: both sides receive an explicit `fromVersion`
 * via MutatorContext. On rel, version.php has already been bumped past
 * the value SqlUpgradeSkeletonMutator needs to anchor at; on master,
 * version.php is for the next-minor line entirely and bears no
 * relationship to the rel-branch patch. The conductor supplies the
 * correct prior patch via --prev-version on both sides for uniformity.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerUpgradeScaffoldMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\MasterSqlPatchBridgeMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\PatchPrepReleaseTargetsMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SqlUpgradeSkeletonMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class PatchPrepCommand extends Command
{
    private const SIDE_REL = 'rel';
    private const SIDE_MASTER = 'master';

    /**
     * Constructor takes optional mutator lists for testability. The
     * SymfonyCommandRunner instantiates with no arguments and the
     * command builds the production lists internally on demand.
     *
     * @param list<MutatorInterface>|null $relSideMutators
     * @param list<MutatorInterface>|null $masterSideMutators
     */
    public function __construct(
        private readonly ?array $relSideMutators = null,
        private readonly ?array $masterSideMutators = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('openemr:patch-prep')
            ->setDescription('Apply mechanical patch-prep mutations when a rel branch enters dev for a new patch (sibling to openemr:branch-cut)')
            ->addOption(
                'target-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Target patch version for the new dev cycle (MAJOR.MINOR.PATCH, e.g. 8.1.1)',
            )
            ->addOption(
                'rel-branch',
                null,
                InputOption::VALUE_REQUIRED,
                'Rel branch identifier the patch is happening on (e.g. rel-810)',
            )
            ->addOption(
                'prev-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Previous patch version (MAJOR.MINOR.PATCH, e.g. 8.1.0); used as the from-version for the SQL skeleton + master-side bridge rename',
            )
            ->addOption(
                'side',
                null,
                InputOption::VALUE_REQUIRED,
                'Mutation side: rel (the rel branch itself) or master (the master checkout)',
            )
            ->addOption(
                'project-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Project root (defaults to the repo containing this command file)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $target = $this->requiredString($input, 'target-version');
            $side = $this->requiredString($input, 'side');
            $relBranch = $this->requiredString($input, 'rel-branch');
            $prevVersion = $this->requiredString($input, 'prev-version');
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::INVALID;
        }
        if ($side !== self::SIDE_REL && $side !== self::SIDE_MASTER) {
            $output->writeln('<error>--side must be "rel" or "master"; got: ' . $side . '</error>');
            return Command::INVALID;
        }

        $rawProjectDir = $input->getOption('project-dir');
        $projectDir = is_string($rawProjectDir) && $rawProjectDir !== ''
            ? $rawProjectDir
            : $this->defaultProjectDir();

        try {
            $context = MutatorContext::fromVersionString(
                $projectDir,
                $target,
                $relBranch,
                null,
                $prevVersion,
            );
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::INVALID;
        }

        $mutators = $side === self::SIDE_REL
            ? ($this->relSideMutators ?? $this->buildDefaultRelSideMutators())
            : ($this->masterSideMutators ?? $this->buildDefaultMasterSideMutators());

        $allChanged = [];
        foreach ($mutators as $mutator) {
            $output->writeln(sprintf('<info>→</info> %s', $mutator->name()));
            $result = $mutator->apply($context);
            foreach ($result->messages as $message) {
                $output->writeln('  ' . $message);
            }
            if (!$result->changed()) {
                $output->writeln('  no diff (already at target)');
                continue;
            }
            foreach ($result->changedFiles as $changed) {
                $output->writeln('  changed: ' . $changed);
                $allChanged[] = $changed;
            }
        }
        $output->writeln(sprintf(
            '<info>%d file(s) changed across %d mutator(s) for patch-prep %s side=%s</info>',
            count($allChanged),
            count($mutators),
            $context->versionString(),
            $side,
        ));
        return Command::SUCCESS;
    }

    /**
     * Production rel-side mutator list. Runs against the rel branch
     * checkout itself, post-$v_patch-bump.
     *
     * @return list<MutatorInterface>
     */
    private function buildDefaultRelSideMutators(): array
    {
        return [
            new DockerUpgradeScaffoldMutator(),
            new SqlUpgradeSkeletonMutator(),
        ];
    }

    /**
     * Production master-side mutator list. Runs against a fresh master
     * checkout.
     *
     * Ordering note: SqlUpgradeSkeletonMutator runs BEFORE
     * MasterSqlPatchBridgeMutator. The skeleton mutator creates the new
     * patch's SQL file (a write); the bridge mutator renames an
     * unrelated file (a move). They touch disjoint paths, but keeping
     * the skeleton first matches the rel-side mutator order and means
     * a partial run produces the new skeleton file before disturbing
     * the long-lived bridge file.
     *
     * @return list<MutatorInterface>
     */
    private function buildDefaultMasterSideMutators(): array
    {
        return [
            new DockerUpgradeScaffoldMutator(),
            new SqlUpgradeSkeletonMutator(),
            new MasterSqlPatchBridgeMutator(),
            new PatchPrepReleaseTargetsMutator(),
        ];
    }

    private function defaultProjectDir(): string
    {
        // src/Common/Command/PatchPrepCommand.php → repo root is 3 levels up.
        return dirname(__DIR__, 3);
    }

    private function requiredString(InputInterface $input, string $name): string
    {
        $raw = $input->getOption($name);
        if (!is_string($raw) || $raw === '') {
            throw new \InvalidArgumentException('--' . $name . ' is required');
        }
        return $raw;
    }
}
