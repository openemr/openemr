<?php
/**
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Sharon Cohen<sharonco@matrix.co.il>
 * @link    http://www.open-emr.org
 */


/**picture free taken from https://pixabay.com/en/balloons-party-celebration-floating-154949*/
include_once("../../globals.php");

?>

<html xmlns="http://www.w3.org/1999/html">
<head>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-7-2/index.js"></script>
</head>
<body>

<h1><?php echo xl('Happy Birthday');?><img src="../../../images/balloons-154949_960_720.png" height="42" width="42"></h1>
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