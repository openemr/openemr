<?php
/**
 * interface/patient_file/ccr_pending_approval.php Approval screen for uploaded CCR XML.
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
require_once(dirname(__FILE__) . "/../../library/options.inc.php");
require_once(dirname(__FILE__) . "/../../library/patient.inc");
require_once(dirname(__FILE__) . "/../../library/parse_patient_xml.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (isset($_GET['approve']) && $_GET['approve'] == 1) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    insert_patient($_GET['am_id']);
    ?>
  <html>
        <head>
            <title><?php echo xlt('CCR Approve');?></title>
            <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css" >
        </head>
        <body class="body_top" >
            <center><?php echo xlt('Approved Successfully'); ?></center>
        </body>
    </html>
    <?php
    exit;
}

?>
<html>
<head>
<title><?php echo xlt('Pending Approval');?></title>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<style>

table {
    color: #000;
    font: .8em/1.6em "Trebuchet MS",Verdana,sans-serif;
    border-collapse: collapse;
    margin: 0 auto;
    border: 1px solid #CCC;
}

tbody th,td {
    border-left: 0;
    padding: 8px
}

tbody{
    background: rgb(255,255,255); /* Old browsers */
    background: -moz-linear-gradient(top, rgba(255,255,255,1) 0%, rgba(229,229,229,1) 100%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,1)), color-stop(100%,rgba(229,229,229,1))); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(229,229,229,1) 100%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(229,229,229,1) 100%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, rgba(255,255,255,1) 0%,rgba(229,229,229,1) 100%); /* IE10+ */
    background: linear-gradient(to bottom, rgba(255,255,255,1) 0%,rgba(229,229,229,1) 100%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#e5e5e5',GradientType=0 ); /* IE6-9 */
}

tbody th {
    color: #3e3e3e;
    padding: 5px 10px;
    background: #f5f6f6; /* Old browsers */
    background: -moz-linear-gradient(top, #f5f6f6 0%, #dbdce2 21%, #b8bac6 49%, #dddfe3 80%, #f5f6f6 100%); /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f5f6f6), color-stop(21%,#dbdce2), color-stop(49%,#b8bac6), color-stop(80%,#dddfe3), color-stop(100%,#f5f6f6)); /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #f5f6f6 0%,#dbdce2 21%,#b8bac6 49%,#dddfe3 80%,#f5f6f6 100%); /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #f5f6f6 0%,#dbdce2 21%,#b8bac6 49%,#dddfe3 80%,#f5f6f6 100%); /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #f5f6f6 0%,#dbdce2 21%,#b8bac6 49%,#dddfe3 80%,#f5f6f6 100%); /* IE10+ */
    background: linear-gradient(to bottom, #f5f6f6 0%,#dbdce2 21%,#b8bac6 49%,#dddfe3 80%,#f5f6f6 100%); /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f5f6f6', endColorstr='#f5f6f6',GradientType=0 ); /* IE6-9 */
    border-bottom: 1px solid;
}

tbody tr.odd {
    background-color: #F7F7F7;
    color: #666
}

.button-link {
    padding: 3px 10px;
    background: #c0c0c0;
    color: #000 !important;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    border: solid 1px #000000;
    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
    -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
    -webkit-transition-duration: 0.2s;
    -moz-transition-duration: 0.2s;
    transition-duration: 0.2s;
    -webkit-user-select:none;
    -moz-user-select:none;
    -ms-user-select:none;
    user-select:none;
}

.button-link:hover {
    background: #808080;
    border: solid 1px #000000;
    text-decoration: none;
    color: #FFF !important;
}

</style>
<script type="text/javascript" >

</script>
</head>
<body class="body_top" >
<center>
<p><b><?php echo xlt('Pending Approval');?></b></p>
</center>
<form method="post" name="approve" "onsubmit='return top.restoreSession()'" >
<center>
<table style="width:80%;" border="0" >
    <tr>
        <th>
            <?php echo xlt('Patient Name'); ?>
        </th>
        <th>
            <?php echo xlt('Match Found'); ?>
        </th>
        <th>
            <?php echo xlt('Action'); ?>
        </th>
    </tr>
    <?php
    $query = sqlStatement("SELECT *,am.id amid,CONCAT(ad.field_value,' ',ad1.field_value) as pat_name FROM audit_master am JOIN audit_details ad ON
		ad.audit_master_id = am.id AND ad.table_name = 'patient_data' AND ad.field_name = 'lname' JOIN audit_details ad1 ON
		ad1.audit_master_id = am.id AND ad1.table_name = 'patient_data' AND ad1.field_name = 'fname' WHERE type='11' AND approval_status='1'");
    if (sqlNumRows($query) > 0) {
        while ($res = sqlFetchArray($query)) {
            $dup_query = sqlStatement(
                "SELECT * FROM audit_master am JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name = 'patient_data'
			AND ad.field_name = 'lname' JOIN audit_details ad1 ON ad1.audit_master_id = am.id AND ad1.table_name = 'patient_data' AND
			ad1.field_name = 'fname' JOIN audit_details ad2 ON ad2.audit_master_id = am.id AND ad2.table_name = 'patient_data' AND ad2.field_name = 'DOB'
			JOIN patient_data pd ON pd.lname = ad.field_value AND pd.fname = ad1.field_value AND pd.DOB = DATE(ad2.field_value) WHERE am.id = ?",
                array($res['amid'])
            );
            ?>
    <tr>
        <td class="bold" >
            <?php echo text($res['pat_name']); ?>
        </td>
            <?php
            if (sqlNumRows($dup_query)>0) {
                $dup_res = sqlFetchArray($dup_query);
                ?>
        <td align="center" class="bold" >
                <?php echo xlt('Yes'); ?>
        </td>
        <td align="center" >
            <a href="ccr_review_approve.php?revandapprove=1&amid=<?php echo attr_url($res['amid']); ?>&pid=<?php echo attr_url($dup_res['pid']); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" class="button-link" onclick="top.restoreSession()" ><?php echo xlt('Review & Approve'); ?></a>
        </td>
                <?php
            } else {
                ?>
        <td align="center" class="bold" >
                <?php echo xlt('No'); ?>
        </td>
        <td align="center" >
            <a href="ccr_pending_approval.php?approve=1&am_id=<?php echo attr_url($res['amid']); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" class="button-link" onclick="top.restoreSession()" ><?php echo xlt('Approve'); ?></a>
        </td>
                <?php
            }
            ?>
    </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="3" >
                <?php echo xlt('Nothing Pending for Approval')."."; ?>
            </td>
        </tr>
        <?php
    }
    ?>
</table>
</center>
</form>
</body>
</html>
