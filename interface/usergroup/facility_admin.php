<?
include_once("../globals.php");


include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
require_once("$srcdir/classes/POSRef.class.php");


if ($_POST["mode"] == "facility")
{
	 sqlStatement("update facility set
	 	name='{$_POST['facility']}',
		phone='{$_POST['phone']}',
		street='{$_POST['street']}',
		city='{$_POST['city']}',
		state='{$_POST['state']}',
		postal_code='{$_POST['postal_code']}',
		country_code='{$_POST['country_code']}',
		federal_ein='{$_POST['federal_ein']}',
		billing_location='{$_POST['billing_location']}',
		accepts_assignment='{$_POST['accepts_assignment']}',
		pos_code='{$_POST['pos_code']}',
		x12_sender_id='{$_POST['x12_sender_id']}',
		attn='{$_POST['attn']}'
	where id='{$_POST['fid']}'");
}

if (isset($_GET["fid"])) {
	$my_fid = $_GET["fid"];
}

if (isset($_POST["fid"])) {
	$my_fid = $_POST["fid"];
}



?>

<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<span class="title">Edit Facility Information</span>

<form name='facility' method='post' action="facility_admin.php">
<input type=hidden name=mode value="facility">
<input type=hidden name=fid value="<?echo $my_fid;?>">
<span class=bold>Facility Information: </span>
</td><td>
<?php $facility = sqlQuery("select * from facility where id='$my_fid'"); ?>
<br><br>
<table border=0 cellpadding=0 cellspacing=0>
<tr>
	<td><span class=text>Name: </span></td><td><input type=entry name=facility size=20 value="<?=$facility['name']?>"></td><td rowspan="10" width="15"></d>
	<td><span class=text>Phone as (000) 000-0000:</span></td><td><input type=entry name=phone size=20 value="<?=$facility['phone']?>"></td>
</tr>
<tr>
	<td><span class=text>Address: </span></td><td><input type=entry size=20 name=street value="<?=$facility["street"]?>"></td>
	<td><span class=text>City: </span></td><td><input type=entry size=20 name=city value="<?echo $facility{"city"}?>"></td>
</tr>
<tr>
	<td><span class=text>State: </span></td><td><input type=entry size=20 name=state value="<?echo $facility{"state"}?>"></td>
	<td><span class=text>Zip Code: </span></td><td><input type=entry size=20 name=postal_code value="<?echo $facility{"postal_code"}?>"></td>
</tr>
<tr>
	<td><span class=text>Country: </span></td><td><input type=entry size=20 name=country_code value="<?echo $facility{"country_code"}?>"></td>
	<td><span class=text>Federal EIN: </span></td><td><input type=entry size=20 name=federal_ein value="<?echo $facility{"federal_ein"}?>"></td>
</tr>
<tr>
	<td><span class=text>Billing Location: </span></td><td><input type=checkbox name="billing_location" value="1" <?if ($facility['billing_location'] == 1) echo "checked"?>"></td>
	<td><span class=text>Accepts Assignment<br>(only if billing location): </span></td><td><input type=checkbox name="accepts_assignment" value="1" <?if ($facility['accepts_assignment'] == 1) echo "checked"?>"></td>
</tr>
<tr>
	<td><span class=text>POS Code: </span></td>
	<td colspan="6">
		<select name="pos_code">
		<?php
		$pc = new POSRef();
		
		foreach ($pc->get_pos_ref() as $pos) {
			echo "<option value=\"" . $pos["code"] . "\" ";
			if ($facility['pos_code'] == $pos['code']) {
				echo "selected";
			}
			echo ">" . $pos['code']  . ": ". $pos['title'];
			echo "</option>\n";
		}
		
		?>
		</select>
	</td>
</tr>
<tr>
	<td><span class="text">Billing Attn:</span></td>
	<td colspan="4"><input type="text" name="attn" size="45" value="<?=$facility['attn']?>"></td>
</tr>
<!--
This is now deprecated use the newer X12 partner management in practice settings
<tr>
	<td><span class="text">X12 Sender ID: </span></td>
	<td><input type="text" name="x12_sender_id" size="15" value="<?=$facility['x12_sender_id']?>"></td>
</tr>
-->
<tr>
	<td>&nbsp;</td><td>&nbsp;</td>
	<td>&nbsp;</td><td><br><br><input type="submit" value="Update Info">&nbsp;&nbsp;&nbsp;<a href="usergroup_admin.php" class=link_submit>[Back]</font></a></td>
</tr>
</table>
</form>
















</body>
</html>

