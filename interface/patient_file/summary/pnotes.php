<?
include_once("../../globals.php");
include_once("$srcdir/pnotes.inc");
?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>
<td background="<?echo $linepic;?>" width=7 height=100%>
&nbsp;
</td>
<td valign=top>

<a href="pnotes_full.php" target="Main"><font class="title">Patient Notes</font><font class=more><?echo $tmore;?></font></a>

<br>

<table border=0>

<?
//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 4;

$conn = $GLOBALS['adodb']['db'];

// Get the billing note if there is one.
$billing_note = "";
$colorbeg = "";
$colorend = "";
$sql = "select genericname2, genericval2 " .
    "from patient_data where pid = '$pid' limit 1";
$resnote = $conn->Execute($sql);
if($resnote && !$resnote->EOF && $resnote->fields['genericname2'] == 'Billing') {
  $billing_note = $resnote->fields['genericval2'];
  $colorbeg = "<font color='red'>";
  $colorend = "<font>";
}

//Display what the patient owes
require_once($GLOBALS['fileroot'] ."/library/classes/WSWrapper.class.php");
$customer_info['id'] = 0;
$sql = "SELECT foreign_id from integration_mapping as im LEFT JOIN patient_data as pd on im.local_id=pd.id where pd.pid = '" . $pid . "' and im.local_table='patient_data' and im.foreign_table='customer'";
$result = $conn->Execute($sql);
if($result && !$result->EOF) 
{
		$customer_info['id'] = $result->fields['foreign_id'];
}

$function['ezybiz.customer_balance'] = array(new xmlrpcval($customer_info,"struct"));
$ws = new WSWrapper($function);
if(is_numeric($ws->value)) {
  $formatted = sprintf('$%01.2f', $ws->value);
  print "<tr><td>$colorbeg" . "Balance Due$colorend</td><td>$colorbeg$formatted$colorend</td></tr>\n";
}

if($billing_note) {
  print "<tr><td>$colorbeg" . "Billing Note$colorend</td><td>$colorbeg$billing_note$colorend</td></tr>\n";
}

//retrieve all active notes
if ($result = getPnotesByDate("", 1, "date,body,user",$pid,"all",0)){

$notes_count = 0;//number of notes so far displayed
foreach ($result as $iter) {
	if ($notes_count >= $N) {
		//we have more active notes to print, but we've reached our display maximum
		print "<tr><td colspan=3 align=center><a target=Main href='pnotes_full.php?active=1' class=alert>Some notes were not displayed. Click here to view all.</a></td></tr>\n";
		break;
	}
	
	
	if (getdate() == strtotime($iter{"date"})) {
		$date_string = "Today, " . date( "D F jS" ,strtotime($iter{"date"}));
	} else {
		$date_string = date( "D F jS" ,strtotime($iter{"date"}));
	}
	
	print "<tr><td><a href='pnotes_full.php?active=1' target=Main class=bold>".$date_string . " (". $iter{"user"}.")</a></td><td>" . "<a href='pnotes_full.php?active=1' target=Main class=text>" . stripslashes($iter{"body"}) . "</a></td></tr>\n";
	
	
	$notes_count++;
}
}

?>

</table>

</td>
</tr>
</table>

</body>
</html>
