<?php

/**
 * Regenerate swagger/openemr-api.yaml by subprocessing the existing
 * `openemr:create-api-documentation` console command. We don't duplicate
 * its logic; the conductor just re-runs it after OpenApiVersionMutator
 * has bumped the source-of-truth version constant.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type ProcessRunner callable(Process): int
 */
final readonly class SwaggerRegenMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'swagger/openemr-api.yaml';

    /**
     * @param ProcessRunner|null $processRunner Override only in tests.
     */
    public function __construct(
        private mixed $processRunner = null,
    ) {
    }

    public function name(): string
    {
        return self::RELATIVE_PATH . ' (regenerate via openemr:create-api-documentation)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $before = file_exists($path) ? (string) file_get_contents($path) : '';

        $process = $this->buildProcess($context->projectDir);
        $exitCode = $this->runProcess($process);
        if ($exitCode !== 0) {
            throw new \RuntimeException(
                'openemr:create-api-documentation exited ' . $exitCode,
            );
        }

        $after = file_exists($path) ? (string) file_get_contents($path) : '';
        // Validate the regenerated OpenAPI document beyond
        // well-formedness: assert it parses AND its info.version equals
        // the target. The subprocess uses Symfony\Yaml::dump so output
        // is valid by construction, but a stale or wrong version slipped
        // in (e.g. the OpenApiVersionMutator didn't run first) is the
        // mistake worth catching here.
        try {
            $parsed = Yaml::parse($after);
        } catch (ParseException $e) {
            throw new \RuntimeException(
                self::RELATIVE_PATH . ': openemr:create-api-documentation produced invalid YAML',
                0,
                $e,
            );
        }
        $actualVersion = is_array($parsed)
            && is_array($parsed['info'] ?? null)
            ? ($parsed['info']['version'] ?? null)
            : null;
        $expectedVersion = $context->versionString();
        if ($actualVersion !== $expectedVersion) {
            throw new \RuntimeException(sprintf(
                '%s: post-regen info.version was %s; expected %s'
                . ' (did OpenApiVersionMutator run first?)',
                self::RELATIVE_PATH,
                var_export($actualVersion, true),
                $expectedVersion,
            ));
        }
        if ($before === $after) {
            return MutatorResult::noop();
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }

    public function buildProcess(string $projectDir): Process
    {
        return new Process(
            ['php', $projectDir . '/bin/console', 'openemr:create-api-documentation', '--skip-globals'],
            $projectDir,
        );
    }

    private function runProcess(Process $process): int
    {
        if ($this->processRunner !== null) {
            return ($this->processRunner)($process);
        }
        $process->run();
        return $process->getExitCode() ?? 1;
    }
}
