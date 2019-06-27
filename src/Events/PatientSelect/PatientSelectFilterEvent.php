<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientSelect;

use OpenEMR\Services\UserService;
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
     * @var null|UserService
     *
     */
    private $userService = null;

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
     * PatientSelectFilterEvent constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getUserService()
    {
        return $this->userService;
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

    public function setCustomWhereFilter($customWhereFilter)
    {
        $this->customWhereFilter = $customWhereFilter;
    }
}
