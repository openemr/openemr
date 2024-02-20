<?php

/**
 * This report lists  patient immunizations for a given date range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c) 2011 Ensoftek Inc.
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2022-2023 Stephen Waite <stephen.waite@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (
        new TwigContainer(
            null,
            $GLOBALS['kernel']
        ))->getTwig()->render(
            'core/unauthorized.html.twig',
            ['pageTitle' => xl("Immunization Registry")]
        );
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : '';
$form_to_date = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : '';

function tr($a)
{
    return (str_replace(' ', '^', $a));
}

function format_cvx_code($cvx_code)
{

    if ($cvx_code < 10) {
        return "0$cvx_code";
    }

    return $cvx_code;
}

function format_phone($phone)
{

    $phone = preg_replace("/[^0-9]/", "", $phone);
    switch (strlen($phone)) {
        case 7:
            return tr(preg_replace("/([0-9]{3})([0-9]{4})/", "000 $1$2", $phone));
        case 10:
            return tr(preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 $2$3", $phone));
        default:
            return tr("000 0000000");
    }
}

function format_ethnicity($ethnicity)
{

    switch ($ethnicity) {
        case "hisp_or_latin":
            return ("H^Hispanic or Latino^HL70189");
        case "not_hisp_or_latin":
            return ("N^not Hispanic or Latino^HL70189");
        default: // Unknown
            return ("U^Unknown^HL70189");
    }
}

$sqlBindArray = array();
$query =
    "select " .
    "i.patient_id as patientid, " .
    "p.language, " .
    "i.cvx_code , ";
if (!empty($_POST['form_get_hl7']) && ($_POST['form_get_hl7'] === 'true')) {
    $query .=
        "DATE_FORMAT(p.DOB,'%Y%m%d') as DOB, " .
        "concat(p.street, '^^', p.city, '^', p.state, '^', p.postal_code) as address, " .
        "p.country_code, " .
        "p.phone_home, " .
        "p.phone_biz, " .
        "p.status, " .
        "p.sex, " .
        "p.ethnoracial, " .
        "p.race, " .
        "p.ethnicity, " .
        "c.code_text, " .
        "c.code, " .
        "c.code_type, " .
        "DATE_FORMAT(i.vis_date,'%Y%m%d') as immunizationdate, " .
        "DATE_FORMAT(i.administered_date,'%Y%m%d') as administered_date, " .
        "i.lot_number as lot_number, " .
        "i.manufacturer as manufacturer, " .
        "concat(p.fname, '^', p.lname) as patientname, ";
} else {
    $query .= "concat(p.fname, ' ',p.mname,' ', p.lname) as patientname, " .
        "i.vis_date as immunizationdate, ";
}

$query .=
    "i.id as immunizationid, c.code_text_short as immunizationtitle " .
    "from immunizations i, patient_data p, codes c " .
    "left join code_types ct on c.code_type = ct.ct_id " .
    "where " .
    "ct.ct_key='CVX' and ";

if (!empty($form_from_date)) {
    $query .= "i.vis_date >= ? and ";
    array_push($sqlBindArray, $form_from_date);
}

if (!empty($form_to_date)) {
    $query .= "i.vis_date <= ? and ";
    array_push($sqlBindArray, $form_to_date);
}

$form_code = isset($_POST['form_code']) ? $_POST['form_code'] : array();
if (empty($form_code)) {
    $query_codes = '';
} else {
    $query_codes = "c.id in (";
    $codes = '';
    foreach ($form_code as $code) {
        $codes .= '?,';
        array_push($sqlBindArray, $code);
    }
    $codes = substr($codes, 0, -1);
    $query_codes .= $codes . ") and ";
}

$query .= "i.patient_id=p.pid and " .
    $query_codes .
    "i.cvx_code = c.code and ";

//do not show immunization added erroneously
$query .= "i.added_erroneously = 0";

$D = "\r";
$nowdate = date('Ymd');
$now = date('YmdGi');
$now1 = date('Y-m-d G:i');
$filename = "imm_reg_" . $now . ".hl7";

// GENERATE HL7 FILE
if (!empty($_POST['form_get_hl7']) && ($_POST['form_get_hl7'] === 'true')) {
    $content = '';

    $res = sqlStatement($query, $sqlBindArray);

    while ($r = sqlFetchArray($res)) {
        $content .= "MSH|^~\&|OPENEMR||||$nowdate||" .
            "VXU^V04^VXU_V04|OPENEMR-110316102457117|P|2.5.1" .
            "$D";
        if ($r['sex'] === 'Male') {
            $r['sex'] = 'M';
        }

        if ($r['sex'] === 'Female') {
            $r['sex'] = 'F';
        }

        if ($r['status'] === 'married') {
            $r['status'] = 'M';
        }

        if ($r['status'] === 'single') {
            $r['status'] = 'S';
        }

        if ($r['status'] === 'divorced') {
            $r['status'] = 'D';
        }

        if ($r['status'] === 'widowed') {
            $r['status'] = 'W';
        }

        if ($r['status'] === 'separated') {
            $r['status'] = 'A';
        }

        if ($r['status'] === 'domestic partner') {
            $r['status'] = 'P';
        }

        $content .= "PID|" . // [[ 3.72 ]]
            "|" . // 1. Set id
            "|" . // 2. (B)Patient id
            $r['patientid'] . "^^^MPI&2.16.840.1.113883.19.3.2.1&ISO^MR" . "|" . // 3. (R) Patient identifier list. TODO: Hard-coded the OID from NIST test.
            "|" . // 4. (B) Alternate PID
            $r['patientname'] . "|" . // 5.R. Name
            "|" . // 6. Mather Maiden Name
            $r['DOB'] . "|" . // 7. Date, time of birth
            $r['sex'] . "|" . // 8. Sex
            "|" . // 9.B Patient Alias
            "2106-3^" . $r['race'] . "^HL70005" . "|" . // 10. Race // Ram change
            $r['address'] . "^^M" . "|" . // 11. Address. Default to address type  Mailing Address(M)
            "|" . // 12. county code
            "^PRN^^^^" . format_phone($r['phone_home']) . "|" . // 13. Phone Home. Default to Primary Home Number(PRN)
            "^WPN^^^^" . format_phone($r['phone_biz']) . "|" . // 14. Phone Work.
            "|" . // 15. Primary language
            $r['status'] . "|" . // 16. Marital status
            "|" . // 17. Religion
            "|" . // 18. patient Account Number
            "|" . // 19.B SSN Number
            "|" . // 20.B Driver license number
            "|" . // 21. Mathers Identifier
            format_ethnicity($r['ethnicity']) . "|" . // 22. Ethnic Group
            "|" . // 23. Birth Plase
            "|" . // 24. Multiple birth indicator
            "|" . // 25. Birth order
            "|" . // 26. Citizenship
            "|" . // 27. Veteran military status
            "|" . // 28.B Nationality
            "|" . // 29. Patient Death Date and Time
            "|" . // 30. Patient Death Indicator
            "|" . // 31. Identity Unknown Indicator
            "|" . // 32. Identity Reliability Code
            "|" . // 33. Last Update Date/Time
            "|" . // 34. Last Update Facility
            "|" . // 35. Species Code
            "|" . // 36. Breed Code
            "|" . // 37. Breed Code
            "|" . // 38. Production Class Code
            "" . // 39. Tribal Citizenship
            "$D";
        $content .= "ORC" . // ORC mandatory for RXA
            "|" .
            "RE" .
            "$D";
        $content .= "RXA|" .
            "0|" . // 1. Give Sub-ID Counter
            "1|" . // 2. Administrattion Sub-ID Counter
            $r['administered_date'] . "|" . // 3. Date/Time Start of Administration
            $r['administered_date'] . "|" . // 4. Date/Time End of Administration
            format_cvx_code($r['code']) . "^" . $r['immunizationtitle'] . "^" . "CVX" . "|" . // 5. Administration Code(CVX)
            "999|" . // 6. Administered Amount. TODO: Immunization amt currently not captured in database, default to 999(not recorded)
            "|" . // 7. Administered Units
            "|" . // 8. Administered Dosage Form
            "|" . // 9. Administration Notes
            "|" . // 10. Administering Provider
            "|" . // 11. Administered-at Location
            "|" . // 12. Administered Per (Time Unit)
            "|" . // 13. Administered Strength
            "|" . // 14. Administered Strength Units
            $r['lot_number'] . "|" . // 15. Substance Lot Number
            "|" . // 16. Substance Expiration Date
            "MSD" . "^" . $r['manufacturer'] . "^" . "HL70227" . "|" . // 17. Substance Manufacturer Name
            "|" . // 18. Substance/Treatment Refusal Reason
            "|" . // 19.Indication
            "|" . // 20.Completion Status
            "A" . // 21.Action Code - RXA
            "$D";
    }

    // send the header here
    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename=' . $filename);

    // put the content in the file
    echo($content);
    exit;
}
?>
<html>
<head>
    <title><?php echo xlt('Immunization Registry'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
        function confirmHl7() {
            let msg = <?php echo js_escape(xlt('This step will generate a file which you have to save for future use.') .
                '<br />' . xlt('The file cannot be generated again. Do you want to proceed?')); ?>;
            dialog.confirm(msg).then(returned => {
                if (returned === true) {
                    $('#form_get_hl7').attr('value', 'true');
                    $('#theform').submit();
                } else {
                    return false;
                }
            });
        }

        $(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });
    </script>

    <style>
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }

            #report_parameters_daterange {
                visibility: visible;
                display: inline;
                margin-bottom: 10px;
            }

            #report_results table {
                margin-top: 0px;
            }
        }

        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }

            #report_results {
                width: 100%;
            }
        }
    </style>
</head>

<body class="body_top">

    <span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Immunization Registry'); ?></span>

    <div id="report_parameters_daterange">
        <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
    </div>

    <form name='theform' id='theform' method='post' action='immunization_report.php' onsubmit='return top.restoreSession()'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <div id="report_parameters">
            <input type='hidden' name='form_refresh' id='form_refresh' value='' />
            <input type='hidden' name='form_get_hl7' id='form_get_hl7' value='' />
            <input type="hidden" name="form_export" id="form_export" value="" />
            <table>
                <tr>
                    <td class='w-50'>
                        <div style='float:left'>
                            <table class='text'>
                                <tr>
                                    <td class='col-form-label'>
                                        <?php echo xlt('Codes'); ?>:
                                    </td>
                                    <td>
                                        <?php
                                        // Build a drop-down list of codes.
                                        //
                                        $query1 = "select id, concat('CVX:',code) as name from codes " .
                                            " left join code_types ct on codes.code_type = ct.ct_id " .
                                            " where ct.ct_key='CVX' ORDER BY name";
                                        $cres = sqlStatement($query1);
                                        echo "   <select multiple='multiple' size='3' name='form_code[]' class='form-control'>\n";
                                        //echo "    <option value=''>-- " . xl('All Codes') . " --\n";
                                        while ($crow = sqlFetchArray($cres)) {
                                            $codeid = $crow['id'];
                                            echo "    <option value='" . attr($codeid) . "'";
                                            if (in_array($codeid, $form_code)) {
                                                echo " selected";
                                            }
                                            echo ">" . text($crow['name']) . "\n";
                                        }

                                        echo "   </select>\n";
                                        ?>
                                    </td>
                                    <td class='col-form-label'>
                                        <?php echo xlt('From VIS Date'); ?>:
                                    </td>
                                    <td>
                                        <input type='text' name='form_from_date' id="form_from_date"
                                            class='datepicker form-control'
                                            size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>' />
                                    </td>
                                    <td class='col-form-label'>
                                        <?php echo xlt('To VIS Date{{Range}}'); ?>:
                                    </td>
                                    <td>
                                        <input type='text' name='form_to_date' id="form_to_date"
                                            class='datepicker form-control'
                                            size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>' />
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td class='h-100' valign='middle'>
                        <table class='w-100 h-100' style='border-left:1px solid;'>
                            <tr>
                                <td>
                                    <div class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href='#' class='btn btn-secondary btn-save'
                                                onclick='
                                                $("#form_refresh").attr("value","true");
                                                $("#form_get_hl7").attr("value","false");
                                                $("#theform").submit();
                                                '>
                                                <?php echo xlt('Refresh'); ?>
                                            </a>
                                            <?php if (!empty($_POST['form_refresh'])) { ?>
                                                <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                                    <?php echo xlt('Print'); ?>
                                                </a>
                                                <a href='#' class='btn btn-secondary btn-transmit' onclick="confirmHl7()">
                                                    <?php echo xlt('Get HL7'); ?>
                                                </a>
                                                <a href='#' type="submit" class='btn btn-secondary btn-sheet'
                                                       onclick='exportData()'>
                                                    <?php echo xlt('Export'); ?>
                                                </a>

                                            <?php } ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div> <!-- end of parameters -->

        <?php
        if (!empty($_POST['form_refresh'])) {
            ?>
            <div id="report_results">
                <table class='table'>
                    <thead class='thead-light'>
                    <tr>
                        <th> <?php echo xlt('Patient ID'); ?> </th>
                        <th> <?php echo xlt('Patient Name'); ?> </th>
                        <th> <?php echo xlt('Immunization Code'); ?> </th>
                        <th> <?php echo xlt('Immunization Title'); ?> </th>
                        <th> <?php echo xlt('Immunization Date'); ?> </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $total = 0;

                    $res = sqlStatement($query, $sqlBindArray);

                    while ($row = sqlFetchArray($res)) {
                        $rows[] = $row;
                        ?>
                        <tr>
                            <td>
                                <?php echo text($row['patientid']); ?>
                            </td>
                            <td>
                                <?php echo text($row['patientname']); ?>
                            </td>
                            <td>
                                <?php echo text($row['cvx_code']); ?>
                            </td>
                            <td>
                                <?php echo text($row['immunizationtitle']); ?>
                            </td>
                            <td>
                                <?php echo text($row['immunizationdate']); ?>
                            </td>
                        </tr>
                        <?php
                        ++$total;
                    }
                    ?>
                    <tr class="report_totals">
                        <td colspan='9'>
                            <?php echo xlt('Total Number of Immunizations'); ?>
                            :
                            <?php echo text($total); ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div> <!-- end of results -->
        <?php } else { ?>
            <div class='text'>
                <?php echo xlt('Click Refresh to view all results, or please input search criteria above to view specific results.'); ?>
            </div>
        <?php } ?>
    </form>
    <script>
        
        function exportData() {
            let data = <?php echo json_encode($rows ?? ''); ?>;
            let csrf_token = <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
            dlgopen(
                "../../library/ajax/immunization_export.php?csrf_token_form=" + encodeURIComponent(csrf_token) +
                    "&data=" + encodeURIComponent(data),
                'Export',
                'modal-xs',
                300,
                false,
                'Export',
                {
                    buttons: [
                        {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
                    ]
                }
            );
            return false;
        }
    </script>
</body>
</html>
