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
use OpenEMR\OeUI\OemrUI;


$oemr_ui = new OemrUI($arrOeUiSettings);

if (isset($_GET['set_pid'])) {
    include_once("$srcdir/pid.inc");
    setpid($_GET['set_pid']);
}

$panel = $_POST['panel'] ?? '';
$enrollment_id = $_POST['enrollment_id'] ?? '';

?>
<html>
<head>
<?php
?>

<?php Header::setupHeader(['datetime-picker', 'select2']); ?>

<style>
.highlight {
  color: green;
}
tr.selected {
  background-color: white;
}

#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}
#customers td, #customers th {
  border: 1px #ddd;
  padding: 8px;
}
#customers tr:nth-child(even){background-color: #f2f2f2;}
#customers tr:hover {background-color: #ddd;}
#customers tr:nth-child(even) { border-top: solid thin; }
#customers tr:nth-child(even) { border-bottom: solid thin; }
#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: white;
  color: black;
}
input[type=submit] {
  background-color: #1E90FF;
  padding: 5px 10px;
  border: none;
  color: white;
  text-decoration: none;
  margin: 4px 2px;
  cursor: pointer;
}
</style>
<title><?php echo xlt("Panel History"); ?></title>


</head>

<body class="body_top">
  <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
    <h1>Panel History</h1>
    <?php
    //TODO print all the appointment history  for that panel
    $resultSet = getPanelAppointment($row['panel'], $pid, "all");
    echo "<b>Appointments List: </b></br>";
    while ($row = sqlFetchArray($resultSet)) {
      $pc_startTime = $row['pc_startTime'];
      $pc_eventDate = $row['pc_eventDate'];
      echo attr($pc_eventDate) . ", "
        . attr(date('h:i A', strtotime($pc_startTime))) . " ("
        . date('D', strtotime($pc_eventDate)) . ") "
        . "   ";
      echo attr($row['e.pc_startTime']) . "   ";
      echo attr($row['e.pc_endtTime']). "</br>";
      }
          //TODO print all the diagnoses history for that panel
    ?>
  </div>
</body>

</html>
