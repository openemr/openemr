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
//Payments in database can be searched through this screen and edit popup is also its part.
//Deletion of the payment is done with logging.
//===============================================================================
require_once("../globals.php");
require_once("$srcdir/log.inc");
require_once("../../library/acl.inc");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/billrep.inc");
require_once(dirname(__FILE__) . "/../../library/classes/OFX.class.php");
require_once(dirname(__FILE__) . "/../../library/classes/X12Partner.class.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/payment.inc.php");
//===============================================================================
//Deletion of payment and its corresponding distributions.
//===============================================================================
set_time_limit(0);
if (isset($_POST["mode"]))
 {
  if ($_POST["mode"] == "DeletePayments")
   {
    $DeletePaymentId=trim(formData('DeletePaymentId' ));
	$ResultSearch = sqlStatement("SELECT distinct encounter,pid from ar_activity where  session_id ='$DeletePaymentId'");
	if(sqlNumRows($ResultSearch)>0)
	 {
	  while ($RowSearch = sqlFetchArray($ResultSearch))
	   {
	    $Encounter=$RowSearch['encounter'];
		$PId=$RowSearch['pid'];
		sqlStatement("update form_encounter set last_level_closed=last_level_closed - 1 where pid ='$PId' and encounter='$Encounter'" );
	   }
	 }
	//delete and log that action
	row_delete("ar_session", "session_id ='$DeletePaymentId'");
	row_delete("ar_activity", "session_id ='$DeletePaymentId'");
	$Message='Delete';
	//------------------
    $_POST["mode"] = "SearchPayment";
   }
//===============================================================================
//Search section.
//===============================================================================
  if ($_POST["mode"] == "SearchPayment")
   {
    $FromDate=trim(formData('FromDate' ));
    $ToDate=trim(formData('ToDate' ));
    $PaymentMethod=trim(formData('payment_method' ));
    $CheckNumber=trim(formData('check_number' ));
    $PaymentAmount=trim(formData('payment_amount' ));
    $PayingEntity=trim(formData('type_name' ));
    $PaymentCategory=trim(formData('adjustment_code' ));
    $PaymentFrom=trim(formData('hidden_type_code' ));
    $PaymentStatus=trim(formData('PaymentStatus' ));
    $PaymentSortBy=trim(formData('PaymentSortBy' ));
    $PaymentDate=trim(formData('payment_date' ));
	$QueryString.="Select * from  ar_session where  ";
	$And='';
	
	if($PaymentDate=='date_val')
	 {
	  $PaymentDateString=' check_date ';
	 }
	elseif($PaymentDate=='post_to_date')
	 {
	  $PaymentDateString=' post_to_date ';
	 }
	elseif($PaymentDate=='deposit_date')
	 {
	  $PaymentDateString=' deposit_date ';
	 }
	
	if($FromDate!='')
	 {
		 $QueryString.=" $And $PaymentDateString >='".DateToYYYYMMDD($FromDate)."'";
		 $And=' and ';
	 }
	if($ToDate!='')
	 {
		 $QueryString.=" $And $PaymentDateString <='".DateToYYYYMMDD($ToDate)."'";
		 $And=' and ';
	 }
	if($PaymentMethod!='')
	 {
		 $QueryString.=" $And payment_method ='".$PaymentMethod."'";
		 $And=' and ';
	 }
	if($CheckNumber!='')
	 {
		 $QueryString.=" $And reference like '%".$CheckNumber."%'";
		 $And=' and ';
	 }
	if($PaymentAmount!='')
	 {
		 $QueryString.=" $And pay_total ='".$PaymentAmount."'";
		 $And=' and ';
	 }
	if($PayingEntity!='')
	 {
		 if($PayingEntity=='insurance')
		  {
			 $QueryString.=" $And payer_id !='0'";
		  }
		 if($PayingEntity=='patient')
		  {
			 $QueryString.=" $And payer_id ='0'";
		  }
		 $And=' and ';
	 }
	if($PaymentCategory!='')
	 {
		 $QueryString.=" $And adjustment_code ='".$PaymentCategory."'";
		 $And=' and ';
	 }
	if($PaymentFrom!='')
	 {
		 if($PayingEntity=='insurance' || $PayingEntity=='')
		  {
			//-------------------
			$res = sqlStatement("SELECT insurance_companies.name FROM insurance_companies
					where insurance_companies.id ='$PaymentFrom'");
			$row = sqlFetchArray($res);
			$div_after_save=$row['name'];
			//-------------------

			 $QueryString.=" $And payer_id ='".$PaymentFrom."'";
		  }
		 if($PayingEntity=='patient')
		  {
			//-------------------
			$res = sqlStatement("SELECT fname,lname,mname FROM patient_data
					where pid ='$PaymentFrom'");
			$row = sqlFetchArray($res);
				$fname=$row['fname'];
				$lname=$row['lname'];
				$mname=$row['mname'];
				$div_after_save=$lname.' '.$fname.' '.$mname;
			//-------------------

			 $QueryString.=" $And patient_id ='".$PaymentFrom."'";
		  }
		 $And=' and ';
	 }

	if($PaymentStatus!='')
	 {
		 	$QsString="select ar_session.session_id,pay_total,global_amount,sum(pay_amount) sum_pay_amount from ar_session,ar_activity
				where ar_session.session_id=ar_activity.session_id group by ar_activity.session_id,ar_session.session_id
				having pay_total-global_amount-sum_pay_amount=0 or pay_total=0";
			$rs= sqlStatement("$QsString");
			while($rowrs=sqlFetchArray($rs))
			 {
			  $StringSessionId.=$rowrs['session_id'].',';
			 }
		 	$QsString="select ar_session.session_id from ar_session	where  pay_total=0";
			$rs= sqlStatement("$QsString");
			while($rowrs=sqlFetchArray($rs))
			 {
			  $StringSessionId.=$rowrs['session_id'].',';
			 }
			 $StringSessionId=substr($StringSessionId, 0, -1);
		 if($PaymentStatus=='fully_paid')
		  {
			 $QueryString.=" $And session_id in($StringSessionId) ";
		  }
		 elseif($PaymentStatus=='unapplied')
		  {
			 $QueryString.=" $And session_id not in($StringSessionId) ";
		  }
		 $And=' and ';
	 }
	if($PaymentSortBy!='')
	 {
		 $SortFieldOld=trim(formData('SortFieldOld' ));
		 $Sort=trim(formData('Sort' ));
		 if($SortFieldOld==$PaymentSortBy)
		  {
		   if($Sort=='DESC' || $Sort=='')
		    $Sort='ASC';
		   else
		    $Sort='DESC';
		  }
		 else
		  {
		   $Sort='ASC';
		  }
		$QueryString.=" order by $PaymentSortBy $Sort";
	 }
	 $ResultSearch = sqlStatement($QueryString);
   }
 }
//===============================================================================
$DateFormat=DateFormatRead();
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

<?php include_once("{$GLOBALS['srcdir']}/payment_jav.inc.php"); ?>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>

<script type='text/javascript'>  
//For different browsers there was disparity in width.So this code is used to adjust the width.
if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)){ //test for MSIE x.x;
 var ieversion=new Number(RegExp.$1) // capture x.x portion and store as a number
 if (ieversion>=5 && ieversion<=8)
   {
	 $(document).ready(function() {
		// fancy box
		// special size for
		$(".medium_modal").fancybox( {
			'overlayOpacity' : 0.0,
			'showCloseButton' : true,
			'frameHeight' : 500,
			'frameWidth' : 1097,
			'centerOnScroll' : false
		});
	});
   }
  else
   {
	 $(document).ready(function() {
		// fancy box
		// special size for
		$(".medium_modal").fancybox( {
			'overlayOpacity' : 0.0,
			'showCloseButton' : true,
			'frameHeight' : 500,
			'frameWidth' : 1050,
			'centerOnScroll' : false
		});
	});
   }
}
else
{
 $(document).ready(function() {
	// fancy box
	// special size for
	$(".medium_modal").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 500,
		'frameWidth' : 1050,
		'centerOnScroll' : false
	});
});
}
</script>
<script language='JavaScript'>
 var mypcc = '1';
</script>
<script language='JavaScript'>
 function SearchPayment()
  {//Search  validations.
	if(document.getElementById('FromDate').value=='' && document.getElementById('ToDate').value=='' && document.getElementById('PaymentStatus').selectedIndex==0 && document.getElementById('payment_method').selectedIndex==0 && document.getElementById('type_name').selectedIndex==0 && document.getElementById('adjustment_code').selectedIndex==0 && document.getElementById('check_number').value==''  && document.getElementById('payment_amount').value==''  && document.getElementById('hidden_type_code').value=='' )
	 {
		alert("<?php echo htmlspecialchars( xl('Please select any Search Option.'), ENT_QUOTES) ?>");
		return false;
	 }
	if(document.getElementById('FromDate').value!='' && document.getElementById('ToDate').value!='')
	 {
		if(!DateCheckGreater(document.getElementById('FromDate').value,document.getElementById('ToDate').value,'<?php echo DateFormatRead();?>'))
		 {
			alert("<?php echo htmlspecialchars( xl('From Date Cannot be Greater than To Date.'), ENT_QUOTES) ?>");
			document.getElementById('FromDate').focus();
			return false;
		 }
	 }
	top.restoreSession();
	document.getElementById('mode').value='SearchPayment';
	document.forms[0].submit();
  }
function DeletePayments(DeleteId)
 {//Confirms deletion of payment and all its distribution.
	if(confirm("<?php echo htmlspecialchars( xl('Would you like to Delete Payments?'), ENT_QUOTES) ?>"))
	 {
		document.getElementById('mode').value='DeletePayments';
		document.getElementById('DeletePaymentId').value=DeleteId;
		top.restoreSession();
		document.forms[0].submit();
	 }
	else
	 return false;
 }
function OnloadAction()
 {//Displays message after deletion.
  after_value=document.getElementById('after_value').value;
  if(after_value=='Delete')
   {
    alert("<?php echo htmlspecialchars( xl('Successfully Deleted'), ENT_QUOTES) ?>")
   }
 }
function SearchPayingEntityAction()
 {
  //Which ajax is to be active(patient,insurance), is decided by the 'Paying Entity' drop down, where this function is called.
  //So on changing some initialization is need.Done below.
  document.getElementById('type_code').value='';
  document.getElementById('hidden_ajax_close_value').value='';
  document.getElementById('hidden_type_code').value='';
  document.getElementById('div_insurance_or_patient').innerHTML='&nbsp;';
  document.getElementById('description').value='';
  if(document.getElementById('ajax_div_insurance'))
   {
	 $("#ajax_div_patient_error").empty();
	 $("#ajax_div_patient").empty();
	 $("#ajax_div_insurance_error").empty();
	 $("#ajax_div_insurance").empty();
	 $("#ajax_div_insurance").hide();
	  document.getElementById('payment_method').style.display='';
   }
 }
</script>
<script language="javascript" type="text/javascript">
document.onclick=HideTheAjaxDivs;
</script>
<style>
.class1{width:125px;}
.class2{width:250px;}
.class3{width:100px;}
.class4{width:103px;}
#ajax_div_insurance {
	position: absolute;
	z-index:10;
	background-color: #FBFDD0;
	border: 1px solid #ccc;
	padding: 10px;
}
#ajax_div_patient {
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
<form name='new_payment' method='post'  style="display:inline" >
<table width="560" border="0"  cellspacing="0" cellpadding="0">
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
		 <li class='current'><a href='search_payments.php'><?php echo htmlspecialchars( xl('Search Payment'), ENT_QUOTES) ?></a></li>
		 <li><a href='era_payments.php'><?php echo htmlspecialchars( xl('ERA Posting'), ENT_QUOTES) ?></a></li>
		</ul>	</td>
  </tr>
  <tr>
    <td colspan="3" align="left" >
    <table width="974" border="0" cellspacing="0" cellpadding="10" bgcolor="#DEDEDE"><tr><td>
	<table width="954" border="0" style="border:1px solid black" cellspacing="0" cellpadding="0">
	  <tr height="5">
		<td width="954" colspan="6" align="left" ></td>
	  </tr>
	  <tr>
		<td colspan="6" align="left">&nbsp;<font class='title'><?php echo htmlspecialchars( xl('Payment List'), ENT_QUOTES) ?></font></td>
	  </tr>
	  <tr height="5">
		<td colspan="6" align="left" ></td>
	  </tr>
	  <tr>
	    <td colspan="6" align="left" ><table width="954" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="5"></td>
            <td width="106"></td>
            <td width="128"></td>
            <td width="5"></td>
            <td width="82"></td>
            <td width="128"></td>
            <td width="5"></td>
            <td width="113"></td>
            <td width="125"></td>
            <td width="5"></td>
            <td width="90"></td>
            <td width="162"></td>
          </tr>
          <tr>
            <td align="right"></td>
            <td align="left" class="text"><?php	echo generate_select_list("payment_date", "payment_date", "$PaymentDate", "Payment Date","","class4 text");?></td>
            <td colspan="4">
				<table  border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('From'), ENT_QUOTES).':' ?></td>
					<td><input type='text'  style="width:90px;" name='FromDate' id='FromDate' class="text" value='<?php echo htmlspecialchars($FromDate); ?>' />
					   <img src='../../interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg' align="texttop"
						id='img_FromDate' border='0' alt='[?]' style='cursor:pointer'
						title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
					   <script>
						Calendar.setup({inputField:"FromDate", ifFormat:"<?php echo $DateFormat; ?>", button:"img_FromDate"});
					   </script></td>
					<td width="53">&nbsp;</td>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('To'), ENT_QUOTES).':' ?></td>
					<td><input type='text'  style="width:103px;"  name='ToDate' id='ToDate' class="text" value='<?php echo htmlspecialchars($ToDate); ?>' />
					   <img src='../../interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg' align="texttop"
						id='img_ToDate' border='0' alt='[?]' style='cursor:pointer'
						title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
					   <script>
						Calendar.setup({inputField:"ToDate", ifFormat:"<?php echo $DateFormat; ?>", button:"img_ToDate"});
					   </script></td>
				  </tr>
				</table>	   
	    </td>
        <td class="text"></td>
	    <td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Method'), ENT_QUOTES).':' ?></td>
	    <td align="left"><?php	echo generate_select_list("payment_method", "payment_method", "$PaymentMethod", "Payment Method"," ","class1 text");?></td>
	    <td></td>
	    <td align="left" class="text"><?php echo htmlspecialchars( xl('Check Number'), ENT_QUOTES).':' ?></td>
	    <td><input type="text" name="check_number"   autocomplete="off"  value="<?php echo htmlspecialchars(formData('check_number'));?>"  id="check_number"  class=" class1 text "   /></td>
          </tr>
          <tr>
            <td align="right"></td>
		<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Amount'), ENT_QUOTES).':' ?></td>
		<td align="left"><input   type="text" name="payment_amount"   autocomplete="off"  id="payment_amount" onKeyUp="ValidateNumeric(this);"  value="<?php echo htmlspecialchars(formData('payment_amount'));?>"  style="text-align:right"    class="class1 text "   /></td>
	    <td align="left" ></td>
		<td align="left" class="text"><?php echo htmlspecialchars( xl('Paying Entity'), ENT_QUOTES).':' ?></td>
		<td align="left"><?php	echo generate_select_list("type_name", "payment_type", "$type_name","Paying Entity"," ","class1 text","SearchPayingEntityAction()");?>	   </td>
	    <td align="left" ></td>
		<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Category'), ENT_QUOTES).':' ?></td>
		<td align="left"><?php	echo generate_select_list("adjustment_code", "payment_adjustment_code", "$adjustment_code","Paying Category"," ","class1 text");?>			</td>
            <td></td>
			<td align="left" class=" text " ><?php echo htmlspecialchars( xl('Pay Status'), ENT_QUOTES).':' ?></td>
			<td align="left" ><?php echo generate_select_list("PaymentStatus", "payment_status", "$PaymentStatus","Pay Status"," ","class1 text");?></td>
          </tr>
          <tr>
            <td align="right"></td>
			<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment From'), ENT_QUOTES).':' ?></td>
			<td align="left" colspan="5" >

			<table width="335" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td width="280">
				<input type="hidden" id="hidden_ajax_close_value" value="<?php echo htmlspecialchars($div_after_save);?>" /><input name='type_code'  id='type_code' class="text "
				style=" width:280px;"   onKeyDown="PreventIt(event)" value="<?php echo htmlspecialchars($div_after_save);?>"  autocomplete="off"   /><br> 
				<!--onKeyUp="ajaxFunction(event,'non','search_payments.php');"-->
					<div id='ajax_div_insurance_section'>
					<div id='ajax_div_insurance_error'>					</div>
					<div id="ajax_div_insurance" style="display:none;"></div>
					</div>
					</div>				</td>
				<td width="50" style="padding-left:5px;"><div  name="div_insurance_or_patient" id="div_insurance_or_patient" class="text"  style="border:1px solid black; padding-left:5px; width:50px; height:17px;"><?php echo htmlspecialchars(formData('hidden_type_code'));?></div><input type="hidden" name="description"  id="description" /><input type="text" name="deposit_date"  id="deposit_date"  style="display:none"/></td>
			  </tr>
			</table>			</td>
            <td align="left" class="text"><?php echo htmlspecialchars( xl('Sort Result by'), ENT_QUOTES).':' ?></td>
            <td align="left" class="text"><?php echo generate_select_list("PaymentSortBy", "payment_sort_by", "$PaymentSortBy","Sort Result by"," ","class1 text");?>            </td>
            <td align="left" class="text"></td>
            <td align="left" class="text"><table  border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td><a href="#" class="css_button" onClick="javascript:return SearchPayment();" ><span><?php echo htmlspecialchars( xl('Search'), ENT_QUOTES);?></span></a></td>
				  </tr>
				</table></td>
            <td align="left"></td>
          </tr>
		  <tr height="5">
			<td colspan="12" align="left" ></td>
		  </tr>
        </table></td>
	    </tr>
	</table>
	</td></tr>

		<!--Search-->
		<?php //
		  if ($_POST["mode"] == "SearchPayment")
		   {
			?>
			  <tr>
				<td>
					<table  border="0" cellspacing="0" cellpadding="0">
					<?php //
						if(sqlNumRows($ResultSearch)>0)
						 {
						?>
							  <tr class="text" bgcolor="#dddddd">
								<td width="25" class="left top" >&nbsp;</td>
								<td width="60" class="left top" ><?php echo htmlspecialchars( xl('ID'), ENT_QUOTES) ?></td>
								<td width="70" class="left top" ><?php echo htmlspecialchars( xl('Date'), ENT_QUOTES) ?></td>
								<td width="83" class="left top" ><?php echo htmlspecialchars( xl('Paying Entity'), ENT_QUOTES) ?></td>
								<td width="245" class="left top" ><?php echo htmlspecialchars( xl('Payer'), ENT_QUOTES) ?></td>
								<td width="57" class="left top" ><?php echo htmlspecialchars( xl('Ins Code'), ENT_QUOTES) ?></td>
								<td width="110" class="left top" ><?php echo htmlspecialchars( xl('Payment Method'), ENT_QUOTES) ?></td>
								<td width="130" class="left top" ><?php echo htmlspecialchars( xl('Check Number'), ENT_QUOTES) ?></td>
								<td width="70" class="left top" ><?php echo htmlspecialchars( xl('Pay Status'), ENT_QUOTES) ?></td>
								<td width="50" class="left top" ><?php echo htmlspecialchars( xl('Payment'), ENT_QUOTES) ?></td>
							    <td width="70" class="left top right" ><?php echo htmlspecialchars( xl('Undistributed'), ENT_QUOTES) ?></td>
							  </tr>
							  <?php
								$CountIndex=0;
								while ($RowSearch = sqlFetchArray($ResultSearch))
								 {
									 $Payer='';
									 if($RowSearch['payer_id']*1 > 0)
									  {
										//-------------------
										$res = sqlStatement("SELECT insurance_companies.name FROM insurance_companies
												where insurance_companies.id ='{$RowSearch['payer_id']}'");
										$row = sqlFetchArray($res);
										$Payer=$row['name'];
										//-------------------
									  }
									 elseif($RowSearch['patient_id']*1 > 0)
									  {
										//-------------------
										$res = sqlStatement("SELECT fname,lname,mname FROM patient_data
												where pid ='{$RowSearch['patient_id']}'");
										$row = sqlFetchArray($res);
											$fname=$row['fname'];
											$lname=$row['lname'];
											$mname=$row['mname'];
											$Payer=$lname.' '.$fname.' '.$mname;
										//-------------------
									  }
									//=============================================
									$CountIndex++;
									if($CountIndex==sqlNumRows($ResultSearch))
									 {
										$StringClass=' bottom left top ';
									 }
									else
									 {
										$StringClass=' left top ';
									 }
									if($CountIndex%2==1)
									 {
										$bgcolor='#ddddff';
									 }
									else
									 {
										$bgcolor='#ffdddd';
									 }
								?>
							  <tr class="text"  bgcolor='<?php echo $bgcolor; ?>'>
								<td class="<?php echo $StringClass; ?>" ><a href="#" onClick="javascript:return DeletePayments(<?php echo htmlspecialchars($RowSearch['session_id']); ?>);" ><img src="../pic/Delete.gif" border="0"/></a></td>
								<td class="<?php echo $StringClass; ?>" ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal' ><?php echo htmlspecialchars($RowSearch['session_id']); ?></a></td>
								<td class="<?php echo $StringClass; ?>" ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal' ><?php echo $RowSearch['check_date']=='0000-00-00' ? '&nbsp;' : htmlspecialchars(oeFormatShortDate($RowSearch['check_date'])); ?></a></td>
								<td class="<?php echo $StringClass; ?>" ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal'  ><?php
								$frow['data_type']=1;
								$frow['list_id']='payment_type';
								$PaymentType='';
								if($RowSearch['payment_type']=='insurance' || $RowSearch['payer_id']*1 > 0)
								 {
										$PaymentType='insurance';
								 }
								elseif($RowSearch['payment_type']=='patient' || $RowSearch['patient_id']*1 > 0)
								 {
										$PaymentType='patient';
								 }
								elseif(($RowSearch['payer_id']*1 == 0 && $RowSearch['patient_id']*1 == 0))
								 {
										$PaymentType='';
								 }
								
								generate_print_field($frow, $PaymentType);
				  ?></a></td>
								<td class="<?php echo $StringClass; ?>" ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal'  ><?php echo  $Payer=='' ? '&nbsp;' : htmlspecialchars($Payer) ;?></a></td>
								<td class="<?php echo $StringClass; ?>" ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal' ><?php echo $RowSearch['payer_id']*1 > 0 ? htmlspecialchars($RowSearch['payer_id']) : '&nbsp;'; ?></a></td>
								<td align="left" class="<?php echo $StringClass; ?> " ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal' ><?php
								$frow['data_type']=1;
								$frow['list_id']='payment_method';
								generate_print_field($frow, $RowSearch['payment_method']);
				  ?></a></td>
								<td align="left" class="<?php echo $StringClass; ?> " ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal' ><?php echo $RowSearch['reference']=='' ? '&nbsp;' : htmlspecialchars($RowSearch['reference']); ?></a></td>
								<td align="left" class="<?php echo $StringClass; ?> " ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal' ><?php 
								$rs= sqlStatement("select pay_total,global_amount from ar_session where session_id='".$RowSearch['session_id']."'");
								$row=sqlFetchArray($rs);
								$pay_total=$row['pay_total'];
								$global_amount=$row['global_amount'];
								$rs= sqlStatement("select sum(pay_amount) sum_pay_amount from ar_activity where session_id='".$RowSearch['session_id']."'");
								$row=sqlFetchArray($rs);
								$pay_amount=$row['sum_pay_amount'];
								$UndistributedAmount=$pay_total-$pay_amount-$global_amount;
								echo $UndistributedAmount*1==0 ? htmlspecialchars( xl('Fully Paid'), ENT_QUOTES) : htmlspecialchars( xl('Unapplied'), ENT_QUOTES); ?></a></td>
								<td align="right" class="<?php echo $StringClass; ?>" ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal' ><?php echo htmlspecialchars($RowSearch['pay_total']); ?></a></td>
							    <td align="right" class="<?php echo $StringClass; ?> right" ><a href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"  class='iframe medium_modal' ><?php echo htmlspecialchars(number_format($UndistributedAmount,2)); ?></a></td>
							  </tr>
								<?php
								 }//while ($RowSearch = sqlFetchArray($ResultSearch))
							}//if(sqlNumRows($ResultSearch)>0)
						   else
							{
						   ?>
							  <tr>
								<td colspan="11" class="text"><?php echo htmlspecialchars( xl('No Result Found, for the above search criteria.'), ENT_QUOTES) ?></td>
							  </tr>
						   <?php
							}// else
						   ?>
					</table>
				</td>
			  </tr>
		   <?php
			}//if ($_POST["mode"] == "SearchPayment")
		?>
    </table>
	</td>
  </tr>
</table>
<input type='hidden' name='mode' id='mode' value='' />
<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
<input type="hidden" name="hidden_type_code" id="hidden_type_code" value="<?php echo htmlspecialchars(formData('hidden_type_code'));?>"/>
<input type='hidden' name='DeletePaymentId' id='DeletePaymentId' value='' />
<input type='hidden' name='SortFieldOld' id='SortFieldOld' value='<?php echo htmlspecialchars($PaymentSortBy);?>' />
<input type='hidden' name='Sort' id='Sort' value='<?php echo htmlspecialchars($Sort);?>' />
<input type="hidden" name="after_value" id="after_value" value="<?php echo htmlspecialchars($Message);?>"/>
</form>
</body>
</html>
