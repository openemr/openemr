<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2005-2010 Z&H Healthcare Solutions, LLC <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Paul Simon K <paul@zhservices.com> 
//
// +------------------------------------------------------------------------------+
//===============================================================================
//Electronic posting is handled here.
//===============================================================================
require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/sql-ledger.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");
require_once("$srcdir/parse_era.inc.php");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/formatting.inc.php");
//===============================================================================
// This is called back by parse_era() if we are processing X12 835's.
$alertmsg = '';
$where = '';
$eraname = '';
$eracount = 0;
$Processed=0;
function era_callback(&$out) {
  global $where, $eracount, $eraname, $INTEGRATED_AR;
  ++$eracount;
  $eraname = $out['gs_date'] . '_' . ltrim($out['isa_control_number'], '0') .
    '_' . ltrim($out['payer_id'], '0');
  list($pid, $encounter, $invnumber) = slInvoiceNumber($out);
  if ($pid && $encounter) {
    if ($where) $where .= ' OR ';
    if ($INTEGRATED_AR) {
      $where .= "( f.pid = '$pid' AND f.encounter = '$encounter' )";
    } else {
      $where .= "invnumber = '$invnumber'";
    }
  }
}
//===============================================================================
  // Handle X12 835 file upload.
  if ($_FILES['form_erafile']['size']) {
    $tmp_name = $_FILES['form_erafile']['tmp_name'];
    // Handle .zip extension if present.  Probably won't work on Windows.
    if (strtolower(substr($_FILES['form_erafile']['name'], -4)) == '.zip') {
      rename($tmp_name, "$tmp_name.zip");
      exec("unzip -p $tmp_name.zip > $tmp_name");
      unlink("$tmp_name.zip");
    }
    $alertmsg .= parse_era($tmp_name, 'era_callback');
    $erafullname = $GLOBALS['OE_SITE_DIR'] . "/era/$eraname.edi";
    if (is_file($erafullname)) {
      $alertmsg .=  xl("Warning").': '. xl("Set").' '.$eraname.' '. xl("was already uploaded").' ';
      if (is_file($GLOBALS['OE_SITE_DIR'] . "/era/$eraname.html"))
	   {
        $Processed=1;
		$alertmsg .=  xl("and processed.").' ';
	   } 
      else
        $alertmsg .=  xl("but not yet processed.").' ';;
    }
    rename($tmp_name, $erafullname);
  } // End 835 upload
//===============================================================================
$DateFormat=DateFormatRead();
//===============================================================================
?>
<html>
<head>
<?php if (function_exists('html_header_show')) html_header_show(); ?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script language='JavaScript'>
 var mypcc = '1';
</script>
<script type="text/javascript" language="javascript" >
function Validate()
 {
  if(document.getElementById('uploadedfile').value=='')
   {
    alert("<?php echo htmlspecialchars( xl('Please Choose a file'), ENT_QUOTES) ?>");
	return false;
   }
  if(document.getElementById('hidden_type_code').value=='')
   {
	alert("<?php echo htmlspecialchars( xl('Select Insurance, by typing'), ENT_QUOTES) ?>");
	document.getElementById('type_code').focus();
	return false;
   }
  if(document.getElementById('hidden_type_code').value!=document.getElementById('div_insurance_or_patient').innerHTML)
   {
	alert("<?php echo htmlspecialchars( xl('Take Insurance, from Drop Down'), ENT_QUOTES) ?>");
	document.getElementById('type_code').focus();
	return false;
   }
	top.restoreSession();
	document.forms[0].submit();
 }
function OnloadAction()
 {//Displays message after upload action,and popups the details.
  after_value=document.getElementById('after_value').value;
  if(after_value!='') 
   {
    alert(after_value);
   }
  <?php
  if ($_FILES['form_erafile']['size']) {
  ?>
	  var f = document.forms[0];
	  var debug = <?php echo htmlspecialchars($_REQUEST['form_without']*1);?> ;
	  var paydate = f.check_date.value;
	  var post_to_date = f.post_to_date.value;
	  var deposit_date = f.deposit_date.value;
	  window.open('sl_eob_process.php?eraname=<?php echo htmlspecialchars($eraname); ?>&debug=' + debug + '&paydate=' + paydate + '&post_to_date=' + post_to_date + '&deposit_date=' + deposit_date + '&original=original' + '&InsId=<?php echo htmlspecialchars(formData('hidden_type_code')); ?>' , '_blank');
	  return false;
  <?php
  }
  ?>
 }
</script>
<script language="javascript" type="text/javascript">
document.onclick=HideTheAjaxDivs;
</script>
<style>
#ajax_div_insurance {
	position: absolute;
	z-index:10;
	background-color: #FBFDD0;
	border: 1px solid #ccc;
	padding: 10px;
}
.bottom{border-bottom:1px solid black;}
.top{border-top:1px solid black;}
.left{border-left:1px solid black;}
.right{border-right:1px solid black;}
</style>
</head>
<body class="body_top" onLoad="OnloadAction()">
<form enctype="multipart/form-data" method='post'  action='era_payments.php'  style="display:inline"  >
<table width="455" border="0"  cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="3" align="left"><b><?php echo htmlspecialchars( xl('Payments'), ENT_QUOTES) ?></b></td>
  </tr>
  <tr height="15">
    <td colspan="3" align="left" ></td>
  </tr>
  <tr>
    <td colspan="3" align="left">
		<ul class="tabNav"> 
		 <li><a href='new_payment.php'><?php echo htmlspecialchars( xl('New Payment'), ENT_QUOTES) ?></a></li> 
		 <li><a href='search_payments.php'><?php echo htmlspecialchars( xl('Search Payment'), ENT_QUOTES) ?></a></li> 
		 <li class='current'><a href='era_payments.php'><?php echo htmlspecialchars( xl('ERA Posting'), ENT_QUOTES) ?></a></li> 
		</ul>	</td>
  </tr>
  <tr>
    <td colspan="3" align="left" >
    <table width="455" border="0" cellspacing="0" cellpadding="10" bgcolor="#DEDEDE"><tr><td>
	<table width="435" border="0" style="border:1px solid black" cellspacing="0" cellpadding="0">
	  <tr height="5">
	    <td width="5"  align="left" ></td>
		<td width="85"  align="left" ></td>
	    <td width="105"  align="left" ></td>
	    <td width="240"  align="left" ></td>
	    </tr>
	  <tr>
	    <td  align="left"></td>
		<td colspan="3"  align="left"><font class='title'><?php echo htmlspecialchars( xl('ERA'), ENT_QUOTES) ?></font></td>
	  </tr>
	  <tr height="5">
	    <td  align="left" ></td>
		<td colspan="3"  align="left" ></td>
	  </tr>
	  <tr>
	    <td  align="left"  class="text"></td>
	    <td  align="left"  class="text"><?php echo htmlspecialchars( xl('Date'), ENT_QUOTES).':' ?></td>
	    <td  align="left"  class="text"><input type='text' size='6' name='check_date' id='check_date' value="<?php echo formData('check_date') ?>"  class="class1 text " onKeyDown="PreventIt(event)" />
		<img src='../../interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg' align='texttop' 
		id='img_checkdate' border='0' alt='[?]' style='cursor:pointer'
		title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
	   <script>
		Calendar.setup({inputField:"check_date", ifFormat:"<?php echo $DateFormat; ?>", button:"img_checkdate"});
	   </script></td>
	    <td  align="left"  class="text"><input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
   <input name="form_erafile" id="uploadedfile"  type="file" class="text" size="10" style="display:inline" /></td>
	    </tr>
	  <tr>
	    <td  align="left"  class="text"></td>
	    <td  align="left"  class="text"><?php echo htmlspecialchars( xl('Post To Date'), ENT_QUOTES).':' ?></td>
	    <td  align="left"  class="text"><input type='text' size='6' name='post_to_date' id='post_to_date'  value="<?php echo formData('post_to_date') ?>" class="class1 text "   onKeyDown="PreventIt(event)"  />
		<img src='../../interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg' align='texttop' 
		id='img_post_to_date' border='0' alt='[?]' style='cursor:pointer'
		title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
	   <script>
		Calendar.setup({inputField:"post_to_date", ifFormat:"<?php echo $DateFormat; ?>", button:"img_post_to_date"});
	   </script></td>
	    <td  align="left"  class="text"><input type='checkbox' name='form_without' value='1' <?php echo $_REQUEST['form_without']*1==1 || ($_REQUEST['form_without']*1==0 && !isset($_FILES['form_erafile'])) ? "checked" : '' ?>/> <?php echo htmlspecialchars( xl('Without Update'), ENT_QUOTES); ?></td>
	    </tr>
	  <tr>
	    <td  align="left"  class="text"></td>
	    <td  align="left"  class="text"><?php echo htmlspecialchars( xl('Deposit Date'), ENT_QUOTES).':' ?></td>
	    <td  align="left"  class="text"><input type='text' size='6' name='deposit_date' id='deposit_date'  onKeyDown="PreventIt(event)"   class="text " value="<?php echo formData('deposit_date') ?>"    />
		<img src='../../interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg' align='texttop' 
		id='img_depositdate' border='0' alt='[?]' style='cursor:pointer'
		title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
	   <script>
		Calendar.setup({inputField:"deposit_date", ifFormat:"<?php echo $DateFormat; ?>", button:"img_depositdate"});
	   </script></td>
	    <td  align="left"  class="text"></td>
	    </tr>
	  <tr>
	    <td  align="left"  class="text"></td>
	    <td  align="left"  class="text"><?php echo htmlspecialchars( xl('Insurance'), ENT_QUOTES).':' ?></td>
	    <td colspan="2"  align="left"  class="text">
		
		
		<table width="335" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td width="280">
				<input type="hidden" id="hidden_ajax_close_value" value="<?php echo formData('type_code') ?>" /><input name='type_code'  id='type_code' class="text "
				style=" width:280px;"   onKeyDown="PreventIt(event)" value="<?php echo formData('type_code') ?>"  autocomplete="off"   /><br> 
				<!--onKeyUp="ajaxFunction(event,'non','search_payments.php');"-->
					<div id='ajax_div_insurance_section'>
					<div id='ajax_div_insurance_error'>
					</div>
					<div id="ajax_div_insurance" style="display:none;"></div>
					</div>
					</div>

				</td>
				<td width="50" style="padding-left:5px;"><div  name="div_insurance_or_patient" id="div_insurance_or_patient" class="text"  style="border:1px solid black; padding-left:5px; width:50px; height:17px;"><?php echo formData('hidden_type_code') ?></div><input type="hidden" name="description"  id="description" /></td>
			  </tr>
			</table>
		
		
		
		
		</td>
	    </tr>
	  
	  <tr height="5">
	    <td colspan="4"  align="center" ><table  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><a href="#" onClick="javascript:return Validate();" class="css_button"><span><?php echo htmlspecialchars( xl('Process ERA File'), ENT_QUOTES);?></span></a></td>
  </tr>
</table></td>
		</tr>
	  <tr height="5">
	    <td  align="left" ></td>
	    <td colspan="3"  align="left" ></td>
	    </tr>
	</table>
	</td></tr>
    </table>
	</td>
  </tr>
</table>
<input type="hidden" name="after_value" id="after_value" value="<?php echo htmlspecialchars($alertmsg, ENT_QUOTES);?>"/>
<input type="hidden" name="hidden_type_code" id="hidden_type_code" value="<?php echo formData('hidden_type_code') ?>"/>
<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
</form>
</body>
</html>
