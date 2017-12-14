<?php
/**
 * Birthday alert .
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/*picture free taken from https://pixabay.com/en/balloons-party-celebration-floating-154949*/
require_once("../../globals.php");
use OpenEMR\Core\Header;
?>

<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("Happy Birthday"); ?></title>
</head>
<body>
    <div style="padding: 15px; text-align: center">
        <p class="h2"><?php echo xlt('Happy Birthday');?>&ensp;<img src="<?php echo$GLOBALS['images_static_relative']?>/balloons-154949_960_720.png" height="42" width="42"></p>

        <?php if ($GLOBALS['patient_birthday_alert_manual_off']) { ?>
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
    <?php if ($GLOBALS['patient_birthday_alert_manual_off']) { ?>
        $("#turnOff").change(function () {
    <?php } ?>
            var pid = "<?php echo attr($_GET['pid'])?>";
            var user_id = "<?php echo attr($_GET['user_id'])?>";
            var value = $("#turnOff").prop('checked');
            var data =  {"pid": pid, "user_id": user_id, "turnOff": value};
            $.ajax({
                type: "POST",
                url: "../../../library/ajax/turnoff_birthday_alert.php",
                async: true,
                data: data,
                success: function (msg) {
                }
            });
    <?php if ($GLOBALS['patient_birthday_alert_manual_off']) { ?>
        });
    <?php } ?>
</script>
</body>
</html>
