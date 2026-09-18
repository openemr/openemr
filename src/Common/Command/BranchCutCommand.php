<?php

/**
 * Branch-cut conductor command: applies the mechanical edits that a new
 * rel-NNN0 cut requires, on either the rel-side or the master-side of
 * the cut.
 *
 * This is a SIBLING command to `openemr:release-prep`, not an extension
 * of it. Phase A of workstream 3 (PR #12662) established
 * `--scope=master` on release-prep as RELEASE-TIME only (master-side
 * release-finalize PR for post-release release-targets.yml updates).
 * Branch-cut is a distinct lifecycle event that runs its own mutator
 * lists against both sides of the cut from a separate workflow.
 *
 * Mutators are idempotent — running the command twice on the same input
 * produces no diff. The branch-cut workflow may re-run after PR review
 * fixes; non-idempotent mutators would generate churn.
 *
 * Side selection:
 *   --side=rel    Applied to a fresh checkout of the newly-cut rel-NNN0
 *                 branch. Pre-publishing changes (docker upgrade scaffold,
 *                 Dockerfile ARG, translation file copy, globals).
 *   --side=master Applied to a fresh checkout of master. Post-cut "next
 *                 dev" advance (version.php bump, OpenAPI bump, SQL
 *                 skeleton, release-targets.yml row insert + slot
 *                 shuffle, plus the docker upgrade scaffold which must
 *                 stay in sync across branches).
 *
 * See docs/release-mechanism-gaps.md (G4 + G5) and
 * docs/release-mechanism-migration-from-devops.md (workstream 2 detail)
 * for the full design.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Command\ReleasePrep\Mutator\BranchCutReleaseTargetsMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerfileOpenemrVersionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerUpgradeScaffoldMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\GlobalsIncMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\OpenApiVersionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SqlUpgradeSkeletonMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SwaggerRegenMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\TranslationFileCopyFromPriorRelMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\VersionPhpMasterMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class BranchCutCommand extends Command
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
            ->setName('openemr:branch-cut')
            ->setDescription('Apply mechanical branch-cut mutations for a new rel-NNN0 cut (sibling to openemr:release-prep)')
            ->addOption(
                'target-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Target release version for the new rel branch (MAJOR.MINOR.PATCH, e.g. 8.2.0)',
            )
            ->addOption(
                'rel-branch',
                null,
                InputOption::VALUE_REQUIRED,
                'Freshly-cut rel branch identifier (e.g. rel-820)',
            )
            ->addOption(
                'prev-rel-branch',
                null,
                InputOption::VALUE_REQUIRED,
                'Previous rel branch identifier (e.g. rel-810); used by rel-side to copy translation blob',
            )
            ->addOption(
                'side',
                null,
                InputOption::VALUE_REQUIRED,
                'Mutation side: rel (newly-cut rel branch) or master (post-cut master)',
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
            $prevRelBranch = $this->requiredString($input, 'prev-rel-branch');
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
            $targetContext = MutatorContext::fromVersionString(
                $projectDir,
                $target,
                $relBranch,
                $prevRelBranch,
            );
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::INVALID;
        }

        // Master-side mutators that advance to "next dev" need the
        // (major, minor+1, 0) context (e.g., at rel-820 cut with
        // target-version=8.2.0, master version.php bumps to 8.3.0-dev,
        // OpenAPI to 8.3.0, SQL skeleton from 8.2.0 to 8.3.0). The
        // exception is BranchCutReleaseTargetsMutator, which needs the
        // ORIGINAL target version because the new rel row's docker_tags
        // value is "<target>,next" — the master row bump to (minor+1) is
        // computed internally by the mutator from the target context.
        $nextDevContext = new MutatorContext(
            $targetContext->projectDir,
            $targetContext->major,
            $targetContext->minor + 1,
            0,
            $targetContext->relBranch,
            $targetContext->prevRelBranch,
        );

        $mutators = $side === self::SIDE_REL
            ? ($this->relSideMutators ?? $this->buildDefaultRelSideMutators())
            : ($this->masterSideMutators ?? $this->buildDefaultMasterSideMutators());

        $allChanged = [];
        foreach ($mutators as $mutator) {
            $context = $side === self::SIDE_MASTER && $this->mutatorWantsNextDevContext($mutator)
                ? $nextDevContext
                : $targetContext;
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
            '<info>%d file(s) changed across %d mutator(s) for branch-cut %s side=%s</info>',
            count($allChanged),
            count($mutators),
            $targetContext->versionString(),
            $side,
        ));
        return Command::SUCCESS;
    }

    /**
     * Master-side version-advance mutators need the next-dev context
     * (target minor + 1). BranchCutReleaseTargetsMutator and the docker
     * upgrade scaffold use the original target context.
     */
    private function mutatorWantsNextDevContext(MutatorInterface $mutator): bool
    {
        return $mutator instanceof VersionPhpMasterMutator
            || $mutator instanceof OpenApiVersionMutator
            || $mutator instanceof SwaggerRegenMutator
            || $mutator instanceof SqlUpgradeSkeletonMutator;
    }

    /**
     * Production rel-side mutator list. All mutations target the
     * freshly-cut rel branch checkout (e.g., rel-820).
     *
     * @return list<MutatorInterface>
     */
    private function buildDefaultRelSideMutators(): array
    {
        return [
            new DockerUpgradeScaffoldMutator(),
            new DockerfileOpenemrVersionMutator(),
            new TranslationFileCopyFromPriorRelMutator(),
            new GlobalsIncMutator(),
        ];
    }

    /**
     * Production master-side mutator list. All mutations target a fresh
     * master checkout for the "next-dev advance" post-cut work.
     *
     * Ordering note: SqlUpgradeSkeletonMutator MUST run before
     * VersionPhpMasterMutator. It reads the current version.php to derive
     * the "from" version of the upgrade-skeleton filename
     * (e.g., `8_2_0-to-8_3_0_upgrade.sql` at rel-820 cut). If
     * VersionPhpMasterMutator runs first, version.php already reads as
     * the post-bump (next-dev) version and the skeleton no-ops.
     *
     * @return list<MutatorInterface>
     */
    private function buildDefaultMasterSideMutators(): array
    {
        return [
            new DockerUpgradeScaffoldMutator(),
            new SqlUpgradeSkeletonMutator(),
            new VersionPhpMasterMutator(),
            new OpenApiVersionMutator(),
            new SwaggerRegenMutator(),
            new BranchCutReleaseTargetsMutator(),
        ];
    }

    private function defaultProjectDir(): string
    {
        // src/Common/Command/BranchCutCommand.php → repo root is 3 levels up.
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
