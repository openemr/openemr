<?php

/**
 * PatientReportEvent class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientReport;

use Symfony\Contracts\EventDispatcher\Event;

class PatientReportEvent extends Event
{
    /**
     * This event fires after the action buttons for the report have rendered.
     * It allows listeners to render additional content or buttons.
     */
    const ACTIONS_RENDER_POST = 'patientReport.actions.render.post';

    /**
     * This event fires after the document.ready event has fired in javascript and all of the javascript functions
     * that we want to execute on that ready event have rendered to the screen.  Listeners can insert additional
     * javascript onto the screen for the patient report.
     */
    const JAVASCRIPT_READY_POST = 'patientReport.javascript.load.post';
}
