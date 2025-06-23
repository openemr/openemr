<?php

/**
* Functions to support HL7 order generation.
*
* Copyright (C) 2012-2013 Rod Roark <rod@sunsetsystems.com>
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

/*
* A bit of documentation that will need to go into the manual:
*
* The lab may want a list of your insurances for mapping into their system.
* To produce it, go into phpmyadmin and run this query:
*
* SELECT i.id, i.name, a.line1, a.line2, a.city, a.state, a.zip, p.area_code,
* p.prefix, p.number FROM insurance_companies AS i
* LEFT JOIN addresses AS a ON a.foreign_id = i.id
* LEFT JOIN phone_numbers AS p ON p.type = 2 AND p.foreign_id = i.id
* ORDER BY i.name, i.id;
*
* Then export as a CSV file and read it into your favorite spreadsheet app.
*/

require_once("$webserver_root/custom/code_types.inc.php");

use OpenEMR\Common\Logging\EventAuditLogger;

function hl7Text($s)
{
  // See http://www.interfaceware.com/hl7_escape_protocol.html:
    $s = str_replace('\\', '\\E\\', $s);
    $s = str_replace('^', '\\S\\', $s);
    $s = str_replace('|', '\\F\\', $s);
    $s = str_replace('~', '\\R\\', $s);
    $s = str_replace('&', '\\T\\', $s);
    $s = str_replace("\r", '\\X0d\\', $s);
    return $s;
}

function hl7Zip($s)
{
    return hl7Text(preg_replace('/[-\s]*/', '', $s));
}

function hl7Date($s)
{
    return preg_replace('/[^\d]/', '', $s);
}

function hl7Time($s)
{
    if (empty($s)) {
        return '';
    }

    return date('YmdHis', strtotime($s));
}

function hl7Sex($s)
{
    $s = strtoupper(substr($s, 0, 1));
    if ($s !== 'M' && $s !== 'F') {
        $s = 'U';
    }

    return $s;
}

function hl7Phone($s)
{
    if (preg_match("/([2-9]\d\d)\D*(\d\d\d)\D*(\d\d\d\d)\D*$/", $s, $tmp)) {
        return '(' . $tmp[1] . ')' . $tmp[2] . '-' . $tmp[3];
    }

    if (preg_match("/(\d\d\d)\D*(\d\d\d\d)\D*$/", $s, $tmp)) {
        return $tmp[1] . '-' . $tmp[2];
    }

    return '';
}

function hl7SSN($s)
{
    if (preg_match("/(\d\d\d)\D*(\d\d)\D*(\d\d\d\d)\D*$/", $s, $tmp)) {
        return $tmp[1] . '-' . $tmp[2] . '-' . $tmp[3];
    }

    return '';
}

function hl7Priority($s)
{
    return strtoupper(substr($s, 0, 1)) == 'H' ? 'S' : 'R';
}

function hl7Relation($s)
{
    $tmp = strtolower($s);
    if ($tmp == 'self' || $tmp == '') {
        return 'self';
    } elseif ($tmp == 'spouse') {
        return 'spouse';
    } elseif ($tmp == 'child') {
        return 'child';
    } elseif ($tmp == 'other') {
        return 'other';
    }

  // Should not get here so this will probably get noticed if we do.
    return $s;
}

/**
 * Get array of insurance payers for the specified patient as of the specified
 * date. If no date is passed then the current date is used.
 *
 * @param  integer $pid             Patient ID.
 * @param  date    $encounter_date  YYYY-MM-DD date.
 * @return array   Array containing an array of data for each payer.
 */
function loadPayerInfo($pid, $date = '')
{
    if (empty($date)) {
        $date = date('Y-m-d');
    }

    $payers = getEffectiveInsurances($pid, $date);

    foreach ($payers as $key => $drow) {
        // Very important to check for a missing provider because
        // that indicates no insurance as of the given date.
        if (empty($drow['provider'])) {
            continue;
        }

        $crow = sqlQuery(
            "SELECT * FROM insurance_companies WHERE id = ?",
            array($drow['provider'])
        );

        $orow = new InsuranceCompany($drow['provider']);
        $payers[$key] = array();
        $payers[$key]['data']    = $drow;
        $payers[$key]['company'] = $crow;
        $payers[$key]['object']  = $orow;
    }

    return $payers;
}

/**
 * Generate HL7 for the specified procedure order.
 *
 * @param  integer $orderid  Procedure order ID.
 * @param  string  &$out     Container for target HL7 text.
 * @return string            Error text, or empty if no errors.
 */
function gen_hl7_order($orderid, &$out)
{

  // Delimiters
    $d0 = "\r";
    $d1 = '|';
    $d2 = '^';

    $today = time();
    $out = '';

    $porow = sqlQuery(
        "SELECT " .
        "po.*, " .
        "pp.*, " .
        "pd.pid, pd.pubpid, pd.fname, pd.lname, pd.mname, pd.DOB, pd.ss, " .
        "pd.phone_home, pd.phone_biz, pd.sex, pd.street, pd.city, pd.state, pd.postal_code, " .
        "f.encounter, u.fname AS docfname, u.lname AS doclname, u.npi AS docnpi " .
        "FROM procedure_order AS po, procedure_providers AS pp, " .
        "forms AS f, patient_data AS pd, users AS u " .
        "WHERE " .
        "po.procedure_order_id = ? AND " .
        "pp.ppid = po.lab_id AND " .
        "f.formdir = 'procedure_order' AND " .
        "f.form_id = po.procedure_order_id AND " .
        "pd.pid = f.pid AND " .
        "u.id = po.provider_id",
        array($orderid)
    );
    if (empty($porow)) {
        return "Procedure order, ordering provider or lab is missing for order ID '$orderid'";
    }

    $pcres = sqlStatement(
        "SELECT " .
        "pc.procedure_code, pc.procedure_name, pc.procedure_order_seq, pc.diagnoses " .
        "FROM procedure_order_code AS pc " .
        "WHERE " .
        "pc.procedure_order_id = ? AND " .
        "pc.do_not_send = 0 " .
        "ORDER BY pc.procedure_order_seq",
        array($orderid)
    );

    $padOrderId = trim($porow['send_fac_id']) . "-" . trim(str_pad((string)$orderid, 4, "0", STR_PAD_LEFT));
  // Message Header
    $out .= "MSH" .
    $d1 . "$d2~\\&" .               // Encoding Characters (delimiters)
    $d1 . $porow['send_app_id'] .   // Sending Application ID
    $d1 . $porow['send_fac_id'] .   // Sending Facility ID
    $d1 . $porow['recv_app_id'] .   // Receiving Application ID
    $d1 . $porow['recv_fac_id'] .   // Receiving Facility ID
    $d1 . date('YmdHis', $today) .  // Date and time of this message
    $d1 .
    $d1 . 'ORM' . $d2 . 'O01' .     // Message Type
    $d1 . $padOrderId .  // Unique Message Number
    $d1 . $porow['DorP'] .          // D=Debugging, P=Production
    $d1 . '2.3' .                   // HL7 Version ID
    $d0;

  // Patient Identification
    $out .= "PID" .
    $d1 . "1" .                      // Set ID (always just 1 of these)
    $d1 . $porow['pid'] .            // Patient ID (not required)
    $d1 . $porow['pid'] .            // Patient ID (required)
    $d1 .                            // Alternate Patient ID (not required)
    $d1 . hl7Text($porow['lname']) .
    $d2 . hl7Text($porow['fname']);
    if ($porow['mname']) {
        $out .= $d2 . hl7Text($porow['mname']);
    }

    $out .=
    $d1 .
    $d1 . hl7Date($porow['DOB']) .   // DOB
    $d1 . hl7Sex($porow['sex'])  .   // Sex: M, F or U
    $d1 . $d1 .
    $d1 . hl7Text($porow['street']) .
    $d2 .
    $d2 . hl7Text($porow['city']) .
    $d2 . hl7Text($porow['state']) .
    $d2 . hl7Zip($porow['postal_code']) .
    $d1 .
    $d1 . hl7Phone($porow['phone_home']) .
    $d1 . hl7Phone($porow['phone_biz']) .
    $d1 . $d1 . $d1 .
    $d1 . $porow['encounter'] .
    $d1 . hl7SSN($porow['ss']) .
    $d1 . $d1 . $d1 .
    $d0;

    // NTE segment(s).
    $msql = sqlStatement("SELECT drug FROM prescriptions WHERE active=1 AND patient_id=?", [$porow['pid']]);
    $drugs = array();
    while ($mres = sqlFetchArray($msql)) {
        $drugs[] = trim($mres['drug']);
    }
    $med_list = count($drugs) > 0 ? implode(",", $drugs) : 'NONE';

    $out .= "NTE" .
        $d1 . "1" .
        $d1 . "L" .
        $d1 . "medications " . $med_list .
        $d0;

  // Patient Visit.
    $out .= "PV1" .
    $d1 . "1" .                           // Set ID (always just 1 of these)
    $d1 .                                 // Patient Class (if required, O for Outpatient)
    $d1 .                                 // Patient Location (for inpatient only?)
    $d1 . $d1 . $d1 .
    $d1 . hl7Text($porow['docnpi']) .     // Attending Doctor ID
    $d2 . hl7Text($porow['doclname']) . // Last Name
    $d2 . hl7Text($porow['docfname']) . // First Name
    str_repeat($d1, 11) .                 // PV1 8 to 18 all empty
    $d1 . $porow['encounter'] .           // Encounter Number
    str_repeat($d1, 13) .                 // PV1 20 to 32 all empty
    $d0;

  // Insurance stuff.
    $ins_type = trim($porow['billing_type']);
    $payers = loadPayerInfo($porow['pid'], $porow['date_ordered']);
    $setid = 0;
    if ($ins_type == 'T') {
        // only send primary and secondary insurance
        foreach ($payers as $payer) {
            $payer_object = $payer['object'];
            $payer_address = $payer_object->get_address();
            $out .= "IN1" .
                $d1 . ++$setid .                                // Set ID
                $d1 .                                           // Insurance Plan Identifier ??
                $d1 . hl7Text($payer['company']['ins_comp_id']) .  // Insurance Company ID
                $d1 . hl7Text($payer['company']['name']) .    // Insurance Company Name
                $d1 . hl7Text($payer_address->get_line1()) .    // Street Address
                $d2 .
                $d2 . hl7Text($payer_address->get_city()) .   // City
                $d2 . hl7Text($payer_address->get_state()) .  // State
                $d2 . hl7Zip($payer_address->get_zip()) .     // Zip Code
                $d1 .
                $d1 . hl7Phone($payer_object->get_phone()) .    // Phone Number
                $d1 . hl7Text($payer['data']['group_number']) . // Insurance Company Group Number
                str_repeat($d1, 7) .                            // IN1 9-15 all empty
                $d1 . hl7Text($payer['data']['subscriber_lname']) .   // Insured last name
                $d2 . hl7Text($payer['data']['subscriber_fname']) . // Insured first name
                $d2 . hl7Text($payer['data']['subscriber_mname']) . // Insured middle name
                $d1 . hl7Relation($payer['data']['subscriber_relationship']) .
                $d1 . hl7Date($payer['data']['subscriber_DOB']) .     // Insured DOB
                $d1 . hl7Date($payer['data']['subscriber_street']) .  // Insured Street Address
                $d2 .
                $d2 . hl7Text($payer['data']['subscriber_city']) .  // City
                $d2 . hl7Text($payer['data']['subscriber_state']) . // State
                $d2 . hl7Zip($payer['data']['subscriber_postal_code']) . // Zip
                $d1 .
                $d1 .
                $d1 . $setid .                                  // 1=Primary, 2=Secondary, 3=Tertiary
                str_repeat($d1, 13) .                           // IN1-23 to 35 all empty
                $d1 . hl7Text($payer['data']['policy_number']) . // Policy Number
                str_repeat($d1, 12) .                           // IN1-37 to 48 all empty
                $d0;
            if ($setid === 2) {
                break;
            }
        }
        if ($setid === 0) {
            return "\nInsurance is being billed but patient does not have any payers on record!";
        }
    } else { // no insurance record
        ++$setid;
        $out .= "IN1|$setid||||||||||||||||||||||||||||||||||||||||||||||$ins_type" . $d0;
    }
    if ($ins_type != 'C') {
        $out .= "GT1" .
            $d1 . "1" .                      // Set ID (always just 1 of these)
            $d1 .
            $d1 . hl7Text($porow['lname']) .
            $d2 . hl7Text($porow['fname']);
        if ($porow['mname']) {
            $out .= $d2 . hl7Text($porow['mname']);
        }

        $out .=
            $d1 .
            $d1 . hl7Text($porow['street']) .
            $d2 .
            $d2 . hl7Text($porow['city']) .
            $d2 . hl7Text($porow['state']) .
            $d2 . hl7Zip($porow['postal_code']) .
            $d1 . hl7Phone($porow['phone_home']) .
            $d1 . hl7Phone($porow['phone_biz']) .
            $d1 . hl7Date($porow['DOB']) .   // DOB
            $d1 . hl7Sex($porow['sex']) .   // Sex: M, F or U
            $d1 .
            $d1 . 'self' .                   // Relationship
            $d1 . hl7SSN($porow['ss']) .
            $d0;
    }
  // Common Order.
    $out .= "ORC" .
    $d1 . "NW" .                     // New Order
    $d1 . $padOrderId . // Placer Order Number
    str_repeat($d1, 6) .             // ORC 3-8 not used
    $d1 . date('YmdHis') .           // Transaction date/time
    $d1 . $d1 .
    $d1 . hl7Text($porow['docnpi']) .     // Ordering Provider
      $d2 . hl7Text($porow['doclname']) . // Last Name
      $d2 . hl7Text($porow['docfname']) . // First Name
    str_repeat($d1, 7) .             // ORC 13-19 not used
    $d1 . "2" .                      // ABN Status: 2 = Notified & Signed, 4 = Unsigned
    $d0;

    $setid = 0;
    while ($pcrow = sqlFetchArray($pcres)) {
        // Observation Request.
        $out .= "OBR" .
        $d1 . ++$setid .                              // Set ID
        $d1 . $padOrderId . // Placer Order Number
        $d1 .
        $d1 . hl7Text($pcrow['procedure_code']) .
        $d2 . hl7Text($pcrow['procedure_name']) .
        $d1 . hl7Priority($porow['order_priority']) . // S=Stat, R=Routine
        $d1 .
        $d1 . hl7Time($porow['date_collected']) .     // Observation Date/Time
        str_repeat($d1, 8) .                  // OBR 8-15 not used
        $d1 . hl7Text($porow['docnpi']) .             // Physician ID
        $d2 . hl7Text($porow['doclname']) .         // Last Name
        $d2 . hl7Text($porow['docfname']) .         // First Name
        $d1 .
        $d1 . (count($payers) ? 'I' : 'P') .          // I=Insurance, C=Client, P=Self Pay
        str_repeat($d1, 8) .                          // OBR 19-26 not used
        $d1 . '0' .                                   // ?
        $d0;

        // Diagnoses.  Currently hard-coded for ICD10 and we'll surely want to make
        // this more flexible (probably when some lab needs another diagnosis type).
        $test_diagnosis = $pcrow['diagnoses'];
        if (empty($test_diagnosis)) { // add default primary dianosis
            $test_diagnosis = $porow['order_diagnosis'];
        }
        $setid2 = 0;
        if (!empty($test_diagnosis)) {
            $relcodes = explode(';', $test_diagnosis);
            foreach ($relcodes as $codestring) {
                if ($codestring === '') {
                    continue;
                }

                list($codetype, $code) = explode(':', $codestring);
                if ($codetype !== 'ICD10') {
                    continue;
                }

                $desc = lookup_code_descriptions($codestring);
                $out .= "DG1" .
                    $d1 . ++$setid2 .                         // Set ID
                    $d1 .                                     // Diagnosis Coding Method
                    $d1 . $code .                             // Diagnosis Code
                    $d2 . hl7Text($desc) .                    // Diagnosis Description
                    $d2 . "I10" .                             // Diagnosis Type
                    $d1 . $d0;
            }
        }

        // Order entry questions and answers.
        $qres = sqlStatement(
            "SELECT " .
            "a.question_code, a.answer, q.fldtype " .
            "FROM procedure_answers AS a " .
            "LEFT JOIN procedure_questions AS q ON " .
            "q.lab_id = ? " .
            "AND q.procedure_code = ? AND " .
            "q.question_code = a.question_code " .
            "WHERE " .
            "a.procedure_order_id = ? AND " .
            "a.procedure_order_seq = ? " .
            "ORDER BY q.seq, a.answer_seq",
            array($porow['ppid'], $pcrow['procedure_code'], $orderid, $pcrow['procedure_order_seq'])
        );
        $setid2 = 0;
        while ($qrow = sqlFetchArray($qres)) {
              // Formatting of these answer values may be lab-specific and we'll figure
              // out how to deal with that as more labs are supported.
              $answer = trim($qrow['answer']);
              $fldtype = $qrow['fldtype'];
              $datatype = 'ST';
            if ($fldtype == 'N') {
                $datatype = "NM";
            } elseif ($fldtype == 'D') {
                  $answer = hl7Date($answer);
            } elseif ($fldtype == 'G') {
                  $weeks = intval($answer / 7);
                  $days = $answer % 7;
                  $answer = $weeks . 'wks ' . $days . 'days';
            }

              $out .= "OBX" .
                $d1 . ++$setid2 .                           // Set ID
                $d1 . $datatype .                           // Structure of observation value
                $d1 . hl7Text($qrow['question_code']) .     // Clinical question code
                $d1 .
                $d1 . hl7Text($answer) .                    // Clinical question answer
                $d0;
        }
    }

    return '';
}

/**
 * Transmit HL7 for the specified lab.
 *
 * @param  integer $ppid  Procedure provider ID.
 * @param  string  $out   The HL7 text to be sent.
 * @return string         Error text, or empty if no errors.
 */
function send_hl7_order($ppid, $out)
{
    global $srcdir;

    $d0 = "\r";

    $pprow = sqlQuery("SELECT * FROM procedure_providers " .
    "WHERE ppid = ?", array($ppid));
    if (empty($pprow)) {
        return xl('Procedure provider') . " $ppid " . xl('not found');
    }

    $protocol = $pprow['protocol'];
    $remote_host = $pprow['remote_host'];

  // Extract MSH-10 which is the message control ID.
    $segmsh = explode(substr($out, 3, 1), substr($out, 0, strpos($out, $d0)));
    $msgid = $segmsh[9];
    if (empty($msgid)) {
        return xl('Internal error: Cannot find MSH-10');
    }

    if ($protocol == 'DL' || $pprow['orders_path'] === '') {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=order_$msgid.hl7");
        header("Content-Description: File Transfer");
        echo $out;
        exit;
    } elseif ($protocol == 'SFTP') {
        // Compute the target path/file name.
        $filename = $msgid . '.txt';
        if ($pprow['orders_path']) {
            $filename = $pprow['orders_path'] . '/' . $filename;
        }

        // Connect to the server and write the file.
        $sftp = new \phpseclib3\Net\SFTP($remote_host);
        if (!$sftp->login($pprow['login'], $pprow['password'])) {
            return xl('Login to this remote host failed') . ": '$remote_host'";
        }

        if (!$sftp->put($filename, $out)) {
            return xl('Creating this file on remote host failed') . ": '$filename'";
        }
    } elseif ($protocol == 'FS') {
        // Compute the target path/file name.
        $filename = $msgid . '.txt';
        if ($pprow['orders_path']) {
            $filename = $pprow['orders_path'] . '/' . $filename;
        }

        $fh = fopen("$filename", 'w');
        if ($fh) {
            fwrite($fh, $out);
            fclose($fh);
        } else {
            return xl('Cannot create file') . ' "' . "$filename" . '"';
        }
    } else {// TBD: Insert "else if ($protocol == '???') {...}" to support other protocols.
        return xl('This protocol is not implemented') . ": '$protocol'";
    }

  // Falling through to here indicates success.
    EventAuditLogger::instance()->newEvent(
        "proc_order_xmit",
        $_SESSION['authUser'],
        $_SESSION['authProvider'],
        1,
        "ID: $msgid Protocol: $protocol Host: $remote_host"
    );
    return '';
}
