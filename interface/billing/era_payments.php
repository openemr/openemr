<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
use OpenEMR\Core\Header;
require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");
require_once("$srcdir/parse_era.inc.php");
require_once("$srcdir/sl_eob.inc.php");
//===============================================================================
// This is called back by parse_era() if we are processing X12 835's.
$alertmsg = '';
$where = '';
$eraname = '';
$eracount = 0;
$Processed=0;
function era_callback(&$out) {
  global $where, $eracount, $eraname;
  ++$eracount;
  $eraname = $out['gs_date'] . '_' . ltrim($out['isa_control_number'], '0') .
    '_' . ltrim($out['payer_id'], '0');
  list($pid, $encounter, $invnumber) = slInvoiceNumber($out);
  if ($pid && $encounter) {
    if ($where) $where .= ' OR ';
      $where .= "( f.pid = '$pid' AND f.encounter = '$encounter' )";
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

//===============================================================================
?>
<!DOCTYPE html>
<html>
<head>
	<?php Header::setupHeader(['bootstrap', 'datetime-picker']);?>
	<!--<?php //if (function_exists('html_header_show')) html_header_show(); ?>
	<link href="<?php//echo $css_header;?>" rel="stylesheet" type="text/css">-->
	<!--<link href="../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" rel="stylesheet" type="text/css">-->
	<!--<link href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.min.css" rel="stylesheet">
	<script src="../../library/textformat.js?v=<?php echo $v_js_includes; ?>" type="text/javascript">
	</script>
	<script src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>" type="text/javascript">-->
	</script>
	<!--<script src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-7-2/index.js" type="text/javascript">
	</script>-->
	<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
	<!--<script src="../../library/js/common.js?v=<?php echo $v_js_includes; ?>" type="text/javascript">
	</script>
	<script src="../../library/js/fancybox/jquery.fancybox-1.2.6.js" type="text/javascript">
	</script>-->
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.css">

	<!-- jQuery library -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.js"></script>
	<!--<script src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker-2-5-4/build/jquery.datetimepicker.full.min.js" type="text/javascript">
	</script>-->
	<script language='JavaScript'>
	var mypcc = '1';
	</script>
	<script language="javascript" type="text/javascript">
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

	$(document).ready(function() {
	   $('.datepicker').datetimepicker({
	       <?php $datetimepicker_timepicker = false; ?>
	       <?php $datetimepicker_showseconds = false; ?>
	       <?php $datetimepicker_formatInput = true; ?>
	       <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
	       <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
	   });
	});
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
	
	.form-group{
	   margin-bottom: 5px;
	}
	legend{
	   border-bottom: 2px solid  #E5E5E5;   
	   background:#E5E5E5;
	   padding-left:10px;
	}
	.form-horizontal .control-label {
	   padding-top: 2px;
	}
	fieldset{
	   border-color: #68171A !important;
	   background-color: #f2f2f2;/*#e7e7e7*/
	   margin-bottom:10px;
	   padding-bottom:15px;
	}
	@media only screen and (max-width: 700px) {
		[class*="col-"] {
		width: 100%;
		text-align:left!Important;
		}
		#form_without{
		margin-left:0px !Important;
		}
		
	}
	.input-group .form-control{
		margin-bottom: 3px;
		margin-left:0px;
	}
	.oe-ckbox-label{
		padding-left: 30px;
		font-weight: 500;
	}
	.oe-file-div{
		padding:10px 15px;
	}
	#form_without{
		margin-left:5px !Important;
	}
	</style>
	<title><?php xlt('ERA Posting'); ?></title>
</head>
<body class="body_top" onload="OnloadAction()">
	<div class="container">
		<div class="row">
			<div class="page-header">
				<h2><?php echo xlt('Payments'); ?></h2>
			</div>
		</div>
		<div class="row" >
			<nav class="navbar navbar-default navbar-color navbar-static-top" >
				<div class="container-fluid">
					<div class="navbar-header">
						<button class="navbar-toggle" data-target="#myNavbar" data-toggle="collapse" type="button"><span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span></button>
					</div>
					<div class="collapse navbar-collapse" id="myNavbar" >
						<ul class="nav navbar-nav" >
							<li class="oe-bold-black">
								<a href='new_payment.php' style="font-weight:700; color:#000000"><?php echo xlt('New Payment'); ?></a>
							</li>
							<li class="oe-bold-black" >
								<a href='search_payments.php' style="font-weight:700; color:#000000"><?php echo xlt('Search Payment'); ?></a>
							</li>
							<li class="active">
								<a href='era_payments.php' style="font-weight:700; color:#000000"><?php echo xlt('ERA Posting'); ?></a>
							</li>
						</ul>
					</div>
				</div>
			</nav>
		</div>
		
		<div class = "row">
			<form action='era_payments.php' enctype="multipart/form-data" method='post' style="display:inline">
				<fieldset>
					<div class="col-xs-12" style="padding-bottom:5px">
						<div class="form-group col-xs9 oe-file-div">
							<div class="input-group"> 
								<label class="input-group-btn">
									<span class="btn btn-default">
										Browse&hellip;<input type="file" id="uploadedfile" name="form_erafile" style="display: none;" >
										<input name="MAX_FILE_SIZE" type="hidden" value="5000000"> 
									</span>
								</label>
								<input type="text" class="form-control" placeholder="<?php echo xlt('Click Browse and select one Electronic Remittance Advice (ERA) file...'); ?>" readonly>
							</div>
						</div>
					</div>
					<div class="col-xs-12" style="padding-bottom:5px">
						<div class="form-group col-xs-3">
							<label class="control-label" for="check_date"><?php echo xlt('Date'); ?>:</label>
							<input class="form-control datepicker" id='check_date' name='check_date' onkeydown="PreventIt(event)" type='text' value="<?php echo formData('check_date') ?>">
						</div>
						<div class="form-group col-xs-3">
							<label class="control-label" for="post_to_date"><?php echo xlt('Post To Date'); ?>:</label>
							<input class="form-control datepicker" id='post_to_date' name='post_to_date' onkeydown="PreventIt(event)" type='text' value="<?php echo formData('post_to_date') ?>">
						</div>
						<div class="form-group col-xs-3 clearfix">
							<label class="control-label" for="form_without"><?php echo xlt('Select'); ?>:</label>
							<label class="checkbox">
								<input name='form_without'  id='form_without' type='checkbox' value='1'> <span class="oe-ckbox-label"><?php echo htmlspecialchars( xl('Without Update'), ENT_QUOTES); ?></span>
							</label>
						</div>
						<div class="form-group col-xs-3">
							<label class="control-label" for="deposit_date"><?php echo xlt('Deposit Date'); ?>:</label>
							<input class="form-control datepicker" id='deposit_date' name='deposit_date' onkeydown="PreventIt(event)" type='text' value="<?php echo formData('deposit_date') ?>">
						</div>
					</div>
					<div class="col-xs-12" style="padding-bottom:5px">
						<div class="form-group col-xs-6">
							<label class="control-label" for="type_code"><?php echo xlt('Insurance'); ?>:</label>
							<input id="hidden_ajax_close_value" type="hidden" value="<?php echo formData('type_code') ?>">
							<input autocomplete="off" class="form-control" id='type_code' name='type_code' onkeydown="PreventIt(event)"  type="text" value="<?php echo formData('type_code') ?>"><br>
							<!--onKeyUp="ajaxFunction(event,'non','search_payments.php');"-->
							<div id='ajax_div_insurance_section'>
								<div id='ajax_div_insurance_error'></div>
								<div id="ajax_div_insurance" style="display:none;"></div>
							</div>
						</div>
						<div class="form-group col-xs-3">
							<label class="control-label" for="div_insurance_or_patient"><?php echo xlt('Insurance ID'); ?>:</label>
							<div class="form-control" id="div_insurance_or_patient" >
								<?php echo formData('hidden_type_code') ?>
							</div>
							<input id="description" name="description" type="hidden">
						</div>
					</div>
				</fieldset>
				<div class="form-group">
					<div class="col-sm-12 text-center">
						<div class="btn-group" role="group">
							<a class="btn btn-default btn-save" href="#" onclick="javascript:return Validate();"><span><?php echo xlt('Process ERA File');?></span></a>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div><!-- End of Container Div-->
	<script>
		$(function() {
			//https://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3
			// We can attach the `fileselect` event to all file inputs on the page
			$(document).on('change', ':file', function() {
				var input = $(this),
				numFiles = input.get(0).files ? input.get(0).files.length : 1,
				label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
				input.trigger('fileselect', [numFiles, label]);
			});

			// We can watch for our custom `fileselect` event like this
			$(document).ready( function() {
				$(':file').on('fileselect', function(event, numFiles, label) {
					var input = $(this).parents('.input-group').find(':text'),
					log = numFiles > 1 ? numFiles + ' files selected' : label;
					
					if( input.length ) {
					input.val(log);
					} 
					else {
					if( log ) alert(log);
					}
				});
			});

			});
	</script>
</body>
</html>