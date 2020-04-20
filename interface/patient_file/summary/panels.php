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
////////////////////////////////////////////////////////////////
//post request section
//handle the post request for enroll or discharge a panel
$is_post_request = $_SERVER["REQUEST_METHOD"] == "POST";

if($is_post_request){
  $request = $_POST['request'] ?? '';

  if($request == "enroll"){
    $panel['risk_stratification'] = $_POST['risk_stratification'] ?? '';
    $panel['panel_id'] = $_POST['panel'] ?? '';
    $panel['patient_id'] =  $pid ?? '';

    insertEnrolment($panel);

  } else if ($request == "discharge"){
    $enrollment_id = $_POST['enrollment_id'] ?? '';

    dischargePatient($enrollment_id);
  }
}
//end of post request section
////////////////////////////////////////////////////////////////
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
<title><?php echo xlt("Panels"); ?></title>

<!--This scrept for discharge a pation from a panels
It is called in the table discharge a tage-->
<script>
function testFunction(panel) {
if (confirm("Do you want to discharge from "+panel+"?")) {
  return true ;
} else {
  return false ;
}
}
</script>

</head>

<body class="body_top">
  <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
    <h1>Patient's Panels</h1>
    <?php
    ////////////////////////////////////////////////////////////////
    //display panels information
    // check if the patien inrolled in any panels
        if (isset($pid)) {
        $resultSet = getPatientPanelsInfo($pid,"all");
        if ($resultSet === -1 or sqlNumRows($resultSet)<1) {
           echo ("This patien is not inrolled in any panel</br></br>");
         }else {  //if the patient inrolled into a panel then print the table
           //print the table start
     ?>
     <table id="customers">
      <tr>
        <th>Panel Catgegory</th>
        <th>Panel</th>
        <th>Status</th>
        <th>Risk Stratification</th>
        <th>Enrollment Date</th>
        <th>Discharge Date</th>
        <th>Next Follow Up Date</th>
        <th>&nbsp;</th>
      </tr>

   <?php
   // print the panels info for the selected pation in a talbe format
   // TODO add edit to the table so patient can be distcharged
   while ($row = sqlFetchArray($resultSet)) {  ?>
     <tr>
       <td><?php echo attr($row['category']); ?></td>
       <td><?php echo attr($row['panel']); ?></td>
       <td><?php echo attr($row['status']); ?></td>
       <td><?php echo attr($row['risk_stratification']); ?></td>
       <td><?php echo attr($row['enrollment_date']); ?></td>
       <td><?php echo attr($row['discharge_date']); ?></td>
       <td><?php
              if($row['status'] == "Active"){
                echo attr(getPanelAppointment($row['panel'], $pid)['pc_eventDate'])
                . " " . attr(getPanelAppointment($row['panel'], $pid)['pc_startTime']) ;
              }
          ?></td>
       <td>
         <form action="#" method="post">
           <input type="hidden" name="request" value="discharge" />
           <input type="hidden" name="enrollment_id" value="<?php echo attr($row['id']); ?>" />
          <input type="submit" value="Discharge"
          onClick="return testFunction('<?php echo attr($row['category']) . ": " . attr($row['panel']); ?>')" />
          </form>
       </td>
     </tr>
   <?php } // end the while loop?>
   </table>
</br></br>
<?php } // end the if isset pid
  //End of display panels information
}//end of print the table
////////////////////////////////////////////////////////////////
  ?>

<?php
//adding the patient into a new panels
?>
<div>
<form action="#" method="post">
  <h3>Enroll to a panel</h3>
  <?php
   $panels = getAllPanels();
  ?>
  <p>Select the panel:
  <select name="panel">
  <?php
    while ($row = sqlFetchArray($panels)) {
      echo "<option value=\"" . attr($row['id']) . "\"";
      echo ">";
      echo attr(getPanelCategory($row['category_id'])["name"]);
      echo ": " . attr($row['name']) . "</option>";
    }
  ?>
  </select></p>
  <p>Select the risk stratification:
  <select name="risk_stratification">
    <option value="High">High</option>
    <option value="Moderate" selected>Moderate</option>
    <option value="Low">Low</option>
  </select></p>
  <input type="hidden" name="request" value="enroll" />
  <input type="submit" value="Enroll Patient" />
</form>
</div>
<?php //end of the adding panels section ?>

  </div>
</body>

</html>
