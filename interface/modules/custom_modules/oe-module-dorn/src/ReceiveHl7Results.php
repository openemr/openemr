<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\Dorn;

use Document;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Modules\Dorn\ConnectorApi;
use OpenEMR\Modules\Dorn\models\ReceiveResultsResponseModel;

class ReceiveHl7Results
{
    /**
     * This receives a single lab result base on guid, the catch here is that
     * a lab could be split!  if this happens then more than one result is
     * retrieved and not expected.
     */
    public function receiveResults($resultsGuid, $rejectResult): array
    {
        $labResults = ConnectorApi::getLabResults($resultsGuid);
        $responseModels = []; // Initialize an empty array to store response models

        if (empty($labResults)) {
            $rtv = new ReceiveResultsResponseModel();
            $rtv->isSuccess = false;
            $rtv->message = "No results found with GUID " . $resultsGuid;
            return [$rtv]; // Return an array with a single response model
        }

        foreach ($labResults as $result) {
            $receiveResultsResponseModel = $this->receiveSingleResults($result, $rejectResult);
            $responseModels[] = $receiveResultsResponseModel; // Add each response model to the array
        }

        return $responseModels;
    }

    public function receiveSingleResults($result, $rejectResult): ReceiveResultsResponseModel
    {
        $returnValue = new ReceiveResultsResponseModel();
        $resultPath = $GLOBALS['OE_SITE_DIR'] . "/documents/procedure_results";

        $orderNumber = $result->orderNumber;
        $patientId = $result->patientId;

        $returnValue->resultsGuid = $result->resultGuid;
        $returnValue->isUnsolicited = $result->isUnsolicited;


        $sql = "SELECT pp.name, pp.npi, pp.protocol, pp.remote_host, pp.send_fac_id, pp.recv_fac_id,
                pp.recv_app_id,pp.DorP, pp.direction, r.*
                FROM mod_dorn_routes r
                INNER JOIN procedure_providers pp ON
                    r.ppid = pp.ppid
                WHERE lab_guid = ?
                AND lab_account_number = ?
                LIMIT 1;";
        $record = sqlQuery($sql, [$result->labGuid, $result->accountNumber]);


        $ppid = (int)$record['ppid'];
        $send_account = $record['send_fac_id'];
        $recv_account = $record['recv_fac_id'];
        $lab_app = $record['recv_app_id'];
        $lab_npi = strtoupper(trim((string) $record['npi']));
        $lab_name = $record['name'];
        $direction = $record['direction'];
        $hl7 = '';

        $protocol = $record['protocol'];
        $remote_host = $record['remote_host'];
        $debug = trim((string) $record['DorP']) === 'D';

        $logpath = $GLOBALS['OE_SITE_DIR'] . "/documents/procedure_results/logs/$lab_npi";
        $prpath .= $resultPath . '/' . $ppid . '-' . $lab_npi;
        $file = "result_" . $orderNumber . ".hl7";

        $msg = $this->validatePaths($resultPath);
        if (!empty($msg)) {
            $returnValue->message = $msg;
            $returnValue->isSuccess = false;
            return $returnValue;
        }
        $msg = $this->validatePaths($prpath);
        if (!empty($msg)) {
            $returnValue->message = $msg;
            $returnValue->isSuccess = false;
            return $returnValue;
        }

        if (!empty($result->hl7Base64)) {
            $hl7 = base64_decode((string) $result->hl7Base64);
        } else {
            $returnValue->message = "HL7 appears to be empty";
            $returnValue->isSuccess = false;
            return $returnValue;
        }
        // If user requested reject and delete, do that.
        if ($rejectResult) {
            $fh = fopen("$prpath/$file.rejected", 'w');
            if ($fh) {
                $hl7_crypt = $this->hl7Crypt($hl7);
                fwrite($fh, $hl7_crypt);
                fclose($fh);
            } else {
                $returnValue->message = xl('Cannot create file') . ' "' . "$prpath/$file.rejected" . '"';
                $returnValue->isSuccess = false;
                return $returnValue;
            }
            ConnectorApi::sendAck($returnValue->resultsGuid, true, ['']);
            return $returnValue;
        }
        if (!$this->matchLab($hl7, $send_account, $recv_account, $lab_app, $lab_npi)) {
            $returnValue->message = "No Lab Match Account: $send_account\n";
            $returnValue->isSuccess = false;
            return $returnValue;
        }

        // Do a dry run of its contents and check for errors and match requests.
        $tmp = $this->receiveHl7Results($hl7, $info['match'], $ppid, $direction, true, $patientId);
        $returnValue->resultsParseMsg .= "Lab matched account $send_account. Results Dry Run Parse for Errors: ";

        if ($tmp['mssgs']) {
            $returnValue->resultsParseMsg .=  print_r($tmp['mssgs'], true);
            //should we say there are errors here an nack it?
            ConnectorApi::sendAck($returnValue->resultsGuid, true, [$tmp['mssgs']]);
            $returnValue->message = "No Lab Match Account: $send_account\n";
            $returnValue->isSuccess = false;
            return $returnValue;
        } else {
            $returnValue->resultsParseMsg .= "None";
        }

        // Now the money shot - not a dry run.
        $returnValue->resultsParseMsg .= "Lab matched account $send_account. Results Parse for Errors: ";
        $tmp = $this->receiveHl7Results($hl7, $info['match'], $ppid, $direction, false, $patientId);
        if ($tmp['mssgs']) {
            $returnValue->resultsParseMsg .=  print_r($tmp['mssgs'], true);
        } else {
            $returnValue->resultsParseMsg .= "None";
        }

        $returnValue->resultsParseMsg = $tmp['mssgs'];
        if (empty($tmp['fatal']) && empty($tmp['needmatch'])) {
            // It worked, archive and delete the file.
            $fh = fopen("$prpath/$file", 'w');
            if ($fh) {
                $hl7_crypt = $this->hl7Crypt($hl7);
                fwrite($fh, $hl7_crypt);
                fclose($fh);
                $log .= "Success Saved Results to: $file\n";
            } else {
                $returnValue->message = "Success but Couldn't Save File: $file\n";
                $returnValue->isSuccess = true;
                $returnValue->message .= xl('Cannot create file') . ' "' . "$prpath/$file" . '"';
                return $returnValue;
            }
        }
        $returnValue->isSuccess = true;
        $returnValue->message .= "Complete";
        ConnectorApi::sendAck($returnValue->resultsGuid, false, null);
        return $returnValue;
    }
    private function validatePaths($prpath)
    {
        if (!file_exists($prpath)) {
            if (!mkdir($prpath, 0755, true) && !is_dir($prpath)) {
                return "Directory " . $prpath . " was not created";
            }
        }
        return "";
    }
    private function receiveHl7Results(&$hl7, &$matchreq, $lab_id = 0, $direction = 'B', $dryrun = false, $patientId = null)
    {
        $log = '';
        global $rhl7_return;
        global $orphanLog;
        global $lab_npi;
        //$direction = 'R';
        // This will hold returned error messages and related variables.
        $rhl7_return = [];
        $rhl7_return['mssgs'] = [];
        $rhl7_return['needmatch'] = false; // indicates if this file is pending a match request

        $rhl7_segnum = 0;
        $obrPerformingOrganization = '';

        if (!str_starts_with((string) $hl7, 'MSH')) {
            return $this->rhl7LogMsg(xl('Input does not begin with a MSH segment'), true);
        }

        // This array holds everything to be written to the database.
        // We save and postpone these writes in case of errors while processing the message,
        // so we can look up data from parent results when child results are encountered,
        // and for other logic simplification.
        // Each element of this array is another array containing the following possible keys:
        // 'rep' - row of data to write to procedure_report
        // 'res' - array of rows to write to procedure_result for this procedure_report
        // 'fid' - unique lab-provided identifier for this report
        //
        $amain = [];

        // End-of-line delimiter for text in procedure_result.comments and other multi-line notes.
        $commentdelim = "\n";

        // Ensoftek: Different labs seem to send different EOLs. Edit HL7 input to a character we know.
        $hl7 = (string)str_replace(["\r\n", "\r", "\n"], "\r", $hl7);

        $today = time();
        $in_message_lab_name = '';
        $in_message_id = '';
        $in_ssn = '';
        $in_dob = '';
        $in_lname = '';
        $in_fname = '';
        $in_orderid = 0;
        $in_procedure_code = '';
        $in_report_status = '';
        $in_encounter = 0;
        $patient_id = 0; // for results-only patient matching logic
        $porow = false;
        $pcrow = false;
        $oprow = false;

        $code_seq_array = []; // tracks sequence numbers of order codes
        $results_category_id = 0;  // document category ID for lab results

        // This is so we know where we are if a segment like NTE that can appear in
        // different places is encountered.
        $context = '';

        // This will be "ORU" or "MDM".
        $msgtype = '';

        // Stuff collected for MDM documents.
        $mdm_datetime = '';
        $mdm_docname = '';
        $mdm_text = '';

        // Delimiters
        $d0 = "\r";
        $d1 = substr($hl7, 3, 1); // typically |
        $d2 = substr($hl7, 4, 1); // typically ^
        $d3 = substr($hl7, 5, 1); // typically ~
        $d4 = substr($hl7, 6, 1); // typically \
        $d5 = substr($hl7, 7, 1); // typically &

        // We'll need the document category IDs for any embedded documents.
        $catrow = sqlQuery(
            "SELECT id FROM categories WHERE name = ?",
            [$GLOBALS['lab_results_category_name']]
        );
        if (empty($catrow['id'])) {
            return $this->rhl7LogMsg(
                xl('Document category for lab results does not exist') .
                ': ' . $GLOBALS['lab_results_category_name'],
                true
            );
        } else {
            $results_category_id = $catrow['id'];
            $mdm_category_id = $results_category_id;
            $catrow = sqlQuery(
                "SELECT id FROM categories WHERE name = ?",
                [$GLOBALS['gbl_mdm_category_name']]
            );
            if (!empty($catrow['id'])) {
                $mdm_category_id = $catrow['id'];
            }
        }

        $segs = explode($d0, $hl7);

        foreach ($segs as $seg) {
            if (empty($seg)) {
                continue;
            }
            if ($seg == chr(28)) {
                continue;
            }
            // echo "<!-- $dryrun $seg -->\n"; // debugging

            ++$rhl7_segnum;
            $a = explode($d1, $seg);

            if ($a[0] == 'MSH') {
                if (!$dryrun) {
                    $this->rhl7FlushMain($amain, $commentdelim);
                }

                $amain = [];

                if ('MDM' == $msgtype && !$dryrun) {
                    $rc = $this->rhl7FlushMDM(
                        $patient_id,
                        $mdm_docname,
                        $mdm_datetime,
                        $mdm_text,
                        $mdm_category_id,
                        $oprow ? $oprow['username'] : 0
                    );
                    if ($rc) {
                        return $this->rhl7LogMsg($rc);
                    }

                    $patient_id = 0;
                }

                $context = $a[0];
                // Ensoftek: Could come is as 'ORU^R01^ORU_R01'. Handle all cases when 'ORU^R01' is seen.
                if (strstr($a[8], "ORU^R01")) {
                    $msgtype = 'ORU';
                } elseif (in_array($a[8], ['MDM^T02', 'MDM^T04', 'MDM^T08'])) {
                    $msgtype = 'MDM';
                    $mdm_datetime = '';
                    $mdm_docname = '';
                    $mdm_text = '';
                } elseif (strstr($a[8], "ORU")) { // Labcorp does not send trigger
                    $msgtype = 'ORU';
                } else {
                    return $this->rhl7LogMsg(xl('MSH.8 message type is not supported') . ": '" . $a[8] . "'", true);
                }

                $in_message_id = $a[9];
                $in_message_lab_name = $this->rhl7Text($a[3]);
            } elseif ($a[0] == 'PID') {
                $context = $a[0];

                if ('MDM' == $msgtype && !$dryrun) {
                    $rc = $this->rhl7FlushMDM(
                        $patient_id,
                        $mdm_docname,
                        $mdm_datetime,
                        $mdm_text,
                        $mdm_category_id,
                        $oprow ? $oprow['username'] : 0
                    );
                    if ($rc) {
                        return $this->rhl7LogMsg($rc);
                    }
                }
                $porow = false;
                $pcrow = false;
                $oprow = false;
                $in_orderid = 0;
                $in_ssn = preg_replace('/[^0-9]/', '', $a[4]);
                $in_dob = $this->rhl7Date($a[7]);
                // foreign MRN
                $in_pubpid = $this->rhl7Text($a[3] ?? '');
                $tmp = explode($d2, $a[11]);
                $in_street = $this->rhl7Text($tmp[0]) ?? '';
                $in_street1 = $this->rhl7Text($tmp[1] ?? '');
                $in_city = $this->rhl7Text($tmp[2] ?? '');
                $in_state = $this->rhl7Text($tmp[3] ?? '');
                $in_zip = $this->rhl7Text($tmp[4] ?? '');
                $in_phone = $this->rhl7Text($a[13]) ?? '';
                $in_sex = match (strtoupper($a[8])) {
                    'M' => 'Male',
                    'F' => 'Female',
                    'T' => 'Transgender',
                    default => 'Unassigned',
                };
                $tmp = explode($d2, $a[5]);
                $in_lname = $this->rhl7Text($tmp[0]);
                $in_fname = $this->rhl7Text($tmp[1]);
                $in_mname = $this->rhl7Text($tmp[2] ?? '');

                $patient_id = 0;

                // Patient matching is needed for a results-only interface or MDM message type.
                if ('R' == $direction || 'MDM' == $msgtype) {
                    $ptarr = [
                        'ss' => strtoupper((string) $in_ssn),
                        'fname' => $this->ucname($in_fname),
                        'lname' => $this->ucname($in_lname),
                        'mname' => $this->ucname($in_mname),
                        'DOB' => strtoupper((string) $in_dob),
                        'sex' => $in_sex,
                        'street' => $in_street,
                        'city' => $in_city,
                        'state' => $in_state,
                        'postal_code' => $in_zip,
                        'phone_home' => $in_phone,
                        'pubpid' => $in_pubpid
                    ];
                    $patient_id = $this->matchPatient($ptarr);
                    if ($patient_id == -1) {
                        // Result is indeterminate.
                        // Make a stringified form of $ptarr to use as a key.
                        $ptstring = serialize($ptarr);
                        // Check if the user has specified the patient.
                        if (isset($patientId)) {
                            // This will be an existing pid, or 0 to specify creating a patient.
                            $patient_id = intval($patientId);
                        } else {
                            if ($dryrun) {
                                // Nope, ask the user to match.
                                $matchreq[$ptstring] = true;
                                $rhl7_return['needmatch'] = true;
                            } else {
                                // Should not happen, but it would be bad to abort now.  Create the patient.
                                $patient_id = 0;
                                $this->rhl7LogMsg(
                                    xl('Unexpected non-match, creating new patient for segment') .
                                    ' ' . $rhl7_segnum,
                                    false
                                );
                            }
                        }
                    }

                    if ($patient_id == 0 && !$dryrun) {
                        // We must create the patient.
                        $patient_id = $this->createSkeletonPatient($ptarr);
                    }

                    if ($patient_id == -1) {
                        $patient_id = 0;
                    }
                } // end results-only/MDM logic
            } elseif ('PD1' == $a[0]) {
                // TBD: Save primary care provider name ($a[4]) somewhere?
            } elseif ('PV1' == $a[0]) {
                if ('ORU' == $msgtype) {
                    // Save placer encounter number if present.
                    if ($direction != 'R' && !empty($a[19])) {
                        $tmp = explode($d2, $a[19]);
                        $in_encounter = intval($tmp[0]);
                    }
                } elseif ('MDM' == $msgtype) {
                    // For documents we want the ordering provider.
                    // Try Referring Provider first.
                    $oprow = $this->matchProvider(explode($d2, $a[8]));
                    // If no match, try Other Provider.
                    if (empty($oprow)) {
                        $oprow = $this->matchProvider(explode($d2, $a[52]));
                    }
                }
            } elseif ('ORC' == $a[0] && 'ORU' == $msgtype) {
                $context = $a[0];
                $arep = [];
                $porow = false;
                $pcrow = false;
                if ($direction != 'R' && $a[2]) {
                    $in_orderid = intval($a[2]);
                }
            } elseif ('TXA' == $a[0] && 'MDM' == $msgtype) {
                $context = $a[0];
                $mdm_datetime = $this->rhl7DateTime($a[4]);
                $mdm_docname = $this->rhl7Text($a[12]);
            } elseif ($a[0] == 'NTE' && ($context == 'ORC' || $context == 'TXA')) {
                // Is this ever used?
            } elseif ('OBR' == $a[0] && 'ORU' == $msgtype) {
                $context = $a[0];
                $arep = [];
                if ($direction != 'R' && $a[2]) {
                    $in_orderid = intval($a[2]);
                    $porow = false;
                    $pcrow = false;
                }

                $tmp = explode($d2, $a[4]);
                $in_procedure_code = $tmp[0];
                $in_procedure_name = $this->rhl7Text($tmp[1]);
                $in_report_status = $this->rhl7ReportStatus($a[25]);
                if ($lab_npi == "QUEST") {
                    // for profiles that may return dif component codes for profile code.
                    $tmp = explode($d2, $a[20]);
                    if (!empty($tmp[3]) && !empty($tmp[4])) {
                        $in_procedure_code = $tmp[3];
                        $in_procedure_name = $tmp[4];
                    }
                }
                // Filler identifier is supposed to be unique for each incoming report.
                $in_filler_id = $a[3];
                // Child results will have these pointers to their parent.
                $in_parent_obrkey = '';
                $in_parent_obxkey = '';
                $parent_arep = false; // parent report, if any
                $parent_ares = false; // parent result, if any
                if (!empty($a[29])) {
                    // This is a child so there should be a parent.
                    $tmp = explode($d2, $a[29]);
                    $in_parent_obrkey = isset($tmp[1]) ? str_replace($d5, $d2, $tmp[1]) : '';
                    $tmp = explode($d2, $a[26]);
                    $in_parent_obxkey = (isset($tmp[0]) ? str_replace($d5, $d2, $tmp[0]) : '') . $d1 . ($tmp[1] ?? '');

                    // Look for the parent report.
                    foreach ($amain as $arr) {
                        if (isset($arr['fid']) && $arr['fid'] == $in_parent_obrkey) {
                            $parent_arep = $arr['rep'];
                            // Now look for the parent result within that report.
                            foreach ($arr['res'] as $tmpres) {
                                if (isset($tmpres['obxkey']) && $tmpres['obxkey'] == $in_parent_obxkey) {
                                    $parent_ares = $tmpres;
                                    break;
                                }
                            }

                            break;
                        }
                    }
                }

                if ($parent_arep) {
                    $in_orderid = $parent_arep['procedure_order_id'];
                }

                if ($direction == 'R') {
                    // Save their order ID to procedure_order.control_id.
                    // Look for an existing order using that plus lab_id.
                    // Ordering provider is OBR.16 (NPI^Last^First).
                    // Might not need to create a dummy encounter.
                    // Need also provider_id (probably), patient_id, date_ordered, lab_id.
                    // We have observation date/time in OBR.7.
                    // We have report date/time in OBR.22.
                    // We do not have an order date.

                    $external_order_id = empty((int)$a[2]) ? $a[3] : $a[2];
                    $porow = false;

                    if (!$in_orderid && $external_order_id) {
                        $porow = sqlQuery(
                            "SELECT * FROM procedure_order " .
                            "WHERE lab_id = ? AND control_id = ? " .
                            "ORDER BY procedure_order_id DESC LIMIT 1",
                            [$lab_id, $external_order_id]
                        );
                    }

                    if (!empty($porow)) {
                        $in_orderid = intval($porow['procedure_order_id']);
                    }

                    if (!$in_orderid) {
                        // Create order.
                        // Need to identify the ordering provider and, if possible, a recent encounter.
                        $datetime_report = $this->rhl7DateTime($a[22]);
                        $encounter_id = 0;
                        $provider_id = 0;
                        $external_id = $this->rhl7Text($a[3]) ?? null;
                        // Look for the most recent encounter within 30 days of the report date.
                        $encrow = sqlQuery(
                            "SELECT encounter FROM form_encounter WHERE " .
                            "pid = ? AND date <= ? AND DATE_ADD(date, INTERVAL 30 DAY) > ? " .
                            "ORDER BY date DESC, encounter DESC LIMIT 1",
                            [$patient_id, $datetime_report, $datetime_report]
                        );
                        if (!empty($encrow)) {
                            $encounter_id = intval($encrow['encounter']);
                            $provider_id = intval($encrow['provider_id'] ?? '');
                        }

                        if (!$provider_id) {
                            // Attempt ordering provider matching by name or NPI.
                            $oprow = $this->matchProvider(explode($d2, $a[16]));
                            if (!empty($oprow)) {
                                $provider_id = (int)$oprow['id'];
                                $provider_username = $oprow['username'];
                            }
                        }

                        if (!$dryrun) {
                            // create an encounter. I mean, why not...
                            if (empty($encrow) && !$encounter_id) {
                                $encounter_id = $this->createEncounter(
                                    $patient_id,
                                    $provider_id,
                                    $datetime_report,
                                    $in_message_lab_name
                                );
                            }
                            // Now create the procedure order.
                            $in_orderid = sqlInsert(
                                "INSERT INTO procedure_order SET " .
                                    "date_ordered   = ?, " .
                                    "provider_id    = ?, " .
                                    "lab_id         = ?, " .
                                    "date_collected = ?, " .
                                    "date_transmitted = ?, " .
                                    "patient_id     = ?, " .
                                    "encounter_id   = ?, " .
                                    "control_id     = ?, " .
                                    "external_id    = ?",
                                [
                                    $datetime_report,
                                    $provider_id,
                                    $lab_id,
                                    $this->rhl7DateTime($a[22]),
                                    $this->rhl7DateTime($a[7]),
                                    $patient_id,
                                    $encounter_id,
                                    $external_order_id,
                                    $external_id
                                ]
                            );
                            // If an encounter was identified then link the order to it.
                            $form_name = ($lab_npi > '' ? $lab_npi : "Auto Result") . "-" . $in_orderid;
                            if ($encounter_id && $in_orderid) {
                                addForm($encounter_id, $form_name, $in_orderid, "procedure_order", $patient_id);
                            }
                            // create a note to provider about these actions
                            $txdate = $this->rhl7DateTime($a[7]);
                            $ptext = "$orphanLog $form_name Order ordered by" .  ($provider_username ?? '') .
                                " with lab result file " .
                                "creation date on $datetime_report and specimen collections on $txdate has been created. " .
                                "Please review these items to ensure proper resolution of order results.";
                            $dumb = $this->labNotice($patient_id, $ptext, ($provider_username ?? ''), '', $in_message_lab_name);
                        }
                    } // end no $porow
                } // end results-only
                if (empty($porow)) {
                    $porow = sqlQuery(
                        "SELECT * FROM procedure_order WHERE " .
                        "procedure_order_id = ?",
                        [$in_orderid]
                    );
                    // The order must already exist. Currently we do not handle electronic
                    // results returned for manual orders.
                    if (empty($porow) && !($dryrun && $direction == 'R')) {
                        return $this->rhl7LogMsg(xl('Procedure order not found') . ": $in_orderid", true);
                    }

                    if ($in_encounter) {
                        if ($direction != 'R' && $porow['encounter_id'] != $in_encounter) {
                            return $this->rhl7LogMsg(
                                xl('Encounter ID') .
                                " '" . $porow['encounter_id'] . "' " .
                                xl('for OBR placer order number') .
                                " '$in_orderid' " .
                                xl('does not match the PV1 encounter number') .
                                " '$in_encounter'"
                            );
                        }
                    } else {
                        // They did not return an encounter number to verify, so more checking
                        // might be done here to make sure the patient seems to match.
                    }

                    // Save the lab's control ID if there is one.
                    $tmp = explode($d2, $a[3]);
                    $control_id = $tmp[0];
                    if ($control_id && empty($porow['control_id']) && $in_orderid) {
                        sqlStatement(
                            "UPDATE procedure_order SET control_id = ? WHERE " .
                            "procedure_order_id = ?",
                            [$control_id, $in_orderid]
                        );
                    }

                    $code_seq_array = [];
                }

                // Find the order line item (procedure code) that matches this result.
                // If there is more than one, then we select the one whose sequence number
                // is next after the last sequence number encountered for this procedure
                // code; this assumes that result OBRs are returned in the same sequence
                // as the corresponding OBRs in the order.
                if (!isset($code_seq_array[$in_procedure_code])) {
                    $code_seq_array[$in_procedure_code] = 0;
                }

                $pcquery = "SELECT pc.* FROM procedure_order_code AS pc " .
                    "WHERE pc.procedure_order_id = ? AND pc.procedure_code = ? " .
                    "ORDER BY (procedure_order_seq <= ?), procedure_order_seq LIMIT 1";
                $pcqueryargs = [$in_orderid, $in_procedure_code, $code_seq_array[$in_procedure_code]];
                $pcrow = sqlQuery($pcquery, $pcqueryargs);
                if (empty($pcrow)) {
                    // There is no matching procedure in the order, so it must have been
                    // added after the original order was sent, either as a manual request
                    // from the physician or as a "reflex" from the lab.
                    // procedure_source = '2' indicates this.
                    if (!$dryrun) {
                        $lkup = $this->lookupTestCode($lab_id, $in_procedure_code);
                        $code_type = ($lkup['procedure_type'] ?? '') ? trim($lkup['procedure_type']) : '';
                        $code_transport = ($lkup['transport'] ?? '') ? trim($lkup['transport']) : '';
                        sqlBeginTrans();
                        $procedure_order_seq = sqlQuery(
                            "SELECT IFNULL(MAX(procedure_order_seq),0) + 1 AS increment FROM procedure_order_code " .
                            "WHERE procedure_order_id = ? ",
                            [$in_orderid]
                        );
                        sqlInsert(
                            "INSERT INTO procedure_order_code SET " .
                            "procedure_order_id = ?, " .
                            "procedure_order_seq = ?, " .
                            "procedure_code = ?, " .
                            "procedure_name = ?, " .
                            "procedure_type = ?, " .
                            "transport = ?, " .
                            "procedure_source = '2'",
                            [
                                $in_orderid,
                                $procedure_order_seq['increment'],
                                $in_procedure_code,
                                $in_procedure_name,
                                $code_type,
                                $code_transport
                            ]
                        );
                        $pcrow = sqlQuery($pcquery, $pcqueryargs);
                        sqlCommitTrans();
                    } else {
                        // Dry run, make a dummy procedure_order_code row.
                        $pcrow = [
                            'procedure_order_id' => $in_orderid,
                            'procedure_order_seq' => 0, // TBD?
                        ];
                    }
                }
                if (!empty($a[21])) {
                    $obrPerformingOrganization = $this->getPerformingOrganizationDetails('', $a[21], '', $d2, $commentdelim);
                }
                $code_seq_array[$in_procedure_code] = 0 + $pcrow['procedure_order_seq'];
                $arep = [];
                $arep['procedure_order_id'] = $in_orderid;
                $arep['procedure_order_seq'] = $pcrow['procedure_order_seq'];
                $arep['date_collected'] = $this->rhl7DateTime($a[7]);
                $arep['date_collected_tz'] = $this->rhl7DateTimeZone($a[7]);
                $arep['date_report'] = $this->rhl7DateTime($a[22]);
                $arep['date_report_tz'] = $this->rhl7DateTimeZone($a[22]);
                $arep['report_status'] = $in_report_status;
                $arep['report_notes'] = '';
                $arep['specimen_num'] = '';

                // If this is a child report, add some info from the parent.
                if (!empty($parent_ares)) {
                    $arep['report_notes'] .= xl('This is a child of result') . ' ' .
                        $parent_ares['result_code'] . ' ' . xl('with value') . ' "' .
                        $parent_ares['result'] . '".' . "\n";
                }

                if (!empty($parent_arep)) {
                    $arep['report_notes'] .= $parent_arep['report_notes'];
                    $arep['specimen_num'] = $parent_arep['specimen_num'];
                }

                // Create the main array entry for this report and its results.
                $i = count($amain);
                $amain[$i] = [];
                $amain[$i]['rep'] = $arep;
                $amain[$i]['fid'] = $in_filler_id;
                $amain[$i]['res'] = [];
            } elseif ($a[0] == 'NTE' && $context == 'OBR') {
                // Append this note to those for the most recent report.
                $amain[count($amain) - 1]['rep']['report_notes'] .= $this->rhl7Text($a[3], true) . "\n";
            } elseif ('OBX' == $a[0] && 'ORU' == $msgtype) {
                $tmp = explode($d2, $a[3]);
                $result_code = $this->rhl7Text($tmp[0]);
                $result_text = $this->rhl7Text($tmp[1]);
                if ($lab_npi == "QUEST") {
                    if (empty($result_code)) {
                        $result_code = $this->rhl7Text($tmp[3]);
                    }
                    $result_text = $this->rhl7Text($tmp[4]);
                }
                // If this is a text result that duplicates the previous result except
                // for its value, then treat it as an extension of that result's value.
                $i = count($amain) - 1;
                $j = count($amain[$i]['res']) - 1;
                if (
                    $j >= 0 && $context == 'OBX' && $a[2] == 'TX'
                    && $amain[$i]['res'][$j]['result_data_type'] == 'L'
                    && $amain[$i]['res'][$j]['result_code'] == $result_code
                    && $amain[$i]['res'][$j]['date'] == $this->rhl7DateTime($a[14] ?? '')
                    && ($amain[$i]['res'][$j]['facility'] ?? '') == $this->rhl7Text($a[15] ?? '')
                    && $amain[$i]['res'][$j]['abnormal'] == $this->rhl7Abnormal($a[8] ?? '')
                    && $amain[$i]['res'][$j]['result_status'] == $this->rhl7ReportStatus($a[11] ?? '')
                ) {
                    $amain[$i]['res'][$j]['comments'] =
                        substr(
                            (string) $amain[$i]['res'][$j]['comments'],
                            0,
                            strlen((string) $amain[$i]['res'][$j]['comments'])
                        ) . $this->rhl7Text($a[5] ?? '') . $commentdelim;
                    continue;
                }

                $context = $a[0];
                $ares = [];
                $ares['result_data_type'] = substr($a[2], 0, 1); // N, S, F or E
                $ares['comments'] = $commentdelim;
                if ($a[2] == 'ED') {
                    // This is the case of results as an embedded document. We will create
                    // a normal patient document in the assigned category for lab results.
                    $tmp = explode($d2, $a[5]);
                    $fileext = strtolower($tmp[2]);
                    $filename = date("Ymd_His") . '_orderid_' . $in_orderid . '.' . $fileext;
                    if ($tmp[4] != 'EMBEDDED DOCUMENT') { // an OBX indicating a document in next Seg ZEF so pass this by.
                        $data = $this->rhl7DecodeData($tmp[3], $tmp[4]);
                        if ($data === false) {
                            return $this->rhl7LogMsg(xl('Invalid encapsulated data encoding type') . ': ' . $tmp[3]);
                        }
                        if (!$dryrun) {
                            $d = new Document();
                            $rc = $d->createDocument(
                                $porow['patient_id'],
                                $results_category_id, // TBD: Make sure not 0
                                $filename,
                                $this->rhl7MimeType($fileext),
                                $data
                            );
                            if ($rc) {
                                return $this->rhl7LogMsg($rc);
                            }

                            $ares['document_id'] = $d->get_id();
                        }
                    } // @todo suspect below!!
                    $ares['date'] = $arep['date_report']; // $arep is left over from the OBR logic.
                    // Append this result to those for the most recent report.
                    // Note the 'procedure_report_id' item is not yet present.
                    //$amain[count($amain) - 1]['res'][] = $ares;
                } elseif ($a[2] == 'CWE') {
                    $ares['result'] = $this->rhl7CWE($a[5], $d2);
                } elseif ($a[2] == 'SN') {
                    $ares['result'] = trim(str_replace($d2, ' ', $a[5]));
                } elseif ($a[2] == 'TX' || strlen($a[5]) > 200) {
                    // OBX-5 can be a very long string of text with "~" as line separators.
                    // The first line of comments is reserved for such things.
                    $ares['result_data_type'] = 'L';
                    $ares['result'] = '';
                    $vTx = empty($a[5]) ? $this->rhl7Text(str_replace('^', ' ', $a[3])) : $this->rhl7Text($a[5]);
                    $ares['comments'] = $vTx . $commentdelim;
                } else {
                    $ares['result'] = $this->rhl7Text($a[5]);
                }

                $ares['result_code'] = $result_code;
                $ares['result_text'] = $result_text;
                $ares['date'] = $this->rhl7DateTime($a[14] ?? '');
                //$ares['facility'] = $this->rhl7Text($a[15]);
                // Ensoftek: Units may have mutiple segments(as seen in MU2 samples), parse and take just first segment.
                $tmp = explode($d2, ($a[6] ?? ''));
                $ares['units'] = $this->rhl7Text($tmp[0]);
                $ares['range'] = $this->rhl7Text($a[7] ?? '');
                $ares['abnormal'] = $this->rhl7Abnormal($a[8] ?? ''); // values are lab dependent
                $ares['result_status'] = $this->rhl7ReportStatus($a[11] ?? '');

                // Ensoftek: Performing Organization Details.
                // Goes into "Pending Review/Patient Results--->Notes--->Facility" section.
                if (empty($obrPerformingOrganization)) {
                    $performingOrganization = $this->getPerformingOrganizationDetails(
                        $a[23] ?? '',
                        $a[24] ?? '',
                        $a[25] ?? '',
                        $d2,
                        $commentdelim
                    );
                } else {
                    $performingOrganization = $obrPerformingOrganization;
                }
                if (!empty($performingOrganization)) {
                    $ares['facility'] .= $performingOrganization . $commentdelim;
                }

                /****
                 * // Probably need a better way to report this, if it matters.
                 * if (!empty($a[19])) {
                 * $ares['comments'] .= xl('Analyzed') . ' ' . $this->rhl7DateTime($a[19]) . '.' . $commentdelim;
                 * }
                 ****/

                // obxkey is to allow matching this as a parent result.
                $ares['obxkey'] = $a[3] . $d1 . ($a[4] ?? '');

                // Append this result to those for the most recent report.
                // Note the 'procedure_report_id' item is not yet present.
                $amain[count($amain) - 1]['res'][] = $ares;
            } elseif ('OBX' == $a[0] && 'MDM' == $msgtype) {
                $context = $a[0];
                if ($a[2] == 'TX') {
                    if ($mdm_text !== '') {
                        $mdm_text .= "\r\n";
                    }

                    $mdm_text .= $this->rhl7Text($a[5]);
                } else {
                    return $this->rhl7LogMsg(xl('Unsupported MDM OBX result type') . ': ' . $a[2]);
                }
            } elseif ('ZEF' == $a[0] && 'ORU' == $msgtype) {
                // ZEF segment is treated like an OBX with an embedded Base64-encoded PDF.
                $context = 'OBX';
                $ares = [];
                $ares['result_data_type'] = 'E';
                $ares['comments'] = $commentdelim;
                //
                $fileext = 'pdf';
                $filename = date("Ymd_His") . '_orderid_' . $in_orderid . '.' . $fileext;
                $data = $this->rhl7DecodeData('Base64', $a[2]);
                if ($data === false) {
                    return $this->rhl7LogMsg(xl('ZEF segment internal error'));
                }

                if (!$dryrun) {
                    $d = new Document();
                    $rc = $d->createDocument(
                        $porow['patient_id'],
                        $results_category_id, // TBD: Make sure not 0
                        $filename,
                        $this->rhl7MimeType($fileext),
                        $data
                    );
                    if ($rc) {
                        return $this->rhl7LogMsg($rc);
                    }

                    $ares['document_id'] = $d->get_id();
                }

                $ares['date'] = $arep['date_report']; // $arep is left over from the OBR logic.
                // Append this result to those for the most recent report.
                // Note the 'procedure_report_id' item is not yet present.
                $amain[count($amain) - 1]['res'][] = $ares;
            } elseif ('NTE' == $a[0] && 'OBX' == $context && 'ORU' == $msgtype) {
                // Append this note to the most recent result item's comments.
                $alast = count($amain) - 1;
                $rlast = count($amain[$alast]['res']) - 1;
                $amain[$alast]['res'][$rlast]['comments'] .= $this->rhl7Text($a[3] ?? '', true) . $commentdelim;
                // Ensoftek: Get data from SPM segment for specimen.
                // SPM segment always occurs after the OBX segment.
            } elseif ('SPM' == $a[0] && 'ORU' == $msgtype) {
                $this->rhl7UpdateReportWithSpecimen($amain, $a, $d2);
                // Add code here for any other segment types that may be present.
                // Ensoftek: Get data from SPM segment for specimen. Comes in with MU2 samples, but can be ignored.
            } elseif ('TQ1' == $a[0] && 'ORU' == $msgtype) {
                // Ignore and do nothing.
            } elseif ($a[0] == 'NTE' && 'PID' == $context) {
                // will get orderid on save.
                if (!empty($amain[0])) {
                    $amain[0]['rep']['report_notes'] .= $this->rhl7Text($a[3], true) . "\n";
                }
            } elseif ('ZPS' == $a[0] && 'ORU' == $msgtype) {
                //global $ares;
                $performingOrganization = $this->parseZPS($a);
                if (!empty($performingOrganization)) {
                    $alast = count($amain) - 1;
                    $amain[$alast]['res'][0]['facility'] .= $performingOrganization . $commentdelim;
                }
            } else {
                return $this->rhl7LogMsg(xl('Segment name') . " '{$a[0]}' " . xl('is misplaced or unknown'));
            }
        }

        // Write all reports and their results to the database.
        // This will do nothing if a dry run or MDM message type.
        if ('ORU' == $msgtype && !$dryrun) {
            $this->rhl7FlushMain($amain, $commentdelim);
        }

        if ('MDM' == $msgtype && !$dryrun) {
            // Write documents.
            $rc = $this->rhl7FlushMDM(
                $patient_id,
                $mdm_docname,
                $mdm_datetime,
                $mdm_text,
                $mdm_category_id,
                $oprow ? $oprow['username'] : 0
            );
            if ($rc) {
                return $this->rhl7LogMsg($rc);
            }
        }

        return $rhl7_return;
    }

    /**
     * Look for a lab matching the given XCN field from some segment.
     *
     * @param  array $seg MSH seg identifying a provider.
     * @return mixed        TRUE, or FALSE if no match.
     */
    private function matchLab(&$hl7, $send_acct, $lab_acct = '', $lab_app = '', $lab_npi = '')
    {
        if (empty($hl7)) {
            return false;
        }
        $d0 = "\r";
        $d1 = substr((string) $hl7, 3, 1); // typically |

        $segs = explode($d0, (string) $hl7);
        $a = explode($d1, $segs[0]);
        if ($a[0] != 'MSH') {
            unset($segs);
            return false;
        }

        unset($segs);
        // CMS has deactivated NPI 1891752424, not sure who AMMON is
        if ($lab_npi == '1891752424' || strtoupper((string) $lab_npi) == 'AMMON') {
            if (strtoupper(trim($a[5])) == strtoupper(trim((string) $send_acct))) {
                $srch = '|' . strtoupper(trim((string) $send_acct)) . '-';
                $hl7 = str_replace($srch, "|", $hl7);
                return true;
            }
            return false;
        }

        if (
            strtoupper(trim($a[5])) == strtoupper(trim((string) $send_acct))
            || strtoupper(trim($a[3])) == strtoupper(trim((string) $lab_acct))
            || strtoupper(trim($a[2])) == strtoupper(trim((string) $lab_app))
        ) {
            return true;
        }
        return false;
    }

    private function parseZPS($segment)
    {
        $composites = $segment; //explode('|', $segment);

        // Try to parse composites
        foreach ($composites as $key => $composite) {
            // If it is a composite ...
            if (str_contains((string) $composite, '^')) {
                $composites[$key] = explode('^', (string) $composite);
            }
        }

        // Find out where we are
        $pos = 0;

        [
            $__garbage, // Skip index [0], it's the type
            $zps[$pos]['set_id'],
            $zps[$pos]['lab_id'],
            $zps[$pos]['lab_name'],
            $zps[$pos]['lab_address'],
            $zps[$pos]['lab_phone'],
            $__garbage,
            $zps[$pos]['lab_director'],
            $__garbage,
            $zps[$pos]['lab_clia']
        ] = $composites;
        $labdir = $zps[0]['lab_director'][0] . " " . $zps[0]['lab_director'][1] . ", " . $zps[0]['lab_director'][2];
        $address = $zps[0]['lab_address'][0] . "\n" . $zps[0]['lab_address'][2] . " " . $zps[0]['lab_address'][3] .
            " " . $zps[0]['lab_address'][4];
        $r = $zps[0]['lab_name'] . "\n" . $address . "\n" . $zps[0]['lab_phone'] . "\n" . $labdir . "\n";
        return $r;
    }


    private function rhl7LogMsg($msg, $fatal = true)
    {
        global $rhl7_return;
        if ($fatal) {
            $rhl7_return['mssgs'][] = '*' . $msg;
            $rhl7_return['fatal'] = true;
            EventAuditLogger::getInstance()->newEvent(
                "lab-results-error",
                $_SESSION['authUser'],
                $_SESSION['authProvider'],
                0,
                $msg
            );
        } else {
            $rhl7_return['mssgs'][] = '>' . $msg;
        }

        return $rhl7_return;
    }

    private function rhl7InsertRow(&$arr, $tablename)
    {
        if (empty($arr)) {
            return;
        }

        // echo "<!-- ";   // debugging
        // print_r($arr);
        // echo " -->\n";

        $query = "INSERT INTO $tablename SET";
        $binds = [];
        $sep = '';
        foreach ($arr as $key => $value) {
            $query .= "$sep `$key` = ?";
            $sep = ',';
            $binds[] = $value;
        }

        $arr = [];
        return sqlInsert($query, $binds);
    }

    // Write all of the accumulated reports and their results.
    private function rhl7FlushMain(&$amain, $commentdelim = "\n")
    {
        foreach ($amain as $arr) {
            if (!isset($amain[0]['rep']['procedure_order_id'])) {
                if (!isset($arr['rep']['procedure_order_id'])) {
                    continue;
                } elseif (isset($amain[0]['rep']['report_notes'])) {
                    $arr['rep']['report_notes'] .= $amain[0]['rep']['report_notes'];
                    unset($amain[0]);
                }
            }
            $procedure_report_id = $this->rhl7InsertRow($arr['rep'], 'procedure_report');
            foreach ($arr['res'] as $ares) {
                $ares['procedure_report_id'] = $procedure_report_id;
                // obxkey was used to identify parent results but is not stored.
                unset($ares['obxkey']);
                // If TX result is not over 10 characters, move it from comments to result field.
                if ($ares['result'] === '' && $ares['result_data_type'] == 'L') {
                    $i = strpos((string) $ares['comments'], (string) $commentdelim);
                    if ($i && $i <= 10) {
                        $ares['result'] = substr((string) $ares['comments'], 0, $i);
                        $ares['comments'] = substr((string) $ares['comments'], $i);
                    }
                }

                $this->rhl7InsertRow($ares, 'procedure_result');
            }
        }
    }

    // Write the MDM document if appropriate.
    //
    private function rhl7FlushMDM($patient_id, $mdm_docname, $mdm_datetime, $mdm_text, $mdm_category_id, $provider)
    {
        if ($patient_id) {
            if (!empty($mdm_docname)) {
                $mdm_docname .= '_';
            }

            $mdm_docname .= preg_replace('/[^0-9]/', '', (string) $mdm_datetime);
            $filename = $mdm_docname . '.txt';
            $d = new Document();
            $rc = $d->createDocument($patient_id, $mdm_category_id, $filename, 'text/plain', $mdm_text);
            if (!$rc) {
                $this->rhl7LogMsg(xl('Document created') . ": $filename", false);
                if ($provider) {
                    $d->postPatientNote($provider, $mdm_category_id, xl('Electronic document received'));
                    $this->rhl7LogMsg(xl('Notification sent to') . ": $provider", false);
                } else {
                    $this->rhl7LogMsg(xl('No provider was matched'), false);
                }
            }

            return $rc;
        }

        return '';
    }

    private function rhl7Text($s, $allow_newlines = false)
    {
        $s = str_replace('\\S\\', '^', $s);
        $s = str_replace('\\F\\', '|', $s);
        $s = str_replace('\\R\\', '~', $s);
        $s = str_replace('\\T\\', '&', $s);
        $s = str_replace('\\X0d\\', "\r", $s);
        $s = str_replace('\\E\\', '\\', $s);
        $s = $allow_newlines ? str_replace('\\.br\\', "\n", $s) : str_replace('\\.br\\', '~', $s);

        return $s;
    }

    private function rhl7DateTime($s)
    {
        // Remove UTC offset if present.
        if (preg_match('/^([0-9.]+)[+-]/', (string) $s, $tmp)) {
            $s = $tmp[1];
        }

        $s = preg_replace('/[^0-9]/', '', (string) $s);
        if (empty($s)) {
            return '0000-00-00 00:00:00';
        }

        $ret = substr($s, 0, 4) . '-' . substr($s, 4, 2) . '-' . substr($s, 6, 2);
        if (strlen($s) > 8) {
            $ret .= ' ' . substr($s, 8, 2) . ':' . substr($s, 10, 2) . ':';
            if (strlen($s) > 12) {
                $ret .= substr($s, 12, 2);
            } else {
                $ret .= '00';
            }
        }

        return $ret;
    }

    private function rhl7DateTimeZone($s)
    {
        // UTC offset if present always begins with "+" or "-".
        if (preg_match('/^[0-9.]+([+-].*)$/', (string) $s, $tmp)) {
            return trim($tmp[1]);
        }

        return '';
    }

    private function rhl7Date($s)
    {
        return substr((string) $this->rhl7DateTime($s), 0, 10);
    }

    private function rhl7Abnormal($s)
    {
        if ($s == '') {
            return 'no';
        }

        if ($s == 'N') {
            return 'no';
        }

        if ($s == 'A') {
            return 'yes';
        }

        if ($s == 'H') {
            return 'high';
        }

        if ($s == 'L') {
            return 'low';
        }

        if ($s == 'HH') {
            return 'vhigh';
        }

        if ($s == 'LL') {
            return 'vlow';
        }

        return $this->rhl7Text($s);
    }

    private function rhl7ReportStatus($s)
    {
        if ($s == 'F') {
            return 'final';
        }

        if ($s == 'P') {
            return 'prelim';
        }

        if ($s == 'C') {
            return 'correct';
        }

        if ($s == 'X') {
            return 'error';
        }

        return $this->rhl7Text($s);
    }

    /**
     * Convert a lower case file extension to a MIME type.
     * The extension comes from OBX[5][0] which is itself a huge assumption that
     * the HL7 2.3 standard does not help with. Don't be surprised when we have to
     * adapt to conventions of various other labs.
     *
     * @param  string $fileext The lower case extension.
     * @return string            MIME type.
     */
    private function rhl7MimeType($fileext)
    {
        if ($fileext == 'pdf') {
            return 'application/pdf';
        }

        if ($fileext == 'doc') {
            return 'application/msword';
        }

        if ($fileext == 'rtf') {
            return 'application/rtf';
        }

        if ($fileext == 'txt') {
            return 'text/plain';
        }

        if ($fileext == 'zip') {
            return 'application/zip';
        }

        return 'application/octet-stream';
    }

    /**
     * Extract encapsulated document data according to its encoding type.
     *
     * @param  string $enctype Encoding type from OBX[5][3].
     * @param  string &$src    Encoded data  from OBX[5][4].
     * @return string            Decoded data, or FALSE if error.
     */
    private function rhl7DecodeData($enctype, &$src)
    {
        if ($enctype == 'Base64') {
            return base64_decode($src);
        }

        if ($enctype == 'A') {
            return $this->rhl7Text($src);
        }

        if ($enctype == 'Hex') {
            $data = '';
            for ($i = 0; $i < strlen($src) - 1; $i += 2) {
                $data .= chr(hexdec($src[$i] . $src[$i + 1]));
            }

            return $data;
        }

        return false;
    }

    private function rhl7CWE($s, $componentdelimiter)
    {
        $out = '';
        if ($s === '') {
            return $out;
        }

        $arr = explode($componentdelimiter, (string) $s);
        if (!empty($arr[8])) {
            $out = $arr[8];
        } else {
            $out = $arr[0];
            if (isset($arr[1])) {
                $out .= " (" . $arr[1] . ")";
            }
        }

        return $out;
    }

    /**
     * Parse the SPM segment and get the specimen display name and update the table.
     *
     * @param string $specimen Encoding type from SPM.
     */
    private function rhl7UpdateReportWithSpecimen(&$amain, $specimen, $d2)
    {
        $specimen_display = '';

        // SPM4: Specimen Type: Example: 119297000^BLD^SCT^BldSpc^Blood^99USA^^^Blood Specimen
        $specimen_display = $this->rhl7CWE($specimen[4], $d2);

        $tmpnotes = xl('Specimen type') . ': ' . $specimen_display;
        $tmp = $this->rhl7CWE($specimen[21], $d2);
        if ($tmp) {
            $tmpnotes .= '; ' . xl('Rejected') . ': ' . $tmp;
        }

        $tmp = $this->rhl7CWE($specimen[24], $d2);
        if ($tmp) {
            $tmpnotes .= '; ' . xl('Condition') . ': ' . $tmp;
        }

        $alast = count($amain) - 1;
        $amain[$alast]['rep']['specimen_num'] = $specimen_display;
        $amain[$alast]['rep']['report_notes'] .= $this->rhl7Text($tmpnotes) . "\n";
    }

    /**
     * Get the Performing Lab Details from the OBX segment. Mandatory for MU2.
     *
     * @param string $obx23 Encoding type from OBX23.
     * @param string $obx23 Encoding type from OBX24.
     * @param string $obx23 Encoding type from OBX25.
     * @param string $obx23 New line character.
     */
    private function getPerformingOrganizationDetails($obx23, $obx24, $obx25, $componentdelimiter, $commentdelim)
    {
        $s = null;

        if (!empty($obx23) || !empty($obx24) || !empty($obx25)) {
            // Organization Name
            // OBX23 Example: "Century Hospital^^^^^NIST-AA-1&2.16.840.1.113883.3.72.5.30.1&ISO^XX^^^987"
            $obx23_segs = explode($componentdelimiter, $obx23);
            if (!empty($obx23_segs[0])) {
                $s .= $obx23_segs[0] . $commentdelim;
            }

            // Medical Director
            // OBX25 Example: "2343242^Knowsalot^Phil^J.^III^Dr.^^^NIST-AA-1&2.16.840.1.113883.3.72.5.30.1&ISO^L^^^DNSPM"
            //             Dr. Phil Knowsalot J. III
            if (!empty($obx25)) {
                $obx25_segs = explode($componentdelimiter, (string) $obx25);
                $s .= "$obx25_segs[5] $obx25_segs[2] $obx25_segs[1] $obx25_segs[3] $obx25_segs[4]" . $commentdelim;
            }

            // Organization Address
            // OBX24 Example: "2070 Test Park^^Los Angeles^CA^90067^USA^B^^06037"
            if (!empty($obx24)) {
                $obx24 = str_replace('~', ' ', $obx24);
                $obx24_segs = explode($componentdelimiter, $obx24);
                $s .= "$obx24_segs[0]$commentdelim$obx24_segs[1]$commentdelim$obx24_segs[2], " .
                    "$obx24_segs[3] $obx24_segs[4]$commentdelim$obx24_segs[5]$commentdelim";
                if (!empty($obx24_segs[6])) {
                    $s .= "$obx24_segs[6]$commentdelim";
                }
                if (!empty($obx24_segs[8])) {
                    $s .= "County/Parish Code: $obx24_segs[8]$commentdelim";
                }
            }
        }

        return $s;
    }

    /**
     * Look for a patient matching the given data.
     * Return values are:
     *  >0  Definite match, this is the pid.
     *   0  No patient is close to a match.
     *  -1  It's not clear if there is a match.
     */
    private function matchPatient($ptarr)
    {
        $in_ss = str_replace('-', '', $ptarr['ss']);
        $in_fname = $ptarr['fname'];
        $in_lname = $ptarr['lname'];
        $in_dob = $ptarr['DOB'];
        $in_sex = strtoupper((string) $ptarr['sex']) == 'M' ? 'Male' : 'Female'; // AND sex IS NOT NULL AND sex = ?

        $patient_id = 0;
        $res = sqlStatement(
            "SELECT pid FROM patient_data WHERE " .
            "((ss IS NULL OR ss = '' OR '' = ?) AND " .
            "fname IS NOT NULL AND fname != '' AND fname = ? AND " .
            "lname IS NOT NULL AND lname != '' AND lname = ? AND " .
            "DOB IS NOT NULL AND DOB = ?) OR " .
            "(ss IS NOT NULL AND ss != '' AND REPLACE(ss, '-', '') = ? AND (" .
            "fname IS NOT NULL AND fname != '' AND fname = ? OR " .
            "lname IS NOT NULL AND lname != '' AND lname = ? OR " .
            "DOB IS NOT NULL AND DOB = ?)) " .
            "ORDER BY ss DESC, pid DESC LIMIT 2",
            [$in_ss, $in_fname, $in_lname, $in_dob, $in_ss, $in_fname, $in_lname, $in_dob]
        );
        if (sqlNumRows($res) > 1) {
            // Multiple matches, so ambiguous.
            $patient_id = -1;
        } elseif (sqlNumRows($res) == 1) {
            // Got exactly one match, so use it.
            $tmp = sqlFetchArray($res);
            $patient_id = intval($tmp['pid']);
        } else {
            // No match good enough, figure out if there's enough ambiguity to ask the user.
            $tmp = sqlQuery(
                "SELECT pid FROM patient_data WHERE " .
                "(ss IS NOT NULL AND ss != '' AND REPLACE(ss, '-', '') = ?) OR " .
                "(fname IS NOT NULL AND fname != '' AND fname = ? AND " .
                "lname IS NOT NULL AND lname != '' AND lname = ?) OR " .
                "(DOB IS NOT NULL AND DOB = ?) " .
                "LIMIT 1",
                [$in_ss, $in_fname, $in_lname, $in_dob]
            );
            if (!empty($tmp['pid'])) {
                $patient_id = -1;
            }
        }

        return $patient_id;
    }

    private function lookupTestCode($labid, $procedure_code)
    {

        $query = "SELECT procedure_type_id, procedure_code, procedure_type, name, transport " .
            "FROM procedure_type WHERE " .
            "lab_id = ? AND " .
            "(procedure_type LIKE 'ord' OR procedure_type LIKE 'pro') AND " .
            "activity = 1 AND procedure_code = ? " .
            "LIMIT 1";
        $res = sqlQuery($query, [$labid, $procedure_code]);

        return $res;
    }

    // create encounter
    private function createEncounter($pid, $provider_id, $order_date, $lab_name)
    {
        global $orphanLog;
        $encounter = QueryUtils::generateId();
        addForm(
            $encounter,
            "Auto Generated Lab Encounter",
            sqlInsert(
                "INSERT INTO form_encounter SET " .
                "date = ?, " .
                "onset_date = '', " .
                "reason = ?, " .
                "sensitivity = 'normal', " .
                "referral_source = '', " .
                "pid = ?, " .
                "encounter = ?, " .
                "provider_id = ?",
                [
                    date('Y-m-d H:i:s', strtotime((string) $order_date)),
                    "Generated encounter for " . strtoupper((string) $lab_name) . " result",
                    $pid,
                    $encounter,
                    ($provider_id ?? '')
                ]
            ),
            "newpatient",
            $pid,
            0,
            date('Y-m-d'),
            'SYSTEM'
        );
        $orphanLog .= "New Encounter: $encounter created. ";
        return $encounter ?: 0;
    }

    // send a message (pnote) to provider to inform about orders creation
    private function labNotice($pid, $newtext, $assigned_to = 'admin', $datetime = '', $labname = '')
    {
        if ($pid > 999999990) {
            return;
        }

        $message_sender = $_SESSION['authUser'];
        $message_group = 'Default';
        $authorized = '0';
        $activity = '1';
        $title = 'Lab Results';
        $message_status = 'New';
        if (empty($datetime)) {
            $datetime = date('Y-m-d H:i:s');
        }

        if (!$assigned_to) {
            $assigned_to = $_SESSION['authUser'];
        }
        $notify = $assigned_to; //@todo get user lookup

        $body = date('Y-m-d H:i') . ' (' . $labname . ' to ' . $notify . ') ' . $newtext;

        return sqlInsert(
            "INSERT INTO pnotes (date, body, pid, user, groupname, " .
            "authorized, activity, title, assigned_to, message_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $datetime,
                $body,
                $pid,
                $message_sender,
                $message_group,
                $authorized,
                $activity,
                $title,
                $notify,
                $message_status
            ]
        );
    }
    /**
     * Look for a local provider matching the given XCN field from some segment.
     *
     * @param  array $arr array(NPI, lastname, firstname) identifying a provider.
     * @return mixed        Array(id, username), or FALSE if no match.
     */
    private function matchProvider($arr)
    {
        if (empty($arr)) {
            return false;
        }

        $op_lname = $op_fname = '';
        $op_npi = preg_replace('/[^0-9]/', '', (string) $arr[0]);
        if (!empty($arr[1])) {
            $op_lname = $arr[1];
        }

        if (!empty($arr[2])) {
            $op_fname = $arr[2];
        }

        if ($op_npi || ($op_fname && $op_lname)) {
            if ($op_npi) {
                if ($op_fname && $op_lname) {
                    $where = "((npi IS NOT NULL AND npi = ?) OR ((npi IS NULL OR npi = ?) AND lname = ? AND fname = ?))";
                    $qarr = [$op_npi, '', $op_lname, $op_fname];
                } else {
                    $where = "npi IS NOT NULL AND npi = ?";
                    $qarr = [$op_npi];
                }
            } else {
                $where = "lname = ? AND fname = ?";
                $qarr = [$op_lname, $op_fname];
            }

            $oprow = sqlQuery(
                "SELECT id, username FROM users WHERE " .
                "username IS NOT NULL AND username != '' AND $where " .
                "ORDER BY active DESC, authorized DESC, username, id LIMIT 1",
                $qarr
            );
            if (!empty($oprow)) {
                return $oprow;
            }
        }

        return false;
    }

    private function ucname($string)
    {
        $string = ucwords(strtolower((string) $string));

        foreach (['-', '\''] as $delimiter) {
            if (str_contains($string, $delimiter)) {
                $string = implode($delimiter, array_map(ucfirst(...), explode($delimiter, $string)));
            }
        }
        return $string;
    }

    /**
     * Create a patient using whatever patient_data attributes are provided.
     */
    private function createSkeletonPatient($patient_data)
    {
        global $orphanLog;
        $employer_data = [];
        $tmp = sqlQuery("SELECT MAX(pid)+1 AS pid FROM patient_data");
        $ptid = empty($tmp['pid']) ? 1 : intval($tmp['pid']);
        if (!isset($patient_data['pubpid'])) {
            $patient_data['pubpid'] = $ptid;
        }

        updatePatientData($ptid, $patient_data, true);
        updateEmployerData($ptid, $employer_data, true);
        newHistoryData($ptid);
        $tmp = "Pid: $ptid " . $patient_data['fname'] . ' ' . $patient_data['lname'] . ' ' . $patient_data['DOB'];
        $orphanLog .= "New Patient for $tmp created. ";
        return $ptid;
    }
    /**
     * Encrypt the content of the hl7 file if the global is turned on.
     *
     * @param  string $content The unencrypted content of the hl7.
     * @return string         The encrypted content of the hl7 if the global is set.
     */
    private function hl7Crypt($content)
    {
        if ($GLOBALS['drive_encryption']) {
            $content = (new CryptoGen())->encryptStandard($content, null, 'database');
        }

        return $content;
    }
}
