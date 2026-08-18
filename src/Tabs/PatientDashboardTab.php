<?php

/**
 * Build the tab entry used when opening a patient in a top-level window.
 *
 * @package   OpenEMR
 *
 * @link https://www.open-emr.org
 * @author RobinALG87
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tabs;

/**
 * Keep the patient dashboard as the active tab when a new top-level window
 * is opened from a patient list.  The tab shell activates the first entry in
 * the list, so appending this entry would display the user's default tab
 * (often Message Center or Calendar) instead of the selected patient.
 * The notes path must be relative to the project root because it is validated
 * by {@see DefaultTabsFilter} before the tab shell renders it.
 */
final class PatientDashboardTab
{
    /**
     * @param array<int, array<string, mixed>> $tabs
     * @return array<int, array<string, mixed>>
     */
    public static function prepend(array $tabs, string $notes, string $title): array
    {
        array_unshift($tabs, [
            'notes' => $notes,
            'id' => 'pat',
            'label' => $title,
        ]);

        return $tabs;
    }
}
