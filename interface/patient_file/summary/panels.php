<?php
/**
 * Panels
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Wejdan Bagais <w.bagais@gmail.com>
 * @copyright Copyright (c) 2020 Wejdan Bagais <w.bagais@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/panel.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;

if (isset($_GET['set_pid'])) {
    include_once("$srcdir/pid.inc");
    setpid($_GET['set_pid']);
}
?>
<html>
<head>
<?php
// TODO add code to allow add or remove a patient from a panel
// TODO fix the header

?>

<?php Header::setupHeader(['datetime-picker', 'select2']); ?>

<style>
.highlight {
  color: green;
}
tr.selected {
  background-color: white;
}
</style>

</head>

<body class="body_top">

    <?php
        if (isset($pid)) {
        $resultSet = getPatientPanelsInfo($pid);
        if ($resultSet === -1) {
           echo ("This patien is not inrolled in any panel");
         }
         while ($row = sqlFetchArray($resultSet)) {
           //print the category and the sub category
           echo "<b>" . attr($row['category']) . ": </b>";
           echo attr($row['panel']) . " <br/>";
           echo "<b>Enrollment Date: </b>" . attr($row['enrollment_date']) . " <br/><br/>";
         }

       echo "<br/>";
       echo "</div>";
      }
     ?>

</body>

</html>
