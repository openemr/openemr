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
 *   --scope=rel    pre-tag mutations on the release branch
 *   --scope=master post-cut version bump on master
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
use OpenEMR\Common\Command\ReleasePrep\Mutator\DockerVersionFileMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\GlobalsIncMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\OpenApiVersionMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SqlUpgradeSkeletonMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\SwaggerRegenMutator;
use OpenEMR\Common\Command\ReleasePrep\Mutator\VersionPhpMasterMutator;
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
                'image-digest',
                null,
                InputOption::VALUE_REQUIRED,
                'Optional sha256: digest for the docker image pin',
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
        $target = $this->requiredString($input, 'target-version');
        $scope = $this->requiredString($input, 'scope');
        if ($scope !== self::SCOPE_REL && $scope !== self::SCOPE_MASTER) {
            $output->writeln('<error>--scope must be "rel" or "master"; got: ' . $scope . '</error>');
            return Command::INVALID;
        }

        $rawDigest = $input->getOption('image-digest');
        $imageDigest = is_string($rawDigest) && $rawDigest !== '' ? $rawDigest : null;

        $rawProjectDir = $input->getOption('project-dir');
        $projectDir = is_string($rawProjectDir) && $rawProjectDir !== ''
            ? $rawProjectDir
            : $this->defaultProjectDir();

        try {
            $context = MutatorContext::fromVersionString($projectDir, $target, $imageDigest);
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
        return [
            new VersionPhpMutator(),
            new GlobalsIncMutator(),
            new DockerComposeProductionMutator(),
            new OpenApiVersionMutator(),
            new DockerVersionFileMutator(),
            new SwaggerRegenMutator(),
        ];
    }

    /**
     * @return list<MutatorInterface>
     */
    private function buildDefaultMasterMutators(): array
    {
        return [
            new SqlUpgradeSkeletonMutator(),
            new VersionPhpMasterMutator(),
        ];
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
