<?php
include_once ('../../globals.php'); 
include_once ('../../../library/sql.inc'); 
include_once ('../../../library/classes/Prescription.class.php');
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
    "fname = '" . $_POST['practice_fname'] . "', " .  
    "lname = '" . $_POST['practice_lname'] . "', " .  
    "title = '" . $_POST['practice_title'] . "', " .  
    "street = '" . $_POST['practice_address'] . "', " .  
    "city = '" . $_POST['practice_city'] . "', " .  
    "state = '" . $_POST['practice_state'] . "', " .  
    "zip = '" . $_POST['practice_zip'] . "', " .  
    "phone = '" . $_POST['practice_phone'] . "', " .  
    "fax = '" . $_POST['practice_fax'] . "', " .  
    "federaldrugid = '" . $_POST['practice_dea'] . "' " .  
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
if ($_POST['print']) { 
  $camos_content = array();
  foreach ($_POST as $key => $val) {
    if (substr($key,0,3) == 'ch_') {
      $query = sqlStatement("select content from form_CAMOS where id =" . 
        substr($key,3));
      if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
  	if (!$_GET['letterhead']) { //do this change to formatting only for web output (rx output)
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
<? html_header_show();?>
<title>
Four Pane Prescription Printer
</title>
<link rel="stylesheet" type="text/css" href="./rx.css" />
</head>
<body onload='init()'>
<img src='./hline.jpg' id='hline'>
<img src='./vline.jpg' id='vline'>
<?
if ($camos_content[0]) { //decide if we are printing this rx
?>
<div id='rx1'  class='rx' >
  <div class='topheader'>
  <?
    print $physician_name . "<br/>\n";
    print $practice_address . "<br/>\n";
    print $practice_city . ", ";
    print $practice_state . " ";
    print $practice_zip . "<br/>\n";
    print 'voice: ' . $practice_phone . ' / fax: ' . $practice_fax . "<br/>\n";
    print 'DEA: ' . $practice_dea;
  ?>
    </div>
    <hr/>
  <div class='bottomheader'>
  <?
    print "<span class='mytagname'> Name:</span>\n";
    print "<span class='mydata'> $patient_name </span>\n";
    print "<span class='mytagname'> Address: </span>\n";
    print "<span class='mydata'> $patient_address, $patient_city, " . 
      "$patient_state $patient_zip </span><br/>\n";
    print "<span class='mytagname'>Phone:</span>\n";
    print "<span class='mydata'>$patient_phone</span>\n";
    print "<span class='mytagname'>DOB:</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>Date:</span>\n";
    print "<span class='mydata'>" . date("F d, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>Rx</div><br/>\n";
  ?>
  </div>
  <div class='content'>
    <?
        print $camos_content[0]; 
    ?>
  </div>
  <? print $sigline[$_GET[sigline]] ?>
</div> <!-- end of rx block -->
<?
} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx1'>\n";
}
?>
<?
if ($camos_content[1]) { //decide if we are printing this rx
?>
<div id='rx2'  class='rx' >
  <div class='topheader'>
  <?
    print $physician_name . "<br/>\n";
    print $practice_address . "<br/>\n";
    print $practice_city . ", ";
    print $practice_state . " ";
    print $practice_zip . "<br/>\n";
    //print $practice_phone . "<br/>\n";
    print 'voice: ' . $practice_phone . ' / fax: ' . $practice_fax  . "<br/>\n";
    print 'DEA: ' . $practice_dea;
  ?>
  </div>
    <hr/>
  <div class='bottomheader'>
  <?
    print "<span class='mytagname'> Name:</span>\n";
    print "<span class='mydata'> $patient_name </span>\n";
    print "<span class='mytagname'> Address: </span>\n";
    print "<span class='mydata'> $patient_address, $patient_city, " . 
      "$patient_state $patient_zip </span><br/>\n";
    print "<span class='mytagname'>Phone:</span>\n";
    print "<span class='mydata'>$patient_phone</span>\n";
    print "<span class='mytagname'>DOB:</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>Date:</span>\n";
    print "<span class='mydata'>" . date("F d, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>Rx</div><br/>\n";
  ?>
  </div>
  <div class='content'>
    <?
        print $camos_content[1]; 
    ?>
  </div>
  <? print $sigline[$_GET[sigline]] ?>
</div> <!-- end of rx block -->
<?
} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx2'>\n";
}
?>
<?
if ($camos_content[2]) { //decide if we are printing this rx
?>
<div id='rx3'  class='rx' >
  <div class='topheader'>
  <?
    print $physician_name . "<br/>\n";
    print $practice_address . "<br/>\n";
    print $practice_city . ", ";
    print $practice_state . " ";
    print $practice_zip . "<br/>\n";
    //print $practice_phone . "<br/>\n";
    print 'voice: ' . $practice_phone . ' / fax: ' . $practice_fax . "<br/>\n";
    print 'DEA: ' . $practice_dea;
  ?>
  </div>
    <hr/>
  <div class='bottomheader'>
  <?
    print "<span class='mytagname'> Name:</span>\n";
    print "<span class='mydata'> $patient_name </span>\n";
    print "<span class='mytagname'> Address: </span>\n";
    print "<span class='mydata'> $patient_address, $patient_city, " . 
      "$patient_state $patient_zip </span><br/>\n";
    print "<span class='mytagname'>Phone:</span>\n";
    print "<span class='mydata'>$patient_phone</span>\n";
    print "<span class='mytagname'>DOB:</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>Date:</span>\n";
    print "<span class='mydata'>" . date("F d, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>Rx</div><br/>\n";
  ?>
  </div>
  <div class='content'>
    <?
        print $camos_content[2]; 
    ?>
  </div>
  <? print $sigline[$_GET[sigline]] ?>
</div> <!-- end of rx block -->
<?
} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx3'>\n";
}
?>
<?
if ($camos_content[3]) { //decide if we are printing this rx
?>
<div id='rx4'  class='rx' >
  <div class='topheader'>
  <?
    print $physician_name . "<br/>\n";
    print $practice_address . "<br/>\n";
    print $practice_city . ", ";
    print $practice_state . " ";
    print $practice_zip . "<br/>\n";
    //print $practice_phone . "<br/>\n";
    print 'voice: ' . $practice_phone . ' / fax: ' . $practice_fax . "<br/>\n";
    print 'DEA: ' . $practice_dea;
  ?>
  </div>
    <hr/>
  <div class='bottomheader'>
  <?
    print "<span class='mytagname'> Name:</span>\n";
    print "<span class='mydata'> $patient_name </span>\n";
    print "<span class='mytagname'> Address: </span>\n";
    print "<span class='mydata'> $patient_address, $patient_city, " . 
      "$patient_state $patient_zip </span><br/>\n";
    print "<span class='mytagname'>Phone:</span>\n";
    print "<span class='mydata'>$patient_phone</span>\n";
    print "<span class='mytagname'>DOB:</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>Date:</span>\n";
    print "<span class='mydata'>" . date("F d, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>Rx</div><br/>\n";
  ?>
  </div>
  <div class='content'>
    <?
        print $camos_content[3]; 
    ?>
  </div>
  <? print $sigline[$_GET[sigline]] ?>
</div> <!-- end of rx block -->
<?
} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx4'>\n";
}
?>
</body>
</html>
<?php
  }//end of printing to rx not letterhead
  elseif ($_GET['letterhead']) { //OPTION print to pdf letterhead
	$content = preg_replace('/PATIENTNAME/i',$patient_name,$camos_content[0]);
	include_once('../../../library/classes/class.ezpdf.php');
  	$pdf =& new Cezpdf();
	$pdf->selectFont('../../../library/fonts/Times-Bold');
	$pdf->ezSetCmMargins(3,1,1,1);
	$pdf->ezText($physician_name,12);
	$pdf->ezText($practice_address,12);
	$pdf->ezText($practice_city.', '.$practice_state.' '.$practice_zip,12);
	$pdf->ezText($practice_phone . ' (voice)',12);
	$pdf->ezText($practice_phone . ' (fax)',12);
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
		$pdf->ezText("Print name, sign and date.",12);
	} 
	elseif ($_GET['signer'] == 'doctor') {
		$pdf->ezText('Sincerely,',12);
		$pdf->ezText('',12);
		$pdf->ezText('',12);
		$pdf->ezText($physician_name,12);
	}
	$pdf->ezStream();
  }
} //end of if print
  else { //OPTION selection of what to print
?>
<html>
<head>
<? html_header_show();?>
<title>
Four Pane Prescription Printer
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
<h1>Select CAMOS Entries for printing</h1>
<form method=POST name='pick_items' target=_new>
<input type=button name=cyclerx value='cycle' onClick='cycle()'><br/>
<input type='button' value='check all' onClick='checkall()'>
<input type='button' value='uncheck all' onClick='uncheckall()'>
<input type=submit name=print value=print>
<?
$query = sqlStatement("select x.id as id, x.category, x.subcategory, x.item from " . 
 "form_CAMOS  as x join forms as y on (x.id = y.form_id) " . 
 "where y.encounter = " .  $_SESSION['encounter'] . 
 " and y.pid = " . $_SESSION['pid'] .  
 " and y.form_name like 'CAMOS%'" .
 " and x.activity = 1");
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
<input type=submit name=print value=print>
</form>
<h1>Update User Information</h1>
<form method=POST name='pick_items'>
<table>
<tr>
  <td> First Name: </td> 
  <td> <input type=text name=practice_fname value ='<? echo $practice_fname; ?>'> </td>
</tr>
<tr>
  <td> Last Name: </td> 
  <td> <input type=text name=practice_lname value ='<? echo $practice_lname; ?>'> </td>
</tr>
<tr>
  <td> Title: </td> 
  <td> <input type=text name=practice_title value ='<? echo $practice_title; ?>'> </td>
</tr>
<tr>
  <td> Street Address: </td> 
  <td> <input type=text name=practice_address value ='<? echo $practice_address; ?>'> </td>
</tr>
<tr>
  <td> City: </td> 
  <td> <input type=text name=practice_city value ='<? echo $practice_city; ?>'> </td>
</tr>
<tr>
  <td> State: </td> 
  <td> <input type=text name=practice_state value ='<? echo $practice_state; ?>'> </td>
</tr>
<tr>
  <td> Zip: </td> 
  <td> <input type=text name=practice_zip value ='<? echo $practice_zip; ?>'> </td>
</tr>
<tr>
  <td> Phone: </td> 
  <td> <input type=text name=practice_phone value ='<? echo $practice_phone; ?>'> </td>
</tr>
<tr>
  <td> Fax: </td> 
  <td> <input type=text name=practice_fax value ='<? echo $practice_fax; ?>'> </td>
</tr>
<tr>
  <td> DEA: </td> 
  <td> <input type=text name=practice_dea value ='<? echo $practice_dea; ?>'> </td>
</tr>
</table>
<input type=submit name=update value=update>
</form>
<?php
} //end of else statement
?>
</body>
</html>
