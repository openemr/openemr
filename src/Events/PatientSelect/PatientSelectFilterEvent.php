<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientSelect;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for creating custom patient filters for patient select (New/Search) results
 *
 * @package OpenEMR\Events
 * @subpackage PatinetSelect
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class PatientSelectFilterEvent extends Event
{
    /**
     * The customWhereFilter event occurs in the patient_select.php script that generates
     * results for the legacy patient new/search dialog. Subscribe to this event and set a customWhereFilter to
     * alter the results of the patient finder query
     */
    const EVENT_HANDLE = 'patientSelect.customFilter';

    /**
     * @var array
     *
     * Custom where filter, applied "before" the user-generated filters through the UI.
     * This filters are hidden from the end user.
     *
     * This defaults to "1" so that effectively no filter is applied
     */
    private $customWhereFilter = "1";

    /**
     * Get an string representing a patient filter
     *
     * @return string
     */
    public function getCustomWhereFilter()
    {
        return $this->customWhereFilter;
    }

    /**
     * @param $customWhereFilter
     *
     * Add a custom filter to the WHERE clause of patient select query
     */
    public function setCustomWhereFilter($customWhereFilter)
    {
        $this->customWhereFilter = $customWhereFilter;
    }
}
