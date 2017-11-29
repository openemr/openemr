<?php
/**
 * weno rxlog.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
use OpenEMR\Core\Header;

?>

<html>
<head>
    <title><?php echo xlt("Rx Log"); ?></title>
    <?php Header::setupHeader(); ?>

</head>
<body class="body_top">
<h1><?php print xlt("Rx Log"); ?></h1>
<?php

$log = sqlStatement("SELECT * FROM erx_rx_log ORDER BY id DESC");

print "<table width='100%'>";
print "<tr align='left'>

<th>". xlt("Rx ID") ."</th>
<th>". xlt("Date") ."</th>
<th>". xlt("Time") ."</th>
<th>". xlt("Code") ."</th>
<th>". xlt("Status") ."</th>
<th>". xlt("Message") ."</th>
</tr>";
while ($row = sqlFetchArray($log)) {
    print "<tr><td>" .text($row['prescription_id'])."</td><td>".text(oeFormatShortDate($row['date'])).
          "</td><td>".text($row['time'])."</td><td>".text($row['code'])."</td><td>".text($row['status']).
          "</td><td>".text($row['message_id'])."</td></tr>";
}
print "</table>";
?>
</body>
</html>