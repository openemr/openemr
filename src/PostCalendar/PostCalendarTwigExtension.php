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

}
