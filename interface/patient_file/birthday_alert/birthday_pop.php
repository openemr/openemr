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
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Http\RequestTerminator;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Response;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!AclMain::aclCheckCore('patients', 'appt')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for patients/appt: Birthday Alert", xl("Birthday Alert"));
}

// Popup is opened from the main patient-birthday alert with the pid embedded
// in the URL. Keep the value request-local: never call setpid() from a GET
// handler (would change active patient without a form-token round-trip). The
// AJAX turnoff below re-sends pid in a form-token-protected POST; the turnoff
// handler (library/ajax/turnoff_birthday_alert.php) validates the token but
// does not currently compare the submitted pid against the session pid --
// tightening that comparison is a separate improvement outside this refactor.
$query = CurrentRequest::get()->query;
$pid = $query->getInt('pid');
if ($pid <= 0) {
    (new RequestTerminator())->error(Response::HTTP_BAD_REQUEST, xlt('Missing PID.'));
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
            $userId = $query->getInt('user_id');
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
