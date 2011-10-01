<?php
include_once("../globals.php");
include_once("$srcdir/md5.js");
include_once("$srcdir/sql.inc");
require_once("$srcdir/classes/POSRef.class.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

if (isset($_GET["fid"])) {
	$my_fid = $_GET["fid"];
}

if (isset($_POST["fid"])) {
	$my_fid = $_POST["fid"];
}
if ($_POST["mode"] == "facility")
{

	echo '
<script type="text/javascript">
<!--
parent.$.fn.fancybox.close();
//-->
</script>

	';
}
?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/AnchorPosition.js"></script>
<script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/PopupWindow.js"></script>
<script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/ColorPicker2.js"></script>
<script type="text/javascript">
function submitform() {
	<?php if($GLOBALS['erx_enable']){ ?>
	alertMsg='';
	f=document.forms[0];
	for(i=0;i<f.length;i++){
		if(f[i].type=='text' && f[i].value)
		{
			if(f[i].name == 'facility' || f[i].name == 'Washington')
			{
				alertMsg += checkLength(f[i].name,f[i].value,35);
				alertMsg += checkFacilityName(f[i].name,f[i].value);
			}
			else if(f[i].name == 'street')
			{
				alertMsg += checkLength(f[i].name,f[i].value,35);
				alertMsg += checkAlphaNumeric(f[i].name,f[i].value);
			}
			else if(f[i].name == 'phone' || f[i].name == 'fax')
			{
				alertMsg += checkPhone(f[i].name,f[i].value);
			}
			else if(f[i].name == 'federal_ein')
			{
				alertMsg += checkLength(f[i].name,f[i].value,10);
				alertMsg += checkFederalEin(f[i].name,f[i].value);
			}
		}		
	}
	if(alertMsg)
	{
		alert(alertMsg);
		return false;
	}
	<?php } ?>
    if (document.forms[0].facility.value.length>0 && document.forms[0].ncolor.value != '') {
        top.restoreSession();
        document.forms[0].submit();
    } else {
	if(document.forms[0].facility.value.length<=0){
        document.forms[0].facility.style.backgroundColor="red";
        document.forms[0].facility.focus();
	}
	else if(document.forms[0].ncolor.value == ''){
	document.forms[0].ncolor.style.backgroundColor="red";
        document.forms[0].ncolor.focus();	
	}
    }
}

$(document).ready(function(){
    $("#cancel").click(function() {
		  parent.$.fn.fancybox.close();
	 });
});
var cp = new ColorPicker('window');
  // Runs when a color is clicked
function pickColor(color) {
 	document.getElementById('ncolor').value = color;
}
var field;
function pick(anchorname,target) {
	var cp = new ColorPicker('window');
  	field=target;
        cp.show(anchorname);
}
function displayAlert()
{
	if(document.getElementById('primary_business_entity').checked==false)
	alert("<?php echo addslashes(xl('Primary Business Entity tax id is used as account id for NewCrop ePrescription. Changing the facility will affect the working in NewCrop.'));?>");
	else if(document.getElementById('primary_business_entity').checked==true)
	alert("<?php echo addslashes(xl('Once the Primary Business Facility is set, it should not be changed. Changing the facility will affect the working in NewCrop ePrescription.'));?>");
}
</script>

</head>
<body class="body_top" style="width:600px;height:330px !important;">

<table>
    <tr>
        <td>
        <span class="title"><?php xl('Edit Facility','e'); ?></span>&nbsp;&nbsp;&nbsp;</td><td>
        <a class="css_button large_button" name='form_save' id='form_save' onclick='submitform()' href='#' >
            <span class='css_button_span large_button_span'><?php xl('Save','e');?></span>
        </a>
        <a class="css_button large_button" id='cancel' href='#'>
            <span class='css_button_span large_button_span'><?php xl('Cancel','e');?></span>
        </a>
     </td>
  </tr>
</table>

<form name='facility' method='post' action="facilities.php" target="_parent">
    <input type=hidden name=mode value="facility">
    <input type=hidden name=newmode value="admin_facility">	<!--	Diffrentiate Admin and add post backs -->
    <input type=hidden name=fid value="<?php echo $my_fid;?>">
    <?php $facility = sqlQuery("select * from facility where id='$my_fid'"); ?>

    <table border=0 cellpadding=0 cellspacing=1 style="width:630px;">
         <tr>
          <td width='150px'><span class='text'><?php xl('Name','e'); ?>: </span></td>
          <td width='220px'><input type='entry' name='facility' size='20' value='<?php echo htmlspecialchars($facility['name'], ENT_QUOTES) ?>'><font class="mandatory">&nbsp;*</font></td>
          <td width='200px'><span class='text'><?php xl('Phone','e'); ?> <?php xl('as','e'); ?> (000) 000-0000:</span></td>
          <td width='220px'><input type='entry' name='phone' size='20' value='<?php echo htmlspecialchars($facility['phone'], ENT_QUOTES) ?>'></td>
         </tr>
         <tr>
          <td><span class=text><?php xl('Address','e'); ?>: </span></td><td><input type=entry size=20 name=street value="<?php echo htmlspecialchars($facility["street"], ENT_QUOTES) ?>"></td>
          <td><span class='text'><?php xl('Fax','e'); ?> <?php xl('as','e'); ?> (000) 000-0000:</span></td>
          <td><input type='entry' name='fax' size='20' value='<?php echo htmlspecialchars($facility['fax'], ENT_QUOTES) ?>'></td>
         </tr>
        <tr>

            <td><span class=text><?php xl('City','e'); ?>: </span></td>
            <td><input type=entry size=20 name=city value="<?php echo htmlspecialchars($facility{"city"}, ENT_QUOTES) ?>"></td>
            <td><span class=text><?php xl('Zip Code','e'); ?>: </span></td><td><input type=entry size=20 name=postal_code value="<?php echo htmlspecialchars($facility{"postal_code"}, ENT_QUOTES) ?>"></td>
        </tr>
	<?php 
		$ssn='';
		$ein='';
		if($facility['tax_id_type']=='SY'){
		$ssn='selected';
		}
		else{
		$ein='selected';
		}
	?>
        <tr>
            <td><span class=text><?php xl('State','e'); ?>: </span></td><td><input type=entry size=20 name=state value="<?php echo htmlspecialchars($facility{"state"}, ENT_QUOTES) ?>"></td>
            <td><span class=text><?php xl('Tax ID','e'); ?>: </span></td><td><select name=tax_id_type><option value="EI" <?php echo $ein;?>><?php xl('EIN','e'); ?></option><option value="SY" <?php echo $ssn;?>><?php xl('SSN','e'); ?></option></select><input type=entry size=11 name=federal_ein value="<?php echo htmlspecialchars($facility{"federal_ein"}, ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <td><span class=text><?php xl('Country','e'); ?>: </span></td><td><input type=entry size=20 name=country_code value="<?php echo htmlspecialchars($facility{"country_code"}, ENT_QUOTES) ?>"></td>
            <td width="21"><span class=text><?php ($GLOBALS['simplified_demographics'] ? xl('Facility Code','e') : xl('Facility NPI','e')); ?>:
          </span></td><td><input type=entry size=20 name=facility_npi value="<?php echo htmlspecialchars($facility{"facility_npi"}, ENT_QUOTES) ?>"></td>
        </tr>
         <tr>
          <td><span class='text'><?php xl('Billing Location','e'); ?>: </span></td>
          <td><input type='checkbox' name='billing_location' value='1' <?php if ($facility['billing_location'] != 0) echo 'checked'; ?>></td>
          <td rowspan='2'><span class='text'><?php xl('Accepts Assignment','e'); ?><br>(<?php xl('only if billing location','e'); ?>): </span></td>
          <td><input type='checkbox' name='accepts_assignment' value='1' <?php if ($facility['accepts_assignment'] == 1) echo 'checked'; ?>></td>
         </tr>
         <tr>
          <td><span class='text'><?php xl('Service Location','e'); ?>: </span></td>
          <td><input type='checkbox' name='service_location' value='1' <?php if ($facility['service_location'] == 1) echo 'checked'; ?>></td>
          <td>&nbsp;</td>
         </tr>
	 <?php
	 $disabled='';
	 $resPBE=sqlStatement("select * from facility where primary_business_entity='1' and id!='".$my_fid."'");
	 if(sqlNumRows($resPBE)>0)
	 $disabled='disabled';
	 ?>
	 <tr>
          <td><span class='text'><?php xl('Primary Business Entity','e'); ?>: </span></td>
          <td><input type='checkbox' name='primary_business_entity' id='primary_business_entity' value='1' <?php if ($facility['primary_business_entity'] == 1) echo 'checked'; ?> <?php if($GLOBALS['erx_enable']){ ?> onchange='return displayAlert()' <?php } ?> <?php echo $disabled;?>></td>
          <td>&nbsp;</td>
         </tr>
	 <tr>
	  <td><span class='text'><?php echo htmlspecialchars(xl('Color'),ENT_QUOTES); ?>: </span><span class="mandatory">&nbsp;*</span></td> <td><input type=entry name=ncolor id=ncolor size=20 value="<?php echo htmlspecialchars($facility{"color"}, ENT_QUOTES) ?>"></td>
	  <td>[<a href="javascript:void(0);" onClick="pick('pick','newcolor');return false;" NAME="pick" ID="pick"><?php  echo htmlspecialchars(xl('Pick'),ENT_QUOTES); ?></a>]</td><td>&nbsp;</td>

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
        <tr height="20" valign="bottom">
            <td colspan=2><span class="text"><font class="mandatory">*</font> <?php echo xl('Required','e');?></span></td>
        </tr>

    </table>
</form>

</body>
</html>
