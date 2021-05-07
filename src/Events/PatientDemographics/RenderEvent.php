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
class RenderEvent extends Event
{
    /**
     * This event occurs after a patient demographics section list has been rendered
     * It allows event listeners to render additional functionality after a section
     * list.
     */
    const EVENT_SECTION_LIST_RENDER_BEFORE = 'patientDemographics.render.section.before';

    /**
     * This event occurs after a patient demographics section list has been rendered
     * It allows event listeners to render additional functionality after a section
     * list.
     */
    const EVENT_SECTION_LIST_RENDER_AFTER = 'patientDemographics.render.section.after';

    /**
     * @var null|integer
     *
     * Represents the patient we are viewing in the patient demographics
     */
    private $pid = null;

    /**
     * constructor.
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
}
