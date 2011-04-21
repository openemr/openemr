<?php
include_once ('../../globals.php'); 
include_once ('../../../library/sql.inc'); 
include_once ('../../../library/classes/Prescription.class.php');
include_once("../../../library/formdata.inc.php");
//practice data
$physician_name = ''; 
$practice_fname = '';
$practice_lname = '';
$practice_title = '';
$practice_address = ''; 
$practice_city = ''; 
$practice_state = '';
$practice_zip  = '';
$practice_phone = '';
$practice_fax = '';
$practice_license = '';
$practice_dea = '';
//patient data
$patient_name = '';
$patient_address = '';
$patient_city = ''; 
$patient_state = '';
$patient_zip = '';
$patient_phone = '';
$patient_dob = '';
$sigline = array();
$sigline['plain'] =  
    "<div class='signature'>" 
  . " ______________________________________________<br/>"
  . "</div>\n";
$sigline['embossed'] =  
    "<div class='signature'>" 
  . " _____________________________________________________<br/>"
#  . "Signature - Valid for three days and in Broward County only."
  . "Signature"
  . "</div>\n";
$sigline['signed'] =  
    "<div class='sig'>"
  . "<img src='./sig.jpg'>"
  . "</div>\n";
$query = sqlStatement("select fname,lname,street,city,state,postal_code,phone_home,DATE_FORMAT(DOB,'%m/%d/%y') as DOB from patient_data where pid =" . $_SESSION['pid']);
if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
  $patient_name = $result['fname'] . ' ' . $result['lname'];
  $patient_address = $result['street'];
  $patient_city = $result['city'];
  $patient_state = $result['state'];
  $patient_zip = $result['postal_code'];
  $patient_phone = $result['phone_home'];
  $patient_dob = $result['DOB'];
}
//update user information if selected from form
if ($_POST['update']) { // OPTION update practice inf
  $query = "update users set " .
    "fname = '" . formData('practice_fname') . "', " .  
    "lname = '" . formData('practice_lname') . "', " .  
    "title = '" . formData('practice_title') . "', " .  
    "street = '" . formData('practice_address') . "', " .  
    "city = '" . formData('practice_city') . "', " .  
    "state = '" . formData('practice_state') . "', " .  
    "zip = '" . formData('practice_zip') . "', " .  
    "phone = '" . formData('practice_phone') . "', " .  
    "fax = '" . formData('practice_fax') . "', " .  
    "federaldrugid = '" . formData('practice_dea') . "' " .  
    "where id =" . $_SESSION['authUserID'];
  sqlInsert($query);
}
//get user information
$query = sqlStatement("select * from users where id =" . $_SESSION['authUserID']);
if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
  $physician_name = $result['fname'] . ' ' . $result['lname'] . ', ' . $result['title']; 
  $practice_fname = $result['fname'];
  $practice_lname = $result['lname'];
  $practice_title = $result['title'];
  $practice_address = $result['street']; 
  $practice_city = $result['city']; 
  $practice_state = $result['state'];
  $practice_zip  = $result['zip'];
  $practice_phone = $result['phone'];
  $practice_fax = $result['fax'];
  $practice_dea = $result['federaldrugid'];
}
if ($_POST['print_pdf'] || $_POST['print_html']) { 
  $camos_content = array();
  foreach ($_POST as $key => $val) {
    if (substr($key,0,3) == 'ch_') {
      $query = sqlStatement("select content from form_CAMOS where id =" . 
        substr($key,3));
      if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
  	if ($_POST['print_html']) { //do this change to formatting only for html output
        	$content = preg_replace('|\n|','<br/>', $result['content']);
	        $content = preg_replace('|<br/><br/>|','<br/>', $content);
	} else {
		$content = $result['content'];
	}
        array_push($camos_content,$content); 
      }
    }
    if (substr($key,0,5) == 'chrx_') {
      $rx = new Prescription(substr($key,5));
      //$content = $rx->drug.' '.$rx->form.' '.$rx->dosage;
      $content = '' 
      . $rx->drug . ' '
      . $rx->size . ''
      . $rx->unit_array[$rx->unit] . '<br/>' 
      . $rx->quantity. ' '
      . $rx->form_array[$rx->form]. '<br/>'
      . $rx->dosage . ' '
      . $rx->form_array[$rx->form]. ' '
      . $rx->route_array[$rx->route] . ' '
      . $rx->interval_array[$rx->interval] . '<br/>'
      . 'refills:' . $rx->refills . '';
//      . $rx->substitute_array[$rx->substitute]. ''
//      . $rx->per_refill . '';
      array_push($camos_content,$content); 
    }
  }
  if (!$_GET['letterhead']) { //OPTION print a prescription with css formatting
?>
<html>
<head>
<?php html_header_show();?>
<title>
<?php xl('CAMOS','e'); ?>
</title>
<link rel="stylesheet" type="text/css" href="./rx.css" />
</head>
<body onload='init()'>
<img src='./hline.jpg' id='hline'>
<img src='./vline.jpg' id='vline'>
<?php
if ($camos_content[0]) { //decide if we are printing this rx
?>
<?php
function topHeaderRx() {
    global $physician_name,$practice_address,$practice_city,$practice_state,$practice_zip,$practice_phone,$practice_fax,$practice_dea;
    print $physician_name . "<br/>\n";
    print $practice_address . "<br/>\n";
    print $practice_city . ", ";
    print $practice_state . " ";
    print $practice_zip . "<br/>\n";
    print xl('Voice') . ': ' . $practice_phone . ' / ' . xl('Fax') . ': ' . $practice_fax . "<br/>\n";
    print xl('DEA') . ': ' . $practice_dea;   
}
function bottomHeaderRx() {
    global $patient_name,$patient_address,$patient_city,$patient_state,$patient_zip,$patient_phone,$patient_dob;
    print "<span class='mytagname'> " . xl('Name') . ":</span>\n";
    print "<span class='mydata'> $patient_name </span>\n";
    print "<span class='mytagname'> " . xl('Address') . ": </span>\n";
    print "<span class='mydata'> $patient_address, $patient_city, " .
      "$patient_state $patient_zip </span><br/>\n";
    print "<span class='mytagname'>" . xl('Phone') . ":</span>\n";
    print "<span class='mydata'>$patient_phone</span>\n";
    print "<span class='mytagname'>" . xl('DOB') . ":</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>" . xl('Date') . ":</span>\n";
    print "<span class='mydata'>" . date("F d, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>" . xl('Rx') . "</div><br/>\n";
}
?>
<div id='rx1'  class='rx' >
  <div class='topheader'>
  <?php
    topHeaderRx();
  ?>
    </div>
    <hr/>
  <div class='bottomheader'>
  <?php
    bottomHeaderRx();
  ?>
  </div>
  <div class='content'>
    <?php
        print $camos_content[0]; 
    ?>
  </div>
  <?php print $sigline[$_GET[sigline]] ?>
</div> <!-- end of rx block -->
<?php
} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx1'>\n";
}
?>
<?php

if ($camos_content[1]) { //decide if we are printing this rx
?>
<div id='rx2'  class='rx' >
  <div class='topheader'>
  <?php

    topHeaderRx();
  ?>
  </div>
    <hr/>
  <div class='bottomheader'>
  <?php

    bottomHeaderRx();  
  ?>
  </div>
  <div class='content'>
    <?php

        print $camos_content[1]; 
    ?>
  </div>
  <?php print $sigline[$_GET[sigline]] ?>
</div> <!-- end of rx block -->
<?php

} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx2'>\n";
}
?>
<?php

if ($camos_content[2]) { //decide if we are printing this rx
?>
<div id='rx3'  class='rx' >
  <div class='topheader'>
  <?php

    topHeaderRx();  
  ?>
  </div>
    <hr/>
  <div class='bottomheader'>
  <?php

    bottomHeaderRx();
  ?>
  </div>
  <div class='content'>
    <?php

        print $camos_content[2]; 
    ?>
  </div>
  <?php print $sigline[$_GET[sigline]] ?>
</div> <!-- end of rx block -->
<?php

} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx3'>\n";
}
?>
<?php

if ($camos_content[3]) { //decide if we are printing this rx
?>
<div id='rx4'  class='rx' >
  <div class='topheader'>
  <?php

    topHeaderRx();
  ?>
  </div>
    <hr/>
  <div class='bottomheader'>
  <?php

    bottomHeaderRx();
  ?>
  </div>
  <div class='content'>
    <?php

        print $camos_content[3]; 
    ?>
  </div>
  <?php print $sigline[$_GET[sigline]] ?>
</div> <!-- end of rx block -->
<?php

} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx4'>\n";
}
?>
</body>
</html>
<?php
  }//end of printing to rx not letterhead
  elseif ($_GET['letterhead']) { //OPTION print to letterhead
    $content = preg_replace('/PATIENTNAME/i',$patient_name,$camos_content[0]);
    if($_POST['print_html']) { //print letterhead to html
?>
        <html>
        <head>
        <style>
        body {
	 font-family: sans-serif;
	 font-weight: normal;
	 font-size: 12pt;
	 background: white;
	 color: black;
	}	
	.paddingdiv {
	 width: 524pt;
	 padding: 0pt;
	 margin-top: 50pt;
	}
	.navigate {
	 margin-top: 2.5em;
	}	
	@media print {
	 .navigate {
	  display: none;
	 }	
	}	
	</style>	
	<title><?php xl('Letter','e'); ?></title>
	</head>
        <body>
	<div class='paddingdiv'>
<?php
	//bold
        print "<div style='font-weight:bold;'>";
        print $physician_name . "<br/>\n";
        print $practice_address . "<br/>\n";
        print $practice_city.', '.$practice_state.' '.$practice_zip . "<br/>\n";
        print $practice_phone . ' (' . xl('Voice') . ')' . "<br/>\n";
        print $practice_phone . ' ('. xl('Fax') . ')' . "<br/>\n";
        print "<br/>\n";
        print date("l, F jS, Y") . "<br/>\n";
        print "<br/>\n";
	print "</div>";
        //not bold
	print "<div style='font-size:90%;'>";
        print $content;
	print "</div>";
        //bold
	print "<div style='font-weight:bold;'>";
        print "<br/>\n";
        print "<br/>\n";
        if ($_GET['signer'] == 'patient') {
                print "__________________________________________________________________________________" . "<br/>\n";
                print xl("Print name, sign and date.") . "<br/>\n";
        }
        elseif ($_GET['signer'] == 'doctor') {
                print xl('Sincerely,') . "<br/>\n";
                print "<br/>\n";
                print "<br/>\n";
                print $physician_name . "<br/>\n";
        }
	print "</div>";
?>
        <script language='JavaScript'>
        window.print();
        </script>
	</div>
        </body>
        </html>
<?php
        exit;
    }
    else { //print letterhead to pdf
	include_once('../../../library/classes/class.ezpdf.php');
  	$pdf =& new Cezpdf();
	$pdf->selectFont('../../../library/fonts/Times-Bold');
	$pdf->ezSetCmMargins(3,1,1,1);
	$pdf->ezText($physician_name,12);
	$pdf->ezText($practice_address,12);
	$pdf->ezText($practice_city.', '.$practice_state.' '.$practice_zip,12);
	$pdf->ezText($practice_phone . ' (' . xl('Voice') . ')',12);
	$pdf->ezText($practice_phone . ' ('. xl('Fax') . ')',12);
	$pdf->ezText('',12);
	$pdf->ezText(date("l, F jS, Y"),12);
	$pdf->ezText('',12);
	$pdf->selectFont('../../../library/fonts/Helvetica');
	$pdf->ezText($content,10);
	$pdf->selectFont('../../../library/fonts/Times-Bold');
	$pdf->ezText('',12);
	$pdf->ezText('',12);
	if ($_GET['signer'] == 'patient') {
		$pdf->ezText("__________________________________________________________________________________",12);
		$pdf->ezText(xl("Print name, sign and date."),12);
	} 
	elseif ($_GET['signer'] == 'doctor') {
		$pdf->ezText(xl('Sincerely,'),12);
		$pdf->ezText('',12);
		$pdf->ezText('',12);
		$pdf->ezText($physician_name,12);
	}
	$pdf->ezStream();
    } //end of html vs pdf print
  }
} //end of if print
  else { //OPTION selection of what to print
?>
<html>
<head>
<?php html_header_show();?>
<title>
<?php xl('CAMOS','e'); ?>
</title>
<script type="text/javascript">
//below init function just to demonstrate how to do it.
//now need to create 'cycle' function triggered by button to go by fours
//through selected types of subcategories.
//this is to be very very cool.
function init() {}
function checkall(){
  var f = document.forms[0];
  var x = f.elements.length;
  var i;
  for(i=0;i<x;i++) {
    if (f.elements[i].type == 'checkbox') {
      f.elements[i].checked = true;
    }
  }
}
function uncheckall(){
  var f = document.forms[0];
  var x = f.elements.length;
  var i;
  for(i=0;i<x;i++) {
    if (f.elements[i].type == 'checkbox') {
      f.elements[i].checked = false;
    }
  }
}
function cycle() {
  var log = document.getElementById('log');
  var cboxes = document.getElementById('checkboxes');
  var cb = cboxes.getElementsByTagName('div');
  if (cycle_engine(cb,0) == 0) {cycle_engine(cb,1);}
}
function cycle_engine(cb,seed) {
  //seed determines if we should turn on up to first 4
  var count_turnon = 0;
  var count_turnoff = 0;
  for (var i=0;i<cb.length;i++) {
    cbc = cb[i].childNodes;
    if (cbc[2].innerHTML == 'prescriptions') {
      if (cbc[1].checked == true) {
        cbc[1].checked = false;
        count_turnoff++;   
      } else {
        if ((count_turnoff > 0 || seed == 1) && count_turnon < 4) {
          cbc[1].checked = true;
          count_turnon++;   
        } 
      }
    }
  }
  return count_turnoff;
}

</script>
<link rel="stylesheet" type="text/css" href="./rx.css" />
</head>
<h1><?php xl('Select CAMOS Entries for Printing','e'); ?></h1>
<form method=POST name='pick_items' target=_new>
<input type=button name=cyclerx value='<?php xl('Cycle','e'); ?>' onClick='cycle()'><br/>
<input type='button' value='<?php xl('Select All','e'); ?>' onClick='checkall()'>
<input type='button' value='<?php xl('Unselect All','e'); ?>' onClick='uncheckall()'>

<?php if ($_GET['letterhead']) { ?>
<input type=submit name='print_pdf' value='<?php xl('Print (PDF)','e'); ?>'>
<?php } ?>
	
<input type=submit name='print_html' value='<?php xl('Print (HTML)','e'); ?>'>
<?php

//check if an encounter is set
if ($_SESSION['encounter'] == NULL) { 
  $query = sqlStatement("select x.id as id, x.category, x.subcategory, x.item from " . 
  "form_CAMOS as x join forms as y on (x.id = y.form_id) " . 
  "where y.pid = " . $_SESSION['pid'] . 
  " and y.form_name like 'CAMOS%'" . 
  " and x.activity = 1"); 
} 
else { 
  $query = sqlStatement("select x.id as id, x.category, x.subcategory, x.item from " . 
  "form_CAMOS  as x join forms as y on (x.id = y.form_id) " . 
  "where y.encounter = " .  $_SESSION['encounter'] . 
  " and y.pid = " . $_SESSION['pid'] .  
  " and y.form_name like 'CAMOS%'" .
  " and x.activity = 1");
}
$results = array();
echo "<div id='checkboxes'>\n";
$count = 0;
while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
  $checked = '';
  if ($result['category'] == 'prescriptions' && $count < 4) {
    $count++;
    $checked = 'checked';
  }
  echo "<div>\n";
  echo "<input type=checkbox name='ch_" . $result['id'] . "' $checked><span>" .
    $result['category'] . '</span>:' . $result['subcategory'] . ':' . $result['item'] . "<br/>\n";
  echo "</div>\n";
}
echo "</div>\n";
echo "<div id='log'>\n";//temp for debugging
echo "</div>\n";
//create Prescription object for the purpose of drawing data from the Prescription
//table for those who wish to do so
$rxarray = Prescription::prescriptions_factory($_SESSION['pid']);
//now give a choice of drugs from the Prescription table
foreach($rxarray as $val) {
  echo "<input type=checkbox name='chrx_" . $val->id . "'>" .
    $val->drug . ':' . $val->start_date . "<br/>\n";
}
?>
	
<?php if ($_GET['letterhead']) { ?>
<input type=submit name='print_pdf' value='<?php xl('Print (PDF)','e'); ?>'>
<?php } ?>
	
<input type=submit name='print_html' value='<?php xl('Print (HTML)','e'); ?>'>
</form>
<h1><?php xl('Update User Information','e'); ?></h1>
<form method=POST name='pick_items'>
<table>
<tr>
  <td> <?php xl('First Name','e'); ?>: </td> 
  <td> <input type=text name=practice_fname value ='<?php echo htmlspecialchars($practice_fname,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('Last Name','e'); ?>: </td> 
  <td> <input type=text name=practice_lname value ='<?php echo htmlspecialchars($practice_lname,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('Title','e'); ?>: </td> 
  <td> <input type=text name=practice_title value ='<?php echo htmlspecialchars($practice_title,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('Street Address','e'); ?>: </td> 
  <td> <input type=text name=practice_address value ='<?php echo htmlspecialchars($practice_address,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('City','e'); ?>: </td> 
  <td> <input type=text name=practice_city value ='<?php echo htmlspecialchars($practice_city,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('State','e'); ?>: </td> 
  <td> <input type=text name=practice_state value ='<?php echo htmlspecialchars($practice_state,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('Zip','e'); ?>: </td> 
  <td> <input type=text name=practice_zip value ='<?php echo htmlspecialchars($practice_zip,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('Phone','e'); ?>: </td> 
  <td> <input type=text name=practice_phone value ='<?php echo htmlspecialchars($practice_phone,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('Fax','e'); ?>: </td> 
  <td> <input type=text name=practice_fax value ='<?php echo htmlspecialchars($practice_fax,ENT_QUOTES); ?>'> </td>
</tr>
<tr>
  <td> <?php xl('DEA','e'); ?>: </td> 
  <td> <input type=text name=practice_dea value ='<?php echo htmlspecialchars($practice_dea,ENT_QUOTES); ?>'> </td>
</tr>
</table>
<input type=submit name=update value='<?php xl('Update','e'); ?>'>
</form>
<?php
} //end of else statement
?>
</body>
</html>
