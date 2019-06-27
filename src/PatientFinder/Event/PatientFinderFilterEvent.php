<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PatientFinder\Event;

use OpenEMR\PatientFinder\ColumnFilter;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event object for creating custom patient filters for patient finder
 *
 * @package OpenEMR\PatientFinder
 * @subpackage Event
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class PatientFinderFilterEvent extends Event
{
    const EVENT_HANDLE = 'patient-finder.filter';

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

    public function __construct( $displayedColumns, $userColumnFilters = [] )
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
     * Get an string representing a patinet filter
     *
     * @return string
     */
    public function getCustomWhereFilter()
    {
        return $this->customWhereFilter;
    }

    public function setCustomWhereFilter( $customWhereFilter )
    {
        $this->customWhereFilter = $customWhereFilter;
    }

    /**
     * Add a column filter.
     *
     * Used by listeners to add additional column filters
     *
     */
    public function addCustomColumnFilter( ColumnFilter $columnFilter )
    {
        $this->customColumnFilters[]= $columnFilter;
    }
}
