<?php
include_once("../../globals.php");
include_once("$srcdir/billing.inc");

$targparm = $GLOBALS['concurrent_layout'] ? "" : "target='Main'";

if (isset($mode)) {
	if ($mode == "add") {
		addBilling($encounter, $type, $code, $text,$pid, $userauthorized,$_SESSION['authUserID']);
	}
	elseif ($mode == "delete") {
		deleteBilling($id);
	}
	elseif ($mode == "clear") {
		clearBilling($id);
	}
}
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>

<body <?php echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='4'
 bottommargin='0' marginheight='0'>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="encounter_bottom.php" onclick="top.restoreSession()">
<?php } else { ?>
<a href="patient_encounter.php" target="Main" onclick="top.restoreSession()">
<?php } ?>

<span class=title><?php xl('Billing','e'); ?></span>
<font class=more><?php echo $tback;?></font></a>

<table border=0 cellpadding=3 cellspacing=0>

<?php
if ($result = getBillingByEncounter($pid,$encounter,"*") ) {
	$billing_html = array();
	foreach ($result as $iter) {
		if ($iter["code_type"] == "ICD9") {
			$html = "<tr>";
			$html .= "<td valign=\"middle\"></td>" .
				"<td><div><a $targparm class='small' href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
				$iter{"code"} . "</b> " . ucwords(strtolower($iter{"code_text"})) .
				"</a></div></td>\n";
			$billing_html[$iter["code_type"]] .= $html;
			$counter++;
		}
		elseif ($iter["code_type"] == "COPAY") {
			$billing_html[$iter["code_type"]] .= "<tr><td></td>" .
				"<td><a $targparm class='small' href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
				$iter{"code"}."</b> " . ucwords(strtolower($iter{"code_text"})) .
				"</a></td>\n";
		}
		else {
			$billing_html[$iter["code_type"]] .= "<tr><td></td>" .
				"<td><a $targparm class='small' href='diagnosis_full.php' onclick='top.restoreSession()'><b>" .
				$iter{"code"} . "</b> " . ucwords(strtolower($iter{"code_text"})) .
				"</a><span class=\"small\">";
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

			$billing_html[$iter["code_type"]] .= "</span></td>";
			$billing_html[$iter["code_type"]] .= "<td>" .
				"<a class=\"link_submit\" href='diagnosis_full.php?mode=clear&id=" .
				$iter{"id"} . "' class='link' onclick='top.restoreSession()'>[" . xl('Clear Justification') .
				"]</a></td>";
		}

		$billing_html[$iter["code_type"]] .= "<td>" .
			"<a class=\"link_submit\" href='diagnosis_full.php?mode=delete&id=" .
			$iter{"id"} . "' class='link' onclick='top.restoreSession()'>[Delete]</a></td>";
		$billing_html[$iter["code_type"]] .= "</tr>\n";
	}

	foreach ($billing_html as $key => $val) {
		print "<tr><td>$key</td><td><table>$val</table><td></tr><tr><td height=\"5\"></td></tr>\n";
	}
}

?>
</table>

</body>
</html>
