<?  include_once ('../../globals.php'); ?> 
<?  include_once ('../../../library/sql.inc'); ?> 
<html>
<head>
<title>
Four Pane Prescription Printer
</title>
</head>
<body>
<?
//practice data
$physician_name = ''; 
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
$query = sqlStatement("select * from users where id =" . $_SESSION['authUserID']);
if ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
  $physician_name = $result['fname'] . ' ' . $result['lname']; 
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
        $content = preg_replace('|\n|','<br/>', $result['content']);
        $content = preg_replace('|<br/><br/>|','<br/>', $content);
        array_push($camos_content,$content); 
      }
    }
  }
?>
<link rel="stylesheet" type="text/css" href="./rx.css" />
</head>
<body>
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
    print $practice_phone . "<br/>\n";
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
    print "<span class='mydata'>(954) 446 - 6958</span>\n";
    print "<span class='mytagname'>DOB:</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>Date:</span>\n";
    print "<span class='mydata'>" . date("F m, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>Rx</div><br/>\n";
  ?>
  </div>
  <div class='content'>
    <?
        print $camos_content[0]; 
    ?>
  </div>
  <div class='signature'>
    ______________________________________________<br/>
    Signature
  </div>
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
    print $practice_phone . "<br/>\n";
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
    print "<span class='mydata'>(954) 446 - 6958</span>\n";
    print "<span class='mytagname'>DOB:</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>Date:</span>\n";
    print "<span class='mydata'>" . date("F m, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>Rx</div><br/>\n";
  ?>
  </div>
  <div class='content'>
    <?
        print $camos_content[1]; 
    ?>
  </div>
  <div class='signature'>
    ______________________________________________<br/>
    Signature
  </div>
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
    print $practice_phone . "<br/>\n";
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
    print "<span class='mydata'>(954) 446 - 6958</span>\n";
    print "<span class='mytagname'>DOB:</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>Date:</span>\n";
    print "<span class='mydata'>" . date("F m, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>Rx</div><br/>\n";
  ?>
  </div>
  <div class='content'>
    <?
        print $camos_content[2]; 
    ?>
  </div>
  <div class='signature'>
    ______________________________________________<br/>
    Signature
  </div>
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
    print $practice_phone . "<br/>\n";
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
    print "<span class='mydata'>(954) 446 - 6958</span>\n";
    print "<span class='mytagname'>DOB:</span>\n";
    print "<span class='mydata'> $patient_dob </span>\n";
    print "<span class='mytagname'>Date:</span>\n";
    print "<span class='mydata'>" . date("F m, Y") . "</span><br/><br/>\n";
    print "<div class='symbol'>Rx</div><br/>\n";
  ?>
  </div>
  <div class='content'>
    <?
        print $camos_content[3]; 
    ?>
  </div>
  <div class='signature'>
    ______________________________________________<br/>
    Signature
  </div>
</div> <!-- end of rx block -->
<?
} // end of deciding if we are printing the above rx block
else {
  print "<img src='./xout.jpg' id='rx4'>\n";
}
?>
</body>
</html>
<?
}
else {//pick
?>
<form method=POST name='pick_items'>
<?
$query = sqlStatement("select * from form_CAMOS where date(date) like current_date() and pid = " . $_SESSION['pid']);
while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
  echo "<input type=checkbox name='ch_" . $result['id'] . "'>" .
    $result['category'] . ':' . $result['subcategory'] . ':' . $result['item'] . "<br/>\n";
}
?>
<input type=submit name=print value=print>
</form>
<?

}
//$query = sqlStatement("");
//while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
//}
?> 
</body>
</html>

<?
//FUNCTIONS
?>
