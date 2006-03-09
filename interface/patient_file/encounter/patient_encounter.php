<?
//this code takes care of automatically loading the appropriate
//code page, depending on where the user is coming back from
//for example, if the user clicked on the CPT Custom code editor,
//we should automatically load the cpt custom codes screen,
//not just a default. however, if it is not set, we default to
//the custom icd9 codes
if (!isset($_GET["codefrom"]) ) {
	$code_page = "superbill";
} else {
	$code_page = $_GET["codefrom"];
}
?>
<?
include_once("../../globals.php");

// Session pid must be right.
//
include_once("$srcdir/pid.inc");
if ($_GET["set_pid"] && $_GET["set_pid"] != $_SESSION["pid"]) {
	setpid($_GET["set_pid"]);
}
else if ($_GET["pid"] && $_GET["pid"] != $_SESSION["pid"]) {
	setpid($_GET["pid"]);
}

include_once("$srcdir/encounter.inc");


//only set the global encounter variable if it has been explicityly passed
//thru the url, ie. from the history interface - otherwise, assume
//that the page refresh is a local interface update that is not meant
//to update the encounter variable
if (isset($_GET["set_encounter"])) {
	setencounter($_GET["set_encounter"]);
?>
<HTML>
<HEAD>
<TITLE>
<? xl('Patient Encounters','e'); ?>
</TITLE>
</HEAD>
<frameset rows="50%,50%" cols="*">
  <frameset rows="*" cols="*,200">
	<frame src="forms.php" name="Forms" scrolling="auto">
	<frame src="new_form.php" name="New Form" scrolling="auto">
  </frameset>

  <frameset rows="*" cols="200,400,*">
	<frame src="coding.php" name="Codesets" scrolling="auto">
	<frame src="blank.php" name="Codes" scrolling="auto">
	<frame src="diagnosis.php" name="Diagnosis" scrolling="auto">
  </frameset>

</frameset>
<noframes><body bgcolor="#FFFFFF">
</body></noframes>
</HTML>
<?
	exit(0);
}

//this was either a user click on the encounter menu link
if (isset($_GET["mode"])  && $_GET["mode"] == "new") {
	$enc = date("Ymd");
	if (getFormByEncounter($pid,$enc)) {
		//there is an encounter enterred for today
		$encounter = $enc;
		$_SESSION["encounter"] = $enc;
	} else {
		//no encounter for today yet
		$encounter = "";
		$_SESSION["encounter"] = "";
?>

<HTML>
<HEAD>
<TITLE>
<? xl('New Patient Encounter','e'); ?>
</TITLE>
</HEAD>
<frameset rows="50%,50%" cols="*">
	<frame src="<?echo "$rootdir/forms/newpatient/new.php?autoloaded=1&calenc=".$_GET["calenc"]."";?>" name="New" scrolling="auto">
	<frame src="<?echo "$rootdir/patient_file/history/encounters.php";?>" name="Diagnosis" scrolling="auto">
</frameset>
<noframes><body bgcolor="#FFFFFF">
</body></noframes>
</HTML>

<?
		exit(0);
	}


}



?>


<HTML>
<HEAD>
<TITLE>
<? xl('Patient Encounters','e'); ?>
</TITLE>
</HEAD>
<frameset rows="50%,50%" cols="*">
  <frameset rows="*" cols="*,200">
	<frame src="forms.php" name="Forms" scrolling="auto">
	<frame src="new_form.php" name="New Form" scrolling="auto">
  </frameset>

  <frameset rows="*" cols="200,400,*">
	<frame src="coding.php" name="Codesets" scrolling="auto">
	<frame src="blank.php" name="Codes" scrolling="auto">
	<frame src="diagnosis.php" name="Diagnosis" scrolling="auto">
  </frameset>

</frameset>
<noframes><body bgcolor="#FFFFFF">
</body></noframes>
</HTML>
