<?
include_once("../../globals.php");
include_once("$srcdir/forms.inc");
include_once("$srcdir/encounter.inc");
include_once("$srcdir/patient.inc");
?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $title_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<?

$result = getPatientData($pid, "fname,lname");

//$subresult = getFormByEncounter($pid, $encounter , "*");
//$encounter_date = date( "D F jS Y" ,strtotime($subresult[0]{"date"}));

/*
if (!empty($_GET["set_encounter"])) {
	//get the date:
	$subresult = getFormByEncounter($pid, $_GET["set_encounter"] , "*");
	$encounter_date = date( "D F jS Y" ,strtotime($subresult[0]{"date"}));
} else {
	$encounter_date = "(Today) " . date( "D F jS Y" ); //otherwise, set today's date
}
*/
if( !empty( $encounter ) ){
	$subresult = getFormByEncounter($pid, $_GET["set_encounter"] , "*");
	$encounter_date = date( "D F jS Y" ,strtotime($subresult[0]{"date"}));
} else {
	
	$encounter_date = "(Today) " . date( "D F jS Y" ); //otherwise, set today's date
}

$provider_results = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td nowrap>
<span class="title_bar_top"><?echo $result{"fname"} . " " . $result{"lname"};?></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="title">Logged in as: <?echo $provider_results{"fname"}.' '.$provider_results{"lname"};?></span><span style="font-size:9pt;"> (<?=$_SESSION['authGroup']?>)</span>
</td>
<td align="right" nowrap>
<span class="title_bar_top">Encounter: <?echo $encounter_date?></span><br>

</td>
</tr>
</table>

</body>
</html>
