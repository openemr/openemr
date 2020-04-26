<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientDemographics;

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
     * @var null|integer
     *
     * Represents the patient we are considering access to
     */
    private $pid = null;

    /**
     * @var bool
     *
     * true if the  user is authorized, false ow
     */
    private $authorized = true;

    /**
     * UpdateEvent constructor.
     *
     * @param integer $pid Patient Identifier
     */
    public function __construct($pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return int|null
     *
     * Get the patient identifier of the patient we're attempting to view
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return bool
     *
     * Is user authorized to view patient?
     */
    public function authorized()
    {
        return $this->authorized;
    }

    /**
     * @param bool $authorized
     *
     * Use this function to set whether or not this user is authorized to view patient
     */
    public function setAuthorized($authorized)
    {
        $this->authorized = $authorized;
    }
}
