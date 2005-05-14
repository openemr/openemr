<?
include_once("../../globals.php");
include_once("$srcdir/api.inc");
formHeader('Habits form submitted');

$id = formSubmit('habits', $_POST);

formJump("./print.php?id=$id");


formFooter();
?>
