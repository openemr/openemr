<?php 

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../../globals.php");
include_once("$srcdir/calendar.inc");
include_once("$srcdir/patient.inc");

//the maximum number of patient records to display:
$M = 100;

if (isset($_POST["mode"]) && ($_POST["mode"] == "editappt")) {
  //echo "saved appt";
  $body_code = ' onload="javascript:parent.Calendar.location.href=parent.Calendar.location.href;" ';
  $year = $_POST["year"];
  $month = $_POST["month"];
  $day = $_POST["day"];
  $hour = $_POST["hour"];
  $minute = $_POST["minute"];
  if ($_POST["ampm"] == "pm") {
    $hour += 12;
  }
  $timesave = "$year-$month-$day $hour:$minute";
  //echo $timesave;
  $providerres = sqlQuery("select name from groups where user=? limit 1", array($_POST["provider"]) );

  saveCalendarUpdate($_POST["calid"],$_POST["pid"],$timesave,$_POST["reason"],$_POST["provider"],$providerres{"name"});
}
elseif (isset($_POST["mode"]) && ($_POST["mode"] == "deleteappt")) {
  $body_code = ' onload="javascript:parent.Calendar.location.href=parent.Calendar.location.href;" ';

  deleteCalendarItem($_POST["calid"],$_POST["pid"]);
}
elseif (isset($_POST["mode"]) && ($_POST["mode"] == "saveappt")) {
  $body_code = ' onload="javascript:parent.Calendar.location.href=parent.Calendar.location.href;" ';
  $year = $_POST["year"];
  $month = $_POST["month"];
  $day = $_POST["day"];
  $hour = $_POST["hour"];
  $minute = $_POST["minute"];
  if ($_POST["ampm"] == "pm") {
    $hour += 12;
  }
  $timesave = "$year-$month-$day $hour:$minute";
  $providerres = sqlQuery("select name from groups where user=? limit 1", array($_POST["provider"]) );
  newCalendarItem($_POST["pid"],$timesave,$_POST["reason"],$_POST["provider"],$providerres{"name"});
} else {
  $body_code = "";
  $category = $_GET["event_category"];
  if(empty($category))
  {
    $category = $_POST['category'];
  }
}

if (isset($_GET["mode"]) && ($_GET["mode"] == "reset")) {
  $_SESSION["lastname"] = "";
  $_SESSION["firstname"] = "";
  //$_SESSION["category"] = $_POST["category"];
  $category = $_POST["category"];
}

if (isset($_POST["mode"]) && ($_POST["mode"] == "findpatient")) {
  $_SESSION["findby"] = $_POST["findBy"];
  $_SESSION["lastname"] = $_POST["lastname"];
  $_SESSION["firstname"] = $_POST["firstname"];
  $category = $_POST["category"];
}

$findby = $_SESSION["findby"];
$lastname = $_SESSION["lastname"];
$firstname = $_SESSION["firstname"];

// do the search, if we have some good criteria
if (isset($lastname) && $lastname != "") {
    if ($findby == "Last") {
        $result = getPatientLnames("$lastname","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
    } elseif ($findby == "ID") {
        $result = getPatientId("$lastname","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
    } elseif ($findby == "DOB") {
        $result = getPatientDOB("$lastname","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
    } elseif ($findby == "SSN") {
        $result = getPatientSSN("$lastname","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
    } elseif ($searchby == "Phone") {                  //(CHEMED) Search by phone number
        $result = getPatientPhone("$searchparm","*");
    }
}
?>

<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style>
form {
    padding: 0px;
    margin: 0px;
}
#searchCriteria {
    text-align: center;
    width: 100%;
    font-size: 0.8em;
    background-color: #ddddff;
    font-weight: bold;
    padding: 3px;
    margin: 0px;
    display: inline;
}
#searchCriteria form {
    /* this is to fix some odd thing with Firefox, 
       or is it something odd with IE ?! crazy */
    background-color: #ddddff;
}
#searchResultsHeader { 
    width: 100%;
    background-color: lightgrey;
}
#searchResultsHeader table { 
    width: 96%;  /* not 100% because the 'searchResults' table has a scrollbar */
    border-collapse: collapse;
}
#searchResultsHeader th {
    font-size: 0.7em;
}
#searchResults {
    width: 100%;
    overflow: auto;
}

.srName { width: 30%; }
.srPhone { width: 21%; }
.srSS { width: 17%; }
.srDOB { width: 17%; }
.srID { width: 15%; }

#searchResults table {
    width: 100%;
    border-collapse: collapse;
    background-color: white;
}
#searchResults tr {
    cursor: hand;
    cursor: pointer;
}
#searchResults td {
    font-size: 0.7em;
    border-bottom: 1px solid #eee;
}
.oneResult { }
.billing { color: red; font-weight: bold; }
/* for search results or 'searching' notification */
#searchstatus {
    font-size: 0.8em;
    font-weight: bold;
    padding: 1px 1px 10px 1px;
    font-style: italic;
    color: black;
    text-align: center;
}
.noResults { background-color: #ccc; }
.tooManyResults { background-color: #fc0; }
.howManyResults { background-color: #9f6; }
#searchspinner { 
    display: inline;
    visibility: hidden;
}

.highlight {
    background-color: #336699;
    color: white;
}
</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.2.2.min.js"></script>

<script language='JavaScript'>

 // This is called from the event editor popup to refresh the display.
 function refreshme() {
  var cf = parent.frames[0].frames[0]; // calendar frame
  if (cf && cf.refreshme) cf.refreshme();
 }

 // Cloned from interface/main/calendar/.../views/day/default.html:
 function newEvt(startampm, starttimeh, starttimem, eventdate, providerid, patientid) {
  dlgopen('add_edit_event.php?startampm=' + startampm +
   '&starttimeh=' + starttimeh + '&starttimem=' + starttimem +
   //'&date=' + eventdate + '&userid=' + providerid +
   '&date=' + eventdate +
   '&patientid=' + patientid,
   '_blank', 550, 270);
 }

</script>

</head>
<body class="body_bottom" <?php $body_code;?>>

   <span class='bold'><?php echo htmlspecialchars( xl('Patient Appointment'), ENT_NOQUOTES); ?></span>
<?php  if ($userauthorized == 1) { ?>
   <a class="more" style="font-size:8pt;"
    href="../authorizations/authorizations.php"
    name="Authorizations"><?php echo htmlspecialchars( xl('(Notes and Authorizations)'), ENT_NOQUOTES); ?></a>
<?php  } else { ?>
   <a class="more" style="font-size:8pt;"
    href="../authorizations/authorizations.php"
    name="Authorizations"><?php echo htmlspecialchars( xl('(Patient Notes)'), ENT_NOQUOTES); ?></a>
<?php  } ?>

<div id="searchCriteria">
<form method='post' id="theform" name='findpatientform' action='find_patient.php?no_nav=1'>
   <input type='hidden' name='mode' value="findpatient">
   <?php echo htmlspecialchars( xl('Search by:'), ENT_NOQUOTES); ?>
   <select name='findBy'>
    <option value="Last"><?php echo htmlspecialchars( xl('Name'), ENT_NOQUOTES); ?></option>
    <!-- (CHEMED) Search by phone number -->
    <option value="Phone"<?php if ($searchby == 'Phone') echo ' selected' ?>><?php echo htmlspecialchars( xl('Phone'), ENT_NOQUOTES); ?></option>
    <option value="ID"<?php if ($searchby == 'ID') echo ' selected' ?>><?php echo htmlspecialchars( xl('ID'), ENT_NOQUOTES); ?></option>
    <option value="SSN"<?php if ($searchby == 'SSN') echo ' selected' ?>><?php echo htmlspecialchars( xl('SSN'), ENT_NOQUOTES); ?></option>
    <option value="DOB"<?php if ($searchby == 'DOB') echo ' selected' ?>><?php echo htmlspecialchars( xl('DOB'), ENT_NOQUOTES); ?></option>
   </select>
 <?php echo htmlspecialchars( xl('for:'), ENT_NOQUOTES); ?>
   <input type='text' id='lastname' name='lastname' size='12' value='<?php echo htmlspecialchars( $_REQUEST['lastname'], ENT_QUOTES); ?>' title='<?php echo htmlspecialchars( xl('If name, any part of lastname or lastname,firstname'), ENT_QUOTES); ?>'>
   &nbsp;
   <input type='submit' id="submitbtn" value='<?php echo htmlspecialchars( xl('Search'), ENT_QUOTES); ?>'>
   <div id="searchspinner"><img src="<?php echo $GLOBALS['webroot'] ?>/interface/pic/ajax-loader.gif"></div>

<?php if (! isset($_REQUEST['lastname'])): ?>
<div id="searchstatus"><?php echo htmlspecialchars( xl('Enter your search criteria above'), ENT_NOQUOTES); ?></div>
<?php elseif (count($result) == 0): ?>
<div id="searchstatus" class="noResults"><?php echo htmlspecialchars( xl('No records found. Please expand your search criteria.'), ENT_NOQUOTES); ?></div>
<?php elseif (count($result)>=100): ?>
<div id="searchstatus" class="tooManyResults"><?php echo htmlspecialchars( xl('More than 100 records found. Please narrow your search criteria.'), ENT_NOQUOTES); ?></div>
<?php elseif (count($result)<100): ?>
<div id="searchstatus" class="howManyResults"><?php echo htmlspecialchars( count($result)." ".xl('records found'), ENT_NOQUOTES); ?>.</div>
<?php endif; ?>

<a class='text' href="../../new/new_patient.php" target="_top"><?php echo htmlspecialchars( xl('(New Patient)'), ENT_NOQUOTES); ?></a>

</form>
</div>


<?php if (isset($result)): ?> <!-- we have results -->

<div id="searchResultsHeader">
<table>
 <tr>
  <th class="srName"><?php echo htmlspecialchars( xl('Name'), ENT_NOQUOTES); ?></th>
  <th class="srPhone"><?php echo htmlspecialchars( xl('Phone'), ENT_NOQUOTES); ?></th> <!-- (CHEMED) Search by phone number -->
  <th class="srSS"><?php echo htmlspecialchars( xl('SS'), ENT_NOQUOTES); ?></th>
  <th class="srDOB"><?php echo htmlspecialchars( xl('DOB'), ENT_NOQUOTES); ?></th>
  <th class="srID"><?php echo htmlspecialchars( xl('ID'), ENT_NOQUOTES); ?></th>
 </tr>
</table> 
</div>

<div id="searchResults">
<table> 
<?php 
  //set ampm default for find patient results links event_startampm
  $ampm = 1;
  if (date("H") >= 12) { $ampm = 2; }

  foreach ($result as $iter) {
    if ($total > 100) { break; }

    $iterpid   = $iter['pid'];
    $iterproviderid = $iter['providerID'];
    $iterlname = $iter['lname'];
    $iterfname = $iter['fname'];
    $itermname = $iter['mname'];
    $iterdob   = $iter['DOB'];

    // the special genericname2 of 'Billing' means something, but I'm not sure
    // what, regardless it gets special coloring and an extra line of output
    // in the 'name' column -- JRM
    $trClass = "oneresult";
    if ($iter['genericname2'] == 'Billing') { $trClass .= " billing"; }

    $trTitle = xl("Make new appointment for") . " " . $iterfname . " " . $iterlname;
        
    echo " <tr class='".$trClass."' id='".htmlspecialchars( $iterpid."~".$iterproviderid, ENT_QUOTES)."' title='".htmlspecialchars( $trTitle, ENT_QUOTES)."'>";
    echo "  <td class='srName'>".htmlspecialchars( $iterlname.", ".$iterfname." ".$itermname, ENT_NOQUOTES);
    if ($iter['genericname2'] == 'Billing') { echo "<br>".htmlspecialchars( $iter['genericval2'], ENT_NOQUOTES); }
    echo "</td>\n";
    echo "  <td class='srPhone'>" . htmlspecialchars( $iter['phone_home'], ENT_NOQUOTES) . "</td>\n"; //(CHEMED) Search by phone number
    echo "  <td class='srSS'>" . htmlspecialchars( $iter['ss'], ENT_NOQUOTES) . "</td>\n";
    echo "  <td class='srDOB'>" . htmlspecialchars( $iter['DOB'], ENT_NOQUOTES) . "</td>\n";
    echo "  <td class='srID'>" . htmlspecialchars( $iter['pubpid'], ENT_NOQUOTES) . "</td>\n";
    echo " </tr>";
  }

?>

<?php endif; ?> <!-- end of results -->
</table>
</div> <!-- end search results DIV -->

</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#lastname").focus();
    $(".oneresult").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".oneresult").click(function() { SelectPatient(this); });
    $("#theform").submit(function() { SubmitForm(this); });
});

// show the 'searching...' status and submit the form
var SubmitForm = function(eObj) {
    $("#submitbtn").css("disabled", "true");
    $("#searchspinner").css("visibility", "visible");
    return true;
}

// another way to select a patient from the list of results
// parts[] ==>  0=PID, 1=ProviderID
var SelectPatient = function (eObj) {
    objID = eObj.id;
    var parts = objID.split("~");
    ampm = '<?php echo $ampm ?>';
    starth = '<?php date("H") ?>';
    startdate = '<?php date("Ymd") ?>';
    return newEvt(ampm, starth , 0, startdate, parts[1], parts[0]);
}

var Showme = function (eObj) { alert("showme"); };

</script>
</html>
