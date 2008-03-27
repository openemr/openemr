<?php

////////////////////////////////////////////////////////////////////////////////
// THIS MODULE REPLACES cptcm_codes.php, hcpcs_codes.php AND icd9cm_codes.php.
////////////////////////////////////////////////////////////////////////////////

include_once("../../globals.php");
include_once("../../../custom/code_types.inc.php");
include_once("$srcdir/sql.inc");

//the maximum number of records to pull out with the search:
$M = 30;

//the number of records to display before starting a second column:
$N = 15;

$code_type = $_GET['type'];
?>

<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_bottom">

<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>

<td valign=top>

<form name='search_form' method='post' action='search_code.php?type=<?php echo $code_type ?>'>
<input type=hidden name=mode value="search">

<span class="title" href="search_code.php"><?php echo $code_type ?> <?php xl('Codes','e'); ?></span><br>

<input type=entry size=15 name=text>
<a href="javascript:document.search_form.submit();" class="text" onclick="top.restoreSession()">
<?php xl('Search','e'); ?></a>
</form>

<?php
if (isset($_POST["mode"]) && $_POST["mode"] == "search") {
  // $sql = "SELECT * FROM codes WHERE (code_text LIKE '%" . $_POST["text"] .
  //   "%' OR code LIKE '%" . $_POST["text"] . "%') AND code_type = '" .
  //   $code_types[$code_type]['id'] . "' ORDER BY code LIMIT " . ($M + 1);

  // The above is obsolete now, fees come from the prices table:
  $sql = "SELECT codes.*, prices.pr_price FROM codes " .
    "LEFT OUTER JOIN patient_data ON patient_data.pid = '$pid' " .
    "LEFT OUTER JOIN prices ON prices.pr_id = codes.id AND " .
    "prices.pr_selector = '' AND " .
    "prices.pr_level = patient_data.pricelevel " .
    "WHERE (code_text LIKE '%" . $_POST["text"] . "%' OR " .
    "code LIKE '%" . $_POST["text"] . "%') AND " .
    "code_type = '" . $code_types[$code_type]['id'] . "' " .
    "ORDER BY code LIMIT " . ($M + 1);

	if ($res = sqlStatement($sql) ) {
		for($iter=0; $row=sqlFetchArray($res); $iter++)
		{
			$result[$iter] = $row;
		}
?>

<table><tr><td valign=top>
<?php
		$count = 0;
		$total = 0;

		if ($result) {
			foreach ($result as $iter) {
				if ($count == $N) {
					echo "</td><td valign=top>\n";
					$count = 0;
				}

				echo "<a target='".xl('Diagnosis')."' class='text' href='diagnosis.php?mode=add" .
					"&type="     . urlencode($code_type) .
					"&code="     . urlencode($iter{"code"}) .
					"&modifier=" . urlencode($iter{"modifier"}) .
					"&units="    . urlencode($iter{"units"}) .
          // "&fee="      . urlencode($iter{"fee"}) .
          "&fee="      . urlencode($iter['pr_price']) .
					"&text="     . urlencode($iter{"code_text"}) .
					"' onclick='top.restoreSession()'>" .
					ucwords("<b>" . strtoupper($iter{"code"}) . "&nbsp;" . $iter['modifier'] .
					"</b>" . " " . strtolower($iter{"code_text"}))."</a><br>\n";

				$count++;
				$total++;
				if ($total == $M) {
					echo "</span><span class=alert>".xl('Some codes were not displayed.')."\n";
					break;
				}
			}
		}
?>
</td></tr></table>
<?php
	}
}
?>

</td>
</tr>
</table>

</body>
</html>
