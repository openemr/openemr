<?php

/**
 * Twig functions and filters for the PostCalendar module.
 *
 * Replaces the legacy Smarty plugins under
 * interface/main/calendar/modules/PostCalendar/plugins/. Each plugin
 * (pc_date_format, pc_url, pc_filter, pc_popup, etc.) becomes a method
 * on this class registered via getFunctions() or getFilters().
 *
 * Functions return strings. They do not echo. They do not include() PHP
 * files from the legacy pntemplates/ directory. They do not depend on
 * global state. Caller invokes them via Twig {{ pc_url(...) }} and the
 * template renders the returned string.
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
use Twig\TwigFilter;
use Twig\TwigFunction;

final class PostCalendarTwigExtension extends AbstractExtension
{
    /**
     * @return list<TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            // Plugins to migrate (commented out until each is implemented):
            // new TwigFunction('pc_date_format', $this->pcDateFormat(...)),
            // new TwigFunction('pc_date_select', $this->pcDateSelect(...), ['is_safe' => ['html']]),
            // new TwigFunction('pc_filter', $this->pcFilter(...), ['is_safe' => ['html']]),
            // new TwigFunction('pc_form_nav_close', $this->pcFormNavClose(...), ['is_safe' => ['html']]),
            // new TwigFunction('pc_form_nav_open', $this->pcFormNavOpen(...), ['is_safe' => ['html']]),
            // new TwigFunction('pc_popup', $this->pcPopup(...), ['is_safe' => ['html']]),
            // new TwigFunction('pc_sort_events', $this->pcSortEvents(...)),
            // new TwigFunction('pc_url', $this->pcUrl(...)),
            // new TwigFunction('pc_view_select', $this->pcViewSelect(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return list<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            // new TwigFilter('pc_date_format', $this->pcDateFormatFilter(...)),
        ];
    }
}
