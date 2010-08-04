<?php
/*******************************************************************************\
 * Copyright (C) Visolve (vicareplus_engg@visolve.com)                          *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 ********************************************************************************/

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//


require_once("../../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/options.inc.php");

//if the edit button for editing disclosure is set.
if (isset($_GET['editlid'])) 
{
	$editlid=$_GET['editlid'];
}
?>
<html>
<head>
<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
<!-- supporting javascript code -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript">
//function to validate fields in record disclosure page
function submitform() 
{           
	if (document.forms[0].dates.value.length<=0)
      	
	{document.forms[0].dates.focus();document.forms[0].dates.style.backgroundColor="red";
	}      
        else if (document.forms[0].recipient_name.value.length<=0)
        {
	document.forms[0].dates.style.backgroundColor="white";
	document.forms[0].recipient_name.focus();document.forms[0].recipient_name.style.backgroundColor="red";
	}
        else  if (document.forms[0].desc_disc.value.length<=0)
       	{
	document.forms[0].recipient_name.style.backgroundColor="white";
	document.forms[0].desc_disc.focus();document.forms[0].desc_disc.style.backgroundColor="red";
	}
	else  if (document.forms[0].dates.value.length>0 && document.forms[0].recipient_name.value.length>0 && document.forms[0].desc_disc.value.length>0) 
        {
	top.restoreSession();
        document.forms[0].submit();
	}    
}
</script>
</head>
<body class="body_top">
<div style='float: left; margin-right: 10px'>
<div style='float: left; margin-right: 5px'><?php if($editlid) {?><!--Edit the disclosures-->
<span class="title"><?php echo htmlspecialchars(xl('Edit Disclosure'),ENT_NOQUOTES); ?></span><?php }
else {?> <span class="title"><?php echo htmlspecialchars(xl('Record Disclosure'),ENT_NOQUOTES); ?></span><?php }?>
</div>
<div><a onclick="return submitform()" class="css_button large_button"
	name='form_save' id='form_save' href='#'> <span
	class='css_button_span large_button_span'><?php echo htmlspecialchars(xl('Save'),ENT_NOQUOTES);?></span>
</a></div>
<div><a class="css_button large_button" id='cancel'
	href='disclosure_full.php' target='_parent'> <span
	class='css_button_span large_button_span'><?php echo htmlspecialchars(xl('Cancel'),ENT_NOQUOTES);?></span>
</a></div>
<br>
<form NAME="disclosure_form" METHOD="POST" ACTION="disclosure_full.php" target='_parent' onsubmit='return top.restoreSession()'>
<input type=hidden name=mode value="disclosure">
<table border=0 cellpadding=3 cellspacing=0 align='center'>
	<br>
	<tr>
		<td><span class='text'><?php echo htmlspecialchars(xl('Date'),ENT_NOQUOTES); ?>:</span></td>
		<td><!--retrieve disclosures from extended_log table for modifications--> 
		<?php 
		if($editlid){
			$dres=sqlQuery("select date,recipient,description,event from extended_log where id=?", array($editlid) );
                       $description=$dres{"description"};
			$app_event=$dres{"event"};
			$disc_date=$dres{"date"};
                       $recipient_name=$dres{"recipient"};
		 ?> 
			<input type=hidden name=disclosure_id value="<?php echo htmlspecialchars($editlid,ENT_QUOTES); ?>"> 
			<input type=hidden name=updatemode value="disclosure_update"> 
			<input type='entry' size='20' name='dates' id='dates' readonly='readonly' value='<?php echo htmlspecialchars($disc_date,ENT_QUOTES);?>' style="background-color:white"/>&nbsp; <?php
		}
		else {
			?> <input type='entry' size='20' name='dates' id='dates' value='' readonly="readonly" style="background-color:white"/>&nbsp;
			<?php }
			?> 
		<!-- image for date/time picker --> 
		<img src="../../../interface/pic/show_calendar.gif" id="img_date"
			width="24" height="22" align="absbottom" style="cursor: pointer;"
			title="<?php echo htmlspecialchars(xl('Date selector'),ENT_QUOTES);?>" /></td>
		<script type="text/javascript">
		Calendar.setup({inputField:'dates', ifFormat:'%Y-%m-%d %H:%M:%S',
		button:'img_date', showsTime:true});
</script>
	</tr>
	<tr>
		<td><span class=text><?php echo htmlspecialchars(xl('Type of Disclosure'),ENT_NOQUOTES); ?>: </span></TD>
		<td><?php if($editlid)
		{
		//To incorporate the disclosure types  into the list_options listings
                generate_form_field(array('data_type'=>1,'field_id'=>'disclosure_type','list_id'=>'disclosure_type','fld_length'=>'10','max_length'=>'63','empty_title'=>'SKIP'), $app_event);}
		else{
		//To incorporate the disclosure types  into the list_options listings
                generate_form_field(array('data_type'=>1,'field_id'=>'disclosure_type','list_id'=>'disclosure_type','fld_length'=>'10','max_length'=>'63','empty_title'=>'SKIP'), $title);
		  } ?>
		</td>
	</tr>
	<tr>
		<td><span class=text><?php echo htmlspecialchars(xl('Recipient of the Disclosure'),ENT_NOQUOTES); ?>:
		</span></td>
		<td class='text'>
		<?php 
		if($editlid){
			?> <input type=entry name=recipient_name size=20 value="<?php echo htmlspecialchars($recipient_name,ENT_QUOTES); ?>"></td>
			<?php
		}else
		{?>
			<input type=entry name=recipient_name size=20 value="">
		</td>
		<?php
		}?>
	</tr>
	<tr>   
		<td>
		<span class=text><?php echo htmlspecialchars(xl('Description of the Disclosure'),ENT_NOQUOTES); ?>:</span></td>
		<?php if($editlid) 
		{
		?>
		<td>
		<textarea name=desc_disc wrap=auto rows=4 cols=30><?php echo htmlspecialchars($description,ENT_NOQUOTES); ?></textarea>
		<?php }
		else
		{?>
  		<td><textarea name=desc_disc wrap=auto rows=4 cols=30></textarea><?php }?>
		</td>
	</tr>
</table>
</form>
</body>

