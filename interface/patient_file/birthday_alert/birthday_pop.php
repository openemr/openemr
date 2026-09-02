<?php

/**
 * Birthday alert .
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/*picture free taken from https://pixabay.com/en/balloons-party-celebration-floating-154949*/
require_once("../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!AclMain::aclCheckCore('patients', 'appt')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for patients/appt: Birthday Alert", xl("Birthday Alert"));
}

// Deep-linked popup: bootstrap session pid from the request and drive every
// downstream reference (including the AJAX turnoff call) from session pid.
$rawRequestPid = $_GET['pid'] ?? null;
$requestPid = is_scalar($rawRequestPid) ? (int)$rawRequestPid : 0;
$sessionPid = $session->get('pid');
if ($requestPid > 0 && ($sessionPid === null || $sessionPid === '' || $sessionPid === 0 || $sessionPid === '0')) {
    setpid($requestPid);
}
$pid = PatientSessionUtil::getPid();
if ($pid <= 0) {
    echo "<p>" . xlt('Missing PID.') . "</p>";
    exit;
}
?>

<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("Happy Birthday"); ?></title>
</head>
<body>
    <div style="padding: 15px; text-align: center">
        <p class="h2"><?php echo xlt('Happy Birthday');?>&ensp;<img src="<?php echo OEGlobalsBag::getInstance()->getKernel()->getImagesRelative()?>/balloons-154949_960_720.png" height="42" width="42"></p>

        <?php if (OEGlobalsBag::getInstance()->getBoolean('patient_birthday_alert_manual_off')) { ?>
            <div class="checkbox">
                <label><input type="checkbox" name="turnOff" id="turnOff" value="1"><?php echo xlt('Turn Off birthday alert');?></label>
            </div>
        <?php } else { ?>
            <div class="checkbox" style="visibility: hidden;">
                <label><input type="checkbox" name="turnOff" id="turnOff" value="1" checked><?php echo xlt('Turn Off birthday alert');?></label>
            </div>
        <?php } ?>
    </div>
<script>
    <?php if (OEGlobalsBag::getInstance()->getBoolean('patient_birthday_alert_manual_off')) { ?>
        $("#turnOff").change(function () {
    <?php } ?>
            var pid = <?php echo js_escape((string) $pid)?>;
            <?php
            $rawUserId = $_GET['user_id'] ?? null;
            $userId = is_scalar($rawUserId) ? (int) $rawUserId : 0;
            ?>
            var user_id = <?php echo js_escape((string) $userId)?>;
            var value = $("#turnOff").prop('checked');
            var csrf_token_form = <?php echo js_escape(CsrfUtils::collectCsrfToken(session: $session)); ?>;
            var data =  {
                "pid": pid,
                "user_id": user_id,
                "turnOff": value,
                "csrf_token_form": csrf_token_form
            };
            $.ajax({
                type: "POST",
                url: "../../../library/ajax/turnoff_birthday_alert.php",
                async: true,
                data: data,
                success: function (msg) {
                }
            });
    <?php if (OEGlobalsBag::getInstance()->getBoolean('patient_birthday_alert_manual_off')) { ?>
        });
    <?php } ?>
</script>
</body>
</html>
