<?php

/**
 * demographics_save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\ContactService;
use OpenEMR\Services\ContactAddressService;
use OpenEMR\Events\Patient\PatientUpdatedEventAux;
use OpenEMR\Common\Logging\SystemLogger;

// Initialize logger
$logger = new SystemLogger();

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

global $pid;

// Check authorization
if ($pid) {
    if (!AclMain::aclCheckCore('patients', 'demo', '', 'write')) {
        die(xlt('Updating demographics is not authorized.'));
    }

    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) {
        die(xlt('You are not authorized to access this squad.'));
    }
} else {
    if (!AclMain::aclCheckCore('patients', 'demo', '', ['write','addonly'])) {
        die(xlt('Adding demographics is not authorized.'));
    }
}

foreach ($_POST as $key => $val) {
    if ($val == "MM/DD/YYYY") {
        $_POST[$key] = "";
    }
}

// Update patient_data and employer_data
$newdata = [];
$newdata['patient_data']['id'] = $_POST['db_id'];
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' " .
  "ORDER BY group_id, seq");

$addressFieldsToSave = [];
while ($frow = sqlFetchArray($fres)) {
    $data_type = $frow['data_type'];
    if ((int)$data_type === 52) {
        continue;
    }
    $field_id = $frow['field_id'];
    $colname = $field_id;
    $table = 'patient_data';
    if (str_starts_with((string) $field_id, 'em_')) {
        $colname = substr((string) $field_id, 3);
        $table = 'employer_data';
    }

    // Handle address list fields (data_type 54)
    if ($data_type == 54) {
        try {
            // Try get_layout_form_value first
            $addressFieldsToSave[$field_id] = get_layout_form_value($frow);

            // DEBUG: Log what we received
            $logger->debug("Address field collected via get_layout_form_value", [
                'field_id' => $field_id,
                'data_type' => $data_type,
                'is_array' => is_array($addressFieldsToSave[$field_id]),
                'raw_data_keys' => is_array($addressFieldsToSave[$field_id]) ? array_keys($addressFieldsToSave[$field_id]) : 'not_array'
            ]);

            // Check if POST data exists and is different
            $post_field_name = "form_" . $field_id;
            if (isset($_POST[$post_field_name])) {
                $logger->debug("Raw POST data for address field EXISTS", [
                    'field_id' => $field_id,
                    'post_field_name' => $post_field_name,
                    'post_keys' => array_keys($_POST[$post_field_name])
                ]);

                // If get_layout_form_value returned empty or wrong format, use POST directly
                if (empty($addressFieldsToSave[$field_id]) || !is_array($addressFieldsToSave[$field_id]) || !isset($addressFieldsToSave[$field_id]['data_action'])) {
                    $logger->warning("get_layout_form_value returned invalid data, using POST directly");
                    $addressFieldsToSave[$field_id] = $_POST[$post_field_name];
                }
            } else {
                $logger->warning("POST data not found for field", [
                    'field_id' => $field_id,
                    'expected_post_key' => $post_field_name,
                    'available_post_keys' => array_keys($_POST)
                ]);
            }
        } catch (Exception $e) {
            $logger->error("Error collecting address field", [
                'field_id' => $field_id,
                'error' => $e->getMessage()
            ]);
        }

    } elseif (isset($_POST["form_$field_id"]) || $data_type == 21) {
        $newdata[$table][$colname] = get_layout_form_value($frow);
    }
}

// Save patient and employer data
try {
    updatePatientData($pid, $newdata['patient_data']);
    if (!$GLOBALS['omit_employers']) {
        updateEmployerData($pid, false, $newdata['employer_data']);
    }
} catch (Exception $e) {
    $logger->error("Error updating patient/employer data", [
        'pid' => $pid,
        'error' => $e->getMessage()
    ]);
    die("Error updating patient data: " . $e->getMessage());
}

// Handle address fields through ContactAddressService
if (!empty($addressFieldsToSave)) {
    try {
        $contactService = new ContactService();
        $contactAddressService = new ContactAddressService();

        $logger->info("Starting address save process", [
            'pid' => $pid,
            'field_count' => count($addressFieldsToSave)
        ]);

        foreach ($addressFieldsToSave as $fieldId => $addressFieldData) {
            try {
                $logger->debug("Processing address field", [
                    'field_id' => $fieldId,
                    'data_type' => gettype($addressFieldData),
                    'is_array' => is_array($addressFieldData),
                    'data_sample' => is_array($addressFieldData) ? array_keys($addressFieldData) : 'not_array'
                ]);

                if (is_array($addressFieldData) && !empty($addressFieldData)) {
                    // Log the structure
                    if (isset($addressFieldData['data_action'])) {
                        $logger->info("Address data structure detected", [
                            'field_id' => $fieldId,
                            'action_count' => count($addressFieldData['data_action']),
                            'actions' => $addressFieldData['data_action'],
                            'has_line_1' => isset($addressFieldData['line_1']),
                            'sample_keys' => array_keys($addressFieldData)
                        ]);
                    } else {
                        $logger->warning("No data_action found in address data", [
                            'field_id' => $fieldId,
                            'keys_found' => array_keys($addressFieldData)
                        ]);
                    }

                    // Get or create contact for this patient
                    $contact = $contactService->getOrCreateForEntity('patient_data', $pid);

                    if ($contact) {
                        // Use the generic method that works with any entity
                        $savedRecords = $contactAddressService->saveAddressesForContact(
                            $contact->get_id(),
                            $addressFieldData
                        );

                        $logger->info("Addresses saved successfully", [
                            'pid' => $pid,
                            'contact_id' => $contact->get_id(),
                            'field_id' => $fieldId,
                            'saved_count' => count($savedRecords)
                        ]);
                    } else {
                        $logger->error("Failed to get/create contact for patient", [
                            'pid' => $pid,
                            'field_id' => $fieldId
                        ]);
                    }
                } else {
                    $logger->warning("Address field data invalid", [
                        'field_id' => $fieldId,
                        'is_array' => is_array($addressFieldData),
                        'is_empty' => empty($addressFieldData)
                    ]);
                }
            } catch (Exception $e) {
                $logger->error("Exception processing address field", [
                    'field_id' => $fieldId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                error_log("Exception in address processing: " . $e->getMessage());
            }
        }
    } catch (Exception $e) {
        $logger->error("Fatal error in address processing", [
            'pid' => $pid,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        error_log("Fatal error in address processing: " . $e->getMessage());
    }
}

/**
 * Trigger events for listeners
 */
try {
    $GLOBALS["kernel"]->getEventDispatcher()->dispatch(
        new PatientUpdatedEventAux($pid, $_POST),
        PatientUpdatedEventAux::EVENT_HANDLE,
        10
    );
} catch (Exception $e) {
    $logger->error("Error dispatching event", [
        'error' => $e->getMessage()
    ]);
}

include_once("demographics.php");
