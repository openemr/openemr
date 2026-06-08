<?php

/**
 * Twig functions for the PostCalendar module.
 *
 * Replaces the legacy Smarty plugins under
 * interface/main/calendar/modules/PostCalendar/plugins/. Of the 10 plugin
 * files in that directory, only function.pc_sort_events.php is invoked
 * from any template (all 9 others are dead code, deleted in Phase 10).
 *
 * Functions return data, not strings of HTML. The legacy Smarty plugins
 * mostly echoed; this extension takes the opposite approach — every
 * method returns its result, and the template renders it (or in the
 * case of pc_sort_events, iterates it).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PostCalendar;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PostCalendarTwigExtension extends AbstractExtension
{
    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('pc_sort_events', $this->pcSortEvents(...)),
            new TwigFunction(
                'pc_event_time_anchor',
                $this->pcEventTimeAnchor(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Sort events within each date bucket by start time, ascending.
     *
     * Replaces `function.pc_sort_events.php`, which supported
     * sort=time|category|title × order=asc|desc. Only `sort="time"
     * order="asc"` is actually invoked from any template (see migration
     * notes), so this implementation handles only that combination.
     *
     * @param  array<int|string, list<array<string, mixed>>> $eventsByDate
     * @return array<int|string, list<array<string, mixed>>>
     */
    public function pcSortEvents(array $eventsByDate): array
    {
        foreach ($eventsByDate as $date => $events) {
            usort(
                $events,
                static fn (array $a, array $b): int => ($a['startTime'] ?? '') <=> ($b['startTime'] ?? '')
            );
            $eventsByDate[$date] = $events;
        }

        return $eventsByDate;
    }

    /**
     * HTML anchor that, on click, triggers the event editor for the enclosing
     * appointment block. Caller is responsible for escaping any HTML in
     * $displayString (the legacy contract — see migration notes — that
     * `text()` here intentionally preserves: the caller may supply text or
     * pre-escaped HTML).
     *
     * Replaces the legacy `create_event_time_anchor()` helper defined inside
     * a `[-php-]` block in `pntemplates/default/views/header.html`. Called
     * from `[-php-]` blocks in day/week/month ajax_templates today; after
     * those templates convert to Twig the call site becomes
     * `{{ pc_event_time_anchor(displayTime) }}`.
     */
    public function pcEventTimeAnchor(string $displayString): string
    {
        $title = xl('Click to edit');

        return "<a class='event_time' onclick='event_time_click(this)' title='"
            . attr($title) . "'>" . text($displayString) . "</a>";
    }
}
