<?
# file save.php.
# uploads what comes from the new.php file.
# the use of modifying inserted values is depreciated.
# uploading documents form.
# does not support modifying.

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
formHeader('Document Input submitted');

//main check to be sure it was finely called.
if ($_POST["action"]!="submit" ) {
	die ("You should send the info from the form.");
}//eof main check
    
//echo ("debug passed if<br>");

# we are not using the standard function
# because of the file being uploaded.

// we give the file a unique identifier
$file_new_name=date ("Y-m-d-H-i-s-");
$file_new_name.=$_SESSION['pid'] ;  //add patient id
//save the file extension
$file_ext=$HTTP_POST_FILES['document_image']['name'];
$extension=substr ( $file_ext , -4);
$file_new_name.=$extension;
// we check for a valid type of file.
if (($HTTP_POST_FILES['document_image']['type'] == 'image/gif') || 
	($HTTP_POST_FILES['document_image']['type'] == 'image/jpg') ||
	($HTTP_POST_FILES['document_image']['type'] == 'image/pjpeg') ||
	($HTTP_POST_FILES['document_image']['type'] == 'image/jpeg') ||
	($HTTP_POST_FILES['document_image']['type'] == 'image/bmp')){ 
	$checktype='ok';
}

if ($checktype!='ok') {
	echo ("<br><span class=text>Only Jpeg, gif and bmp images accepted.<br></span>");
	die ();
}

// we check for the patient subdirectory and we create it if it doesn't exist.
$document_path="$webserver_root/interface/forms/documents/scanned/"; 
$document_path.= $_SESSION['pid'] ; //add patient id 

if(!is_dir($document_path)){
	// if it doesn't exist, then create it.
	mkdir($document_path, 0777); // will create 755 permission because of umask()
	//	echo ("debug passed checked dir<br>");
}

// we copy the file on the patient id subdirectory.
if (!is_file("$document_path/".$file_new_name) ) {
	if ( copy($HTTP_POST_FILES['document_image']['tmp_name'], "$document_path/".$file_new_name ) ) {
		echo ("<br><span class=text>File uploaded to patient's document directory<br></span>");
		// we make that file only readable to avoid mistakes.
		chmod ("$document_path/$file_new_name", 0444);  
		unlink ($HTTP_POST_FILES['document_image']);
	  } else {
		echo ("<br><span class=text>Upload Error - please contact administrator<br></span>");
		die ();
	  }
} else {
	echo ("<br><span class=text>There is a file with that name already - please contact administrator<br></span>");
	die ();
}


// if succesfull we keep going with the form input to the table

$document_description=addslashes (trim ($_POST['document_description']) );
$document_source=addslashes (trim($_POST['document_source']));

$now=date ("Y-m-d H:h:s");
  
$sql = "INSERT INTO `form_documents` SET 
	pid = {$_SESSION['pid']}, 
	groupname='".$_SESSION['authProvider']."', 
	user='".$_SESSION['authUser']."', 
	authorized=$userauthorized, 
	activity=1, 
	date ='$now',
	document_image='$file_new_name' ,
	document_path='$document_path',
	document_description='$document_description' ,
	document_source='$document_source'
";

$result= sqlQuery ($sql); //query passed to db


//this function adds the form to a table wich creates a registry
// then it may be retrievable by the report menu
if ($encounter == "")
	$encounter = date("Ymd");
$newid=mysql_insert_id($GLOBALS['dbh']); // last id 
addForm($encounter, "Scanned Documents", $newid, "documents", $pid, $userauthorized);


// i don't get where this id cames from
// formJump("./print.php?id=$id");
formJump("./print.php?id=$newid");

formFooter();
?>
