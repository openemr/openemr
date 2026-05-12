<?php

/**
 * Edit a PHP source string by parsing it, locating target nodes via a
 * caller-supplied finder, and substituting the byte ranges of those
 * nodes with new text. Preserves all formatting outside the targeted
 * ranges (comments, indentation, etc.) — unlike a pretty-printer
 * round-trip.
 *
 * Used by the release-prep mutators to make structural edits to PHP
 * source without resorting to regex on PHP. Identifying targets
 * structurally avoids the silent-miss failure mode where a regex
 * doesn't quite match the actual code.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep;

use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;

final readonly class AstSourceEditor
{
    /**
     * Apply the finder to the parsed AST and return the source with
     * each returned range replaced. Ranges may be returned in any
     * order; this method sorts them so the substitutions don't shift
     * each other's offsets.
     *
     * @param string $source The PHP source to edit.
     * @param callable(array<Stmt>): list<array{0: int, 1: int, 2: string}> $finder
     *     Returns a list of [startFilePos, endFilePos (inclusive), replacement] tuples.
     */
    public function edit(string $source, callable $finder): string
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $ast = $parser->parse($source);
        if ($ast === null) {
            throw new \RuntimeException('PHP parser returned null AST');
        }
        $ranges = $finder($ast);
        if ($ranges === []) {
            return $source;
        }
        // Sort by descending start offset so each substitution leaves
        // earlier offsets unchanged.
        usort($ranges, static fn(array $a, array $b): int => $b[0] <=> $a[0]);
        $result = $source;
        foreach ($ranges as [$start, $end, $replacement]) {
            $length = $end - $start + 1;
            $result = substr_replace($result, $replacement, $start, $length);
        }
        return $result;
    }
}
