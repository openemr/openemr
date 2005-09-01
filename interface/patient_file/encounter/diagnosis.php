<?
 include_once("../../globals.php");
 include_once("$srcdir/billing.inc");
 include_once("$srcdir/sql.inc");
 include_once("$srcdir/acl.inc");

if (isset($mode)) {
	if ($mode == "add") {
		if (strtolower($type) == "copay") {
			addBilling($encounter, $type, sprintf("%01.2f", $code), $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,sprintf("%01.2f", 0 - $code));
		}
		elseif (strtolower($type) == "other") {
			addBilling($encounter, $type, $code, $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,sprintf("%01.2f", $fee));
		}
		else {
			addBilling($encounter, $type, $code, $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,$fee);
		}
	}
	elseif ($mode == "justify") {
		$diags = $_POST['code']['diag'];
		$procs = $_POST['code']['proc'];
		$sql = array();
		if (!empty($procs) && !empty($diags)) {
			$sql = array();
			foreach ($procs as $proc) {
				$justify_string = "";
				foreach ($diags as $diag) {
					$justify_string .= $diag . ":"; 
				}
				$sql[] = "UPDATE billing set justify = concat(justify,'" . mysql_real_escape_string($justify_string)  ."') where encounter = '" . mysql_real_escape_string($_POST['encounter_id']) . "' and pid = '" . mysql_real_escape_string($_POST['patient_id']) . "' and code = '" . mysql_real_escape_string($proc) . "'";
			}
		
		}
		if (!empty($sql)) {
			foreach ($sql as $q) {
				$results = sqlQ($q);
			}
		}	
	}
}

?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>

<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=4 bottommargin=0 marginheight=0>

<?
 $thisauth = acl_check('encounters', 'coding_a');
 if (!$thisauth) {
  $erow = sqlQuery("SELECT user FROM forms WHERE " .
   "encounter = '$encounter' AND formdir = 'newpatient' LIMIT 1");
  if ($erow['user'] == $_SESSION['authUser'])
   $thisauth = acl_check('encounters', 'coding');
 }
 if ($thisauth) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   $thisauth = 0;
 }
 if (!$thisauth) {
  echo "<p>(Coding not authorized)</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }
?>

<form name="diagnosis" method="post" action="diagnosis.php?mode=justify">

<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>
<td background="<?echo $linepic;?>" width=7 height=100%>
&nbsp;
</td>
<td valign=top>

<dl>
<dt><a href="diagnosis_full.php" target="Main"><span class=title><? echo ($GLOBALS['phone_country_code'] == '1') ? 'Billing' : 'Coding' ?></span><font class=more><?echo $tmore;?></font></a>
<?
if( !empty( $_GET["back"] ) || !empty( $_POST["back"] ) ){
	print "&nbsp;<a href=\"superbill_codes.php\" target=\"Main\"><font class=more>$tback</font></a>";
	print "<input type=\"hidden\" name=\"back\" value=\"1\">";
}
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="justify" value="Justify"></dt>
</dl>

<table border="0">
<?
if ($result = getBillingByEncounter($pid,$encounter,"*") ) {
	$billing_html = array();
	foreach ($result as $iter) {
		if ($iter["code_type"] == "ICD9") {
				$html = "<tr>";
				
				$html .= "<td valign=\"middle\">" . '<input  style="width: 11px;height: 11px;" name="code[diag]['. $iter["code"]. ']" type="checkbox" value="' .$iter[code] . '">' . "</td><td><div><a target=Main class=small href='diagnosis_full.php'><b>".$iter{"code"}."</b> " . ucwords(strtolower($iter{"code_text"})) . "</a></div></td></tr>\n";
				$billing_html[$iter["code_type"]] .= $html;
				$counter++;
		}
		elseif ($iter["code_type"] == "COPAY") {
			$billing_html[$iter["code_type"]] .= "<tr><td></td><td><a target=Main class=small href='diagnosis_full.php'><b>".$iter{"code"}."</b> " . ucwords(strtolower($iter{"code_text"})) . "</a></td></tr>\n";
		}
		else {
			$billing_html[$iter["code_type"]] .= "<tr><td>" . '<input  style="width: 11px;height: 11px;" name="code[proc]['. $iter["code"]. ']" type="checkbox" value="'. $iter[code] .'">' . "</td><td><a target=Main class=small href='diagnosis_full.php'><b>".$iter{"code"}. ' ' . $iter['modifier'] . "</b> " . ucwords(strtolower($iter{"code_text"})) . "</a><span class=\"small\">";
			$js = split(":",$iter['justify']);
			$counter = 0;
			foreach ($js as $j) {
				if(!empty($j)) {
					if ($counter == 0) {
						$billing_html[$iter["code_type"]] .= " (<b>$j</b>)";
					}
					else {
						$billing_html[$iter["code_type"]] .= " ($j)";
					}
					$counter++;
				}		
			}
			
			
			$billing_html[$iter["code_type"]] .= "</span></td></tr>\n";
		}
			
	}
	
	foreach ($billing_html as $key => $val) {
		print "<tr><td>$key</td><td><table>$val</table><td></tr><tr><td height=\"5\"></td></tr>\n";
	}
}
?>
</tr></table>
</td>
</tr>
<input type="hidden" name="encounter_id" value="<?=$encounter?>">
<input type="hidden" name="patient_id" value="<?=$pid?>">
</form>
</table>

</body>
</html>
