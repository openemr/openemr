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
use OpenEMR\Core\Header;

if (isset($_GET['approve']) && $_GET['approve'] == 1) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    insert_patient($_GET['am_id']);
    ?>
<html>
    <head>
        <?php Header::setupHeader(); ?>
        <title><?php echo xlt('CCR Approve');?></title>
    </head>
    <body>
        <div class="container mt-3 text-center">
          <?php echo xlt('Approved Successfully'); ?>
        </div>
    </body>
</html>
    <?php
    exit;
}

?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Pending Approval');?></title>
</head>
<body>
    <div class="container mt-3">
        <h2 class="text-center"><?php echo xlt('Pending Approval');?></h2>
        <form class="text-center" method="post" name="approve" onsubmit="return top.restoreSession()">
            <div class="table-responsive">
                <table class="table table-striped table-borderless">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col"><?php echo xlt('Patient Name'); ?></th>
                            <th scope="col"><?php echo xlt('Match Found'); ?></th>
                            <th scope="col"><?php echo xlt('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = sqlStatement("SELECT *,am.id amid,CONCAT(ad.field_value,' ',ad1.field_value) as pat_name FROM audit_master am JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name = 'patient_data' AND ad.field_name = 'lname' JOIN audit_details ad1 ON ad1.audit_master_id = am.id AND ad1.table_name = 'patient_data' AND ad1.field_name = 'fname' WHERE type='11' AND approval_status='1'");
                        if (sqlNumRows($query) > 0) {
                            while ($res = sqlFetchArray($query)) {
                                $dup_query = sqlStatement("SELECT * FROM audit_master am JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name = 'patient_data' AND ad.field_name = 'lname' JOIN audit_details ad1 ON ad1.audit_master_id = am.id AND ad1.table_name = 'patient_data' AND ad1.field_name = 'fname' JOIN audit_details ad2 ON ad2.audit_master_id = am.id AND ad2.table_name = 'patient_data' AND ad2.field_name = 'DOB' JOIN patient_data pd ON pd.lname = ad.field_value AND pd.fname = ad1.field_value AND pd.DOB = DATE(ad2.field_value) WHERE am.id = ?", array($res['amid']));
                                ?>
                        <tr>
                                <td class="font-weight-bold">
                                    <?php echo text($res['pat_name']); ?>
                                </td>
                                    <?php
                                    if (sqlNumRows($dup_query) > 0) {
                                        $dup_res = sqlFetchArray($dup_query);
                                        ?>
                                    <td class="text-center font-weight-bold">
                                        <?php echo xlt('Yes'); ?>
                                    </td>
                                <td class="text-center">
                                    <a href="ccr_review_approve.php?revandapprove=1&amid=<?php echo attr_url($res['amid']); ?>&pid=<?php echo attr_url($dup_res['pid']); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" class="btn btn-primary" onclick="top.restoreSession()" ><?php echo xlt('Review & Approve'); ?></a>
                                </td>
                                        <?php
                                    } else { ?>
                                <td class="text-center font-weight-bold">
                                        <?php echo xlt('No'); ?>
                                </td>
                                <td class="text-center">
                                    <a href="ccr_pending_approval.php?approve=1&am_id=<?php echo attr_url($res['amid']); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" class="btn btn-primary" onclick="top.restoreSession()" ><?php echo xlt('Approve'); ?></a>
                                </td>
                                    <?php } ?>
                        </tr>
                                <?php
                            }
                        } else {
                            ?>
                        <tr>
                            <td colspan="3">
                                <?php echo xlt('Nothing Pending for Approval') . "."; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</body>
</html>
