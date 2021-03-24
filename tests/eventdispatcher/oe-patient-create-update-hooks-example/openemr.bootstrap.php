<?php

/**
 * Bootstrap custom Patient Update/Create Listener module
 *
 * This is the main file for the example module that demonstrates the ability
 * to listen for patient-update and patient-create events and perform additional
 * actions.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Events\Patient\PatientCreatedEvent;
use OpenEMR\Events\Patient\PatientUpdatedEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Get new or updated patient data and do something with it
 *
 * @param $patientData
 */
function send_patient_data_to_remote_system($patientData)
{
    // This is just a stub for example only
    // For example, you could write data to a file and send to a remote SFTP server
    // or build a remote API call.
    return;
}

/**
* This function is called when a patient is created, so we can do
 * any additional processing that a 3rd party may require. For example,
 * sending data to another system like Quickbooks
 *
 * @param PatientCreatedEvent $patientCreatedEvent
 * @return mixed
 */
function oe_module_custom_patient_created_action(PatientCreatedEvent $patientCreatedEvent)
{
    $patientData = $patientCreatedEvent->getPatientData();
    send_patient_data_to_remote_system($patientData);
    return $patientCreatedEvent;
}

/**
 * This function is called when a patient is updated, so we can do
 * any additional processing that a 3rd party may require. For example,
 * sending data to another system like Quickbooks
 *
 * @param PatientUpdatedEvent $patientUpdatedEvent
 * @return PatientUpdatedEvent
 */
function oe_module_custom_patient_update_action(PatientUpdatedEvent $patientUpdatedEvent)
{
    $patientData = $patientUpdatedEvent->getNewPatientData();
    send_patient_data_to_remote_system($patientData);
    return $patientUpdatedEvent;
}

// Listen for the patient update and create events
$eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
$eventDispatcher->addListener(PatientCreatedEvent::EVENT_HANDLE, 'oe_module_custom_patient_created_action');
$eventDispatcher->addListener(PatientUpdatedEvent::EVENT_HANDLE, 'oe_module_custom_patient_update_action');
