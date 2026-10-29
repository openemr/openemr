<?php

/**
 * PatientFinderColumnRegistrationEvent
 *
 * Dispatched by both the Patient Finder page (dynamic_finder.php) and its
 * server-side endpoint (dynamic_finder_ajax.php) while the column set is being
 * assembled, after the core `ptlistcols` columns have been seeded. Listeners
 * append their own columns (see {@see PatientFinderColumn}) so a module can add
 * data drawn from tables other than `patient_data` — either as a searchable,
 * sortable SQL column or as a display-only column enriched in a batch via
 * {@see PatientFinderRowEnrichEvent}.
 *
 * The event carries the same registry instance on both the page and AJAX sides,
 * so the displayed columns and the queried columns stay in lockstep.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Events\PatientFinder;

use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistry;
use Symfony\Contracts\EventDispatcher\Event;

final class PatientFinderColumnRegistrationEvent extends Event
{
    /**
     * Subscribe to this event to add columns to the Patient Finder.
     */
    public const EVENT_HANDLE = 'patientFinder.registerColumns';

    public function __construct(private readonly PatientFinderColumnRegistry $registry)
    {
    }

    public function getRegistry(): PatientFinderColumnRegistry
    {
        return $this->registry;
    }

    /**
     * Convenience passthrough so a listener can add a column without first
     * reaching for the registry.
     */
    public function addColumn(PatientFinderColumn $column): void
    {
        $this->registry->add($column);
    }
}
