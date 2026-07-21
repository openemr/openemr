<?php

/**
 * Render a GitHub-flavored Markdown run summary for a ShipReleaseResult, for
 * writing to $GITHUB_STEP_SUMMARY. Separate from ShipReleaseRenderer, which
 * emits Symfony Console markup for the live job log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class ShipReleaseSummaryRenderer
{
    public static function render(
        string $version,
        string $relBranch,
        bool $dryRun,
        ShipReleaseResult $result,
    ): string {
        $lines = [];
        $lines[] = sprintf('## Ship Release — %s (%s)', $version, $relBranch);
        $lines[] = '';
        $lines[] = sprintf('- **Mode:** %s', $dryRun ? 'dry run (no merges performed)' : 'live');
        $lines[] = sprintf('- **Result:** %s', $result->wasSuccessful() ? '✅ success' : '❌ failure');
        $lines[] = '';

        if ($result->fatalReason !== null) {
            $lines[] = sprintf('> ❌ **Fatal:** %s', $result->fatalReason);
            $lines[] = '';
        }

        $lines[] = '| Role | Repo | PR | Status | Detail |';
        $lines[] = '| --- | --- | --- | --- | --- |';
        foreach ($result->steps as $step) {
            $lines[] = self::renderRow($step);
        }
        $lines[] = '';

        return implode("\n", $lines);
    }

    private static function renderRow(ShipReleaseStepResult $step): string
    {
        return sprintf(
            '| %s | `%s` | %s | %s | %s |',
            $step->target->roleLabel->value,
            $step->target->repo,
            self::prLink($step),
            self::statusLabel($step->status),
            self::detail($step),
        );
    }

    private static function prLink(ShipReleaseStepResult $step): string
    {
        if ($step->prNumber === null) {
            return '—';
        }
        return sprintf('[#%d](https://github.com/%s/pull/%d)', $step->prNumber, $step->target->repo, $step->prNumber);
    }

    private static function statusLabel(ShipReleaseStepStatus $status): string
    {
        return match ($status) {
            ShipReleaseStepStatus::MERGED => '✅ merged',
            ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED => '↷ already merged',
            ShipReleaseStepStatus::SKIPPED_BY_MODE => '↷ skipped (by mode)',
            ShipReleaseStepStatus::WOULD_MERGE => '✅ would merge',
            ShipReleaseStepStatus::BLOCKED => '❌ blocked',
            ShipReleaseStepStatus::NOT_REACHED => '· not reached',
        };
    }

    private static function detail(ShipReleaseStepResult $step): string
    {
        return match ($step->status) {
            ShipReleaseStepStatus::MERGED => '`' . ($step->mergeSha ?? '?') . '`',
            ShipReleaseStepStatus::BLOCKED => self::reasons($step),
            ShipReleaseStepStatus::SKIPPED_BY_MODE => self::reasons($step),
            ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED,
            ShipReleaseStepStatus::WOULD_MERGE,
            ShipReleaseStepStatus::NOT_REACHED => '—',
        };
    }

    private static function reasons(ShipReleaseStepResult $step): string
    {
        if ($step->reasons === []) {
            return '—';
        }
        return implode('<br>', $step->reasons);
    }
}
