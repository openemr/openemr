<?php
/**
 * Birthday alert .
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/*picture free taken from https://pixabay.com/en/balloons-party-celebration-floating-154949*/
require_once("../../globals.php");
use OpenEMR\Core\Header;
?>

<html xmlns="http://www.w3.org/1999/html">
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("Happy Birthday"); ?></title>
</head>
<body>

<h1><?php echo xl('Happy Birthday');?><img src="<?php echo$GLOBALS['images_static_relative']?>/balloons-154949_960_720.png" height="42" width="42"></h1>
<input type="checkbox" name="turnOff" id="turnOff" value="1"/> <?php echo xl('Turn Off birthday alert');?>


<script>
    $("#turnOff").change(function () {
        var pid = <?php echo $_GET['pid']?>;
        var value = $(this).is(':checked');
        var data =  {"pid": pid, "turnOff": value};
        $.ajax({
            type: "POST",
            url: "turnoff_birthday_alert.php",
            async: true,
            data: data,
            success: function (msg) {

            }
        });
    });
</script>
</body>
</html>