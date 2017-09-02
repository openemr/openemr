<?php
/**
 * Patient Records Request.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
use OpenEMR\Core\Header;

?>
<html>
<head>

    <title><?php echo xlt('Patient Records Request'); ?></title>

    <?php Header::setupHeader(); ?>

    <script language="JavaScript">
        $(document).ready(function() {
            $("#req_button").click(function() {
                // hide the button, show the message, and send the ajax call
                $('#req_button').hide();
                $('#openreq').show();
                top.restoreSession();
                $.post( "../../../library/ajax/amc_misc_data.php",
                {
                    amc_id: "provide_rec_pat_amc",
                    complete: false,
                    mode: "add_force",
                    patient_id: <?php echo attr($pid); ?>
                });
            });
        });
    </script>

</head>

<?php // collect data
  $recordRequest = sqlQuery("SELECT * FROM `amc_misc_data` WHERE `pid`=? AND `amc_id`='provide_rec_pat_amc' AND (`date_completed` IS NULL OR `date_completed`='') ORDER BY `date_created` DESC", array($pid));
?>

<body class="body_top">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="page-header">
                    <h2><?php echo xlt('Patient Records Request'); ?></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?php if (empty($recordRequest)) { ?>
                    <a href="javascript:void(0)" id="req_button" class="btn btn-default btn-save"><?php echo xlt('Patient Record Request'); ?></a>
                    <span class="lead" id="openreq" style="display:none"><?php echo xlt('The patient record request has been recorded.'); ?></span>
                <?php } else { ?>
                    <a href="javascript:void(0)" id="req_button" class="btn btn-default btn-save" style="display:none"><?php echo xlt('Patient Record Request'); ?></a>
                    <span class="lead" id="openreq"><?php echo xlt('There is already an open patient record request.'); ?></span>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
