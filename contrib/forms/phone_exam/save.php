<?
# file save.php.

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
formHeader('Phone Exam');

//main check to be sure it was finely called.
if ($_POST["action"]!="submit" ) {
	die ("You should send the info from the form.");
}//eof main check
    
//echo ("debug passed if<br>");

# we are not using the standard function
# because of the file being uploaded.
// if succesfull we keep going with the form input to the table

$notes=addslashes (trim ($_POST['notes']) );

$now=date ("Y-m-d H:h:s");
  
$sql = "INSERT INTO `form_phone_exam` SET 
	pid = {$_SESSION['pid']}, 
	groupname='".$_SESSION['authProvider']."', 
	user='".$_SESSION['authUser']."', 
	authorized=$userauthorized, 
	activity=1, 
	date ='$now',
	notes='$notes'
";

$result= sqlQuery ($sql); //query passed to db


//this function adds the form to a table wich creates a registry
// then it may be retrievable by the report menu
if ($encounter == "")
	$encounter = date("Ymd");
//$newid=mysql_insert_id($GLOBALS['dbh']); // last id 
if($GLOBALS['lastidado'] >0)
$newid = $GLOBALS['lastidado'];
else
$newid=mysql_insert_id($GLOBALS['dbh']); // last id

addForm($encounter, "Phone Exam", $newid, "phone_exam", $pid, $userauthorized);


// i don't get where this id cames from
// formJump("./print.php?id=$id");
formJump("./print.php?id=$newid");

formFooter();
?>
