<?php

/**
 * Symfony Console rendering for ShipReleaseResult. Kept separate so the CLI
 * file declares no helper functions (PSR1 side-effects-vs-symbols).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Component\Console\Output\OutputInterface;

final readonly class ShipReleaseRenderer
{
    public static function render(OutputInterface $output, ShipReleaseResult $result): void
    {
        if ($result->fatalReason !== null) {
            $output->writeln('<error>✗ fatal:</error> ' . $result->fatalReason);
        }
        foreach ($result->steps as $step) {
            $tag = sprintf('[%s] %s', $step->target->roleLabel->value, $step->target->repo);
            $pr = $step->prNumber !== null ? '#' . $step->prNumber : '(no PR)';
            self::renderStep($output, $step, $tag, $pr);
        }
    }

    private static function renderStep(
        OutputInterface $output,
        ShipReleaseStepResult $step,
        string $tag,
        string $pr,
    ): void {
        switch ($step->status) {
            case ShipReleaseStepStatus::MERGED:
                $output->writeln(sprintf(
                    '<info>✓ merged</info>   %s %s → %s',
                    $tag,
                    $pr,
                    $step->mergeSha ?? '?',
                ));
                return;
            case ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED:
                $output->writeln(sprintf('<comment>↷ skipped</comment> %s %s (already merged)', $tag, $pr));
                return;
            case ShipReleaseStepStatus::WOULD_MERGE:
                $output->writeln(sprintf('<info>✓ ready</info>    %s %s (dry-run: would merge)', $tag, $pr));
                return;
            case ShipReleaseStepStatus::BLOCKED:
                $output->writeln(sprintf('<error>✗ blocked</error>  %s %s', $tag, $pr));
                foreach ($step->reasons as $reason) {
                    $output->writeln('    - ' . $reason);
                }
                return;
            case ShipReleaseStepStatus::NOT_REACHED:
                $output->writeln(sprintf('<comment>· skipped</comment>  %s %s (not reached)', $tag, $pr));
        }
    }
}
