<?php

/**
 * interface/patient_file/ccr_review_approve.php Approval screen for uploaded CCR XML.
 *
 * Approval screen for uploaded CCR XML.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Ajil P M <ajilpm@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../globals.php");
require_once(dirname(__FILE__) . "/../../library/parse_patient_xml.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$patient_data = array(
    'sex'                       => 'Sex',
    'pubpid'                => 'External ID',
    'street'                => 'Street',
    'city'                  => 'City',
    'state'                 => 'State',
    'postal_code'       => 'Postal Code',
);

if ($_POST["setval"] == 'approve') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    insertApprovedData($_REQUEST);
    $query = "UPDATE audit_master SET approval_status = '2' WHERE id=?";
    sqlQuery($query, array($_REQUEST['amid']));
    ?>
    <html>
        <head>
            <title><?php echo xlt('CCR Review and Approve');?></title>
            <?php Header::setupHeader(); ?>
        </head>
        <body class="body_top" >
            <center><?php echo xlt('Approved Successfully'); ?></center>
        </body>
    </html>
    <?php
    exit;
} elseif ($_POST["setval"] == 'discard') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $query = "UPDATE audit_master SET approval_status = '3' WHERE id=?";
    sqlQuery($query, array($_REQUEST['amid']));
    ?>
    <html>
        <head>
            <title><?php echo xlt('CCR Review and Approve');?></title>
            <?php Header::setupHeader(); ?>
        </head>
        <body class="body_top" >
            <center><?php echo xlt('Discarded'); ?></center>
        </body>
    </html>
    <?php
    exit;
}

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

?>
<html>
<head>
<title><?php echo xlt('CCR Review and Approve');?></title>
<?php Header::setupHeader(); ?>
<style>

table {
    color: #000;
    font: .85em/1.6em "Trebuchet MS",Verdana,sans-serif;
    border-collapse: collapse;
    margin: 0 auto;
    border: 1px solid #CCC;
}

tbody th,td {
    border-left: 0;
    padding: 8px;
}

tbody {
    background: #D4D4D4;
}

table table tbody tr {
    background: #EEEEEE;
}

.alternate{
    background-color: #C4C4C4;
}

</style>
<script>

function submit_form(val){
    document.getElementById('setval').value = val;
  top.restoreSession();
    document.forms['approveform'].submit();
}

</script>
</head>
<body class="body_top" >
<center>
<p><b><?php echo xlt('CCR Patient Review');?></b></p>
</center>
<form method="post" name="approveform" "onsubmit='return top.restoreSession()'" >
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <table border="0" width="90%;" >
        <tr>
            <td>
                <u><?php echo xlt('Demographics'); ?></u>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="95%" >
                    <tr>
                        <?php
                            $query_pd = sqlStatement("SELECT ad.id as adid, table_name, field_name, field_value FROM audit_master am JOIN audit_details ad ON ad.audit_master_id = am.id
								WHERE am.id = ? AND ad.table_name = 'patient_data' ORDER BY ad.id", array($_REQUEST['amid']));
                            $i = 0;
                            while ($res_pd = sqlFetchArray($query_pd)) {
                                if ($res_pd['field_name'] != 'lname' && $res_pd['field_name'] != 'fname' && $res_pd['field_name'] != 'DOB') {
                                    $i++;
                                    $query_oldpd = sqlQuery("SELECT " . escape_sql_column_name($res_pd['field_name'], array("patient_data")) . " AS val FROM patient_data WHERE pid = ?", array($_REQUEST['pid']));
                                    if ($res_pd['field_name'] == 'sex') {
                                        echo "<td>" . ($patient_data[$res_pd['field_name']] ? text($patient_data[$res_pd['field_name']]) : text($res_pd['field_name'])) . "</td>" .
                                            "<td><select name='" . attr($res_pd['table_name']) . "-" . attr($res_pd['field_name']) . "' style='width:150px;' >" .
                                        "<option value='Male' " . ($res_pd['field_value'] == 'Male' ? 'selected' : '' ) . " >" . xlt('Male') . "</option>" .
                                            "<option value='Female' " . ($res_pd['field_value'] == 'Female' ? 'selected' : '' ) . " >" . xlt('Female') . "</option></select>" .
                                        "<span style='color:red;padding-left:25px;' >" . text($query_oldpd['val']) . "</span></td>" .
                                            "<td><select name='" . attr($res_pd['table_name']) . "-" . attr($res_pd['field_name']) . "-sel'>" .
                                        "<option value='ignore' >" . xlt('Ignore') . "</option> " .
                                        "<option value='update' >" . xlt('Update') . "</option></select></td>";
                                    } else {
                                        echo "<td>" . ($patient_data[$res_pd['field_name']] ? text($patient_data[$res_pd['field_name']]) : text($res_pd['field_name'])) . "</td>" .
                                            "<td><input type='text' name='" . attr($res_pd['table_name']) . "-" . attr($res_pd['field_name']) . "' value='" . attr($res_pd['field_value']) . "' >" .
                                        "<span style='color:red;padding-left:25px;' >" . text($query_oldpd['val']) . "</span></td>" .
                                            "<td><select name='" . attr($res_pd['table_name']) . "-" . attr($res_pd['field_name']) . "-sel' >" .
                                        "<option value='ignore' >" . xlt('Ignore') . "</option><option value='update' >" . xlt('Update') . "</option></select></td>";
                                    }

                                    if ($i % 2 == 0) {
                                        if ($i % 4 == 2) {
                                            echo "</tr><tr class='alternate' >";
                                        } else {
                                            echo "</tr><tr>";
                                        }
                                    } else {
                                        echo "<td>&nbsp;&nbsp;&nbsp;</td>";
                                    }
                                }
                            }
                            ?>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                <u><?php echo xlt('Problems'); ?></u>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="95%" >
                    <tr>
                        <?php
                            $query_existing_prob = sqlStatement("SELECT * FROM lists WHERE pid = ? AND TYPE = 'medical_problem'", array($_REQUEST['pid']));
                            $result = array();
                        while ($res_existing_prob = sqlFetchArray($query_existing_prob)) {
                            array_push($result, $res_existing_prob);
                        }

                            $aud_res = createAuditArray($_REQUEST['amid'], 'lists1');
                        while ($res_existing_prob = array_shift($result)) {
                            if ($res_existing_prob['activity'] == 1) {
                                $activity = 'Active';
                            } else {
                                $activity = 'Inactive';
                            }

                            $set = 0;
                            $cnt = 0;
                            foreach ($aud_res['lists1'] as $k => $v) {
                                $cnt++;
                                if ($cnt % 2 == 0) {
                                    $class = 'alternate';
                                } else {
                                    $class = '';
                                }

                                if (in_array($res_existing_prob['diagnosis'], $aud_res['lists1'][$k])) {
                                    $set = 1;
                                    echo "<tr class='" . attr($class) . "' ><td>" . xlt('Title') . "</td><td><input type='text' name='lists1-title[]' value='' ></td>" .
                                    "<td>" . xlt('Code') . "</td>" .
                                    "<td><input type='text' name='lists1-diagnosis[]' value='" . attr($aud_res['lists1'][$k]['diagnosis']) . "' >" .
                                    "<input type='hidden' name='lists1-old-diagnosis[]' value='" . attr($res_existing_prob['diagnosis']) . "' ></td>" .
                                    "<td>" . xlt('Status') . "</td><td><input type='text' name='lists1-activity[]' value='" . attr($activity) . "' ></td>" .
                                    "<td rowspan='2' ><select name='lists1-sel[]'><option value='ignore' >" . xlt('Ignore') . "</option>" .
                                    "<option value='update' >" . xlt('Update') . "</option></select></td></tr>" .
                                    "<tr style='color:red' ><td>&nbsp;</td><td>" . text($res_existing_prob['title']) . "</td><td>&nbsp;</td>" .
                                    "<td>" . text($res_existing_prob['diagnosis']) . "</td>" .
                                    "<td>&nbsp;</td><td>" . xlt($activity) . "</td>";
                                    unset($aud_res['lists1'][$k]);
                                }
                            }

                            if ($set == 0) {
                                echo "<tr><td>" . xlt('Title') . "</td><td>" . text($res_existing_prob['title']) . "</td>" .
                                "<td>" . xlt('Code') . "</td><td>" . text($res_existing_prob['diagnosis']) . "</td>" .
                                "<td>" . xlt('Status') . "</td><td>" . xlt($activity) . "</td><td>&nbsp;</td>";
                            }

                            echo "</tr>";
                        }

                        foreach ($aud_res['lists1'] as $key => $val) {
                            if ($val['activity'] == 1) {
                                $activity = 'Active';
                            } else {
                                $activity = 'Inactive';
                            }

                            echo "<tr><td>" . xlt('Title') . "</td><td><input type='text' name='lists1-title[]' value='' ></td>" .
                            "<td>" . xlt('Code') . "</td><td><input type='text' name='lists1-diagnosis[]' value='" . attr($val['diagnosis']) . "' ></td>" .
                            "<td>" . xlt('Status') . "</td><td><input type='text' name='lists1-activity[]' value='" . attr($activity) . "' ></td>" .
                            "<td><select name='lists1-sel[]'><option value='ignore' >" . xlt('Ignore') . "</option>" .
                            "<option value='insert' >" . xlt('Insert') . "</option></select></td></tr>";
                        }
                        ?>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                <u><?php echo xlt('Allergy'); ?></u>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="95%" >
                    <tr>
                        <?php
                            $query_existing_alerts = sqlStatement("SELECT * FROM lists WHERE pid = ? AND TYPE = 'allergy'", array($_REQUEST['pid']));
                            $result = array();
                        while ($res_existing_alerts = sqlFetchArray($query_existing_alerts)) {
                            array_push($result, $res_existing_alerts);
                        }

                            $aud_res = createAuditArray($_REQUEST['amid'], 'lists2');
                        while ($res_existing_alerts = array_shift($result)) {
                            if ($res_existing_alerts['activity'] == 1) {
                                $activity = 'Active';
                            } else {
                                $activity = 'Inactive';
                            }

                            echo "<tr><td>" . xlt('Title') . "</td><td>" . text($res_existing_alerts['title']) . "</td>" .
                            "<td>" . xlt('Date Time') . "</td><td>" . text($res_existing_alerts['date']) . "</td>" .
                            "<td>" . xlt('Diagnosis') . "</td><td>" . text($res_existing_alerts['diagnosis']) . "</td>" .
                            "<td>" . xlt('Reaction') . "</td><td>" . text($res_existing_alerts['reaction']) . "</td><td>&nbsp;</td></tr>";
                        }

                        foreach ($aud_res['lists2'] as $key => $val) {
                            if ($val['activity'] == 1) {
                                $activity = 'Active';
                            } else {
                                $activity = 'Inactive';
                            }

                            echo "<tr><td>" . xlt('Title') . "</td><td><input type='text' name='lists2-title[]' value='" . attr($val['title']) . "' ></td>" .
                            "<td>" . xlt('Date Time') . "</td><td><input type='text' name='lists2-date[]' value='" . attr($val['date']) . "' ></td>" .
                            "<td>" . xlt('Diagnosis') . "</td><td><input type='text' name='lists2-diagnosis[]' value='" . attr($val['diagnosis']) . "' ></td>" .
                            "<td>" . xlt('Reaction') . "</td><td><input type='text' name='lists2-reaction[]' value='" . attr($val['reaction']) . "' ></td>" .
                            "<td><select name='lists2-sel[]'><option value='ignore' >" . xlt('Ignore') . "</option>" .
                            "<option value='insert' >" . xlt('Insert') . "</option></select></td>" .
                            "<input type='hidden' name='lists2-type[]' value='" . attr($val['type']) . "' ></tr>";
                        }
                        ?>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                <u><?php echo xlt('Medications'); ?></u>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="95%" >
                    <tr>
                        <?php
                            $query_existing_medications = sqlStatement("SELECT * FROM prescriptions WHERE patient_id = ?", array($_REQUEST['pid']));
                            $result = array();
                        while ($res_existing_medications = sqlFetchArray($query_existing_medications)) {
                            array_push($result, $res_existing_medications);
                        }

                            $aud_res = createAuditArray($_REQUEST['amid'], 'prescriptions');
                        while ($res_existing_medications = array_shift($result)) {
                            if ($res_existing_medications['active'] == 1) {
                                $activity = 'Active';
                            } else {
                                $activity = 'Inactive';
                            }

                            echo "<tr><td>" . xlt('Name') . "</td><td>" . text($res_existing_medications['drug']) . "</td>" .
                            "<td>" . xlt('Date') . "</td><td>" . text($res_existing_medications['date_added']) . "</td>" .
                            "<td>" . xlt('Status') . "</td><td>" . xlt($activity) . "</td><td rowspan='2' >&nbsp;</td></tr><tr><td>" . xlt('Form') . "</td>" .
                            "<td>" . text($res_existing_medications['form']) . "&nbsp;&nbsp;&nbsp;" . xlt('Strength') . "&nbsp;&nbsp;&nbsp;" . text($res_existing_medications['size']) . "</td>" .
                            "<td>" . xlt('Quantity') . "</td><td>" . text($res_existing_medications['quantity']) . "</td>" .
                            "<td>" . xlt('Refills') . "</td><td>" . text($res_existing_medications['refills']) . "</td></tr>";
                        }

                        foreach ($aud_res['prescriptions'] as $key => $val) {
                            if ($val['active'] == 1) {
                                $activity = 'Active';
                            } else {
                                $activity = 'Inactive';
                            }

                            echo "<tr><td>" . xlt('Name') . "</td><td><input type='text' name='prescriptions-drug[]' value='" . attr($val['drug']) . "' ></td>" .
                            "<td>" . xlt('Date') . "</td><td><input type='text' name='prescriptions-date_added[]' value='" . attr($val['date_added']) . "' ></td>" .
                            "<td>" . xlt('Status') . "</td><td><input type='text' name='prescriptions-active[]' value='" . attr($activity) . "' ></td><td rowspan='2' >" .
                            "<select name='prescriptions-sel[]'><option value='ignore' >" . xlt('Ignore') . "</option>" .
                            "<option value='insert' >" . xlt('Insert') . "</option></select></td></tr><tr><td>" . xlt('Form') . "</td>" .
                            "<td><input type='text' size='8' name='prescriptions-form[]' value='" . attr($val['form']) . "' >" .
                            "&nbsp;&nbsp;&nbsp;" . xlt('Strength') . "&nbsp;&nbsp;&nbsp;<input type='text' size='7' name='prescriptions-size[]' value='" . attr($val['size']) . "' ></td>" .
                            "<td>" . xlt('Quantity') . "</td><td><input type='text' name='prescriptions-quantity[]' value='" . attr($val['quantity']) . "' ></td>" .
                            "<td>" . xlt('Refills') . "</td><td><input type='text' name='prescriptions-refills[]' value='" . attr($val['refills']) . "' ></td></tr>";
                        }
                        ?>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                <u><?php echo xlt('Immunizations'); ?></u>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="95%" >
                    <tr>
                        <?php
                            $query_existing_immunizations = sqlStatement("SELECT * FROM immunizations WHERE patient_id = ? AND added_erroneously = 0", array($_REQUEST['pid']));
                            $result = array();
                        while ($res_existing_immunizations = sqlFetchArray($query_existing_immunizations)) {
                            array_push($result, $res_existing_immunizations);
                        }

                            $aud_res = createAuditArray($_REQUEST['amid'], 'immunizations');
                        while ($res_existing_immunizations = array_shift($result)) {
                            echo "<tr><td>" . xlt('Administered Date') . "</td>" .
                            "<td>" . text($res_existing_immunizations['administered_date']) . "</td>" .
                            "<td>" . xlt('Note') . "</td><td>" . text($res_existing_immunizations['note']) . "</td>" .
                            "<td>&nbsp;</td></tr>";
                        }

                        foreach ($aud_res['immunizations'] as $key => $val) {
                            echo "<tr><td>" . xlt('Administered Date') . "</td>" .
                            "<td><input type='text' name='immunizations-administered_date[]' value='" . attr($val['administered_date']) . "' ></td>" .
                            "<td>" . xlt('Note') . "</td><td><input type='text' name='immunizations-note[]' value='" . attr($val['note']) . "' ></td>" .
                            "<td><select name='immunizations-sel[]'><option value='ignore' >" . xlt('Ignore') . "</option>" .
                            "<option value='insert' >" . xlt('Insert') . "</option></select></td></tr>";
                        }
                        ?>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                &nbsp;
            </td>
        </tr>
        <tr>
            <td>
                <u><?php echo xlt('Lab Results'); ?></u>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="95%" >
                    <tr>
                        <?php
                            $query_existing_lab_results = sqlStatement("SELECT * FROM procedure_order AS po LEFT JOIN procedure_order_code AS poc
								ON poc.procedure_order_id = po.procedure_order_id LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id
								LEFT JOIN procedure_result AS prs ON prs.procedure_report_id = pr.procedure_report_id WHERE patient_id = ?", array($_REQUEST['pid']));
                            $result = array();
                            while ($res_existing_lab_results = sqlFetchArray($query_existing_lab_results)) {
                                array_push($result, $res_existing_lab_results);
                            }

                            $aud_res = createAuditArray($_REQUEST['amid'], 'procedure_result,procedure_type');
                            while ($res_existing_lab_results = array_shift($result)) {
                                echo "<tr><td>" . xlt('Name') . "</td>" .
                                    "<td>" . text($res_existing_lab_results['result_text']) . "</td>" .
                                    "<td>" . xlt('Date') . "</td><td>" . text($res_existing_lab_results['date_ordered']) . "</td>" .
                                    "<td>" . xlt('Result') . "</td><td>" . text($res_existing_lab_results['result']) . "</td>" .
                                    "<td>" . xlt('Abnormal') . "</td><td>" . text($res_existing_lab_results['abnormal']) . "</td>" .
                                    "<td>&nbsp;</td></tr>";
                            }

                            foreach ($aud_res['procedure_result,procedure_type'] as $key => $val) {
                                echo "<tr><td>" . xlt('Name') . "</td>" .
                                    "<td><input type='text' name='procedure_type-name[]' value='" . attr($val['name']) . "' ></td>" .
                                    "<td>" . xlt('Date') . "</td><td><input type='text' name='procedure_result-date[]' value='" . attr($val['date']) . "' ></td>" .
                                    "<td>" . xlt('Result') . "</td><td><input type='text' name='procedure_result-result[]' value='" . attr($val['result']) . "' ></td>" .
                                    "<td>" . xlt('Abnormal') . "</td><td><input type='text' name='procedure_result-abnormal[]' value='" . attr($val['abnormal']) . "' ></td>" .
                                    "<td><select name='procedure_result-sel[]'><option value='ignore' >" . xlt('Ignore') . "</option>" .
                                "<option value='insert' >" . xlt('Insert') . "</option></select></td></tr>";
                            }
                            ?>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" >
                <input type="button" name="approve" value="<?php echo xla('Approve'); ?>" onclick="top.restoreSession();submit_form('approve');" >
                <input type="button" name="discard" value="<?php echo xla('Discard'); ?>" onclick="top.restoreSession();submit_form('discard');" >
        <?php
        $aud_res = createAuditArray($_REQUEST['amid'], 'documents');
        ?>
        <input type="hidden" name="doc_id" id="doc_id" value="<?php echo attr($aud_res['documents']['']['id']); ?>" >
                <input type="hidden" name="setval" id="setval" value="" >
            </td>
        </tr>
    </table>
</form>
</body>
</html>
