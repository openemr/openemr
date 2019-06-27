<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientDemographics;

use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\Event;

/**
 *  Event object for restricting access to users viewing patients' demographics screen
 *
 * @package OpenEMR\Events
 * @subpackage PatientDemographics
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class ViewEvent extends Event
{
    /**
     * The checkViewAuth event occurs when a user attempts to view a
     * patient record from the demographics screen
     */
    const EVENT_HANDLE = 'patientDemographics.view';

    /**
     * @var null|UserService
     *
     */
    private $userService = null;

    /**
     * @var array
     *
     *
     */
    private $patientService = null;

    /**
     * @var bool
     */
    private $authorized = true;

    /**
     *
     */
    public function __construct(UserService $userService, PatientService $patientService)
    {
        $this->userService = $userService;
        $this->patientService = $patientService;
    }

    public function getUserService()
    {
        return $this->userService;
    }

    public function authorized()
    {
        return $this->authorized;
    }
}
