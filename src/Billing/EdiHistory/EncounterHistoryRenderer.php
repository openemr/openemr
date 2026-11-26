<?php

/**
 * The Encounter Record as one dated list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

/**
 * Renders the facts in the order the assembler put them (#15530 step 3). It states what
 * the files say and nothing else; a fact with no date is shown as undated, not guessed.
 */
final class EncounterHistoryRenderer
{
    /**
     * @param list<EncounterEvent> $events
     * @param string               $csrf token for the file links; '' renders no links
     */
    public static function html(array $events, string $encounter, string $csrf = ''): string
    {
        $html = "<table class='clmhist'>" . PHP_EOL;
        $html .= "<caption>Encounter Record for " . text($encounter) . "</caption>" . PHP_EOL;
        $html .= "<thead><tr><th>Date</th><th>Event</th><th>Detail</th><th>File</th></tr></thead>" . PHP_EOL;
        $html .= "<tbody>" . PHP_EOL;
        if ($events === []) {
            $html .= "<tr><td colspan='4'>Nothing found for " . text($encounter) . "</td></tr>" . PHP_EOL;
        }
        foreach ($events as $event) {
            $html .= "<tr class='" . attr($event->kind) . "'>";
            $html .= "<td>" . text($event->date !== '' ? $event->date : 'undated') . "</td>";
            $html .= "<td>" . text($event->title) . "</td>";
            $html .= "<td>" . text($event->detail) . "</td>";
            $html .= "<td>";
            if ($event->fileName !== '' && $csrf !== '') {
                $href = 'edih_main.php?gtbl=file&fmt=seg&fname=' . attr_url($event->fileName)
                    . '&csrf_token_form=' . attr_url($csrf);
                $html .= "<a class='seg' href='" . attr($href) . "'>" . text($event->fileName) . "</a>";
            } else {
                $html .= text($event->fileName);
            }
            $html .= "</td></tr>" . PHP_EOL;
        }
        $html .= "</tbody>" . PHP_EOL . "</table>" . PHP_EOL;
        return $html;
    }
}