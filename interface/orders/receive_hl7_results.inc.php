<?php
/**
* Functions to support parsing and saving hl7 results.
*
* Copyright (C) 2013-2015 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

require_once("$srcdir/forms.inc");
require_once("$srcdir/classes/Document.class.php");

$rhl7_return = array();

function rhl7LogMsg($msg, $fatal=true) {
  global $rhl7_return;
  if ($fatal) {
    $rhl7_return['mssgs'][] = '*' . $msg;
    $rhl7_return['fatal'] = true;
    newEvent("lab-results-error", $_SESSION['authUser'], $_SESSION['authProvider'], 0, $msg);
  }
  else {
    $rhl7_return['mssgs'][] = '>' . $msg;
  }
  return $rhl7_return;
}

function rhl7InsertRow(&$arr, $tablename) {
  if (empty($arr)) return;

  // echo "<!-- ";   // debugging
  // print_r($arr);
  // echo " -->\n";

  $query = "INSERT INTO $tablename SET";
  $binds = array();
  $sep = '';
  foreach ($arr as $key => $value) {
    $query .= "$sep `$key` = ?";
    $sep = ',';
    $binds[] = $value;
  }
  $arr = array();
  return sqlInsert($query, $binds);
}

function rhl7FlushResult(&$ares) {
  return rhl7InsertRow($ares, 'procedure_result');
}

function rhl7FlushReport(&$arep) {
  return rhl7InsertRow($arep, 'procedure_report');
}

// Write the MDM document if appropriate.
//
function rhl7FlushMDM($patient_id, $mdm_docname, $mdm_datetime, $mdm_text, $mdm_category_id, $provider) {
  global $dryrun;
  if ($patient_id && !$dryrun) {
    if (!empty($mdm_docname)) $mdm_docname .= '_';
    $mdm_docname .= preg_replace('/[^0-9]/', '', $mdm_datetime);
    $filename = $mdm_docname . '.txt';
    $d = new Document();
    $rc = $d->createDocument($patient_id, $mdm_category_id, $filename, 'text/plain', $mdm_text);
    if (!$rc) {
      rhl7LogMsg(xl('Document created') . ": $filename", false);
      if ($provider) {
        $d->postPatientNote($provider, $mdm_category_id, xl('Electronic document received'));
        rhl7LogMsg(xl('Notification sent to') . ": $provider", false);
      }
      else {
        rhl7LogMsg(xl('No provider was matched'), false);
      }
    }
    return $rc;
  }
  return '';
}

function rhl7Text($s) {
  $s = str_replace('\\S\\'  ,'^' , $s);
  $s = str_replace('\\F\\'  ,'|' , $s);
  $s = str_replace('\\R\\'  ,'~' , $s);
  $s = str_replace('\\T\\'  ,'&' , $s);
  $s = str_replace('\\X0d\\',"\r", $s);
  $s = str_replace('\\E\\'  ,'\\', $s);
  return $s;
}

function rhl7DateTime($s) {
  $s = preg_replace('/[^0-9]/', '', $s);
  if (empty($s)) return '0000-00-00 00:00:00';
  $ret = substr($s, 0, 4) . '-' . substr($s, 4, 2) . '-' . substr($s, 6, 2);
  if (strlen($s) > 8) $ret .= ' ' . substr($s, 8, 2) . ':' . substr($s, 10, 2) . ':';
  if (strlen($s) > 12) {
    $ret .= substr($s, 12, 2);
  } else {
    $ret .= '00';
  }
  return $ret;
}

function rhl7Date($s) {
  return substr(rhl7DateTime($s), 0, 10);
}

function rhl7Abnormal($s) {
  if ($s == ''  ) return 'no';
  if ($s == 'N' ) return 'no';
  if ($s == 'A' ) return 'yes';
  if ($s == 'H' ) return 'high';
  if ($s == 'L' ) return 'low';
  if ($s == 'HH') return 'vhigh';
  if ($s == 'LL') return 'vlow';
  return rhl7Text($s);
}

function rhl7ReportStatus($s) {
  if ($s == 'F') return 'final';
  if ($s == 'P') return 'prelim';
  if ($s == 'C') return 'correct';
  return rhl7Text($s);
}

/**
 * Convert a lower case file extension to a MIME type.
 * The extension comes from OBX[5][0] which is itself a huge assumption that
 * the HL7 2.3 standard does not help with. Don't be surprised when we have to
 * adapt to conventions of various other labs.
 *
 * @param  string  $fileext  The lower case extension.
 * @return string            MIME type.
 */
function rhl7MimeType($fileext) {
  if ($fileext == 'pdf') return 'application/pdf';
  if ($fileext == 'doc') return 'application/msword';
  if ($fileext == 'rtf') return 'application/rtf';
  if ($fileext == 'txt') return 'text/plain';
  if ($fileext == 'zip') return 'application/zip';
  return 'application/octet-stream';
}

/**
 * Extract encapsulated document data according to its encoding type.
 *
 * @param  string  $enctype  Encoding type from OBX[5][3].
 * @param  string  &$src     Encoded data  from OBX[5][4].
 * @return string            Decoded data, or FALSE if error.
 */
function rhl7DecodeData($enctype, &$src) {
  if ($enctype == 'Base64') return base64_decode($src);
  if ($enctype == 'A'     ) return rhl7Text($src);
  if ($enctype == 'Hex') {
    $data = '';
    for ($i = 0; $i < strlen($src) - 1; $i += 2) {
      $data .= chr(hexdec($src[$i] . $src[$i+1]));
    }
    return $data;
  }
  return FALSE;
}

/**
 * Look for a patient matching the given data.
 * Return values are:
 *  >0  Definite match, this is the pid.
 *   0  No patient is close to a match.
 *  -1  It's not clear if there is a match.
 */
function match_patient($ptarr) {
  $in_ss = str_replace('-', '', $ptarr['ss']);
  $in_fname = $ptarr['fname'];
  $in_lname = $ptarr['lname'];
  $in_dob = $ptarr['DOB'];
  $patient_id = 0;
  $res = sqlStatement("SELECT pid FROM patient_data WHERE " .
    "((ss IS NULL OR ss = '' OR '' = ?) AND " .
    "fname IS NOT NULL AND fname != '' AND fname = ? AND " .
    "lname IS NOT NULL AND lname != '' AND lname = ? AND " .
    "DOB IS NOT NULL AND DOB = ?) OR " .
    "(ss IS NOT NULL AND ss != '' AND REPLACE(ss, '-', '') = ? AND (" .
    "fname IS NOT NULL AND fname != '' AND fname = ? OR " .
    "lname IS NOT NULL AND lname != '' AND lname = ? OR " .
    "DOB IS NOT NULL AND DOB = ?)) " .
    "ORDER BY ss DESC, pid DESC LIMIT 2",
    array($in_ss, $in_fname, $in_lname, $in_dob, $in_ss, $in_fname, $in_lname, $in_dob));
  if (sqlNumRows($res) > 1) {
    // Multiple matches, so ambiguous.
    $patient_id = -1;
  }
  else if (sqlNumRows($res) == 1) {
    // Got exactly one match, so use it.
    $tmp = sqlFetchArray($res);
    $patient_id = intval($tmp['pid']);
  }
  else {
    // No match good enough, figure out if there's enough ambiguity to ask the user.
    $tmp = sqlQuery("SELECT pid FROM patient_data WHERE " .
      "(ss IS NOT NULL AND ss != '' AND REPLACE(ss, '-', '') = ?) OR " .
      "(fname IS NOT NULL AND fname != '' AND fname = ? AND " .
      "lname IS NOT NULL AND lname != '' AND lname = ?) OR " .
      "(DOB IS NOT NULL AND DOB = ?) " .
      "LIMIT 1",
      array($in_ss, $in_fname, $in_lname, $in_dob));
    if (!empty($tmp['pid'])) {
      $patient_id = -1;
    }
  }
  return $patient_id;
}

/**
 * Look for a local provider matching the given XCN field from some segment.
 *
 * @param  array  $arr  array(NPI, lastname, firstname) identifying a provider.
 * @return mixed        Array(id, username), or FALSE if no match.
 */
function match_provider($arr) {
  if (empty($arr)) return false;
  $op_lname = $op_fname = '';
  $op_npi = preg_replace('/[^0-9]/', '', $arr[0]);
  if (!empty($arr[1])) $op_lname = $arr[1];
  if (!empty($arr[2])) $op_fname = $arr[2];
  if ($op_npi || ($op_fname && $op_lname)) {
    if ($op_npi) {
      if ($op_fname && $op_lname) {
        $where = "((npi IS NOT NULL AND npi = ?) OR ((npi IS NULL OR npi = ?) AND lname = ? AND fname = ?))";
        $qarr = array($op_npi, '', $op_lname, $op_fname);
      }
      else {
        $where = "npi IS NOT NULL AND npi = ?";
        $qarr = array($op_npi);
      }
    }
    else {
      $where = "lname = ? AND fname = ?";
      $qarr = array($op_lname, $op_fname);
    }
    $oprow = sqlQuery("SELECT id, username FROM users WHERE " .
      "username IS NOT NULL AND username != '' AND $where " .
      "ORDER BY active DESC, authorized DESC, username, id LIMIT 1",
      $qarr);
    if (!empty($oprow)) return $oprow;
  }
  return false;
}

/**
 * Create a patient using whatever patient_data attributes are provided.
 */
function create_skeleton_patient($patient_data) {
  $employer_data = array();
  $tmp = sqlQuery("SELECT MAX(pid)+1 AS pid FROM patient_data");
  $ptid = empty($tmp['pid']) ? 1 : intval($tmp['pid']);
  if (!isset($patient_data['pubpid'])) $patient_data['pubpid'] = $ptid;
  updatePatientData($ptid, $patient_data, true);
  updateEmployerData($ptid, $employer_data, true);
  newHistoryData($ptid);
  return $ptid;
}

/**
 * Parse and save.
 *
 * @param  string  &$hl7      The input HL7 text
 * @param  string  &$matchreq Array of shared patient matching requests
 * @param  int     $lab_id    Lab ID
 * @param  char    $direction B=Bidirectional, R=Results-only
 * @param  bool    $dryrun    True = do not update anything, just report errors
 * @param  array   $matchresp Array of responses to match requests; key is relative segment number,
 *                            value is an existing pid or 0 to specify creating a patient
 * @return array              Array of errors and match requests, if any
 */
function receive_hl7_results(&$hl7, &$matchreq, $lab_id=0, $direction='B', $dryrun=false, $matchresp=NULL) {
  global $rhl7_return;

  // This will hold returned error messages and related variables.
  $rhl7_return = array();
  $rhl7_return['mssgs'] = array();
  $rhl7_return['needmatch'] = false; // indicates if this file is pending a match request

  $rhl7_segnum = 0;

  if (substr($hl7, 0, 3) != 'MSH') {
    return rhl7LogMsg(xl('Input does not begin with a MSH segment'), true);
  }

  // End-of-line delimiter for text in procedure_result.comments
  $commentdelim = "\n";

  $today = time();

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
  $procedure_report_id = 0;
  $arep = array(); // holding area for OBR and its NTE data
  $ares = array(); // holding area for OBX and its NTE data
  $code_seq_array = array(); // tracks sequence numbers of order codes
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

  // We'll need the document category IDs for any embedded documents.
  $catrow = sqlQuery("SELECT id FROM categories WHERE name = ?",
    array($GLOBALS['lab_results_category_name']));
  if (empty($catrow['id'])) {
    return rhl7LogMsg(xl('Document category for lab results does not exist') .
      ': ' . $GLOBALS['lab_results_category_name'], true);
  }
  else {
    $results_category_id = $catrow['id'];
    $mdm_category_id = $results_category_id;
    $catrow = sqlQuery("SELECT id FROM categories WHERE name = ?",
      array($GLOBALS['gbl_mdm_category_name']));
    if (!empty($catrow['id'])) $mdm_category_id = $catrow['id'];
  }

  $segs = explode($d0, $hl7);

  foreach ($segs as $seg) {
    if (empty($seg)) continue;

    // echo "<!-- $dryrun $seg -->\n"; // debugging

    ++$rhl7_segnum;
    $a = explode($d1, $seg);

    if ($a[0] == 'MSH') {
      // The following 2 cases happen only for multiple MSH segments in the same file.
      // But that probably shouldn't happen anyway?
      if ('ORU' == $msgtype && !$dryrun) {
        rhl7FlushResult($ares);
        rhl7FlushReport($arep);
        $ares = array();
        $arep = array();
      }
      if ('MDM' == $msgtype && !$dryrun) {
        $rc = rhl7FlushMDM($patient_id, $mdm_docname, $mdm_datetime, $mdm_text, $mdm_category_id,
          $oprow ? $oprow['username'] : 0);
        if ($rc) return rhl7LogMsg($rc);
        $patient_id = 0;
      }
      $context = $a[0];
      if ($a[8] == 'ORU^R01') {
        $msgtype = 'ORU';
      }
      else if ($a[8] == 'MDM^T02' || $a[8] == 'MDM^T04' || $a[8] == 'MDM^T08') {
        $msgtype = 'MDM';
        $mdm_datetime = '';
        $mdm_docname = '';
        $mdm_text = '';
      }
      else {
        return rhl7LogMsg(xl('MSH.8 message type is not supported') . ": '" . $a[8] . "'", true);
      }
      $in_message_id = $a[9];
    }

    else if ($a[0] == 'PID') {
      $context = $a[0];
      if ('ORU' == $msgtype && !$dryrun) {
        // Note these calls do nothing if the passed array is empty.
        rhl7FlushResult($ares);
        rhl7FlushReport($arep);
      }
      if ('MDM' == $msgtype && !$dryrun) {
        $rc = rhl7FlushMDM($patient_id, $mdm_docname, $mdm_datetime, $mdm_text, $mdm_category_id,
          $oprow ? $oprow['username'] : 0);
        if ($rc) return rhl7LogMsg($rc);
      }
      $ares = array();
      $arep = array();
      $porow = false;
      $pcrow = false;
      $oprow = false;
      $in_orderid = 0;
      $in_ssn = preg_replace('/[^0-9]/', '', $a[4]);
      $in_dob = rhl7Date($a[7]);
      $tmp = explode($d2, $a[5]);
      $in_lname = rhl7Text($tmp[0]);
      $in_fname = rhl7Text($tmp[1]);
      $patient_id = 0;
      // Patient matching is needed for a results-only interface or MDM message type.
      if ('R' == $direction || 'MDM' == $msgtype) {
        $ptarr = array('ss' => strtoupper($in_ss), 'fname' => strtoupper($in_fname),
          'lname' => strtoupper($in_lname), 'DOB' => strtoupper($in_dob));
        $patient_id = match_patient($ptarr);
        if ($patient_id == -1) {
          // Result is indeterminate.
          // Make a stringified form of $ptarr to use as a key.
          $ptstring = serialize($ptarr);
          // Check if the user has specified the patient.
          if (isset($matchresp[$ptstring])) {
            // This will be an existing pid, or 0 to specify creating a patient.
            $patient_id = intval($matchresp[$ptstring]);
          }
          else {
            if ($dryrun) {
              // Nope, ask the user to match.
              $matchreq[$ptstring] = true;
              $rhl7_return['needmatch'] = true;
            }
            else {
              // Should not happen, but it would be bad to abort now.  Create the patient.
              $patient_id = 0;
              rhl7LogMsg(xl('Unexpected non-match, creating new patient for segment') .
                ' ' . $rhl7_segnum, false);
            }
          }
        }
        if ($patient_id == 0 && !$dryrun) {
          // We must create the patient.
          $patient_id = create_skeleton_patient($ptarr);
        }
        if ($patient_id == -1) $patient_id = 0;
      } // end results-only/MDM logic
    }

    else if ('PV1' == $a[0]) {
      if ('ORU' == $msgtype) {
        // Save placer encounter number if present.
        if ($direction != 'R' && !empty($a[19])) {
          $tmp = explode($d2, $a[19]);
          $in_encounter = intval($tmp[0]);
        }
      }
      else if ('MDM' == $msgtype) {
        // For documents we want the ordering provider.
        // Try Referring Provider first.
        $oprow = match_provider(explode($d2, $a[8]));
        // If no match, try Other Provider.
        if (empty($oprow)) $oprow = match_provider(explode($d2, $a[52]));
      }
    }

    else if ('ORC' == $a[0] && 'ORU' == $msgtype) {
      $context = $a[0];
      if (!$dryrun) rhl7FlushResult($ares);
      $ares = array();
      // Next line will do something only if there was a report with no results.
      if (!$dryrun) rhl7FlushReport($arep);
      $arep = array();
      $porow = false;
      $pcrow = false;
      if ($direction != 'R' && $a[2]) $in_orderid = intval($a[2]);
    }

    else if ('TXA' == $a[0] && 'MDM' == $msgtype) {
      $context = $a[0];
      $mdm_datetime = rhl7DateTime($a[4]);
      $mdm_docname = rhl7Text($a[12]);
    }

    else if ($a[0] == 'NTE' && ($context == 'ORC' || $context == 'TXA')) {
      // Is this ever used?
    }

    else if ('OBR' == $a[0] && 'ORU' == $msgtype) {
      $context = $a[0];
      if (!$dryrun) rhl7FlushResult($ares);
      $ares = array();
      // Next line will do something only if there was a report with no results.
      if (!$dryrun) rhl7FlushReport($arep);
      $arep = array();
      $procedure_report_id = 0;
      if ($direction != 'R' && $a[2]) {
        $in_orderid = intval($a[2]);
        $porow = false;
        $pcrow = false;
      }
      $tmp = explode($d2, $a[4]);
      $in_procedure_code = $tmp[0];
      $in_procedure_name = $tmp[1];
      $in_report_status = rhl7ReportStatus($a[25]);
      //
      if ($direction == 'R') {
        // $in_orderid will be 0 here.
        // Save their order ID to procedure_order.control_id.
        // That column will need to change from bigint to varchar.
        // Look for an existing order using that plus lab_id.
        // Ordering provider is OBR.16 (NPI^Last^First).
        // Might not need to create a dummy encounter.
        // Need also provider_id (probably), patient_id, date_ordered, lab_id.
        // We have observation date/time in OBR.7.
        // We have report date/time in OBR.22.
        // We do not have an order date.

        $external_order_id = empty($a[2]) ? $a[3] : $a[2];
        $porow = false;
        if ($external_order_id) {
          $porow = sqlQuery("SELECT * FROM procedure_order " .
            "WHERE lab_id = ? AND control_id = ? " .
            "ORDER BY procedure_order_id DESC LIMIT 1",
            array($lab_id, $external_order_id));
        }
        if (!empty($porow)) {
          $in_orderid = intval($porow['procedure_order_id']);
        }
        else {
          // Create order.
          // Need to identify the ordering provider and, if possible, a recent encounter.
          $datetime_report = rhl7DateTime($a[22]);
          $date_report = substr($datetime_report, 0, 10) . ' 00:00:00';
          $encounter_id = 0;
          $provider_id = 0;
          // Look for the most recent encounter within 30 days of the report date.
          $encrow = sqlQuery("SELECT encounter FROM form_encounter WHERE " .
            "pid = ? AND date <= ? AND DATE_ADD(date, INTERVAL 30 DAY) > ? " .
            "ORDER BY date DESC, encounter DESC LIMIT 1",
            array($patient_id, $date_report, $date_report));
          if (!empty($encrow)) {
            $encounter_id = intval($encrow['encounter']);
            $provider_id = intval($encrow['provider_id']);
          }
          if (!$provider_id) {
            // Attempt ordering provider matching by name or NPI.
            $oprow = match_provider(explode($d2, $a[16]));
            if (!empty($oprow)) $provider_id = intval($oprow['id']);
          }
          if (!$dryrun) {
            // Now create the procedure order.
            $in_orderid = sqlInsert("INSERT INTO procedure_order SET " .
              "date_ordered   = ?, " .
              "provider_id    = ?, " .
              "lab_id         = ?, " .
              "date_collected = ?, " .
              "date_transmitted = ?, " .
              "patient_id     = ?, " .
              "encounter_id   = ?, " .
              "control_id     = ?",
              array($datetime_report, $provider_id, $lab_id, rhl7DateTime($a[22]),
              rhl7DateTime($a[7]), $patient_id, $encounter_id, $external_order_id));
            // If an encounter was identified then link the order to it.
            if ($encounter_id && $in_orderid) {
              addForm($encounter_id, "Procedure Order", $in_orderid, "procedure_order", $patient_id);
            }
          }
        } // end no $porow
      } // end results-only
      if (empty($porow)) {
        $porow = sqlQuery("SELECT * FROM procedure_order WHERE " .
          "procedure_order_id = ?", array($in_orderid));
        // The order must already exist. Currently we do not handle electronic
        // results returned for manual orders.
        if (empty($porow) && !($dryrun && $direction == 'R')) {
          return rhl7LogMsg(xl('Procedure order not found') . ": $in_orderid", true);
        }
        if ($in_encounter) {
          if ($direction != 'R' && $porow['encounter_id'] != $in_encounter) {
            return rhl7LogMsg(xl('Encounter ID') .
              " '" . $porow['encounter_id'] . "' " .
              xl('for OBR placer order number') .
              " '$in_orderid' " .
              xl('does not match the PV1 encounter number') .
              " '$in_encounter'");
          }
        }
        else {
          // They did not return an encounter number to verify, so more checking
          // might be done here to make sure the patient seems to match.
        }
        // Save the lab's control ID if there is one.
        $tmp = explode($d2, $a[3]);
        $control_id = $tmp[0];
        if ($control_id && empty($porow['control_id'])) {
          sqlStatement("UPDATE procedure_order SET control_id = ? WHERE " .
            "procedure_order_id = ?", array($control_id, $in_orderid));
        }
        $code_seq_array = array();
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
      $pcqueryargs = array($in_orderid, $in_procedure_code, $code_seq_array[$in_procedure_code]);
      $pcrow = sqlQuery($pcquery, $pcqueryargs);
      if (empty($pcrow)) {
        // There is no matching procedure in the order, so it must have been
        // added after the original order was sent, either as a manual request
        // from the physician or as a "reflex" from the lab.
        // procedure_source = '2' indicates this.
        if (!$dryrun) {
          sqlInsert("INSERT INTO procedure_order_code SET " .
            "procedure_order_id = ?, " .
            "procedure_code = ?, " .
            "procedure_name = ?, " .
            "procedure_source = '2'",
            array($in_orderid, $in_procedure_code, $in_procedure_name));
          $pcrow = sqlQuery($pcquery, $pcqueryargs);
        }
        else {
          // Dry run, make a dummy procedure_order_code row.
          $pcrow = array(
            'procedure_order_id' => $in_orderid,
            'procedure_order_seq' => 0, // TBD?
          );
        }
      }
      $code_seq_array[$in_procedure_code] = 0 + $pcrow['procedure_order_seq'];
      $arep = array();
      $arep['procedure_order_id'] = $in_orderid;
      $arep['procedure_order_seq'] = $pcrow['procedure_order_seq'];
      $arep['date_collected'] = rhl7DateTime($a[7]);
      $arep['date_report'] = rhl7Date($a[22]);
      $arep['report_status'] = $in_report_status;
      $arep['report_notes'] = '';
    }

    else if ($a[0] == 'NTE' && $context == 'OBR') {
      $arep['report_notes'] .= rhl7Text($a[3]) . "\n";
    }

    else if ('OBX' == $a[0] && 'ORU' == $msgtype) {
      $context = $a[0];
      if (!$dryrun) rhl7FlushResult($ares);
      $ares = array();
      if (!$procedure_report_id) {
        if (!$dryrun) $procedure_report_id = rhl7FlushReport($arep);
        $arep = array();
      }
      $ares['procedure_report_id'] = $procedure_report_id;
      $ares['result_data_type'] = substr($a[2], 0, 1); // N, S, F or E
      $ares['comments'] = $commentdelim;
      if ($a[2] == 'ED') {
        // This is the case of results as an embedded document. We will create
        // a normal patient document in the assigned category for lab results.
        $tmp = explode($d2, $a[5]);
        $fileext = strtolower($tmp[0]);
        $filename = date("Ymd_His") . '.' . $fileext;
        $data = rhl7DecodeData($tmp[3], $tmp[4]);
        if ($data === FALSE) {
          return rhl7LogMsg(xl('Invalid encapsulated data encoding type') . ': ' . $tmp[3]);
        }
        if (!$dryrun) {
          $d = new Document();
          $rc = $d->createDocument($porow['patient_id'], $results_category_id, // TBD: Make sure not 0
            $filename, rhl7MimeType($fileext), $data);
          if ($rc) return rhl7LogMsg($rc);
          $ares['document_id'] = $d->get_id();
        }
      }
      else if (strlen($a[5]) > 200) {
        // OBX-5 can be a very long string of text with "~" as line separators.
        // The first line of comments is reserved for such things.
        $ares['result_data_type'] = 'L';
        $ares['result'] = '';
        $ares['comments'] = rhl7Text($a[5]) . $commentdelim;
      }
      else {
        $ares['result'] = rhl7Text($a[5]);
      }
      $tmp = explode($d2, $a[3]);
      $ares['result_code'] = rhl7Text($tmp[0]);
      $ares['result_text'] = rhl7Text($tmp[1]);
      $ares['date'] = rhl7DateTime($a[14]);
      $ares['facility'] = rhl7Text($a[15]);
      $ares['units'] = rhl7Text($a[6]);
      $ares['range'] = rhl7Text($a[7]);
      $ares['abnormal'] = rhl7Abnormal($a[8]); // values are lab dependent
      $ares['result_status'] = rhl7ReportStatus($a[11]);
    }

    else if ('OBX' == $a[0] && 'MDM' == $msgtype) {
      $context = $a[0];
      if ($a[2] == 'TX') {
        if ($mdm_text !== '') $mdm_text .= "\r\n";
        $mdm_text .= rhl7Text($a[5]);
      }
      else {
        return rhl7LogMsg(xl('Unsupported MDM OBX result type') . ': ' . $a[2]);
      }
    }

    else if ('ZEF' == $a[0] && 'ORU' == $msgtype) {
      // ZEF segment is treated like an OBX with an embedded Base64-encoded PDF.
      $context = 'OBX';
      if (!$dryrun) rhl7FlushResult($ares);
      $ares = array();
      if (!$procedure_report_id) {
        if (!$dryrun) $procedure_report_id = rhl7FlushReport($arep);
        $arep = array();
      }
      $ares['procedure_report_id'] = $procedure_report_id;
      $ares['result_data_type'] = 'E';
      $ares['comments'] = $commentdelim;
      //
      $fileext = 'pdf';
      $filename = date("Ymd_His") . '.' . $fileext;
      $data = rhl7DecodeData('Base64', $a[2]);
      if ($data === FALSE) return rhl7LogMsg(xl('ZEF segment internal error'));
      if (!$dryrun) {
        $d = new Document();
        $rc = $d->createDocument($porow['patient_id'], $results_category_id, // TBD: Make sure not 0
          $filename, rhl7MimeType($fileext), $data);
        if ($rc) return rhl7LogMsg($rc);
        $ares['document_id'] = $d->get_id();
      }
      $ares['date'] = $arep['date_report'];
    }

    else if ('NTE' == $a[0] && 'OBX' == $context && 'ORU' == $msgtype) {
      $ares['comments'] .= rhl7Text($a[3]) . $commentdelim;
    }

    // Add code here for any other segment types that may be present.

    else {
      return rhl7LogMsg(xl('Segment name') . " '${a[0]}' " . xl('is misplaced or unknown'));
    }
  }

  if ('ORU' == $msgtype && !$dryrun) {
    rhl7FlushResult($ares);
    // Next line does something only for a report with no results.
    rhl7FlushReport($arep);
  }

  if ('MDM' == $msgtype && !$dryrun) {
    $rc = rhl7FlushMDM($patient_id, $mdm_docname, $mdm_datetime, $mdm_text, $mdm_category_id,
      $oprow ? $oprow['username'] : 0);
    if ($rc) return rhl7LogMsg($rc);
  }

  return $rhl7_return;
}

/**
 * Poll all eligible labs for new results and store them in the database.
 *
 * @param  array   &$info  Conveys information to and from the caller:
 * FROM THE CALLER:
 * $info["$ppid/$filename"]['delete'] = a non-empty value if file deletion is requested.
 * $info['select'] = array of patient matching responses where key is serialized patient
 *   attributes and value is selected pid for this patient, or 0 to create the patient.
 * TO THE CALLER:
 * $info["$ppid/$filename"]['mssgs'] = array of messages from this function.
 * $info['match'] = array of patient matching requests where key is serialized patient
 *   attributes (ss, fname, lname, DOB) and value is TRUE (irrelevant).
 *
 * @return string  Error text, or empty if no errors.
 */
function poll_hl7_results(&$info) {
  global $srcdir;

  // echo "<!-- post: "; print_r($_POST); echo " -->\n"; // debugging
  // echo "<!-- in:   "; print_r($info); echo " -->\n"; // debugging

  $filecount = 0;
  $badcount = 0;

  if (!isset($info['match' ])) $info['match' ] = array(); // match requests
  if (!isset($info['select'])) $info['select'] = array(); // match request responses

  $ppres = sqlStatement("SELECT * FROM procedure_providers ORDER BY name");

  while ($pprow = sqlFetchArray($ppres)) {
    $ppid        = $pprow['ppid'];
    $protocol    = $pprow['protocol'];
    $remote_host = $pprow['remote_host'];
    $hl7 = '';

    if ($protocol == 'SFTP') {
      $remote_port = 22;
      // Hostname may have ":port" appended to specify a nonstandard port number.
      if ($i = strrpos($remote_host, ':')) {
        $remote_port = 0 + substr($remote_host, $i + 1);
        $remote_host = substr($remote_host, 0, $i);
      }
      ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . "$srcdir/phpseclib");
      require_once("$srcdir/phpseclib/Net/SFTP.php");
      // Compute the target path name.
      $pathname = '.';
      if ($pprow['results_path']) $pathname = $pprow['results_path'] . '/' . $pathname;
      // Connect to the server and enumerate files to process.
      $sftp = new Net_SFTP($remote_host, $remote_port);
      if (!$sftp->login($pprow['login'], $pprow['password'])) {
        return xl('Login to remote host') . " '$remote_host' " . xl('failed');
      }
      $files = $sftp->nlist($pathname);
      foreach ($files as $file) {
        if (substr($file, 0, 1) == '.') continue;
        ++$filecount;
        if (!isset($info["$ppid/$file"])) $info["$ppid/$file"] = array();
        // Ensure that archive directory exists.
        $prpath = $GLOBALS['OE_SITE_DIR'] . "/procedure_results";
        if (!file_exists($prpath)) mkdir($prpath);
        $prpath .= '/' . $pprow['ppid'];
        if (!file_exists($prpath)) mkdir($prpath);
        // Get file contents.
        $hl7 = $sftp->get("$pathname/$file");
        // If user requested reject and delete, do that.
        if (!empty($info["$ppid/$file"]['delete'])) {
          $fh = fopen("$prpath/$file.rejected", 'w');
          if ($fh) {
            fwrite($fh, $hl7);
            fclose($fh);
          }
          else {
            return xl('Cannot create file') . ' "' . "$prpath/$file.rejected" . '"';
          }
          if (!$sftp->delete("$pathname/$file")) {
            return xl('Cannot delete (from SFTP server) file') . ' "' . "$pathname/$file" . '"';
          }
          continue;
        }
        // Do a dry run of its contents and check for errors and match requests.
        $tmp = receive_hl7_results($hl7, $info['match'], $ppid, $pprow['direction'], true, $info['select']);
        $info["$ppid/$file"]['mssgs'] = $tmp['mssgs'];
        // $info["$ppid/$file"]['match'] = $tmp['match'];
        if (!empty($tmp['fatal']) || !empty($tmp['needmatch'])) {
          // There are errors or matching requests so skip this file.
          continue;
        }
        // Now the money shot - not a dry run.
        $tmp = receive_hl7_results($hl7, $info['match'], $ppid, $pprow['direction'], false, $info['select']);
        $info["$ppid/$file"]['mssgs'] = $tmp['mssgs'];
        // $info["$ppid/$file"]['match'] = $tmp['match'];
        if (empty($tmp['fatal']) && empty($tmp['needmatch'])) {
          // It worked, archive and delete the file.
          $fh = fopen("$prpath/$file", 'w');
          if ($fh) {
            fwrite($fh, $hl7);
            fclose($fh);
          }
          else {
            return xl('Cannot create file') . ' "' . "$prpath/$file" . '"';
          }
          if (!$sftp->delete("$pathname/$file")) {
            return xl('Cannot delete (from SFTP server) file') . ' "' . "$pathname/$file" . '"';
          }
        }
      } // end of this file
    } // end SFTP

    else if ($protocol == 'FS') {
      // Filesystem directory containing results files.
      $pathname = $pprow['results_path'];
      if (!($dh = opendir($pathname))) {
        return xl('Unable to access directory') . " '$pathname'";
      }
      // Sort by filename just because.
      $files = array();
      while (false !== ($file = readdir($dh))) {
        if (substr($file, 0, 1) == '.') continue;
        $files[$file] = $file;
      }
      closedir($dh);
      ksort($files);
      // For each file...
      foreach ($files as $file) {
        ++$filecount;
        if (!isset($info["$ppid/$file"])) $info["$ppid/$file"] = array();
        // Ensure that archive directory exists.
        $prpath = $GLOBALS['OE_SITE_DIR'] . "/procedure_results";
        if (!file_exists($prpath)) mkdir($prpath);
        $prpath .= '/' . $pprow['ppid'];
        if (!file_exists($prpath)) mkdir($prpath);
        // Get file contents.
        $hl7 = file_get_contents("$pathname/$file");
        // If user requested reject and delete, do that.
        if (!empty($info["$ppid/$file"]['delete'])) {
          $fh = fopen("$prpath/$file.rejected", 'w');
          if ($fh) {
            fwrite($fh, $hl7);
            fclose($fh);
          }
          else {
            return xl('Cannot create file') . ' "' . "$prpath/$file.rejected" . '"';
          }
          if (!unlink("$pathname/$file")) {
            return xl('Cannot delete file') . ' "' . "$pathname/$file" . '"';
          }
          continue;
        }
        // Do a dry run of its contents and check for errors and match requests.
        $tmp = receive_hl7_results($hl7, $info['match'], $ppid, $pprow['direction'], true, $info['select']);
        $info["$ppid/$file"]['mssgs'] = $tmp['mssgs'];
        // $info["$ppid/$file"]['match'] = $tmp['match'];
        if (!empty($tmp['fatal']) || !empty($tmp['needmatch'])) {
          // There are errors or matching requests so skip this file.
          continue;
        }
        // Now the money shot - not a dry run.
        $tmp = receive_hl7_results($hl7, $info['match'], $ppid, $pprow['direction'], false, $info['select']);
        $info["$ppid/$file"]['mssgs'] = $tmp['mssgs'];
        // $info["$ppid/$file"]['match'] = $tmp['match'];
        if (empty($tmp['fatal']) && empty($tmp['needmatch'])) {
          // It worked, archive and delete the file.
          $fh = fopen("$prpath/$file", 'w');
          if ($fh) {
            fwrite($fh, $hl7);
            fclose($fh);
          }
          else {
            return xl('Cannot create file') . ' "' . "$prpath/$file" . '"';
          }
          if (!unlink("$pathname/$file")) {
            return xl('Cannot delete file') . ' "' . "$pathname/$file" . '"';
          }
        }
      } // end of this file
    } // end FS protocol

    // TBD: Insert "else if ($protocol == '???') {...}" to support other protocols.

  } // end procedure provider

  // echo "<!-- out: "; print_r($info); echo " -->\n"; // debugging

  return '';
}
// PHP end tag omitted intentionally.
