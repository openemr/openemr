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
if ($_POST['update']) {
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
<h1>Select CAMOS Entries for printing</h1>
<form method=POST name='pick_items'>
<?
//foreach ($_SESSION as $key => $val) {
//echo "$key => $val <br/>\n";
//}
$query = sqlStatement("select x.id as id, x.category, x.subcategory, x.item from " . 
 "form_CAMOS  as x join forms as y on (date(x.date) like date(y.date)) " . 
 "where y.encounter = " .  $_SESSION['encounter'] . 
 " and x.pid = " . $_SESSION['pid'] .  " and x.activity = 1");
$results = array();
while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
  $results["<input type=checkbox name='ch_" . $result['id'] . "'>" .
    $result['category'] . ':' . $result['subcategory'] . ':' . $result['item'] . "<br/>\n"]++;
}
foreach($results as $key => $val) {
  echo $key;
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

<?

} //end of else statement
//$query = sqlStatement("");
//while ($result = mysql_fetch_array($query, MYSQL_ASSOC)) {
//}
?> 
</body>
</html>

<?
//FUNCTIONS
?>
