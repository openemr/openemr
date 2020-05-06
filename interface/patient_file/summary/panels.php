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
    $panel['panel_ids'] = $_POST['panel_ids'];
    $panel['patient_id'] =  $pid ?? '';

    insertEnrolment($panel);

  } else if ($request == "discharge"){
    $enrollment_id = $_POST['enrollment_id'] ?? '';

    dischargePatient($enrollment_id);
  }
//post request from jx
  if(isset($_POST['id'])){
    $id = $_POST['id'] ?? '';
    $result = getPanelsByCategory($id);
    $emparray = [];
    while($row =sqlFetchArray($result))
    {
        $emparray[] = $row;
    }
    echo json_encode($emparray);

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
input[type=submit]:hover {
  background-color: #1e65ff;
}
/*for collaps used in the panels table */
.collapsible {
  cursor: pointer;
  padding: 18px;
  width: 100%;
  border: none;
  text-align: left;
  outline: none;
  font-size: 15px;
}

.active, .collapsible:hover {
  background-color: #555;
}

.content {
  padding: 0 18px;
  display: none;
  overflow: hidden;
  background-color: #f1f1f1;
}

.PanelHead{
  background-color: #777;
  color: white;
  cursor: pointer;
}
.active, .PanelHead:hover {
  background-color: #555;
}


#form_background {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
}

input[type=text], select {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}
</style>

<title><?php echo xlt("Panels"); ?></title>

<!--This scrept for discharge a pation from a panels
It is called in the table discharge a tage-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
function testFunction(panel) {
if (confirm("Do you want to discharge from "+panel+"?")) {
  return true ;
} else {
  return false ;
}
}

  $( document ).ready(function() {
    //collapse and expand section
    $('.breakrow').click(function(){
      $(this).nextUntil('tr.breakrow').slideToggle(200);
    });
  });

  function checkform() {
    if(document.enrolment.panels.value == "select_panel") {
        alert("please select a panel");
        return false;
    } else if ($('#sub_panels').find('input[type=checkbox]:checked').length < 1) {
      alert("please select sub panel/s");
      return false;
    } else {
        document.enrolment.submit();
    }
}
</script>
<!-- Print the sub panels based on the panel category -->
<script type="text/javascript">
		$(document).ready(function(){
			$("#panels").change(function(){
				var id = $("#panels").val();
				$.ajax({
					url: "panels.php",
					method: 'post',
					data: 'id=' + id
				}).done(function(sub_panels){
					sub_panels = JSON.parse(sub_panels.split("<html>")[0]);
					$('#sub_panels').empty();
					sub_panels.forEach(function(sub_panels){
						$('#sub_panels').append('<input type="checkbox" id="panel_ids" name="panel_ids[]" value="' + sub_panels.id +
            '"/>' + sub_panels.name + '<br>');
					})
				})
			})
		})
	</script>
</head>

<body class="body_top">
  <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
    <h2>Patient's Panels</h2>
    <?php
    ////////////////////////////////////////////////////////////////
    //display panels information
    // check if the patien inrolled in any panels
        if (isset($pid)) {
        $panels = getPanelCategoryByPatient_id($pid,"all");
        if ($panels === -1 or sqlNumRows($panels)<1) {
           echo ("This patien is not inrolled in any panel</br></br>");
         }else {  //if the patient inrolled into a panel then print the table
           //print the table start
     ?>
     <table id="customers">
      <tr>
        <th>ID</th>
        <th>Panel</th>
        <th>Status</th>
        <th>Risk Stratification</th>
        <th>Enrollment Date</th>
        <th>Discharge Date</th>
        <th>Next Follow Up Date</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
      </tr>

   <?php
   // print the panels info for the selected pation in a talbe format
   // TODO add edit to the table so patient can be distcharged
   while ($row = sqlFetchArray($panels)) {
     $SubPanels = getPatientPanelsInfo($pid,$row['name'],"all");
     ?>

     <tr class="breakrow">
       <td colspan="9" class="PanelHead"><b><?php echo attr($row['name']); ?></b></td>
     </tr>
       <?php while ($row = sqlFetchArray($SubPanels)) { ?>
          <tr class="datarow">
             <td><?php echo attr($row['id']); ?></td>
             <td><?php echo attr($row['panel']); ?></td>
             <td><?php echo attr($row['status']); ?></td>
             <td><?php echo attr($row['risk_stratification']); ?></td>
             <td><?php echo attr($row['enrollment_date']); ?></td>
             <td><?php echo attr($row['discharge_date']); ?></td>
             <td><?php
                  $pc_eventDate = sqlFetchArray(getPanelAppointment($row['panel'], $pid))['pc_eventDate'];
                  if (strtotime($pc_eventDate) > date("d/m/y")){
                    echo attr($pc_eventDate) . " <br/>";
                  }else {
                    echo "&nbsp;";
                  }
                ?></td>
            <td>
              <form action="panel_history.php" method="post">
                <input type="hidden" name="panel" value="<?php echo attr($row['panel']); ?>" />
                <input type="hidden" name="enrollment_id" value="<?php echo attr($row['id']); ?>" />
               <input type="submit" value="History"/>
             </form>
            </td>
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
<div id="form_background">
<form action="#" method="post" name="enrolment"  onsubmit="return checkform()" >
  <h3>Enroll to a panel</h3>
  <?php
   $panels = getAllPanelCategories();
  ?>

  <b><label for="panel">Select the panel:</label></b>
  <select name="panels" id="panels">
    <option value= "select_panel" id="select_panel" selected disabled>Select Panel</option>
    <?php
    while ($row = sqlFetchArray($panels)) {
      echo "<option value=\"" . attr($row['id']) . "\"";
      echo "id=\"" . attr($row['id']) . "\"";
      echo ">";
      echo attr($row['name']) . "</option>";
    }
  ?>
</select>

  <b><label for="sub_panels">Sub Panels</label></b>
  <div id="sub_panels" name="sub_panels">
      <!-- cod from javacript will be past here -->
  </div>
</br>
<b><label for="risk_stratification">Select the risk stratification:</label></b>
  <select name="risk_stratification">
    <option value="High">High</option>
    <option value="Moderate" selected>Moderate</option>
    <option value="Low">Low</option>
  </select>

  <input type="hidden" name="request" value="enroll" />
  <input type="submit" value="Enroll Patient"/>

</form>
</div> <!-- end of the form -->
<?php //end of the adding panels section ?>

  </div>
</body>

</html>
