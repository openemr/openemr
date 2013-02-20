<?php

header("Content-Type:text/xml");
$ignoreAuth = true;
require_once ('includes/pdflibrary/config/lang/eng.php');
require_once ('includes/pdflibrary/tcpdf.php');
require_once 'classes.php';
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/../custom/code_types.inc.php");

$errmsg = "";
$alertmsg = ''; // not used yet but maybe later
$grand_total_charges = 0;
$grand_total_copays = 0;
$grand_total_encounters = 0;


$token = $_POST['token'];
$facility = isset($_POST['facility']) ? $_POST['facility'] : '';
$from_date = fixDate($_POST['from_date'], date('Y-m-d'));
$to_date = fixDate($_POST['to_date'], date('Y-m-d'));
$details = $_POST['details'] ? true : false;

$xml_string = "";
$xml_string .= "<list>";

$single_record_header = "";
$single_record = '';

$html = "<html>
            <head>
                <style>
                        
                              #report_parameters {
                                    background-color: #ececec;
                                        margin-top:10px;
                                }

                                #report_parameters table {
                                    border: solid 1px;
                                        width: 100%;
                                    border-collapse: collapse;
                                }
                                #report_parameters table td {
                                    padding: 5px;
                                }

                                #report_parameters table table {
                                    border: 0px;
                                    border-collapse: collapse;
                                        font-size: 0.8em;
                                }

                                #report_parameters table table td.label {
                                        text-align: right;
                                }

                                #report_results table {
                                border-top: 1px solid black;
                                border-bottom: 1px solid black;
                                border-left: 1px solid black;
                                border-right: 1px solid black;
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 1px;
                                }
                                #report_results table thead {
                                    padding: 5px;
                                    display: table-header-group;
                                    background-color: #ddd;
                                        text-align:left;
                                        font-weight: bold;
                                        font-size: 0.7em;
                                }
                                #report_results table th {
                                    border-bottom: 1px solid black;
                                        padding: 5px;
                                }
                                #report_results table td {
                                        padding: 5px;
                                    border-bottom: 1px dashed;
                                        font-size: 0.8em;
                                }
                                .report_totals td {
                                    background-color: #77ff77;
                                    font-weight: bold;
                                }


                </style>
            </head>
            <body>
            <div id='report_results' style=\" margin-top:10px;\">
            <h3>Report - Appointments and Encounters {$from_date} to {$to_date}</h3>
            <table style=\"border-top: 1px solid black;
                                border-bottom: 1px solid black;
                                border-left: 1px solid black;
                                border-right: 1px solid black;
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 1px;\">
                <thead style=\"padding: 5px;
                                    display: table-header-group;
                                    background-color: #ddd;
                                        text-align:left;
                                        font-weight: bold;
                                        font-size: 0.7em;\">   
                <tr>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Practitioner</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Date/Appt</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Patien</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">ID</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Chart</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Encounter</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Charges</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Copays</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Billed</th>
                    <th style=\"border-bottom: 1px solid black; padding: 5px;\">Error</th>
                </tr>
                </thead>
            ";
$single_record_header .= $html;

function postError($msg) {
    global $errmsg;
    if ($errmsg)
        $errmsg .= '\n';
    $errmsg .= $msg;
}

function bucks($amount) {
    if ($amount)
        echo oeFormatMoney($amount);
}

function endDoctor(&$docrow) {
    global $grand_total_charges, $grand_total_copays, $grand_total_encounters;
    if (!$docrow['docname'])
        return;

    $xml_string .= "<rowtotal>";
    $xml_string .= "<practitioner>Totals for " . $docrow['docname'] . "</practitioner>";
    $xml_string .= "<visits>" . $docrow['encounters'] . "</visits>";
    $xml_string .= "<charges>" . bucks($docrow['charges']) . "</charges>";
    $xml_string .= "<copays>" . bucks($docrow['copays']) . "</copays>";
    $xml_string .= "</rowtotal>";

    $grand_total_charges += $docrow['charges'];
    $grand_total_copays += $docrow['copays'];
    $grand_total_encounters += $docrow['encounters'];

    $docrow['charges'] = 0;
    $docrow['copays'] = 0;
    $docrow['encounters'] = 0;
}

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('encounters', 'auth_a', $user);
    if ($acl_allow) {

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor("Haroon");
        $pdf->SetTitle("My Report");
        $pdf->SetSubject("My Report");
//        $pdf->SetKeywords("TCPDF, PDF, example, test, guide");
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $query = "( " .
                "SELECT " .
                "e.pc_eventDate, e.pc_startTime, " .
                "fe.encounter, fe.date AS encdate, " .
                "f.authorized, " .
                "p.fname, p.lname, p.pid, p.pubpid, " .
                "CONCAT( u.lname, ', ', u.fname ) AS docname " .
                "FROM openemr_postcalendar_events AS e " .
                "LEFT OUTER JOIN form_encounter AS fe " .
                "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid " .
                "LEFT OUTER JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
                "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
                // "LEFT OUTER JOIN users AS u ON BINARY u.username = BINARY f.user WHERE ";
                "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
        if ($to_date) {
            $query .= "e.pc_eventDate >= '$from_date' AND e.pc_eventDate <= '$to_date' ";
        } else {
            $query .= "e.pc_eventDate = '$from_date' ";
        }
        if ($facility !== '') {
            $query .= "AND e.pc_facility = '$facility' ";
        }
        // $query .= "AND ( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
        $query .= "AND e.pc_pid != '' AND e.pc_apptstatus != '?' " .
                ") UNION ( " .
                "SELECT " .
                "e.pc_eventDate, e.pc_startTime, " .
                "fe.encounter, fe.date AS encdate, " .
                "f.authorized, " .
                "p.fname, p.lname, p.pid, p.pubpid, " .
                "CONCAT( u.lname, ', ', u.fname ) AS docname " .
                "FROM form_encounter AS fe " .
                "LEFT OUTER JOIN openemr_postcalendar_events AS e " .
                "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid AND " .
                // "( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
                "e.pc_pid != '' AND e.pc_apptstatus != '?' " .
                "LEFT OUTER JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
                "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
                // "LEFT OUTER JOIN users AS u ON BINARY u.username = BINARY f.user WHERE ";
                "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
        if ($to_date) {
            // $query .= "LEFT(fe.date, 10) >= '$form_from_date' AND LEFT(fe.date, 10) <= '$form_to_date' ";
            $query .= "fe.date >= '$from_date 00:00:00' AND fe.date <= '$to_date 23:59:59' ";
        } else {
            // $query .= "LEFT(fe.date, 10) = '$form_from_date' ";
            $query .= "fe.date >= '$from_date 00:00:00' AND fe.date <= '$from_date 23:59:59' ";
        }
        if ($facility !== '') {
            $query .= "AND fe.facility_id = '$facility' ";
        }
        $query .= ") ORDER BY docname, pc_eventDate, pc_startTime";
        $res = sqlStatement($query, array());



        if ($res->_numOfRows > 0) {
            $docrow = array('docname' => '', 'charges' => 0, 'copays' => 0, 'encounters' => 0);

            while ($row = sqlFetchArray($res)) {
                $patient_id = $row['pid'];
                $encounter = $row['encounter'];
                $docname = $row['docname'] ? $row['docname'] : 'Unknown';

                if ($docname != $docrow['docname']) {
                    endDoctor($docrow);
                }

                $errmsg = "";
                $billed = "Y";
                $charges = 0;
                $copays = 0;
                $gcac_related_visit = false;

   $query = "SELECT code_type, code, modifier, authorized, billed, fee, justify " .
                        "FROM billing WHERE " .
                        "pid = '$patient_id' AND encounter = '$encounter' AND activity = 1";
                $bres = sqlStatement($query);
                while ($brow = sqlFetchArray($bres)) {
                    $code_type = $brow['code_type'];
                    if ($code_types[$code_type]['fee'] && !$brow['billed'])
                        $billed = "";
                    if (!$GLOBALS['simplified_demographics'] && !$brow['authorized'])
                        postError('Needs Auth');
                    if ($code_types[$code_type]['just']) {
                        if (!$brow['justify'])
                            postError('Needs Justify');
                    }
                    if ($code_type == 'COPAY') {
                        $copays -= $brow['fee'];
                        if ($brow['fee'] >= 0)
                            postError('Copay not positive');
                    } else if ($code_types[$code_type]['fee']) {
                        $charges += $brow['fee'];
                        if ($brow['fee'] == 0 && !$GLOBALS['ippf_specific'])
                            postError('Missing Fee');
                    } else {
                        if ($brow['fee'] != 0)
                            postError('Fee is not allowed');
                    }

                    // Custom logic for IPPF to determine if a GCAC issue applies.
                    if ($GLOBALS['ippf_specific']) {
                        if (!empty($code_types[$code_type]['fee'])) {
                            $query = "SELECT related_code FROM codes WHERE code_type = '" .
                                    $code_types[$code_type]['id'] . "' AND " .
                                    "code = '" . $brow['code'] . "' AND ";
                            if ($brow['modifier']) {
                                $query .= "modifier = '" . $brow['modifier'] . "'";
                            } else {
                                $query .= "(modifier IS NULL OR modifier = '')";
                            }
                            $query .= " LIMIT 1";
                            $tmp = sqlQuery($query);
                            $relcodes = explode(';', $tmp['related_code']);
                            foreach ($relcodes as $codestring) {
                                if ($codestring === '')
                                    continue;
                                list($codetype, $code) = explode(':', $codestring);
                                if ($codetype !== 'IPPF')
                                    continue;
                                if (preg_match('/^25222/', $code))
                                    $gcac_related_visit = true;
                            }
                        }
                    } // End IPPF stuff
                } // end while


                if ($gcac_related_visit) {
                    $grow = sqlQuery("SELECT COUNT(*) AS count FROM forms " .
                            "WHERE pid = '$patient_id' AND encounter = '$encounter' AND " .
                            "deleted = 0 AND formdir = 'LBFgcac'");
                    if (empty($grow['count'])) { // if there is no gcac form
                        postError('GCAC visit form is missing');
                    }
                } // end if


                if (!$billed)
                    postError($GLOBALS['simplified_demographics'] ?
                                    'Not checked out' : 'Not billed');
                if (!$encounter)
                    postError('No visit');

                if (!$charges)
                    $billed = "";

                $docrow['charges'] += $charges;
                $docrow['copays'] += $copays;
                if ($encounter)
                    ++$docrow['encounters'];



                if ($details) {
                    $xml_string .= "<apptvisits>";
                    if ($docname == $docrow['docname']) {
                        $docname;
                    }
                    $xml_string .= "<practitioner>" . $docname . "</practitioner>";
                    if (empty($row['pc_eventDate'])) {
                        $date = oeFormatShortDate(substr($row['encdate'], 0, 10));
                    } else {
                        $date = oeFormatShortDate($row['pc_eventDate']) . ' ' . substr($row['pc_startTime'], 0, 5);
                    }
                    $xml_string .= "<date>" . $date . "</date>";
                    $xml_string .= "<patient>" . $row['fname'] . " " . $row['lname'] . "</patient>";
                    $xml_string .= "<id>" . $row['pubpid'] . "</id>";
                    $xml_string .= "<chart>" . $row['pid'] . "</chart>";
                    $xml_string .= "<visit>" . $encounter . "</visit>";
                    $xml_string .= "<charges>" . bucks($charges) . "</charges>";
                    $xml_string .= "<copays>" . bucks($copays) . "</copays>";
                    $xml_string .= "<billed>" . $billed . "</billed>";
                    $xml_string .= "<error>" . $errmsg . "</error>";

                    $old_docname == '';
                    $display_docname = $docname == $old_docname ? '' : $docname;

                    $single_record = "
                            <tr>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . $display_docname . "</td>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">{$date}</td>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] . "</td>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . $row['pubpid'] . "</td>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . $row['pid'] . "</td>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . $encounter . "</td>
                                    
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . bucks($charges) . "</td>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . bucks($copays) . "</td>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . $billed . "</td>
                                <td style=\"padding: 5px; border-bottom: 1px dashed black; font-size: 0.8em;\">" . $errmsg . "</td>
                            </tr>";

                    $html .= $single_record;
                    $old_docname = $docname;
                    $complete_single_record = $single_record_header . $single_record . "<table></div></body></html>";
                    $xml_string .= "<apptvisits_html>" . base64_encode($complete_single_record) . "</apptvisits_html>";

                    $xml_string .= "</apptvisits>";
                } // end of details line

                $docrow['docname'] = $docname;
            } // end of row

            endDoctor($docrow);
            $html .= "
                    <table>
                    </div>
                </body>
            </html>";

            $xml_string .= "<grandtotal>";
            $xml_string .= "<visits>" . $grand_total_encounters . "</visits>";
            $xml_string .= "<charges>" . bucks($grand_total_charges) . "</charges>";
            $xml_string .= "<copays>" . bucks($grand_total_copays) . "</copays>";
            $xml_string .= "</grandtotal>";

            $xml_string .= "<html_report>" . base64_encode($html) . "</html_report>";
            $pdf->writeHTML($html, true, false, true, false, '');

            $pdf_base64 = $pdf->Output("", "E");
            $temp = explode('filename=""', $pdf_base64);
            $xml_string .= "<pdf_report>" . $temp[1] . "</pdf_report>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>Could not find results</reason>";
        }
    } else {
        $xml_string .= "<status>-2</status>\n";
        $xml_string .= "<reason>You are not Authorized to perform this action</reason>\n";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</list>";
echo $xml_string;
?>