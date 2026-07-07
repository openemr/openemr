<?php

/**
 * Master-side patch-prep sibling to BranchCutReleaseTargetsMutator and
 * PostReleaseTargetsMutator. Updates `.github/release-targets.yml` when
 * a rel branch enters dev for a new patch (e.g., rel-810 from 8.1.0
 * shipped → 8.1.1-dev):
 *
 *   (1) Insert a new row for the rel branch carrying
 *       `docker_tags: <X.Y.P>,next` and `openemr_version_ref: <relBranch>`.
 *       Placement: immediately after the master row, before the first
 *       existing rel-* row. Idempotent: if a row with these exact tags
 *       already exists for this branch, no-op.
 *
 *   (2) Drop any rows for this rel branch flagged `unreleased: true`.
 *       Idempotent: no-op if none present. Covers the initial-cycle
 *       case where rel-810 had a placeholder row (added at branch-cut)
 *       that becomes redundant once the real dev cycle begins.
 *
 * Existing finalized rows for prior patches (e.g., the just-shipped
 * 8.1.1 row carrying `latest`) are NOT touched — that row was added by
 * PostReleaseTargetsMutator at finalize and represents a real publishing
 * configuration.
 *
 * Implementation: line-based surgical edits to preserve human-authored
 * comments, mirroring BranchCutReleaseTargetsMutator. Symfony YAML's
 * parser is used at the end as a structural sanity check.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-type RowIndex array{
 *   branch: string,
 *   startLine: int,
 *   dockerTags: ?string,
 *   dockerTagsLine: ?int,
 *   openemrVersionRef: ?string,
 *   openemrVersionRefLine: ?int,
 *   unreleased: ?string,
 * }
 */
final readonly class PatchPrepReleaseTargetsMutator implements MutatorInterface
{
    private const RELATIVE_PATH = '.github/release-targets.yml';

    public function name(): string
    {
        return 'release-targets.yml (patch-prep: insert new dev row + drop unreleased placeholder)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $relBranch = $context->relBranch;
        if ($relBranch === null) {
            throw new \RuntimeException(
                self::class . ' requires --rel-branch to be supplied via MutatorContext',
            );
        }

        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $original = file_get_contents($path);
        if ($original === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $newText = $this->dropUnreleasedRowsForBranch($original, $relBranch);
        $newText = $this->insertNewDevRow($newText, $relBranch, $context);

        if ($newText === $original) {
            return MutatorResult::noop();
        }

        try {
            Yaml::parse($newText);
        } catch (ParseException $e) {
            throw new \RuntimeException(
                'PatchPrepReleaseTargetsMutator produced invalid YAML; refusing to write',
                0,
                $e,
            );
        }

        if (file_put_contents($path, $newText) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }

    /**
     * Walk the YAML and produce one record per row with field values +
     * line indices of each editable line. Matches the indexer shape used
     * by the sibling release-targets mutators.
     *
     * @return list<RowIndex>
     */
    private function indexRows(string $text): array
    {
        $lines = explode("\n", $text);
        $rows = [];
        $current = null;
        foreach ($lines as $i => $line) {
            if (preg_match('/^- branch:\s*(\S+)\s*$/', $line, $m) === 1) {
                if ($current !== null) {
                    $rows[] = $current;
                }
                $current = [
                    'branch' => $m[1],
                    'startLine' => $i,
                    'dockerTags' => null,
                    'dockerTagsLine' => null,
                    'openemrVersionRef' => null,
                    'openemrVersionRefLine' => null,
                    'unreleased' => null,
                ];
                continue;
            }
            if ($current === null) {
                continue;
            }
            if (preg_match('/^  docker_tags:\s*([^#\n]*?)(?:\s+#.*)?$/', $line, $m) === 1) {
                $current['dockerTags'] = trim($m[1]);
                $current['dockerTagsLine'] = $i;
                continue;
            }
            if (preg_match('/^  openemr_version_ref:\s*([^#\n]*?)(?:\s+#.*)?$/', $line, $m) === 1) {
                $current['openemrVersionRef'] = trim($m[1]);
                $current['openemrVersionRefLine'] = $i;
                continue;
            }
            if (preg_match('/^  unreleased:\s*([^#\n]*?)(?:\s+#.*)?$/', $line, $m) === 1) {
                $current['unreleased'] = trim($m[1]);
            }
        }
        if ($current !== null) {
            $rows[] = $current;
        }
        return $rows;
    }

    /**
     * Drop every row for `$relBranch` that carries `unreleased: true`.
     * Mirrors BranchCutReleaseTargetsMutator::dropAllUnreleasedRows, but
     * scoped to the patch-prep target branch — other rel branches'
     * placeholders are not our concern here. Also strips leading attached
     * comment/blank lines (those comments document the placeholder).
     */
    private function dropUnreleasedRowsForBranch(string $text, string $relBranch): string
    {
        while (true) {
            $rows = $this->indexRows($text);
            $target = null;
            foreach ($rows as $row) {
                if ($row['branch'] === $relBranch && $row['unreleased'] === 'true') {
                    $target = $row;
                    break;
                }
            }
            if ($target === null) {
                return $text;
            }

            $lines = explode("\n", $text);

            $endLine = count($lines) - 1;
            foreach ($rows as $row) {
                if ($row['startLine'] > $target['startLine']) {
                    $endLine = $row['startLine'] - 1;
                    break;
                }
            }

            $start = $target['startLine'];
            while ($start > 0) {
                $prev = $lines[$start - 1];
                if (preg_match('/^\s*$/', $prev) === 1 || str_starts_with(ltrim($prev), '#')) {
                    $start--;
                    continue;
                }
                break;
            }
            while ($endLine >= $start && preg_match('/^\s*$/', $lines[$endLine]) === 1) {
                $endLine--;
            }
            $removeCount = $endLine - $start + 1;
            array_splice($lines, $start, $removeCount);
            $text = implode("\n", $lines);
        }
    }

    /**
     * Insert a fresh row for the new dev cycle:
     *   - branch: <relBranch>
     *     docker_tags: <X.Y.P>,next
     *     openemr_version_ref: <relBranch>
     *
     * Placement: immediately after the master row's full block (with a
     * blank-line separator), before the first existing rel-* row.
     *
     * Idempotent: if a row with the exact (branch, docker_tags) pair
     * already exists, no-op.
     */
    private function insertNewDevRow(string $text, string $relBranch, MutatorContext $context): string
    {
        $expectedTags = sprintf('%d.%d.%d,next', $context->major, $context->minor, $context->patch);
        $rows = $this->indexRows($text);
        foreach ($rows as $row) {
            if ($row['branch'] === $relBranch && $row['dockerTags'] === $expectedTags) {
                // Already present at the post-mutation shape; no-op.
                return $text;
            }
        }

        $masterRow = null;
        $nextRowStart = null;
        foreach ($rows as $row) {
            if ($row['branch'] === 'master') {
                $masterRow = $row;
                continue;
            }
            if ($masterRow !== null && $nextRowStart === null) {
                $nextRowStart = $row['startLine'];
            }
        }
        if ($masterRow === null) {
            return $this->appendRowAtEnd($text, $relBranch, $context);
        }

        $lines = explode("\n", $text);
        $insertAt = $nextRowStart ?? count($lines);

        while ($insertAt > 0) {
            $prev = $lines[$insertAt - 1];
            if (preg_match('/^\s*$/', $prev) === 1 || str_starts_with(ltrim($prev), '#')) {
                $insertAt--;
                continue;
            }
            break;
        }

        $rowBlock = $this->renderRelRowLines($relBranch, $context);
        $injected = array_merge([''], $rowBlock);
        array_splice($lines, $insertAt, 0, $injected);
        return implode("\n", $lines);
    }

    private function appendRowAtEnd(string $text, string $relBranch, MutatorContext $context): string
    {
        $rowBlock = $this->renderRelRowLines($relBranch, $context);
        $trailingNewline = str_ends_with($text, "\n") ? '' : "\n";
        return $text . $trailingNewline . "\n" . implode("\n", $rowBlock) . "\n";
    }

    /**
     * Render the new dev row: <X.Y.P>,next + openemr_version_ref=<relBranch>.
     *
     * @return list<string>
     */
    private function renderRelRowLines(string $relBranch, MutatorContext $context): array
    {
        $versionTag = sprintf('%d.%d.%d', $context->major, $context->minor, $context->patch);
        return [
            '- branch: ' . $relBranch,
            '  docker_tags: ' . $versionTag . ',next',
            '  openemr_version_ref: ' . $relBranch,
        ];
    }
}
