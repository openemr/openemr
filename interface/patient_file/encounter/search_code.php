<?

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
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>
<td background="<?echo $linepic;?>" width=7 height=100%>
&nbsp;
</td>
<td valign=top>

<form name='search_form' method='post' action='search_code.php?type=<? echo $code_type ?>'>
<input type=hidden name=mode value="search">

<span class="title" href="search_code.php"><? echo $code_type ?> Codes</span><br>

<input type=entry size=15 name=text><a href="javascript:document.search_form.submit();" class="text">Search</a>
</form>

<?
if (isset($_POST["mode"]) && $_POST["mode"] == "search") {
	$sql = "select * from codes where (code_text like '%" . $_POST["text"] .
		"%' or code like '%" . $_POST["text"] . "%') and code_type = '" .
		$code_types[$code_type]['id'] . "' order by code limit " . ($M + 1);

	if ($res = sqlStatement($sql) ) {
		for($iter=0; $row=sqlFetchArray($res); $iter++)
		{
			$result[$iter] = $row;
		}
?>

<table><tr><td valign=top>
<?
		$count = 0;
		$total = 0;

		if ($result) {
			foreach ($result as $iter) {
				if ($count == $N) {
					echo "</td><td valign=top>\n";
					$count = 0;
				}

				echo "<a target='Diagnosis' class='text' href='diagnosis.php?mode=add" .
					"&type="     . urlencode($code_type) .
					"&code="     . urlencode($iter{"code"}) .
					"&modifier=" . urlencode($iter{"modifier"}) .
					"&units="    . urlencode($iter{"units"}) .
					"&fee="      . urlencode($iter{"fee"}) .
					"&text="     . urlencode($iter{"code_text"}) .
					"'>" .
					ucwords("<b>" . strtoupper($iter{"code"}) . "&nbsp;" . $iter['modifier'] .
					"</b>" . " " . strtolower($iter{"code_text"}))."</a><br>\n";
		
				$count++;
				$total++;
				if ($total == $M) {
					echo "</span><span class=alert>Some codes were not displayed.\n";
					break;
				}
			}
		}
?>
</td></tr></table>
<?
	}
}
?>

</td>
</tr>
</table>

</body>
</html>
