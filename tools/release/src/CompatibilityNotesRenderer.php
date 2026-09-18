<?php

/**
 * Render the "Minimum supported versions" release-notes section from the
 * minimum tested versions derived by CompatibilityDeriver.
 *
 * The section states the minimum supported version per runtime component and
 * links to the release branch's CI directory, which holds the full matrix of
 * versions the release was actually tested against.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class CompatibilityNotesRenderer
{
    /** Display labels for known component keys; others fall back to ucfirst. */
    private const LABELS = [
        'php' => 'PHP',
        'mariadb' => 'MariaDB',
        'mysql' => 'MySQL',
    ];

    /**
     * Render the markdown section.
     *
     * @param array<string, string> $minimums Component key => minimum version
     * @param string $testedMatrixUrl Link to the release branch's ci/ directory
     */
    public function render(array $minimums, string $testedMatrixUrl): string
    {
        if ($minimums === []) {
            throw new \RuntimeException('Cannot render compatibility notes from an empty minimums map');
        }

        $lines = ['### Minimum supported versions', ''];
        foreach ($minimums as $component => $version) {
            $label = self::LABELS[$component] ?? ucfirst($component);
            $lines[] = "- **{$label}** {$version}+";
        }
        $lines[] = '';
        $lines[] = "See the [tested CI matrix]({$testedMatrixUrl}) for all tested version combinations.";

        return implode("\n", $lines) . "\n";
    }

    /**
     * Insert a section into notes just after the first `## ` heading line (and
     * the blank line that conventionally follows it). If the notes have no such
     * heading, prepend the section instead.
     *
     * Idempotent: if the FIRST `## ` section already contains a
     * "### Minimum supported versions" block, that block is stripped in place
     * before the new section is inserted. Only the first `## ` section's
     * contents are considered — older release sections further down the file
     * (which have their own compat blocks from earlier release-prep runs) are
     * left untouched.
     */
    public function inject(string $notes, string $section): string
    {
        $lines = explode("\n", $notes);

        $headingIndex = null;
        foreach ($lines as $index => $line) {
            if (str_starts_with($line, '## ')) {
                $headingIndex = $index;
                break;
            }
        }

        if ($headingIndex === null) {
            return $section . "\n" . $notes;
        }

        // Bound the first section: heading -> next `## ` heading or EOF.
        // Only strip existing compat blocks inside THIS scope so older
        // release sections keep their own Minimum-supported-versions blocks.
        $sectionEnd = count($lines);
        for ($i = $headingIndex + 1, $n = count($lines); $i < $n; $i++) {
            if (str_starts_with($lines[$i], '## ')) {
                $sectionEnd = $i;
                break;
            }
        }
        $targetBlock = implode("\n", array_slice($lines, $headingIndex, $sectionEnd - $headingIndex));
        // Strip an existing compat block bounded by its heading and the next
        // `##`-prefixed heading of any depth (### or ##) or the section end.
        $stripped = preg_replace(
            '/^### Minimum supported versions.*?(?=^##|\z)/ms',
            '',
            $targetBlock,
            1,
        );
        if ($stripped === null) {
            throw new \RuntimeException('CompatibilityNotesRenderer::inject regex failure');
        }
        array_splice(
            $lines,
            $headingIndex,
            $sectionEnd - $headingIndex,
            explode("\n", $stripped),
        );

        $insertAt = isset($lines[$headingIndex + 1]) && trim($lines[$headingIndex + 1]) === ''
            ? $headingIndex + 2
            : $headingIndex + 1;
        array_splice($lines, $insertAt, 0, [rtrim($section, "\n"), '']);
        return implode("\n", $lines);
    }
}
