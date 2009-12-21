<?php
include_once("../globals.php");
include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
require_once("$srcdir/classes/POSRef.class.php");
require_once("$srcdir/formdata.inc.php");

if ($_POST["mode"] == "facility")
{
	sqlStatement("update facility set
		name='"              . formData('facility','P',true) . "',
		phone='"             . formData('phone','P',true) . "',
		fax='"               . formData('fax','P',true) . "',
		street='"            . formData('street','P',true) . "',
		city='"              . formData('city','P',true) . "',
		state='"             . formData('state','P',true) . "',
		postal_code='"       . formData('postal_code','P',true) . "',
		country_code='"      . formData('country_code','P',true) . "',
		federal_ein='"       . formData('federal_ein','P',true) . "',
		service_location='"  . formData('service_location','P',true) . "',
		billing_location='"  . formData('billing_location','P',true) . "',
		accepts_assignment='". formData('accepts_assignment','P',true) . "',
		pos_code='"          . formData('pos_code','P',true) . "',
		domain_identifier='" . formData('domain_identifier','P',true) . "',
		facility_npi='"      . formData('facility_npi','P',true) . "',
		attn='"              . formData('attn','P',true) . "'
		where id='"          . formData('fid','P',true) . "'");
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

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">

<span class="title"><?php xl('Edit Facility Information','e'); ?></span>

<form name='facility' method='post' action="facility_admin.php">
<input type=hidden name=mode value="facility">
<input type=hidden name=fid value="<?php echo $my_fid;?>">
<?php $facility = sqlQuery("select * from facility where id='$my_fid'"); ?>
<br><br>
<table border=0 cellpadding=0 cellspacing=0>
 <tr>
  <td width='24'><span class='text'><?php xl('Name','e'); ?>: </span></td>
  <td width='120'><input type='entry' name='facility' size='20' value='<?php echo htmlspecialchars($facility['name'], ENT_QUOTES) ?>'></td>
  <td rowspan='10' width='15'></td>
  <td><span class='text'><?php xl('Phone','e'); ?> <?php xl('as','e'); ?> (000) 000-0000:</span></td>
  <td width='210'><input type='entry' name='phone' size='20' value='<?php echo htmlspecialchars($facility['phone'], ENT_QUOTES) ?>'></td>
 </tr>
 <tr>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td><span class='text'><?php xl('Fax','e'); ?> <?php xl('as','e'); ?> (000) 000-0000:</span></td>
  <td width='210'><input type='entry' name='fax' size='20' value='<?php echo htmlspecialchars($facility['fax'], ENT_QUOTES) ?>'></td>
 </tr>
<tr>
	<td><span class=text><?php xl('Address','e'); ?>: </span></td><td><input type=entry size=20 name=street value="<?php echo htmlspecialchars($facility["street"], ENT_QUOTES) ?>"></td>
	<td><span class=text><?php xl('City','e'); ?>: </span></td><td><input type=entry size=20 name=city value="<?php echo htmlspecialchars($facility{"city"}, ENT_QUOTES) ?>"></td>
</tr>
<tr>
	<td><span class=text><?php xl('State','e'); ?>: </span></td><td><input type=entry size=20 name=state value="<?php echo htmlspecialchars($facility{"state"}, ENT_QUOTES) ?>"></td>
	<td><span class=text><?php xl('Zip Code','e'); ?>: </span></td><td><input type=entry size=20 name=postal_code value="<?php echo htmlspecialchars($facility{"postal_code"}, ENT_QUOTES) ?>"></td>
</tr>
<tr>
	<td><span class=text><?php xl('Country','e'); ?>: </span></td><td><input type=entry size=20 name=country_code value="<?php echo htmlspecialchars($facility{"country_code"}, ENT_QUOTES) ?>"></td>
	<td><span class=text><?php xl('Federal EIN','e'); ?>: </span></td><td><input type=entry size=20 name=federal_ein value="<?php echo htmlspecialchars($facility{"federal_ein"}, ENT_QUOTES) ?>"></td>
</tr>
<tr>
	 <td>&nbsp;</td><td>&nbsp;</td>
	<td width="21"><span class=text><?php ($GLOBALS['simplified_demographics'] ? xl('Facility Code','e') : xl('Facility NPI','e')); ?>:
  </span></td><td><input type=entry size=20 name=facility_npi value="<?php echo htmlspecialchars($facility{"facility_npi"}, ENT_QUOTES) ?>"></td>
</tr>
 <tr>
  <td><span class='text'><?php xl('Billing Location','e'); ?>: </span></td>
  <td><input type='checkbox' name='billing_location' value='1' <?php if ($facility['billing_location'] == 1) echo 'checked'; ?>></td>
  <td rowspan='2'><span class='text'><?php xl('Accepts Assignment','e'); ?><br>(<?php xl('only if billing location','e'); ?>): </span></td>
  <td><input type='checkbox' name='accepts_assignment' value='1' <?php if ($facility['accepts_assignment'] == 1) echo 'checked'; ?>></td>
 </tr>
 <tr>
  <td><span class='text'><?php xl('Service Location','e'); ?>: </span></td>
  <td><input type='checkbox' name='service_location' value='1' <?php if ($facility['service_location'] == 1) echo 'checked'; ?>></td>
  <td>&nbsp;</td>
 </tr>
<tr>
	<td><span class=text><?php xl('POS Code','e'); ?>: </span></td>
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
	<td><span class="text"><?php xl('Billing Attn','e'); ?>:</span></td>
	<td colspan="4"><input type="text" name="attn" size="45" value="<?php echo htmlspecialchars($facility['attn'], ENT_QUOTES) ?>"></td>
</tr>
<tr>
	<td><span class="text"><?php xl('CLIA Number','e'); ?>:</span></td>
	<td colspan="4"><input type="text" name="domain_identifier" size="45" value="<?php echo htmlspecialchars($facility['domain_identifier'], ENT_QUOTES) ?>"></td>
</tr>
<tr>
	<td>&nbsp;</td><td>&nbsp;</td>
	<td>&nbsp;</td><td><br><br><input type="submit" value=<?php xl('Update Info','e'); ?>>&nbsp;&nbsp;&nbsp;<a href="facilities.php" class=link_submit>[<?php xl('Back','e'); ?>]</font></a></td>
</tr>
</table>
</form>

</body>
</html>
