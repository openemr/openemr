<?php

/**
 * AppointmentJavascriptEventNames class holds an array of javascript event names
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Appointments;

class AppointmentJavascriptEventNames
{
    /**
     * This event is triggered in javascript when a patient is selected for an appointment in the add_edit_event.php class
     * It fires on the form element that contains the appointment data and bubbles up.
     * <example>
     * const form = document.querySelector("form"); // could also use body since this bubbles up
     * form.addEventListener("openemr:appointment:patient:set", (event) => {
     *  console.log(event.detail.form); // the form that the patient was set in
     *  console.log(event.detail.pid); // the pid of the patient that was set
     *  console.log(event.detail); // for the remainder of the data passed in the event
     * });
     * </example>
     * @see add_edit_event.php
     */
    const APPOINTMENT_PATIENT_SET_EVENT = 'openemr:appointment:patient:set';
}
