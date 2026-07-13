<?php

/**
 * Conductor command for release-automation: applies the mechanical edits
 * a release branch (or master, post-cut) needs so the conductor's
 * long-lived release-prep PR can be auto-updated on every push.
 *
 * Mutators are idempotent — running the command twice on the same input
 * produces no diff. The conductor workflow re-runs this command on
 * every push to a rel-* branch, so any non-idempotent mutator would
 * generate churn PRs.
 *
 * Scope:
 *   --scope=rel    pre-tag mutations on the release branch.
 *   --scope=master release-time only: post-tag mutations on master,
 *                  paired with the rel-branch's release-prep PR. The
 *                  conductor workflow opens both PRs together; this
 *                  scope drives the master-side companion that pins
 *                  release-targets.yml entries to the new tag, shuffles
 *                  Docker Hub tag slots, and drops the unreleased
 *                  placeholder row. Requires --rel-branch.
 *
 *                  Branch-cut master mutations (e.g. bumping version.php
 *                  to next-dev via VersionPhpMasterMutator) are a
 *                  workstream 2 concern (G4) and are NOT invoked from
 *                  this command. They are wired by the sibling
 *                  `openemr:branch-cut` command (see `BranchCutCommand`),
 *                  driven by the branch-cut-automation workflow.
 *
 * See docs/release-automation-plan.md and openemr/openemr-devops#664
 * for the full design.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerComposeProductionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\OpenApiVersionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\PostReleaseTargetsMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SwaggerRegenMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\VersionPhpMutator;
use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ReleasePrepCommand extends Command
{
    private const SCOPE_REL = 'rel';
    private const SCOPE_MASTER = 'master';

    /**
     * Constructor takes optional mutator lists for testability. The
     * SymfonyCommandRunner instantiates with no arguments and the
     * command builds the production lists internally on demand.
     *
     * @param list<MutatorInterface>|null $relMutators
     * @param list<MutatorInterface>|null $masterMutators
     */
    public function __construct(private readonly ?array $relMutators = null, private readonly ?array $masterMutators = null)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('openemr:release-prep')
            ->setDescription('Apply mechanical release-prep mutations for the conductor workflow')
            ->addOption(
                'target-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Target release version (MAJOR.MINOR.PATCH)',
            )
            ->addOption(
                'scope',
                null,
                InputOption::VALUE_REQUIRED,
                'Mutation scope: rel (pre-tag) or master (post-cut)',
            )
            ->addOption(
                'project-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Project root (defaults to the repo containing this command file)',
            )
            ->addOption(
                'rel-branch',
                null,
                InputOption::VALUE_REQUIRED,
                'Rel branch identifier (e.g. rel-810). Required for --scope=master.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->requiredString($input, 'target-version');
        $scope = $this->requiredString($input, 'scope');
        if ($scope !== self::SCOPE_REL && $scope !== self::SCOPE_MASTER) {
            $output->writeln('<error>--scope must be "rel" or "master"; got: ' . $scope . '</error>');
            return Command::INVALID;
        }

        $rawProjectDir = $input->getOption('project-dir');
        $projectDir = is_string($rawProjectDir) && $rawProjectDir !== ''
            ? $rawProjectDir
            : $this->defaultProjectDir();

        $rawRelBranch = $input->getOption('rel-branch');
        $relBranch = is_string($rawRelBranch) && $rawRelBranch !== '' ? $rawRelBranch : null;
        if ($scope === self::SCOPE_MASTER && $relBranch === null) {
            $output->writeln('<error>--rel-branch is required for --scope=master</error>');
            return Command::INVALID;
        }

        try {
            $context = MutatorContext::fromVersionString($projectDir, $target, $relBranch);
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::INVALID;
        }

        $mutators = $scope === self::SCOPE_REL
            ? ($this->relMutators ?? $this->buildDefaultRelMutators())
            : ($this->masterMutators ?? $this->buildDefaultMasterMutators());

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
            '<info>%d file(s) changed across %d mutator(s) for %s scope=%s</info>',
            count($allChanged),
            count($mutators),
            $context->versionString(),
            $scope,
        ));
        return Command::SUCCESS;
    }

    /**
     * @return list<MutatorInterface>
     */
    private function buildDefaultRelMutators(): array
    {
        // GlobalsIncMutator intentionally NOT wired here: the
        // `allow_debug_language` flip is owned by branch-cut
        // (BranchCutCommand's rel-side list). Running it again at
        // release-prep time is defensive but redundant, and it hides
        // the "did branch-cut merge?" question behind mutator idempotency
        // — surface as a real diff if branch-cut hasn't landed yet.
        $mutators = [
            new VersionPhpMutator(),
            new DockerComposeProductionMutator(),
            new OpenApiVersionMutator(),
            new SwaggerRegenMutator(),
        ];
        $this->appendOptionalReleaseMutators($mutators);
        return $mutators;
    }

    /**
     * Master-scope mutators are release-time only: they run after the
     * rel-branch's tag is created (or alongside its release-prep PR) to
     * update master's release-targets.yml.
     *
     * Branch-cut master mutators (SqlUpgradeSkeletonMutator,
     * VersionPhpMasterMutator, etc.) are intentionally NOT wired here —
     * they belong to the workstream 2 (G4) branch-cut lifecycle event
     * and are wired by `openemr:branch-cut` (see `BranchCutCommand`)
     * driven by the branch-cut-automation workflow.
     *
     * @return list<MutatorInterface>
     */
    private function buildDefaultMasterMutators(): array
    {
        $mutators = [
            new PostReleaseTargetsMutator(),
        ];
        $this->appendOptionalReleaseMutators($mutators);
        return $mutators;
    }

    /**
     * Late-bound wiring for mutators that live in `tools/release/src/`
     * (autoload-dev), keeping conductor-only classes out of the
     * production autoload map per the original release-mechanism
     * boundary (see composer.json's split between `OpenEMR\` production
     * autoload and `OpenEMR\Release\` autoload-dev). Present in every
     * environment that installs dev dependencies (CI, release workflows);
     * silently absent from a production `composer install --no-dev`, which
     * never invokes `openemr:release-prep` anyway.
     *
     * Class names are referenced as strings rather than `use`d so
     * composer-require-checker does not flag the cross-boundary
     * dependency.
     *
     * @param list<MutatorInterface> $mutators
     */
    private function appendOptionalReleaseMutators(array &$mutators): void
    {
        // String literal (not ::class) so composer-require-checker sees
        // no compile-time reference to a class that lives in autoload-dev.
        // rector's StringClassNameToClassConstantRector would otherwise
        // rewrite this to ::class, breaking the boundary; it is skipped
        // for this file in rector.php's withSkip block. Under a production
        // `composer install --no-dev` the class is not autoloadable,
        // class_exists() returns false, and the mutator is silently
        // skipped.
        $changelogMutator = 'OpenEMR\\Release\\Mutator\\ChangelogMutator';
        if (!class_exists($changelogMutator)) {
            return;
        }
        $mutators[] = new $changelogMutator();
    }

    private function defaultProjectDir(): string
    {
        // src/Common/Command/ReleasePrepCommand.php → repo root is 4 levels up.
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
