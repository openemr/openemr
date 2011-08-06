<?php

include_once("../../globals.php");
include_once("../../../library/sql.inc");	
include_once("../../../library/formdata.inc.php");

function addAppt($days,$time) {
  $days = formDataCore($days);
  $time = formDataCore($time);

  $sql = "insert into openemr_postcalendar_events (pc_pid, pc_eventDate," . 
    "pc_comments, pc_aid,pc_startTime) values (" . 
    $_SESSION['pid'] . ", date_add(current_date(), interval " . $days . 
    " day),'from CAMOS', " . $_SESSION['authId'] . ",'$time')";
  return sqlInsert($sql);
}
function addVitals($weight, $height, $systolic, $diastolic, $pulse, $temp) {
//This is based on code from /openemr/interface/forms/vitals/C_FormVitals.class.php
//if it doesn't work, look there for changes.
  $_POST['process'] = 'true';
  $_POST['weight'] = $weight;
  $_POST['height'] = $height;
  $_POST['bps'] = $systolic;
  $_POST['bpd'] = $diastolic;
  $_POST['pulse'] = $pulse;
  $_POST['temperature'] = $temp;
  include_once("../../../library/api.inc");
  require ("../vitals/C_FormVitals.class.php");
  $c = new C_FormVitals();
  echo $c->default_action_process($_POST);
}

//This function was copied from billing.inc and altered to support 'justify'
function addBilling2($encounter, $code_type, $code, $code_text, $modifier="",$units="",$fee="0.00",$justify)
{
  $justify_string = '';
  if ($justify)
  {
    //trim eahc entry
    foreach ($justify as $temp_justify) {
      $justify_trimmed[] = trim($temp_justify);	
    }
    //format it
    $justify_string = implode(":",$justify_trimmed).":";
  }
  $code_type = formDataCore($code_type);
  $code = formDataCore($code);
  $code_text = formDataCore($code_text);
  $modifier = formDataCore($modifier);
  $units = formDataCore($units);
  $fee = formDataCore($fee);
  $justify_string = formDataCore($justify_string);
    
  // set to authorize billing codes as default - bm
  //  could place logic here via acls to control who
  //  can authorize as a feature in the future
  $authorized=1;

  $sql = "insert into billing (date, encounter, code_type, code, code_text, pid, authorized, user, groupname,activity,billed,provider_id,modifier,units,fee,justify) values (NOW(), '".$_SESSION['encounter']."', '$code_type', '$code', '$code_text', '".$_SESSION['pid']."', '$authorized', '" . $_SESSION['authId'] . "', '" . $_SESSION['authProvider'] . "',1,0,".$_SESSION['authUserID'].",'$modifier','$units','$fee','$justify_string')";
	
  return sqlInsert($sql);
	
}

function content_parser($input) {

   // parse content field
   $content = $input;

//   comments should really be removed in save.php
//   $content = remove_comments($content);

   //reduce more than two empty lines to no more than two.
   $content = preg_replace("/([^\n]\r[^\n]){2,}/","\r\r",$content);
   $content = preg_replace("/([^\r]\n[^\r]){2,}/","\n\n",$content);
   $content = preg_replace("/(\r\n){2,}/","\r\n\r\n",$content);


   return $content;
}

// implement C style comments ie remove anything between /* and */
function remove_comments($string_to_process) {
   return preg_replace("/\/\*.*?\*\//s","",$string_to_process);
}

//process commands embedded in C style comments where function name is first
//followed by args separated by :: delimiter and nothing else

function process_commands(&$string_to_process, &$camos_return_data) {

  //First, handle replace function as special case.  full depth of inserts should be evaluated prior
  //to evaluating other functions in final string assembly.
  $replace_finished = FALSE; 
  while (!$replace_finished) {
    if (preg_match_all("/\/\*\s*replace\s*::.*?\*\//",$string_to_process, $matches)) {
      foreach($matches[0] as $val) {
        $comm = preg_replace("/(\/\*)|(\*\/)/","",$val);
        $comm_array = split('::', $comm); //array where first element is command and rest are args
        $replacement_item = trim($comm_array[1]); //this is the item name to search for in the database.  easy.
        $replacement_text = '';
        $query = "SELECT content FROM form_CAMOS_item WHERE item like '".$replacement_item."'";
        $statement = sqlStatement($query);
        if ($result = sqlFetchArray($statement)) {$replacement_text = $result['content'];}
        $replacement_text = formDataCore($replacement_text);
        $string_to_process = str_replace($val,$replacement_text,$string_to_process);
      }
    }
    else {$replace_finished = TRUE;}
  }
  //date_add is a function to add a given number of days to the date of the current encounter
  //this will be useful for saving templates of prescriptions with 'do not fill until' dates
  //I am going to implement with mysql date functions. 
  //I am putting this before other functions just like replace function because it is replacing text
  //needs to be here.
  if (preg_match("/\/\*\s*date_add\s*::\s*(.*?)\s*\*\//",$string_to_process, $matches)) {
    $to_replace = $matches[0];
    $days = $matches[1];
    $query = "select date_format(date_add(date, interval $days day),'%W, %m-%d-%Y') as date from form_encounter where " . "pid = " . $_SESSION['pid'] . " and encounter = " . $_SESSION['encounter']; 
    $statement = sqlStatement($query);
    if ($result = sqlFetchArray($statement)){ 
        $string_to_process = str_replace($to_replace,$result['date'],$string_to_process);
    }
  }
  if (preg_match("/\/\*\s*date_sub\s*::\s*(.*?)\s*\*\//",$string_to_process, $matches)) {
    $to_replace = $matches[0];
    $days = $matches[1];
    $query = "select date_format(date_sub(date, interval $days day),'%W, %m-%d-%Y') as date from form_encounter where " . "pid = " . $_SESSION['pid'] . " and encounter = " . $_SESSION['encounter']; 
    $statement = sqlStatement($query);
    if ($result = sqlFetchArray($statement)){ 
        $string_to_process = str_replace($to_replace,$result['date'],$string_to_process);
    }
  }


  //end of special case of replace function
  $return_value = 0;
  $camos_return_data = array(); // to be filled with additional camos form submissions if any embedded
  $command_array = array();  //to be filled with potential commands
  $matches= array();  //to be filled with potential commands
  if (!preg_match_all("/\/\*.*?\*\//s",$string_to_process, $matches)) {return $return_value;}
  $command_array = $matches[0];
  foreach($command_array as $val) {
    //process each command 
    $comm = preg_replace("/(\/\*)|(\*\/)/","",$val);
    $comm_array = split('::', $comm); //array where first element is command and rest are args
    //Here is where we process particular commands
    if (trim($comm_array[0])== 'billing') {
      array_shift($comm_array); //couldn't do it in 'if' or would lose element 0 for next if
      //insert data into the billing table, see, easy!
      $type = trim(array_shift($comm_array));  
      $code = trim(array_shift($comm_array));  
      $text = trim(array_shift($comm_array));  
      $modifier = trim(array_shift($comm_array));  
      $units = trim(array_shift($comm_array));
      //make default units 1 if left blank - bm
      if ($units == '') {
        $units = 1;	  
      }
      $fee = sprintf("%01.2f",trim(array_shift($comm_array)));
      //make default fee 0.00 if left blank
      if ($fee == '') {
        $fee = sprintf("%01.2f",'0.00');
      }
      //in function call 'addBilling' note last param is the remainder of the array.  we will look for justifications here...
      addBilling2($encounter, $type, $code, $text, $modifier,$units,$fee,$comm_array);
    }
    if (trim($comm_array[0])== 'appt') {
      array_shift($comm_array);
      $days = trim(array_shift($comm_array));  
      $time = trim(array_shift($comm_array));  
      addAppt($days, $time);
    } 
    if (trim($comm_array[0])== 'vitals') {
      array_shift($comm_array);
      $weight = trim(array_shift($comm_array));  
      $height = trim(array_shift($comm_array));  
      $systolic = trim(array_shift($comm_array));  
      $diastolic = trim(array_shift($comm_array));  
      $pulse = trim(array_shift($comm_array));  
      $temp = trim(array_shift($comm_array));  
      addVitals($weight, $height, $systolic, $diastolic, $pulse, $temp);
    } 
    $command_count = 0;
    if (trim($comm_array[0]) == 'camos') {
      $command_count++;
      //data to be submitted as separate camos forms
      //this is for embedded prescriptions, test orders etc... usually within a soap note or something  
      //data collected here will be returned so that save.php can give it special treatment and insert
      //into the database after the main form data is submitted so it will be in a sensible order 
      array_push($camos_return_data, 
        array("category" => trim($comm_array[1]), 
        "subcategory" => trim($comm_array[2]), 
        "item" => trim($comm_array[3]), 
        "content" => trim($comm_array[4]))); 
    }
  }
  $string_to_process = remove_comments($string_to_process);
  return $return_value;
} 
// I was using this for debugging.  touch logging, chmod 777 logging, then can use.
  //file_put_contents('./logging',$string_to_process."\n\n*************\n\n",FILE_APPEND);//DEBUG

function replace($pid, $enc, $content) { //replace placeholders with values
	$name= '';
	$fname = '';
	$mname = '';
	$lname = '';
	$dob = '';
	$date = '';
	$age = '';
	$gender = '';
	$doctorname = '';
	$query1 = sqlStatement(
		"select t1.fname, t1.mname, t1.lname, " .
		"t1.sex as gender, " .
		"t1.DOB, " .
		"t2.date " .
		"from patient_data as t1 join form_encounter as t2 on " .
		"(t1.pid = t2.pid) " . 
		"where t2.pid = ".$pid." and t2.encounter = ".$enc);
	if ($results = mysql_fetch_array($query1, MYSQL_ASSOC)) {
		$fname = $results['fname'];
		$mname = $results['mname'];
		$lname = $results['lname'];
		if ($mname) {$name = $fname.' '.$mname.' '.$lname;}
		else {$name = $fname.' '.$lname;}
		$dob = $results['DOB'];
		$date = $results['date'];
		$age = patient_age($dob, $date); 
		$gender = $results['gender'];
	}
	$query1 = sqlStatement("select t1.lname from users as t1 join forms as " .
	"t2 on (t1.username like t2.user) where t2.encounter = ".$enc);
	if ($results = mysql_fetch_array($query1, MYSQL_ASSOC)) {
		$doctorname = "Dr. ".$results['lname'];
	}
	$ret = preg_replace(array("/patientname/i","/patientage/i","/patientgender/i","/doctorname/i"),
	array($name,$age,strtolower($gender),$doctorname), $content);
	return $ret;
}
function patient_age($birthday, $date) { //calculate age from birthdate and a given later date
    list($birth_year,$birth_month,$birth_day) = explode("-",$birthday);
    list($date_year,$date_month,$date_day) = explode("-",$date);
    $year_diff  = $date_year - $birth_year;
    $month_diff = $date_month - $birth_month;
    $day_diff   = $date_day - $birth_day;
    if ($month_diff < 0) $year_diff--;
    elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
    return $year_diff;
}
?>
