<?php

/**
 * Functions to support LabCorp HL7 order generation.
 *
 * Copyright (C) 2012-2013 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
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
 * @author    Jerry Padgett <sjpadgett@gmail.com>
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

use OpenEMR\Common\Logging\EventAuditLogger;

require_once("$srcdir/classes/Address.class.php");
require_once("$srcdir/classes/InsuranceCompany.class.php");
require_once("$webserver_root/custom/code_types.inc.php");

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
    return date('YmdHi', strtotime($s));
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
        return $tmp[1] . $tmp[2] . $tmp[3];
    }
    if (preg_match("/(\d\d\d)\D*(\d\d\d\d)\D*$/", $s, $tmp)) {
        return $tmp[1] . $tmp[2];
    }
    return '';
}

function hl7SSN($s)
{
    if (preg_match("/(\d\d\d)\D*(\d\d)\D*(\d\d\d\d)\D*$/", $s, $tmp)) {
        return $tmp[1] . $tmp[2] . $tmp[3];
    }
    return '';
}

function hl7Priority($s)
{
    return strtoupper(substr($s, 0, 1)) === 'H' ? 'S' : 'R';
}

function hl7Relation($s)
{
    $tmp = strtolower($s);
    if ($tmp == 'self' || $tmp == '') {
        return '1';
    }

    if ($tmp == 'spouse') {
        return '2';
    }

    if ($tmp == 'child') {
        return '3';
    }

    if ($tmp == 'other') {
        return '8';
    }
    // Should not get here so this will probably get noticed if we do.
    return $s;
}

function hl7Race($s)
{
    $tmp = strtolower($s);
    if ($tmp == '') {
        return 'X';
    } elseif ($tmp == 'asian') {
        return 'A';
    } elseif ($tmp == 'black_or_afri_amer') {
        return 'B';
    } elseif ($tmp == 'white') {
        return 'C';
    } elseif ($tmp == 'hispanic') {
        return 'H';
    } elseif ($tmp == 'amer_ind_or_alaska_native') {
        return 'I';
    } elseif ($tmp == 'other') {
        return 'O';
    } elseif ($tmp == 'ashkenazi_jewish') {
        return 'J';
    } elseif ($tmp == 'sephardic_jewish') {
        return 'S';
    }
    // Should not get here so this will probably get noticed if we do.
    return $s;
}

function hl7Workman($s)
{
    // $tmp = strtolower($s);
    if ($s == 15) {
        return 'Y';
    } else {
        return 'N';
    }
}

/**
 * Get array of insurance payers for the specified patient as of the specified
 * date. If no date is passed then the current date is used.
 *
 * @param  integer $pid Patient ID.
 * @param  date $encounter_date YYYY-MM-DD date.
 * @return array   Array containing an array of data for each payer.
 */
function loadPayerInfo($pid, $date = '')
{
    if (empty($date)) {
        $date = date('Y-m-d');
    }
    $payers = array();
    $dres = sqlStatement(
        "SELECT * FROM insurance_data WHERE " .
        "pid = ? AND date <= ? ORDER BY type ASC, date DESC",
        array($pid, $date)
    );
    $prevtype = ''; // type is primary, secondary or tertiary
    while ($drow = sqlFetchArray($dres)) {
        if (strcmp($prevtype, $drow['type']) == 0) {
            continue;
        }
        $prevtype = $drow['type'];
        // Very important to check for a missing provider because
        // that indicates no insurance as of the given date.
        if (empty($drow['provider'])) {
            continue;
        }
        $ins = count($payers);
        $crow = sqlQuery(
            "SELECT * FROM insurance_companies WHERE id = ?",
            array($drow['provider'])
        );
        $orow = new InsuranceCompany($drow['provider']);
        $payers[$ins] = array();
        $payers[$ins]['data'] = $drow;
        $payers[$ins]['company'] = $crow;
        $payers[$ins]['object'] = $orow;
    }
    return $payers;
}

function loadGuarantorInfo($pid, $date = '')
{
    if (empty($date)) {
        $date = date('Y-m-d');
    }
    $guarantors = array();
    $gres = sqlStatement(
        "SELECT * FROM insurance_data WHERE " .
        "pid = ? AND date <= ? ORDER BY type ASC, date DESC LIMIT 1",
        array($pid, $date)
    );
    $prevtype = ''; // type is primary, secondary or tertiary
    while ($drow = sqlFetchArray($gres)) {
        $gnt = count($guarantors);

        $guarantors[$gnt] = array();
        $guarantors[$gnt]['data'] = $drow;
    }
    return $guarantors;
}

/**
 * Generate HL7 for the specified procedure order.
 *
 * @param integer $orderid Procedure order ID.
 * @param string &$out     Container for target HL7 text.
 * @param string &$reqStr
 * @return string            Error text, or empty if no errors.
 */
function gen_hl7_order($orderid, &$out, &$reqStr)
{

    // Delimiters
    $d0 = "\r";
    $d1 = '|';
    $d2 = '^';

    $today = time();
    $out = '';
    // init 2d barcode req record arrays
    for ($i = 0; $i < 98; $i++) {
        if ($i < 6) {
            $H[$i] = '';
        }
        if ($i < 9) {
            $G[$i] = '';
        }
        if ($i < 27) {
            $C[$i] = '';
        }
        if ($i < 41) {
            $A[$i] = '';
            $T[$i] = '';
        }
        $P[$i] = '';
    }
    $H[0] = 'H';
    $C[0] = 'C';
    $C[19] = '^';
    $A[0] = 'A';
    $M[0] = 'M';
    $T[0] = 'T';
    $O[0] = 'O';
    $S[0] = 'S';
    $G[0] = 'G';
    $D[0] = 'D';
    $L[0] = 'L';
    $E[0] = 'E';
    $A[21] = "^^";
    $A[22] = "^";
    $A[23] = "^";
    $A[29] = "^";
    $A[30] = "^^^^^";
    $A[33] = "^^^";
    $G[1] = "^";
    $S[1] = "^^^^^^";
    $P[0] = 'P';
    $P[36] = "^";
    $P[45] = "^";
    $P[54] = "^^^^^^^^^^^^^^";
    $P[55] = "^^^^^^^";
    $P[72] = "^^";
    $P[73] = "^^";
    $P[74] = "^^";
    $P[75] = "^^";
    $P[79] = "^";
    $P[85] = "^";
    $P[86] = "^^^^";
    $P[89] = "^";
    $P[94] = "^";
    $P[95] = "^^";
    $B = "B|||||||||||||||||||||";
    $K = "K|^|||||||||||||||^^^^||||||";
    $I = "I|^^|^^|^^|^^|^^|^^|^^|^^|";

    $porow = sqlQuery(
        "SELECT " .
        "po.date_collected, po.date_ordered, po.order_priority,po.billing_type,po.clinical_hx,po.account,po.order_diagnosis, " .
        "pp.*, " .
        "pd.pid, pd.pubpid, pd.fname, pd.lname, pd.mname, pd.DOB, pd.ss, pd.race, " .
        "pd.phone_home, pd.phone_biz, pd.sex, pd.street, pd.city, pd.state, pd.postal_code, " .
        "f.encounter, u.fname AS docfname, u.lname AS doclname, u.npi AS docnpi, u.id as user_id " .
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

    $pdres = sqlStatement(
        "SELECT " .
        "pc.procedure_code, pc.procedure_name, pc.procedure_order_seq, pc.diagnoses " .
        "FROM procedure_order_code AS pc " .
        "WHERE " .
        "pc.procedure_order_id = ? AND " .
        "pc.do_not_send = 0 " .
        "ORDER BY pc.procedure_order_seq",
        array($orderid)
    );

    $vitals = sqlQuery(
        "SELECT * FROM form_vitals v join forms f on f.form_id=v.id WHERE f.pid=? and f.encounter=? ORDER BY v.date DESC LIMIT 1",
        [$porow['pid'], $porow['encounter']]
    );
    $P[68] = $vitals['weight'];
    $P[70] = $vitals['height'];
    $P[88] = $vitals['bps'] . '^' . $vitals['bpd'];
    $P[89] = $vitals['waist_circ'];
    $C[17] = hl7Date(date("Ymd", strtotime($porow['date_collected'])));
    if (empty($porow['account'])) {
        return "ERROR! Missing this orders facility location account code (Facility Id) in Facility!";
    }
    // Message Header
    $bill_type = strtoupper(substr($porow['billing_type'], 0, 1));
    $out .= "MSH" .
        $d1 . "$d2~\\&" .               // Encoding Characters (delimiters)
        $d1 . $porow['send_app_id'] .   // Sending Application ID
        $d1 . $porow['send_fac_id'] .   // Sending Facility ID

        $d1 . $porow['recv_app_id'] .   // Receiving Application ID
        $d1 . $porow['recv_fac_id'] .   // Receiving Facility ID
        $d1 . date('YmdHi', $today) .  // Date and time of this message
        $d1 .
        $d1 . 'ORM' . $d2 . 'O01' .     // Message Type
        $d1 . $orderid .                // Unique Message Number
        $d1 . 'P' . //$porow['DorP'] .          // D=Debugging, P=Production
        $d1 . '2.3' .                   // HL7 Version ID
        $d0;
    $H[1] = $porow['send_app_id'];
    $H[2] = date('Ymd', $today);
    $P[1] = $porow['pid'];
    $P[7] = $porow['recv_fac_id'];
    // Patient Identification
    $out .= "PID" .
        $d1 . "1" .                      // Set ID (always just 1 of these)
        $d1 . $porow['pid'] .            // Patient ID (not required)
        //$d1 . $porow['pid'] .          // Patient ID (required)
        $d1 .
        $d1 .                            // Alternate Patient ID (not required)
        $d1 . hl7Text($porow['lname']) .
        $d2 . hl7Text($porow['fname']);
    if ($porow['mname']) {
        $out .= $d2 . hl7Text($porow['mname']);
    }
    $out .= $d1 .
        $d1 . hl7Date($porow['DOB']) .  // DOB
        $d1 . hl7Sex($porow['sex']) .   // Sex: M, F or U
        $d1 .
        $d1 . hl7Race($porow['race']) . //PID 10
        $d1 . hl7Text($porow['street']) .
        $d2 .
        $d2 . hl7Text($porow['city']) .
        $d2 . hl7Text($porow['state']) .
        $d2 . hl7Zip($porow['postal_code']) .
        $d1 .
        $d1 . hl7Phone($porow['phone_home']) .
        $d1 . hl7Phone($porow['phone_biz']) .
        $d1 . $d1 . $d1 .
        $d1 . hl7Text($porow['account']) .       // This is for a location account number i.e Facility Fac Id
        $d2 .
        $d2 . "" .
        $d2 . hl7Text($bill_type) .
        $d1 . hl7SSN($porow['ss']) .
        $d1 . $d1 . $d1 .
        $d0;
    $P[9] = hl7Text($porow['lname']) . '^' . hl7Text($porow['fname']) . '^' . hl7Text($porow['mname']);
    $P[10] = hl7Date($porow['DOB']);
    $P[11] = hl7Sex($porow['sex']);
    $P[12] = hl7SSN($porow['ss']);
    $P[13] = hl7Text($porow['street']);
    $P[14] = hl7Text($porow['city']);
    $P[15] = hl7Text($porow['state']);
    $P[16] = hl7Zip($porow['postal_code']);
    $P[17] = hl7Phone($porow['phone_home']);
    $P[57] = $orderid;
    $P[58] = $porow['pid'];


    if ($bill_type == 'T') {
        $P[18] = "XI";
    } elseif ($bill_type == 'P') {
        $P[18] = "03";
    } else {
        $P[18] = "04";
    }

    // NTE segment(s) omitted.
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
    $P[29] = hl7Text($porow['doclname']) . "^" . hl7Text($porow['docfname']);
    $P[30] = hl7Text($porow['docnpi']);
    $P[71] = hl7Text($porow['docnpi']);
        // Insurance stuff.
    $payers = loadPayerInfo($porow['pid'], $porow['date_ordered']);
    $setid = 0;
    if ($bill_type == 'T') {
        // only send primary and secondary insurance
        foreach ($payers as $payer) {
            $payer_object = $payer['object'];
            $payer_address = $payer_object->get_address();
            $full_address = $payer_address->get_line1();
            if (!empty($payer_address->get_line2())) {
                $full_address .= "," . $payer_address->get_line2();
            }
            $out .= "IN1" .
                $d1 . ++$setid .                                // Set ID
                $d1 .                                           // Insurance Plan Identifier ??
                $d1 . hl7Text($payer['company']['id']) .        // Insurance Company ID
                $d2 . hl7Text($payer['company']['cms_id']) .        // Insurance Carrier code
                $d1 . hl7Text($payer['company']['name']) .    // Insurance Company Name
                $d1 . hl7Text($full_address) .    // Street Address
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
                $d1 . hl7Relation($payer['data']['subscriber_relationship']) .        //JC this may need to be edited JP this is okay!
                $d1 . hl7Date($payer['data']['subscriber_DOB']) .     // Insured DOB
                $d1 . hl7Text($payer['data']['subscriber_street']) .  // Insured Street Address
                $d2 .
                $d2 . hl7Text($payer['data']['subscriber_city']) .  // City
                $d2 . hl7Text($payer['data']['subscriber_state']) . // State
                $d2 . hl7Zip($payer['data']['subscriber_postal_code']) . // Zip
                $d1 .
                $d1 .
                $d1 . $setid .                                  // 1=Primary, 2=Secondary, 3=Tertiary
                str_repeat($d1, 8) .                           // IN1-23 to 30 all empty
                $d1 . hl7Workman($payer['data']['policy_type']) . // Policy Number
                str_repeat($d1, 4) .                           // IN1-32 to 35 all empty
                $d1 . hl7Text($payer['data']['policy_number']) . // Policy Number
                str_repeat($d1, 12) .                           // IN1-37 to 48 all empty
                $d0;
            if ($payer_object->get_ins_type_code() === '2') { //medicare
                $P[19] = hl7Text($payer['data']['policy_number']);
            } elseif ($payer_object->get_ins_type_code() === '3') { // medicaid
                $P[53] = hl7Text($payer['data']['policy_number']);
            } else {
                $P[40] = hl7Text($payer['data']['policy_number']);
            }
            if ($setid === 2) {
                $P[43] = hl7Text($payer['company']['cms_id']);
                $P[44] = hl7Text($payer['company']['name']);
                $P[45] = hl7Text($full_address);
                $P[46] = hl7Text($payer_address->get_city());
                $P[47] = hl7Text($payer_address->get_state());
                $P[48] = hl7Zip($payer_address->get_zip());
                $P[41] = hl7Text($payer['data']['group_number']);
                $P[52] = hl7Workman($payer['data']['policy_type']);
                break;
            }
            $P[34] = hl7Text($payer['company']['cms_id']);
            $P[35] = hl7Text($payer['company']['name']);
            $P[36] = hl7Text($full_address);
            $P[37] = hl7Text($payer_address->get_city());
            $P[38] = hl7Text($payer_address->get_state());
            $P[39] = hl7Zip($payer_address->get_zip());
            $P[41] = hl7Text($payer['data']['group_number']);
            $P[52] = hl7Workman($payer['data']['policy_type']);
        }
        if ($setid === 0) {
            return "\nInsurance is being billed but patient does not have any payers on record!";
        }
    } else { // no insurance record
        ++$setid;
        $out .= "IN1|$setid||||||||||||||||||||||||||||||||||||||||||||||$bill_type" . $d0;
    }

    $guarantors = loadGuarantorInfo($porow['pid'], $porow['date_ordered']);
    foreach ($guarantors as $guarantor) {
        if (hl7Text($bill_type) != "C") {
            if ($guarantor['data']['subscriber_lname'] != "") {
                // Guarantor. OpenEMR doesn't have these so use the patient.
                $out .= "GT1" .
                    $d1 . "1" .                      // Set ID (always just 1 of these)
                    $d1 .
                    $d1 . hl7Text($guarantor['data']['subscriber_lname']) .   // Insured last name
                    $d2 . hl7Text($guarantor['data']['subscriber_fname']) . // Insured first name
                    $d2 . hl7Text($guarantor['data']['subscriber_mname']); // Insured middle name
                $out .=
                    $d1 .
                    $d1 . hl7Text($guarantor['data']['subscriber_street']) .  // Insured Street Address
                    $d2 .
                    $d2 . hl7Text($guarantor['data']['subscriber_city']) .  // City
                    $d2 . hl7Text($guarantor['data']['subscriber_state']) . // State
                    $d2 . hl7Zip($guarantor['data']['subscriber_postal_code']) . // Zip
                    $d1 . hl7Phone($guarantor['data']['subscriber_phone']) .
                    $d1 .
                    $d1 . hl7Date($guarantor['data']['subscriber_DOB']) .     // Insured DOB
                    $d1 . hl7Sex($guarantor['data']['subscriber_sex']) .   // Sex: M, F or U
                    $d1 .
                    $d1 . hl7Relation($guarantor['data']['subscriber_relationship']) .        //JC this may need to be edited JP this is okay!

                    $d1 . hl7Date($guarantor['data']['subscriber_ss']) .     // Insured ssn
                    $d0;
            }
        }
        $P[20] = hl7Text($guarantor['data']['subscriber_lname']) . '^' . hl7Text($guarantor['data']['subscriber_fname']) . '^';
        $P[21] = hl7Date($guarantor['data']['subscriber_ss']);
        $P[22] = hl7Text($guarantor['data']['subscriber_street']);
        $P[23] = hl7Text($guarantor['data']['subscriber_city']);
        $P[24] = hl7Text($guarantor['data']['subscriber_state']);
        $P[25] = hl7Zip($guarantor['data']['subscriber_postal_code']);
        // $P[26] = // employer;
        $P[27] = hl7Relation($guarantor['data']['subscriber_relationship']);
        $P[56] = hl7Phone($guarantor['data']['subscriber_phone']);
    }

    $setid2 = 0;
    // this gets the order default codes
    $relcodes = explode(';', $porow['order_diagnosis']);
    $relcodes = array_unique($relcodes);
    foreach ($relcodes as $codestring) {
        if ($codestring === '') {
            continue;
        }
        list($codetype, $code) = explode(':', $codestring);
        $desc = lookup_code_descriptions($codestring);
        $out .= "DG1" .
        $d1 . ++$setid2;
        $out .= $d1 . 'I10';
        $out .= $d1 . $code .
        $d1 . hl7Text($desc) . $d0;
        // req
        if ($setid2 < 9) {
            $D[1] .= $code . '^';
        }
    }
    // now from each test order list
    while ($pdrow = sqlFetchArray($pdres)) {
        if (!empty($pdrow['diagnoses'])) {
            $relcodes = explode(';', $pdrow['diagnoses']);
            foreach ($relcodes as $codestring) {
                if ($codestring === '') {
                    continue;
                }
                list($codetype, $code) = explode(':', $codestring);
                $desc = lookup_code_descriptions($codestring);
                $out .= "DG1" .
                $d1 . ++$setid2;             // Set ID
                $out .= $d1 . 'I10';         // Diagnosis Coding Method
                $out .= $d1 . $code .        // Diagnosis Code
                $d1 . hl7Text($desc) . $d0;  // Diagnosis Description
                if ($setid2 < 9) {
                    $D[1] .= $code . '^';
                }
            }
        }
    }
    $D[1] = substr($D[1], 0, strlen($D[1]) - 1);
    $vvalue = strtoupper($_REQUEST['form_specimen_fasting']) == 'YES' ? "Y" : "N";
    $ht = str_pad(round($vitals['height']), 3, "0", STR_PAD_LEFT);
    $lb = floor((float)$vitals['weight']);
    $lb = str_pad($lb, 3, "0", STR_PAD_LEFT);
    $oz = round(((float)$vitals['weight'] * 16) - ($lb * 16));

    $out .= "ZCI|$ht|$lb^^$oz|0|$vvalue" . $d0;
    $setid = 0;
    while ($pcrow = sqlFetchArray($pcres)) {
        // Common Order.
        $out .= "ORC" .
            $d1 . "NW" .                     // New Order
            $d1 . $orderid .                 // Placer Order Number
            str_repeat($d1, 6) .             // ORC 3-8 not used
            $d1 . date('YmdHi') .           // Transaction date/time
            $d1 . $d1 .
            $d1 . hl7Text($porow['docnpi']) .     // Ordering Provider
            $d2 . hl7Text($porow['doclname']) . // Last Name
            $d2 . hl7Text($porow['docfname']) . // First Name
            str_repeat($d2, 4) .
            $d2 . 'N' .
            str_repeat($d1, 7) .             // ORC 13-19 not used
            $d1 . "2" .                      // ABN Status: 2 = Notified & Signed, 4 = Unsigned
            $d0;

        // Observation Request.
        $specprocedure = sqlQuery("SELECT specimen FROM procedure_type WHERE procedure_code=?", [$pcrow['procedure_code']]);
        $out .= "OBR" .
            $d1 . ++$setid .                              // Set ID
            $d1 . $orderid .                              // Placer Order Number
            $d1 .
            $d1 . hl7Text($pcrow['procedure_code']) .
            $d2 . hl7Text($pcrow['procedure_name']) .
            $d2 . 'L' .
            $d1 . hl7Priority($porow['order_priority']) . // S=Stat, R=Routine
            $d1 .
            $d1 . hl7Time($porow['date_collected']) .     // Observation Date/Time
            str_repeat($d1, 3) .                          // OBR 8-15 not used
            $d1 . 'N' .
            str_repeat($d1, 1) .
            $d1 . hl7Text($porow['clinical_hx']) . //clinical info
            $d1 .
            $d1 . $specprocedure['specimen'] .          // was 4
            $d1 . hl7Text($porow['docnpi']) .           // Physician ID
            $d2 . hl7Text($porow['doclname']) .         // Last Name
            $d2 . hl7Text($porow['docfname']) .         // First Name
            str_repeat($d2, 4) .
            $d2 . 'N' .
            $d1 .
            $d1 . //(count($payers) ? 'I' : 'P') .          // I=Insurance, C=Client, P=Self Pay
            str_repeat($d1, 8) .                          // OBR 19-26 not used
            $d1 . '0' .                                   // ?
            $d0;

        // Order entry questions and answers.
        $qres = sqlStatement(
            "SELECT " .
            "a.question_code, a.answer, q.fldtype , q.tips " .
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
        $fastflag = false;
        while ($qrow = sqlFetchArray($qres)) {
            // Formatting of these answer values may be lab-specific and we'll figure
            // out how to deal with that as more labs are supported.
            $answer = trim($qrow['answer']);
            $qcode = trim($qrow['question_code']);
            $fldtype = $qrow['fldtype'];
            $datatype = 'ST';
            if ($qcode == 'FASTIN') {
                $fastflag = true;
            }
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
            $d1 . $qrow['tips'] .                       // Clinical question code
            $d1 .
            $d1 . hl7Text($answer) .                    // Clinical question answer
            $d1 .
            $d1 .
            $d1 .
            $d1 .
            $d1 . "N" .
            $d1 . "F" .
            $d0;
        }
        $vvalue = strtoupper($_REQUEST['form_specimen_fasting']) === 'YES' ? "Y" : "N";
        $C[24] = $vvalue === "Y" ? ($vvalue . '12') : $vvalue;
        $T[$setid] = hl7Text($pcrow['procedure_code']);
        if ($vvalue === "Y" && $fastflag === false) {
            $out .= "OBX" .
            $d1 . ++$setid2 .
            $d1 . "ST" .
            $d1 . "FASTIN^FASTING^L" .
            $d1 . $d1 . $vvalue . $d1 . $d1 . $d1 . $d1 . $d1 . "N" . $d1 . "F" .
            $d0;
        }
    }

    $reqStr = "";
    for ($i = 0; $i < 6; $i++) {
        $reqStr .= $H[$i] . '|';
    }$reqStr .= "\x0D";
    for ($i = 0; $i < 98; $i++) {
        $reqStr .= $P[$i] . '|';
    }$reqStr .= "\x0D";
    for ($i = 0; $i < 27; $i++) {
        $reqStr .= $C[$i] . '|';
    }$reqStr .= "\x0D";
    for ($i = 0; $i < 41; $i++) {
        $reqStr .= $A[$i] . '|';
    }$reqStr .= "\x0D";
    for ($i = 0; $i < 41; $i++) {
        $reqStr .= $T[$i] . '|';
    }$reqStr .= "\x0D";
    for ($i = 0; $i < 6; $i++) {
        $reqStr .= $M[$i] . '|';
    }$reqStr .= "\x0D";

    $reqStr .= $D[0] . '|' . $D[1] . '||' . "\x0D";
    $l = strlen($reqStr);
    $reqStr .= "L|$l|\x0D";
    $reqStr .= 'E|0|' . "\x0D";
    $reqStr = strtoupper($reqStr);
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
        $filename = $msgid . '.hl7';
        if ($pprow['orders_path']) {
            $filename = $pprow['orders_path'] . '/' . $filename;
        }

        // Connect to the server and write the file.
        $sftp = new \phpseclib\Net\SFTP($remote_host);
        if (!$sftp->login($pprow['login'], $pprow['password'])) {
            return xl('Login to this remote host failed') . ": '$remote_host'";
        }

        if (!$sftp->put($filename, $out)) {
            return xl('Creating this file on remote host failed') . ": '$filename'";
        }
    } elseif ($protocol == 'FS') {
        // Compute the target path/file name.
        $filename = $msgid . '.hl7';
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
    } else {
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
