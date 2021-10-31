<?php

/**
* Script to display results for a given procedure order.
*
* Copyright (C) 2013-2016 Rod Roark <rod@sunsetsystems.com>
* Copyright (C) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
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

require_once($GLOBALS["srcdir"] . "/options.inc.php");

use OpenEMR\Common\Acl\AclMain;

function getListItem($listid, $value)
{
    $lrow = sqlQuery(
        "SELECT title FROM list_options " .
        "WHERE list_id = ? AND option_id = ? AND activity = 1",
        array($listid, $value)
    );
    $tmp = xl_list_label($lrow['title'] ?? '');
    if (empty($tmp)) {
        $tmp = (($value === '') ? '' : "($value)");
    }

    return $tmp;
}

function myCellText($s)
{
    $s = trim($s);
    if ($s === '') {
        return '&nbsp;';
    }

    return text($s);
}

// Check if the given string already exists in the $aNotes array.
// If not, stores it as a new entry.
// Either way, returns the corresponding key which is a small integer.
function storeNote($s)
{
    global $aNotes;
    $key = array_search($s, $aNotes);
    if ($key !== false) {
        return $key;
    }

    $key = count($aNotes);
    $aNotes[$key] = $s;
    return $key;
}

// Display a single row of output including order, report and result information.
function generate_result_row(&$ctx, &$row, &$rrow, $priors_omitted = false)
{
    $lab_id = empty($row['lab_id']) ? 0 : ($row['lab_id'] + 0);
    $order_type_id = empty($row['order_type_id']) ? 0 : ($row['order_type_id'] + 0);
    $order_seq = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
    $report_id = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);
    $procedure_code = empty($row['procedure_code']) ? '' : $row['procedure_code'];
    $diagnosis = empty($row['diagnoses']) ? '' : $row['diagnoses'];
    $procedure_name = empty($row['procedure_name']) ? '' : $row['procedure_name'];
    $date_report = empty($row['date_report']) ? '' : substr($row['date_report'], 0, 16);
    $date_report_suf = empty($row['date_report_tz']) ? '' : (' ' . $row['date_report_tz']);
    $date_collected = empty($row['date_collected']) ? '' : substr($row['date_collected'], 0, 16);
    $date_collected_suf = empty($row['date_collected_tz']) ? '' : (' ' . $row['date_collected_tz']);
    $specimen_num = empty($row['specimen_num']) ? '' : $row['specimen_num'];
    $report_status = empty($row['report_status']) ? '' : $row['report_status'];
    $review_status = empty($row['review_status']) ? 'received' : $row['review_status'];

    $report_noteid = '';
    if ($report_id && !isset($ctx['seen_report_ids'][$report_id])) {
        $ctx['seen_report_ids'][$report_id] = true;
        if ($review_status != 'reviewed') {
            if ($ctx['sign_list']) {
                $ctx['sign_list'] .= ',';
            }

            $ctx['sign_list'] .= $report_id;
        }

        // Allowing for multiple report notes separated by newlines.
        if (!empty($row['report_notes'])) {
            $notes = explode("\n", $row['report_notes']);
            foreach ($notes as $note) {
                if ($note === '') {
                    continue;
                }

                if ($report_noteid) {
                    $report_noteid .= ', ';
                }

                $report_noteid .= 1 + storeNote($note);
            }
        }
    }

    // allow for 0 to be displayed as a result value
    $rrow['result'] = $rrow['result'] ?? '';
    if ($rrow['result'] == '' && $rrow['result'] !== 0 && $rrow['result'] !== '0') {
        $result_result = '';
    } else {
        $result_result = $rrow['result'];
        if ($result_result == 'DNR' || $result_result == 'DNRTNP') {
            return;
        }
    }

    $result_code = empty($rrow['result_code']) ? '' : $rrow['result_code'];
    $result_text = empty($rrow['result_text']) ? '' : $rrow['result_text'];
    $result_abnormal = empty($rrow['abnormal']) ? '' : $rrow['abnormal'];
    $result_units = empty($rrow['units']) ? '' : $rrow['units'];
    $result_facility = empty($rrow['facility']) ? '' : $rrow['facility'];
    $result_comments = empty($rrow['comments']) ? '' : $rrow['comments'];
    $result_range = empty($rrow['range']) ? '' : $rrow['range'];
    $result_status = empty($rrow['result_status']) ? '' : $rrow['result_status'];
    $result_document_id = empty($rrow['document_id']) ? '' : $rrow['document_id'];

    // Someone changed the delimiter in result comments from \n to \r.
    // Have to make sure results are consistent with those before that change.
    $result_comments = str_replace("\r", "\n", $result_comments);


    // If the first line of comments is not empty, then it is actually a long textual
    // result value with lines delimited by "~" characters.
    if ($i = strpos($result_comments, "\n")) { // "=" is not a mistake!
            $result_comments = str_replace("~", "\n", substr($result_comments, 0, $i)) .
            substr($result_comments, $i);
    }

    $result_comments = trim($result_comments);

    $result_noteid = '';
    if (!empty($result_comments)) {
        $result_noteid = 1 + storeNote($result_comments);
    }

    if ($priors_omitted) {
        if ($result_noteid) {
            $result_noteid .= ', ';
        }

        $result_noteid .= 1 + storeNote(xl('This is the latest of multiple result values.'));
        $ctx['priors_omitted'] = true;
    }

    // If a performing organization is provided, make a note for it also.
    $result_facility = trim(str_replace("\r", "\n", $result_facility));
    if ($result_facility) {
        if ($result_noteid) {
            $result_noteid .= ', ';
        }

        $result_noteid .= 1 + storeNote(xl('Performing organization') . ":\n" . $result_facility);
    }

    if ($ctx['lastpcid'] != $order_seq) {
        ++$ctx['encount'];
    }

    $bgcolor = "#" . (($ctx['encount'] & 1) ? "ddddff" : "ffdddd");

    echo " <tr class='detail' style='background: $bgcolor;'>\n";

    if ($ctx['lastpcid'] != $order_seq) {
        $ctx['lastprid'] = -1; // force report fields on first line of each procedure
        $tmp = text("$procedure_code: $procedure_name: $diagnosis");
        // Get the LOINC code if one exists in the compendium for this order type.
        if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
            $trow = sqlQuery(
                "SELECT standard_code FROM procedure_type WHERE " .
                "lab_id = ? AND procedure_code = ? AND procedure_type = 'ord' " .
                "ORDER BY procedure_type_id LIMIT 1",
                array($lab_id, $procedure_code)
            );
            if (!empty($trow['standard_code'])) {
                  $tmp = "<a href='javascript:educlick(\"LOINC\"," . attr_js($trow['standard_code']) .
                    ")'>$tmp</a>";
            }
        }

        echo "  <td>$tmp</td>\n";
    } else {
        echo "  <td>&nbsp;</td>";
    }

    // If this starts a new report or a new order, generate the report fields.
    if ($report_id != $ctx['lastprid']) {
        echo "  <td>";
        echo myCellText(oeFormatShortDate(substr($date_report, 0, 10)) . substr($date_report, 10) . $date_report_suf);
        echo "</td>\n";

        echo "  <td>";
        echo myCellText(oeFormatShortDate(substr($date_collected, 0, 10)) . substr($date_collected, 10) . $date_collected_suf);
        echo "</td>\n";

        echo "  <td>";
        echo myCellText($specimen_num);
        echo "</td>\n";

        echo "  <td title='" . xla('Check mark indicates reviewed') . "'>";
        echo myCellText(getListItem('proc_rep_status', $report_status));
        if ($row['review_status'] == 'reviewed') {
            echo " &#x2713;"; // unicode check mark character
        }

        echo "</td>\n";

        echo "  <td class='text-center'>";
        echo myCellText($report_noteid);
        echo "</td>\n";
    } else {
        echo "  <td colspan='5'>&nbsp;</td>\n";
    }

    if ($result_code !== '' || $result_document_id) {
        $tmp = myCellText($result_code);
        if (empty($GLOBALS['PATIENT_REPORT_ACTIVE']) && !empty($result_code)) {
            $tmp = "<a href='javascript:educlick(\"LOINC\"," . attr_js($result_code) .
            ")'>$tmp</a>";
        }

        echo "  <td>$tmp</td>\n";
        echo "  <td>";
        echo myCellText($result_text);
        echo "</td>\n";
        echo "  <td>";
        $tmp = myCellText(getListItem('proc_res_abnormal', $result_abnormal));
        if ($result_abnormal && strtolower($result_abnormal) != 'no') {
            echo "<p class='font-weight-bold text-danger'>$tmp</p>";
        } else {
            echo $tmp;
        }

        echo "</td>\n";

        if ($result_document_id) {
            $d = new Document($result_document_id);
            echo "  <td colspan='3'>";
            if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
                echo "<a href='" . $GLOBALS['webroot'] . "/controller.php?document";
                echo "&retrieve&patient_id=" . attr_url($patient_id) . "&document_id=" . attr_url($result_document_id) . "' ";
                echo "onclick='top.restoreSession()'>";
            }

            echo $d->get_url_file();
            if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
                echo "</a>";
            }

            echo "</td>\n";
            $narrative_notes = sqlQuery("select group_concat(note SEPARATOR '\n') as notes from notes where foreign_id = ?", array($result_document_id));
            if (!empty($narrative_notes)) {
                $nnotes = explode("\n", $narrative_notes['notes']);
                $narrative_note_list = '';
                foreach ($nnotes as $nnote) {
                    if ($narrative_note_list == '') {
                        $narrative_note_list = 'Narrative Notes:';
                    }

                    $narrative_note_list .= $nnote;
                }

                if ($narrative_note_list != '') {
                    if ($result_noteid) {
                        $result_noteid .= ', ';
                    }

                    $result_noteid .= 1 + storeNote($narrative_note_list);
                }
            }
        } else {
            echo "  <td>";
            echo myCellText($result_result);
            echo "</td>\n";
            echo "  <td>";
            echo myCellText($result_range);
            echo "</td>\n";
            echo "  <td>";
            // Units comes from the lab so might not match anything in the proc_unit list,
            // but in that case the call will return the same value.
            echo myCellText(getListItemTitle('proc_unit', $result_units));
            echo "</td>\n";
        }

        echo "  <td align='center'>";
        echo myCellText($result_noteid);
        echo "</td>\n";
    } else {
        echo "  <td colspan='7'>&nbsp;</td>\n";
    }

    echo " </tr>\n";

    $ctx['lastpcid'] = $order_seq;
    $ctx['lastprid'] = $report_id;
    ++$ctx['lino'];
}

function generate_order_report($orderid, $input_form = false, $genstyles = true, $finals_only = false)
{
    global $aNotes;

    // Check authorization.
    $thisauth = AclMain::aclCheckCore('patients', 'med');
    if (!$thisauth) {
        return xl('Not authorized');
    }

    $orow = sqlQuery(
        "SELECT " .
        "po.procedure_order_id, po.date_ordered, po.control_id, " .
        "po.order_status, po.specimen_type, po.patient_id, po.order_diagnosis, " .
        "pd.pubpid, pd.lname, pd.fname, pd.mname, pd.cmsportal_login, pd.language, " .
        "fe.date, " .
        "pp.name AS labname, pp.recv_fac_id AS rcvfacid, " .
        "u.lname AS ulname, u.fname AS ufname, u.mname AS umname " .
        "FROM procedure_order AS po " .
        "LEFT JOIN patient_data AS pd ON pd.pid = po.patient_id " .
        "LEFT JOIN procedure_providers AS pp ON pp.ppid = po.lab_id " .
        "LEFT JOIN users AS u ON u.id = po.provider_id " .
        "LEFT JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
        "WHERE po.procedure_order_id = ?",
        array($orderid)
    );
    $dres = sqlStatementNoLog(
        "Select diagnoses as codes FROM procedure_order_code WHERE procedure_order_id = ? ",
        array($orow['procedure_order_id'])
    );
    $codes = array();
    $bld = '';
    while ($diag = sqlFetchArray($dres)) {
        $bld .= $diag['codes'] . ';';
    }
    $bld .= $orow['order_diagnosis'];
    $diags = explode(';', $bld);
    $diags = array_unique($diags);
    foreach ($diags as $d) {
        if (!$d) {
            continue;
        }
        $r['code'] = $d;
        $r['short_desc'] = lookup_code_descriptions($d, "code_text_short");
        $codes[] = $r;
    }

    $patient_id = $orow['patient_id'];
    $language = $orow['language'];

    ?>

    <?php if ($genstyles) { ?>
<style>

        <?php if (empty($_SESSION['language_direction']) || $_SESSION['language_direction'] == 'ltr') { ?>
    .labres tr.head {
        font-size: 0.8125rem;
        background-color: var(--gray200);
        text-align: center;
    }

    .labres tr.detail {
        font-size: 0.8125rem;
    }

    .labres a, .labres a:visited, .labres a:hover {
        color: #0000cc;
    }

    .labres table {
        border-style: solid;
        border-width: 1px 0px 0px 1px;
        border-color: var(--black);
    }

    .labres td, .labres th {
        border-style: solid;
        border-width: 0px 1px 1px 0px;
        border-color: var(--black);
    }

    <?php } else { ?>
    .labres tr.head {
        font-size: 0.8125rem;
        text-align: center;
    }

    .labres tr.detail {
        font-size: 0.8125rem;
    }

    .labres table {
        border-style: none;
        border-width: 1px 0px 0px 1px;
        border-color: var(--black);
    }

    .labres td, .labres th {
        border-style: none;
        border-width: 0px 1px 1px 0px;
        border-color: var(--black);
        padding: 4px;
    }

    <?php } ?>

    @media print {
        .labres tr.head,
        .labres tr.detail {
            font-size: 10pt;
        }
    }
</style>
<?php } ?>

    <?php if ($input_form) { ?>
        <script src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
    <?php } // end if input form
    ?>

    <?php if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) { ?>
<script>
    var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
    if (typeof top.webroot_url === "undefined") {
        if (typeof opener.top.webroot_url !== "undefined") {
            top.webroot_url = opener.top.webroot_url;
        }
    }

    // This works even if we are in a separate window.
    function showpnotes(orderid) {
        let url = top.webroot_url + '/interface/patient_file/summary/pnotes_full.php?orderid=' + <?php echo js_url($orderid); ?>;
        dlgopen(url, 'notes', 950, 750, false, '');
        return false;
    }

    // Process click on LOINC code for patient education popup.
    function educlick(codetype, codevalue) {
        dlgopen(top.webroot_url + '/interface/patient_file/education.php' +
            '?type=' + encodeURIComponent(codetype) +
            '&code=' + encodeURIComponent(codevalue) +
            '&language=' + <?php echo js_url($language); ?>,
            '_blank', 1024, 750, true); // Force a new window instead of iframe to address cross site scripting potential
    }
</script>

<?php } // end if not patient report ?>
    <?php if ($input_form) { ?>
<form method='post' action='single_order_results.php?orderid=<?php echo attr_url($orderid); ?>'>
    <?php } // end if input form
    ?>

    <div class='labres table-responsive'>
        <table class="table">
            <tr>
                <td class="font-weight-bold text-nowrap" width='5%'><?php echo xlt('Patient ID'); ?></td>
                <td width='45%'><?php echo myCellText($orow['pubpid']); ?></td>
                <td class="font-weight-bold text-nowrap" width='5%'><?php echo xlt('Order ID'); ?></td>
                <td width='45%'>
                    <?php
                    if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
                        echo "   <a href='" . $GLOBALS['webroot'];
                        echo "/interface/orders/order_manifest.php?orderid=";
                        echo attr_url($orow['procedure_order_id']);
                        echo "' target='_blank' onclick='top.restoreSession()'>";
                    }

                    echo myCellText($orow['procedure_order_id']);
                    if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) {
                        echo "</a>\n";
                    }

                    if ($orow['control_id']) {
                        echo myCellText(' ' . xl('Lab') . ': ' . $orow['control_id']);
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class="font-weight-bold text-nowrap"><?php echo xlt('Patient Name'); ?></td>
                <td><?php echo myCellText($orow['lname'] . ', ' . $orow['fname'] . ' ' . $orow['mname']); ?></td>
                <td class="font-weight-bold text-nowrap"><?php echo xlt('Ordered By'); ?></td>
                <td><?php echo myCellText($orow['ulname'] . ', ' . $orow['ufname'] . ' ' . $orow['umname']); ?></td>
            </tr>
            <tr>
                <td class="font-weight-bold text-nowrap"><?php echo xlt('Order Date'); ?></td>
                <td><?php echo myCellText(oeFormatShortDate($orow['date_ordered'])); ?></td>
                <td class="font-weight-bold text-nowrap"><?php echo xlt('Print Date'); ?></td>
                <td><?php echo text(oeFormatShortDate(date('Y-m-d'))); ?></td>
            </tr>
            <tr>
                <td class="font-weight-bold text-nowrap"><?php echo xlt('Order Status'); ?></td>
                <td><?php echo $orow['order_status'] ? myCellText($orow['order_status']) : xlt('Pending'); ?></td>
                <td class="font-weight-bold text-nowrap"><?php echo xlt('Encounter Date'); ?></td>
                <td><?php echo myCellText(oeFormatShortDate(substr($orow['date'], 0, 10))); ?></td>
            </tr>
            <tr>
                <td class="font-weight-bold text-nowrap"><?php echo xlt('Lab'); ?></td>
                <td><?php echo myCellText($orow['labname']); ?></td>
                <td class="font-weight-bold text-nowrap"><?php echo xlt('Receiving Fac.'); ?></td>
                <td><?php echo myCellText($orow['rcvfacid']); ?></td>
                <!-- replaced specimen with receiving facility -->
            </tr>
        </table>
        <br/>
        <table class="table">
            <tr class='head'>
                <td class="align-middle" style="font-size: 1rem;" width='20%'><?php echo xlt('Diagnosis'); ?></td>
                <td class="align-middle" style="font-size: 1rem;"><?php echo xlt('Diagnosis Description'); ?></td>
            </tr>
            <?php
            foreach ($codes as $code) {
                echo "<tr><td>" . myCellText($code['code']) . "</td>";
                echo "<td>" . myCellText($code['short_desc']) . "</td></tr>";
            }
            ?>
        </table>
        <br/>
        <table class="table">
            <tr class='head'>
                <td class="font-weight-bold align-middle" rowspan='2'><?php echo xlt('Ordered Procedure'); ?></td>
                <td class="font-weight-bold" colspan='5'><?php echo xlt('Report'); ?></td>
                <td class="font-weight-bold" colspan='7'><?php echo xlt('Results'); ?></td>
            </tr>
            <tr class='head'>
                <td><?php echo xlt('Reported'); ?></td>
                <td><?php echo xlt('Collected'); ?></td>
                <td><?php echo xlt('Specimen'); ?></td>
                <td><?php echo xlt('Status'); ?></td>
                <td><?php echo xlt('Note'); ?></td>
                <td><?php echo xlt('Code'); ?></td>
                <td><?php echo xlt('Name'); ?></td>
                <td><?php echo xlt('Abn'); ?></td>
                <td><?php echo xlt('Value'); ?></td>
                <td><?php echo xlt('Range'); ?></td>
                <td><?php echo xlt('Units'); ?></td>
                <td><?php echo xlt('Note'); ?></td>
            </tr>

            <?php
            $query = "SELECT " .
                "po.lab_id, po.date_ordered, pc.procedure_order_seq, pc.procedure_code, " .
                "pc.procedure_name, pc.diagnoses, " .
                "pr.date_report, pr.date_report_tz, pr.date_collected, pr.date_collected_tz, " .
                "pr.procedure_report_id, pr.specimen_num, pr.report_status, pr.review_status, pr.report_notes " .
                "FROM procedure_order AS po " .
                "JOIN procedure_order_code AS pc ON pc.procedure_order_id = po.procedure_order_id " .
                "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id AND " .
                "pr.procedure_order_seq = pc.procedure_order_seq " .
                "WHERE po.procedure_order_id = ? " .
                "ORDER BY pc.procedure_order_seq, pr.date_report, pr.procedure_report_id";

            $res = sqlStatement($query, array($orderid));
            $aNotes = array();
            $finals = array();
            $empty_results = array('result_code' => '');

            // Context for this call that may be used in other functions.
            $ctx = array(
                'lastpcid' => -1,
                'lastprid' => -1,
                'encount' => 0,
                'lino' => 0,
                'sign_list' => '',
                'seen_report_ids' => array(),
            );

            while ($row = sqlFetchArray($res)) {
                $report_id = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);

                $query = "SELECT " .
                    "ps.result_code, ps.result_text, ps.abnormal, ps.result, ps.range, " .
                    "ps.result_status, ps.facility, ps.units, ps.comments, ps.document_id, ps.date " .
                    "FROM procedure_result AS ps " .
                    "WHERE ps.procedure_report_id = ? " .
                    "ORDER BY ps.procedure_result_id";

                $rres = sqlStatement($query, array($report_id));

                if ($finals_only) {
                    // We are consolidating reports.
                    if (sqlNumRows($rres)) {
                        $rrowsets = array();
                        // First pass creates a $rrowsets[$key] for each unique result code in *this* report, with
                        // the value being an array of the corresponding result rows. This caters to multiple
                        // occurrences of the same result code in the same report.
                        while ($rrow = sqlFetchArray($rres)) {
                            $result_code = empty($rrow['result_code']) ? '' : $rrow['result_code'];
                            $key = sprintf('%05d/', $row['procedure_order_seq']) . $result_code;
                            if (!isset($rrowsets[$key])) {
                                $rrowsets[$key] = array();
                            }

                            $rrowsets[$key][] = $rrow;
                        }

                        // Second pass builds onto the array of final results for *all* reports, where each final
                        // result for a given result code is its *array* of result rows from *one* of the reports.
                        foreach ($rrowsets as $key => $rrowset) {
                            // When two reports have the same date, use the result date to decide which is "latest".
                            if (
                                isset($finals[$key]) &&
                                $row['date_report'] == $finals[$key][0]['date_report'] &&
                                !empty($rrow['date']) && !empty($finals[$key][1]['date']) &&
                                $rrow['date'] < $finals[$key][1]['date']
                            ) {
                                $finals[$key][2] = true; // see comment below
                                continue;
                            }

                            // $finals[$key][2] indicates if there are multiple results for this result code.
                            $finals[$key] = array($row, $rrowset, isset($finals[$key]));
                        }
                    } else {
                        // We have no results for this report.
                        $key = sprintf('%05d/', $row['procedure_order_seq']);
                        $finals[$key] = array($row, array($empty_results), false);
                    }
                } else {
                    // We are showing all results for all reports.
                    if (sqlNumRows($rres)) {
                        while ($rrow = sqlFetchArray($rres)) {
                            generate_result_row($ctx, $row, $rrow, false);
                        }
                    } else {
                        generate_result_row($ctx, $row, $empty_results, false);
                    }
                }
            }

            if ($finals_only) {
                // The sort here was removed because $finals is already ordered by procedure_result_id
                // within procedure_order_seq which is probably desirable.  Sorting by result code defeats
                // the sequencing of results chosen by the sender.
                // ksort($finals);
                foreach ($finals as $final) {
                    foreach ($final[1] as $rrow) {
                        generate_result_row($ctx, $final[0], $rrow, $final[2]);
                    }
                }
            }

            ?>
        </table>
        <br/>
        <table class="table border-0">
            <tr>
                <td class="border-0">
                    <?php
                    if (!empty($aNotes)) {
                        echo "<div class='table-responsive'>";
                        echo "<table class='table'>\n";
                        echo " <tr style='background-color: var(--gray200);'>\n";
                        echo "  <th class='text-center' colspan='2'>" . xlt('Notes') . "</th>\n";
                        echo " </tr>\n";
                        foreach ($aNotes as $key => $value) {
                            echo " <tr>\n";
                            echo "  <td class='align-top' style='padding:5px 5px;'>" . ($key + 1) . "</td>\n";
                            // <pre> tag because white space and a fixed font are often used to line things up.
                            echo "  <td><pre class='border-0 bg-white' style='white-space:pre-wrap;'>" . text($value) . "</pre></td>\n";
                            echo " </tr>\n";
                        }

                        echo "</table></div>\n";
                    }
                    ?>
                </td>
                <td class="border-0 text-right align-top">
                    <?php if ($input_form && !empty($ctx['priors_omitted']) /* empty($_POST['form_showall']) */) { ?>
                        <input type='submit' class='btn btn-primary' name='form_showall' value='<?php echo xla('Show All Results'); ?>'
                               title='<?php echo xla('Include all values reported for each result code'); ?>'/>
                    <?php } elseif ($input_form && !empty($_POST['form_showall'])) { ?>
                        <input type='submit' class='btn btn-primary' name='form_latest' value='<?php echo xla('Latest Results Only'); ?>'
                               title='<?php echo xla('Show only latest values reported for each result code'); ?>'/>
                    <?php } ?>
                    <?php if (empty($GLOBALS['PATIENT_REPORT_ACTIVE'])) { ?>
                        &nbsp;
                        <input type='button' class='btn btn-primary' value='<?php echo xla('Related Patient Notes'); ?>'
                            onclick='showpnotes(<?php echo attr_js($orderid); ?>)' />
                    <?php } ?>
                    <?php if ($input_form && $ctx['sign_list']) { ?>
                        &nbsp;
                        <input type='hidden' class='btn btn-primary' name='form_sign_list' value='<?php echo attr($ctx['sign_list']); ?>'/>
                        <input type='submit' class='btn btn-primary' name='form_sign' value='<?php echo xla('Sign Results'); ?>'
                               title='<?php echo xla('Mark these reports as reviewed'); ?>'/>
                    <?php } ?>
                    <?php if ($input_form) { ?>
                        &nbsp;
                        <input type='button' class='btn btn-danger' value='<?php echo xla('Close'); ?>' onclick='window.close()'/>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>

    <?php if ($input_form) { ?>
</form>
<?php } // end if input form ?>

    <?php
// end function generate_order_report
}
?>
