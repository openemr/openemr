<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientFinder;

use OpenEMR\Events\AbstractBoundFilterEvent;
use OpenEMR\Events\BoundFilter;

/**
 * Event object for creating custom patient filters for patient finder
 *
 * @package OpenEMR\Events
 * @subpackage PatientFinder
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class PatientFinderFilterEvent extends AbstractBoundFilterEvent
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
     * PatientFinderFilterEvent constructor.
     * @param BoundFilter the filter object to be modified to create custom filter
     * @param $displayedColumns
     * @param array of ColumnFilter objects $userColumnFilters
     */
    public function __construct(BoundFilter $boundFilter, $displayedColumns, $userColumnFilters = [])
    {
        parent::__construct($boundFilter);
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
}
