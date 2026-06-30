<?php

/**
 * Master-side branch-cut sibling to PostReleaseTargetsMutator. Applies
 * the three coordinated edits to `.github/release-targets.yml` that a
 * new rel-NNN0 branch cut requires:
 *
 *   (1) Insert a new row for the freshly-cut rel branch (e.g. rel-820)
 *       carrying `docker_tags: <X.Y.0>,next` and `openemr_version_ref:
 *       <relBranch>`. Placement: immediately after the master row +
 *       before the first existing rel-* row.
 *
 *   (2) Bump the master row's `docker_tags`: minor++ (e.g., 8.2.0 → 8.3.0),
 *       drop `next` if present, keep `dev`.
 *
 *   (3) Remove all rows that carry `unreleased: true`. Covers both the
 *       normal-cut path (no unreleased rows present → no-op) and the
 *       skip-line-cut path (maintainer pre-flagged the prior rel branch
 *       as unreleased; those rows get dropped uniformly).
 *
 * All three transforms are idempotent — re-running on already-mutated
 * input yields no diff. Uses line-based surgical edits to preserve the
 * substantial human-authored comments in release-targets.yml, mirroring
 * PostReleaseTargetsMutator's approach. Symfony YAML's parser is used at
 * the end as a structural sanity check on the surgical edits.
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
final readonly class BranchCutReleaseTargetsMutator implements MutatorInterface
{
    private const RELATIVE_PATH = '.github/release-targets.yml';

    public function name(): string
    {
        return 'release-targets.yml (branch-cut: insert rel row + bump master + drop unreleased)';
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

        $newText = $this->dropAllUnreleasedRows($original);
        $newText = $this->bumpMasterDockerTags($newText, $context);
        $newText = $this->insertNewRelRow($newText, $relBranch, $context);

        if ($newText === $original) {
            return MutatorResult::noop();
        }

        try {
            Yaml::parse($newText);
        } catch (ParseException $e) {
            throw new \RuntimeException(
                'BranchCutReleaseTargetsMutator produced invalid YAML; refusing to write',
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
     * line indices of each editable line. Same shape as
     * PostReleaseTargetsMutator's indexer.
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
     * Drop ALL rows whose `unreleased: true` flag is set. Covers both
     * normal-cut (no unreleased rows → no-op) and skip-line-cut paths.
     * Also removes any leading blank/comment lines attached to the
     * dropped row (those comments document the placeholder, which is
     * being removed).
     */
    private function dropAllUnreleasedRows(string $text): string
    {
        // Loop because each removal renumbers line indices; rebuild the
        // row index after each drop until none remain.
        while (true) {
            $rows = $this->indexRows($text);
            $target = null;
            foreach ($rows as $row) {
                if ($row['unreleased'] === 'true') {
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
     * Bump the master row's docker_tags: minor++ on the bare X.Y.0 tag,
     * drop `next` if present, keep `dev`. Idempotent: if the row is
     * already at the post-bump state for this cut, no-op.
     *
     * Example: target rel-820 (8.2.0) — master goes from 8.2.0,dev,next
     * (or 8.2.0,dev) to 8.3.0,dev.
     */
    private function bumpMasterDockerTags(string $text, MutatorContext $context): string
    {
        $rows = $this->indexRows($text);
        $masterRow = null;
        foreach ($rows as $row) {
            if ($row['branch'] === 'master') {
                $masterRow = $row;
                break;
            }
        }
        if ($masterRow === null || $masterRow['dockerTagsLine'] === null) {
            return $text;
        }

        $current = $this->parseTags($masterRow['dockerTags']);

        // Compute the post-bump tag list:
        //   - find the version tag (X.Y.0); replace its minor with minor+1
        //   - drop `next`
        //   - keep `dev`
        $bumped = [];
        $sawVersion = false;
        foreach ($current as $tag) {
            if ($tag === 'next') {
                continue;
            }
            if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $tag, $m) === 1) {
                $bumpedMinor = (int) $m[2] + 1;
                $bumped[] = $m[1] . '.' . $bumpedMinor . '.' . $m[3];
                $sawVersion = true;
                continue;
            }
            $bumped[] = $tag;
        }
        if (!$sawVersion) {
            // Nothing to bump. Don't touch.
            return $text;
        }

        // Idempotency: already at post-bump state.
        if ($bumped === $current) {
            return $text;
        }
        // Also recognise the case where this is a re-run AFTER the bump
        // has already happened: in that case the master row already has
        // X.(target_minor+1).0 + dev (no next, no version-equal-to-target).
        $expectedAlready = $this->expectedMasterTagsAfterCut($context);
        if ($expectedAlready === $current) {
            return $text;
        }

        $lines = explode("\n", $text);
        $lineIndex = $masterRow['dockerTagsLine'];
        $lines[$lineIndex] = $this->renderDockerTagsLine($lines[$lineIndex], $bumped);
        return implode("\n", $lines);
    }

    /**
     * Compute what the master row's docker_tags should look like after
     * a full branch-cut bump from this context's target version. Used
     * for idempotency detection.
     *
     * @return list<string>
     */
    private function expectedMasterTagsAfterCut(MutatorContext $context): array
    {
        return [sprintf('%d.%d.0', $context->major, $context->minor + 1), 'dev'];
    }

    /**
     * Insert a new row for the freshly-cut rel branch. Placement:
     * immediately after the master row's full block (rendering a blank
     * line separator first), before the first existing rel-* row.
     *
     * Idempotent: if a row with `branch: <relBranch>` already exists
     * anywhere in the file, no-op.
     */
    private function insertNewRelRow(string $text, string $relBranch, MutatorContext $context): string
    {
        $rows = $this->indexRows($text);
        foreach ($rows as $row) {
            if ($row['branch'] === $relBranch) {
                return $text;
            }
        }

        // Locate the master row + figure out where its block ends (line
        // before the first row after master).
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
            // No master row — append at end.
            return $this->appendRowAtEnd($text, $relBranch, $context);
        }

        $lines = explode("\n", $text);
        $insertAt = $nextRowStart ?? count($lines);

        // Walk backward to skip blank/comment lines that anchor to the
        // following row — we want to insert BEFORE those.
        while ($insertAt > 0) {
            $prev = $lines[$insertAt - 1];
            if (preg_match('/^\s*$/', $prev) === 1 || str_starts_with(ltrim($prev), '#')) {
                $insertAt--;
                continue;
            }
            break;
        }

        $rowBlock = $this->renderRelRowLines($relBranch, $context);
        // Render with a leading blank line as separator from master's block.
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
     * Render the row's lines. Tag list at cut time: `<X.Y.0>,next`.
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

    /**
     * @return list<string>
     */
    private function parseTags(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        $parts = preg_split('/\s*,\s*/', trim($value));
        if ($parts === false) {
            return [];
        }
        return array_values(array_filter($parts, static fn (string $t): bool => $t !== ''));
    }

    /**
     * Render an updated `  docker_tags: ...` line, preserving the
     * `  docker_tags: ` prefix and any trailing comment.
     *
     * @param list<string> $tags
     */
    private function renderDockerTagsLine(string $originalLine, array $tags): string
    {
        $value = implode(',', $tags);
        $replaced = preg_replace(
            '/^(  docker_tags:\s*)[^#\n]*?(\s*(?:#.*)?)$/',
            '${1}' . $value . '${2}',
            $originalLine,
            1,
        );
        return $replaced ?? $originalLine;
    }
}
