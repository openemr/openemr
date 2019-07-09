<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientFinder;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for creating custom patient filters for patient finder
 *
 * @package OpenEMR\Events
 * @subpackage PatientFinder
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class PatientFinderFilterEvent extends Event
{
    /**
     * The customWhereFilter event occurs in the dynamic_finder_ajax.php script that generates
     * results for the patient finder. Subscribe to this event and set a customWhereFilter to
     * alter the results of the patient finder query
     */
    const EVENT_HANDLE = 'patientFinder.customFilter';

    /**
     * @var array
     *
     * Array of columns displayed on the UI of patient finder
     */
    private $displayedColumns = [];

    /**
     * @var array
     *
     * Array of ColumnFilters that the end-user has created through UI
     */
    private $userColumnFilters = [];

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
     * PatientFinderFilterEvent constructor.
     * @param $displayedColumns
     * @param array of ColumnFilter objects $userColumnFilters
     */
    public function __construct($displayedColumns, $userColumnFilters = [])
    {
        $this->displayedColumns = $displayedColumns;
        $this->userColumnFilters = $userColumnFilters;
    }

    public function getDisplayedColumns()
    {
        return $this->displayedColumns;
    }

    /**
     * Get an array of column filters
     *
     * @return array Array of user-generated column filters
     */
    public function getUserColumnFilters()
    {
        return $this->userColumnFilters;
    }

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
     * Add a custom filter to the WHERE clause of patient finder query
     */
    public function setCustomWhereFilter($customWhereFilter)
    {
        $this->customWhereFilter = $customWhereFilter;
    }
}
