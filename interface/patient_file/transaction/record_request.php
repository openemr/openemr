<?php

/**
 * Patient Records Request.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient Records Request")]);
    exit;
}

?>
<html>
<head>
    <title><?php echo xlt('Patient Records Request'); ?></title>
    <?php Header::setupHeader(); ?>

    <script>
        $(function () {
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
                    patient_id: <?php echo js_escape($pid); ?>,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                });
            });
        });
    </script>
</head>

<?php // collect data
$recordRequest = sqlQuery("SELECT * FROM `amc_misc_data` WHERE `pid`=? AND `amc_id`='provide_rec_pat_amc' AND " .
    dateEmptySql('date_completed', true) .
    "ORDER BY `date_created` DESC", array($pid));
?>

<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Patient Records Request'); ?></h2>
            </div>
        </div>
        <div class="row">
            <div class="col-12 jumbotron py-4">
                <?php if (empty($recordRequest)) { ?>
                    <a href="javascript:void(0)" id="req_button" class="btn btn-primary btn-save"><?php echo xlt('Patient Record Request'); ?></a>
                    <span class="lead" id="openreq" style="display:none"><?php echo xlt('The patient record request has been recorded.'); ?></span>
                <?php } else { ?>
                    <a href="javascript:void(0)" id="req_button" class="btn btn-primary btn-save" style="display:none"><?php echo xlt('Patient Record Request'); ?></a>
                    <span class="lead" id="openreq"><?php echo xlt('There is already an open patient record request.'); ?></span>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
