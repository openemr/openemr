<?php

/**
 * On master, paired with a rel-branch release-prep PR: applies the three
 * coordinated edits to .github/release-targets.yml that a rel-branch ship
 * requires. (1) Pin the rel branch's openemr_version_ref to the new tag
 * (e.g. rel-810 -> v8_1_1) so daily docker builds stop tracking the
 * branch tip. (2) Slot-shuffle docker_tags across rows: promote `next`
 * to `latest` on the just-shipped rel branch, drop `latest` from the
 * prior holder, and move `next` to the next upcoming-stable owner
 * (next-cut rel branch if one exists, else master). (3) Drop the
 * unreleased placeholder row for the same rel branch if present (the
 * multi-row mechanism from openemr/openemr#12656). All three transforms
 * are idempotent — re-running on already-mutated input yields no diff.
 *
 * Implementation note: uses line-based surgical edits rather than parsing
 * via Symfony YAML's dumper to preserve the substantial human-authored
 * comments in release-targets.yml. Symfony YAML's parser is used at the
 * end as a sanity check that the surgical edits did not produce
 * structurally invalid YAML.
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
final readonly class PostReleaseTargetsMutator implements MutatorInterface
{
    private const RELATIVE_PATH = '.github/release-targets.yml';

    public function name(): string
    {
        return 'release-targets.yml (post-release pin + slot shuffle + drop placeholder)';
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

        // Early-return guard: the slot-shuffle step drops `latest` from
        // the prior holder assuming there is a target row (the just-shipped
        // rel branch) ready to receive it. If no live row exists for the
        // target rel branch — e.g., release-finalize firing on a push
        // before the paired branch-cut PR has landed the new rel-branch
        // row — the shuffle would leave release-targets.yml in an invalid
        // state (nobody holds `latest`). Skip the whole mutator instead.
        if (!$this->hasLiveRowForRelBranch($original, $relBranch)) {
            return new MutatorResult(
                [],
                [
                    'no live row for ' . $relBranch
                    . ' in release-targets.yml; skipping premature slot shuffle',
                ],
            );
        }

        $tagName = $context->tagName();

        $newText = $this->dropUnreleasedPlaceholderRow($original, $relBranch);
        $newText = $this->pinRelBranchVersionRef($newText, $relBranch, $tagName);
        $newText = $this->shuffleSlots($newText, $relBranch);

        if ($newText === $original) {
            return MutatorResult::noop();
        }

        // Sanity check: a bad regex pass could produce structurally
        // invalid YAML. Surface that here rather than at the consumer.
        try {
            Yaml::parse($newText);
        } catch (ParseException $e) {
            throw new \RuntimeException(
                'PostReleaseTargetsMutator produced invalid YAML; refusing to write',
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
     * A "live" row is one whose `branch:` matches the target rel branch
     * AND is NOT marked `unreleased: true`. The unreleased placeholder
     * is the multi-row pattern's development scaffold — it doesn't count
     * as the shippable row this mutator's transforms target.
     */
    private function hasLiveRowForRelBranch(string $text, string $relBranch): bool
    {
        foreach ($this->indexRows($text) as $row) {
            if ($row['branch'] === $relBranch && $row['unreleased'] !== 'true') {
                return true;
            }
        }
        return false;
    }

    /**
     * Walk the YAML and produce one record per row with the field values
     * we care about and the line index of each editable line. The row
     * structure is rigid: each row begins with `- branch:` at column 0;
     * subsequent indented `  <field>:` lines (skipping `#` comments and
     * blank lines) belong to the same row until the next `- branch:`.
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
     * Drop the rel-branch row whose `unreleased: true` flag is set. Per
     * the multi-row pattern docs in release-targets.yml: the placeholder
     * row exists to wire the mechanism without immediately publishing
     * the prior stable version; once a new version ships, the placeholder
     * is redundant.
     *
     * Also removes any blank/comment lines that directly precede the row
     * (those comments document the placeholder, which is being dropped).
     */
    private function dropUnreleasedPlaceholderRow(string $text, string $relBranch): string
    {
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

        // Identify where the target row ends: the line before the next
        // row's `- branch:`, OR end-of-file.
        $endLine = count($lines) - 1;
        foreach ($rows as $row) {
            if ($row['startLine'] > $target['startLine']) {
                $endLine = $row['startLine'] - 1;
                break;
            }
        }

        // Walk backward collecting attached comment/blank lines.
        $start = $target['startLine'];
        while ($start > 0) {
            $prev = $lines[$start - 1];
            if (preg_match('/^\s*$/', $prev) === 1 || str_starts_with(ltrim($prev), '#')) {
                $start--;
                continue;
            }
            break;
        }

        // Trim trailing blank lines that came along with the row body.
        while ($endLine >= $start && preg_match('/^\s*$/', $lines[$endLine]) === 1) {
            $endLine--;
        }

        // Splice out [$start, $endLine] inclusive. The leading-blank we
        // already swept up plays the role of the separator between the
        // surrounding rows, so don't also consume the trailing blank —
        // doing both would collapse two rows into adjacency.
        $removeCount = $endLine - $start + 1;
        array_splice($lines, $start, $removeCount);

        return implode("\n", $lines);
    }

    /**
     * Pin the rel branch row's openemr_version_ref to the new tag, but
     * only the row that is currently still tracking the branch tip
     * (openemr_version_ref == relBranch). The multi-row dev pattern can
     * leave secondary rel-branch rows pinned to historical tags
     * (e.g. v8_1_0) which must stay untouched.
     */
    private function pinRelBranchVersionRef(string $text, string $relBranch, string $tagName): string
    {
        $rows = $this->indexRows($text);

        $target = null;
        foreach ($rows as $row) {
            if (
                $row['branch'] === $relBranch
                && $row['openemrVersionRef'] === $relBranch
            ) {
                $target = $row;
                break;
            }
        }
        if ($target === null || $target['openemrVersionRefLine'] === null) {
            // Already pinned, or no row matches; no-op.
            return $text;
        }

        $lines = explode("\n", $text);
        $lineIndex = $target['openemrVersionRefLine'];
        $replaced = preg_replace(
            '/^(  openemr_version_ref:\s*)[^#\s]+(\s*(?:#.*)?)$/',
            '${1}' . $tagName . '${2}',
            $lines[$lineIndex],
            1,
        );
        if ($replaced !== null) {
            $lines[$lineIndex] = $replaced;
        }

        return implode("\n", $lines);
    }

    /**
     * Slot shuffle: promote `next` to `latest` on the just-shipped rel
     * branch row, drop `latest` from any other row holding it, and add
     * `next` to the next-upcoming-stable owner (a newer rel branch row
     * if one exists, else master).
     */
    private function shuffleSlots(string $text, string $relBranch): string
    {
        $rows = $this->indexRows($text);
        $lines = explode("\n", $text);

        // Find the active rel-branch row (tracks branch tip OR is already
        // pinned to a tag for this rel — idempotency).
        $relRow = null;
        foreach ($rows as $row) {
            if (
                $row['branch'] === $relBranch
                && (
                    $row['openemrVersionRef'] === $relBranch
                    || $this->isVersionTagFor($row['openemrVersionRef'] ?? '', $relBranch)
                )
            ) {
                $relRow = $row;
                break;
            }
        }

        $relAlreadyLatest = $relRow !== null
            && in_array('latest', $this->parseTags($relRow['dockerTags']), true);

        // Step 1: drop `latest` from any other row that holds it (only on
        // the first pass — when the rel row already has `latest`, the
        // shuffle has been done).
        if (!$relAlreadyLatest) {
            foreach ($rows as $row) {
                if ($row['branch'] === $relBranch || $row['dockerTagsLine'] === null) {
                    continue;
                }
                $tags = $this->parseTags($row['dockerTags']);
                if (!in_array('latest', $tags, true)) {
                    continue;
                }
                $tags = array_values(array_filter($tags, static fn (string $t): bool => $t !== 'latest'));
                $lines[$row['dockerTagsLine']] = $this->renderDockerTagsLine($lines[$row['dockerTagsLine']], $tags);
            }
        }

        // Step 2: on the rel-branch row, swap `next` for `latest`.
        if ($relRow !== null && $relRow['dockerTagsLine'] !== null) {
            $relLineIndex = $relRow['dockerTagsLine'];
            $tags = $this->parseTags($relRow['dockerTags']);
            if (in_array('next', $tags, true) && !in_array('latest', $tags, true)) {
                $promoted = array_map(
                    static fn (string $t): string => $t === 'next' ? 'latest' : $t,
                    $tags,
                );
                $lines[$relLineIndex] = $this->renderDockerTagsLine(
                    $lines[$relLineIndex],
                    $promoted,
                );
            }
        }

        // Step 3: add `next` to the next-up owner if no row already has it.
        $nextOwner = $this->findNextUpOwner($rows, $relBranch);
        if ($nextOwner !== null && $nextOwner['dockerTagsLine'] !== null) {
            $nextOwnerLineIndex = $nextOwner['dockerTagsLine'];
            $anyRowHasNext = false;
            foreach ($rows as $row) {
                $rowLineIndex = $row['dockerTagsLine'];
                if ($rowLineIndex === null) {
                    continue;
                }
                $current = $this->parseTags($this->extractDockerTagsValue($lines[$rowLineIndex]));
                if (in_array('next', $current, true)) {
                    $anyRowHasNext = true;
                    break;
                }
            }
            if (!$anyRowHasNext) {
                $currentTags = $this->parseTags($this->extractDockerTagsValue($lines[$nextOwnerLineIndex]));
                $currentTags[] = 'next';
                $lines[$nextOwnerLineIndex] = $this->renderDockerTagsLine(
                    $lines[$nextOwnerLineIndex],
                    $currentTags,
                );
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Heuristic: does this version-ref look like a release tag for the
     * relBranch? Used so the second run (after pin) still finds the
     * "active" row. Recognises `v<MAJOR>_<MINOR>_<anything>` matching
     * the rel branch's MAJOR/MINOR. Supports both the modern shape
     * `rel-<MAJOR><MINOR>0` (e.g. rel-810) where the trailing digit is
     * a "new minor line" sentinel, and legacy shapes like `rel-704`
     * (rel-NMP, third digit was a patch) and `rel-800`.
     */
    private function isVersionTagFor(string $versionRef, string $relBranch): bool
    {
        // Accept any rel-<digits>; parse first digit as MAJOR and second
        // as MINOR. (Two-digit majors don't yet exist in the openemr
        // version space; revisit if/when they do.)
        if (preg_match('/^rel-(\d)(\d)\d*$/', $relBranch, $bm) !== 1) {
            return false;
        }
        if (preg_match('/^v(\d+)_(\d+)_\d+$/', $versionRef, $vm) !== 1) {
            return false;
        }
        return $vm[1] === $bm[1] && $vm[2] === $bm[2];
    }

    /**
     * Decide which row should receive `next`: the next-numerically-higher
     * rel branch if one exists, else master.
     *
     * @param list<RowIndex> $rows
     * @return RowIndex|null
     */
    private function findNextUpOwner(array $rows, string $relBranch): ?array
    {
        $relValue = $this->relBranchSortKey($relBranch);
        if ($relValue === null) {
            return null;
        }
        $bestRel = null;
        $bestRelValue = PHP_INT_MAX;
        foreach ($rows as $row) {
            if ($row['branch'] === $relBranch || $row['branch'] === 'master') {
                continue;
            }
            $value = $this->relBranchSortKey($row['branch']);
            if ($value === null || $value <= $relValue) {
                continue;
            }
            if ($value < $bestRelValue) {
                $bestRel = $row;
                $bestRelValue = $value;
            }
        }
        if ($bestRel !== null) {
            return $bestRel;
        }
        foreach ($rows as $row) {
            if ($row['branch'] === 'master') {
                return $row;
            }
        }
        return null;
    }

    /**
     * Sort key for ordering rel branches: rel-810 -> 810. Returns null
     * for non-rel branches (e.g. master).
     */
    private function relBranchSortKey(string $branch): ?int
    {
        if (preg_match('/^rel-(\d+)$/', $branch, $m) !== 1) {
            return null;
        }
        return (int) $m[1];
    }

    /**
     * Split a docker_tags value into the tag list.
     *
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
     * `  docker_tags: ` prefix and any trailing whitespace.
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

    private function extractDockerTagsValue(string $line): string
    {
        if (preg_match('/^  docker_tags:\s*([^#\n]*?)(?:\s+#.*)?$/', $line, $m) !== 1) {
            return '';
        }
        return trim($m[1]);
    }
}
