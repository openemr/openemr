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
     * Idempotent: if the notes already contain a "### Minimum supported
     * versions" block (bounded by the heading and the next `##`-prefixed
     * heading of any depth or EOF), that block is removed first so a rerun
     * with the same section produces the same output rather than a duplicate.
     */
    public function inject(string $notes, string $section): string
    {
        // Strip any existing Minimum-supported-versions block first so a
        // second inject() call with the same section is a no-op instead of
        // producing a duplicate section. The block runs from the heading
        // to the next `##`-prefixed heading of any depth (### or ##) or
        // EOF, so we anchor on the following heading in a lookahead.
        $stripped = preg_replace(
            '/^### Minimum supported versions.*?(?=^##|\z)/ms',
            '',
            $notes,
            1,
        );
        if ($stripped === null) {
            throw new \RuntimeException('CompatibilityNotesRenderer::inject regex failure');
        }
        $notes = $stripped;

        $lines = explode("\n", $notes);
        foreach ($lines as $index => $line) {
            if (!str_starts_with($line, '## ')) {
                continue;
            }
            $insertAt = isset($lines[$index + 1]) && trim($lines[$index + 1]) === ''
                ? $index + 2
                : $index + 1;
            array_splice($lines, $insertAt, 0, [rtrim($section, "\n"), '']);
            return implode("\n", $lines);
        }

        return $section . "\n" . $notes;
    }
}
