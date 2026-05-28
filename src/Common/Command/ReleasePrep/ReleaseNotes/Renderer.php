<?php

/**
 * Render a Manifest into the Markdown shape expected at the top of
 * repo-root CHANGELOG.md. Format is fixed by the existing 8.0.0.3
 * entry: H2 with a milestone link and ISO date, H3 buckets (Fixed,
 * Added, Changed), H4 sub-buckets for Fixed (Security, Bug Fixes).
 *
 * String assembly only — no Markdown library. The byte-for-byte output
 * is what makes the mutator's idempotence check work.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\ReleaseNotes;

final readonly class Renderer
{
    public function render(Manifest $manifest): string
    {
        $out = sprintf(
            "## [%s](%s) - %s\n",
            $manifest->version,
            $manifest->milestoneUrl,
            $manifest->date,
        );

        $security = $manifest->entriesFor(Section::Security);
        $bugFixes = $manifest->entriesFor(Section::BugFixes);
        $added = $manifest->entriesFor(Section::Added);
        $changed = $manifest->entriesFor(Section::Changed);

        if ($security !== [] || $bugFixes !== []) {
            $out .= "\n### Fixed\n";
            if ($security !== []) {
                $out .= "\n#### Security\n\n";
                $out .= $this->renderEntries($security);
            }
            if ($bugFixes !== []) {
                $out .= "\n#### Bug Fixes\n\n";
                $out .= $this->renderEntries($bugFixes);
            }
        }
        if ($added !== []) {
            $out .= "\n### Added\n\n";
            $out .= $this->renderEntries($added);
        }
        if ($changed !== []) {
            $out .= "\n### Changed\n\n";
            $out .= $this->renderEntries($changed);
        }

        return $out;
    }

    /**
     * @param list<Entry> $entries
     */
    private function renderEntries(array $entries): string
    {
        $out = '';
        foreach ($entries as $entry) {
            $out .= '- ' . $entry->title;
            if ($entry->prNumber !== null && $entry->prUrl !== null) {
                $out .= sprintf(' ([#%d](%s))', $entry->prNumber, $entry->prUrl);
            }
            $out .= "\n";
        }
        return $out;
    }
}
