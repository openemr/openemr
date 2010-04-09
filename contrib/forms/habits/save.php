<?
# file habits/save.php
# saves what cames from habits/new.php

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
formHeader('Habits form submitted');

$_POST["smoke_quit"]=dateToDB ($_POST["smoke_quit"]);

$id = formSubmit('form_habits', $_POST);

//this function adds the form to a table wich creates a registry
//don't forget to add it in your save.php file.
// then it may be retrievable by the report menu
if ($encounter == "")
	$encounter = date("Ymd");
//$newid=mysql_insert_id($GLOBALS['dbh']); // last id 
if($GLOBALS['lastidado'] >0)
$newid = $GLOBALS['lastidado'];
else
$newid=mysql_insert_id($GLOBALS['dbh']); // last id

addForm($encounter, "Habits", $newid, "habits", $pid, $userauthorized);



formJump("./print.php?id=$id");

formFooter();
?>
