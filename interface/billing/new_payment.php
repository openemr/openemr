<?php
/**
 * This screen handles the cash/cheque entry and its distribution to various charges.
 *
 * Copyright (C) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *
 * A copy of the GNU General Public License is included along with this program:
 * openemr/interface/login/GnuGPL.html
 * For more information write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * Author:   Eldho Chacko <eldho@zhservices.com>
 *           Paul Simon K <paul@zhservices.com>
 *
 */
require_once("../globals.php");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/sl_eob.inc.php");
require_once("$srcdir/parse_era.inc.php");
require_once("../../library/acl.inc");
require_once("$srcdir/sql.inc");
require_once("$srcdir/auth.inc");
require_once("$srcdir/formdata.inc.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/billrep.inc");
require_once(dirname(__FILE__) . "/../../library/classes/OFX.class.php");
require_once(dirname(__FILE__) . "/../../library/classes/X12Partner.class.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/payment.inc.php");
//===============================================================================
	$screen='new_payment';
//===============================================================================
// Initialisations
$mode                    = isset($_POST['mode'])                   ? $_POST['mode']                   : '';
$payment_id              = isset($_REQUEST['payment_id'])          ? $_REQUEST['payment_id']          : '';
$request_payment_id              = $payment_id ;
$hidden_patient_code     = isset($_REQUEST['hidden_patient_code']) ? $_REQUEST['hidden_patient_code'] : '';
$default_search_patient  = isset($_POST['default_search_patient']) ? $_POST['default_search_patient'] : '';
$hidden_type_code        = formData('hidden_type_code', true );
//===============================================================================
//ar_session addition code
//===============================================================================

if ($mode == "new_payment" || $mode == "distribute")
{
	if(trim(formData('type_name'   ))=='insurance')
	 {
		$QueryPart="payer_id = '$hidden_type_code', patient_id = '0" ; // Closing Quote in idSqlStatement below
	 }
	elseif(trim(formData('type_name'   ))=='patient')
	 {
		$QueryPart="payer_id = '0', patient_id = '$hidden_type_code" ; // Closing Quote in idSqlStatement below
	 }
      $user_id=$_SESSION['authUserID'];
	  $closed=0;
	  $modified_time = date('Y-m-d H:i:s');
	  $check_date=DateToYYYYMMDD(formData('check_date'));
	  $deposit_date=DateToYYYYMMDD(formData('deposit_date'));
	  $post_to_date=DateToYYYYMMDD(formData('post_to_date'));
	  if($post_to_date=='')
	   $post_to_date=date('Y-m-d');
	  if(formData('deposit_date')=='')
	   $deposit_date=$post_to_date;
	  $payment_id = idSqlStatement("insert into ar_session set "    .
	  	$QueryPart .
        "', user_id = '"     . trim($user_id                  )  .
        "', closed = '"      . trim($closed                   )  .
        "', reference = '"   . trim(formData('check_number'   )) .
        "', check_date = '"  . trim($check_date					) .
        "', deposit_date = '" . trim($deposit_date            )  .
        "', pay_total = '"    . trim(formData('payment_amount')) .
        "', modified_time = '" . trim($modified_time            )  .
        "', payment_type = '"   . trim(formData('type_name'   )) .
        "', description = '"   . trim(formData('description'   )) .
        "', adjustment_code = '"   . trim(formData('adjustment_code'   )) .
        "', post_to_date = '" . trim($post_to_date            )  .
        "', payment_method = '"   . trim(formData('payment_method'   )) .
        "'");
}
 
//===============================================================================
//ar_activity addition code
//===============================================================================
if ($mode == "PostPayments" || $mode == "FinishPayments")
{
	$user_id=$_SESSION['authUserID'];
	$created_time = date('Y-m-d H:i:s');
	for($CountRow=1;;$CountRow++)
	 {
	  if (isset($_POST["HiddenEncounter$CountRow"]))
	   {
	    DistributionInsert($CountRow,$created_time,$user_id);
	   }
	  else
	   break;
	 }
	if($_REQUEST['global_amount']=='yes')
		sqlStatement("update ar_session set global_amount=".trim(formData("HidUnappliedAmount"   ))*1 ." where session_id ='$payment_id'");
	if($mode=="FinishPayments")
	 {
	  header("Location: edit_payment.php?payment_id=$payment_id&ParentPage=new_payment");
	  die();
	 }
    $mode = "search";
	$_POST['mode'] = $mode;
}
 
//==============================================================================
//===============================================================================
$payment_id=$payment_id*1 > 0 ? $payment_id : $request_payment_id;
//===============================================================================
$DateFormat=DateFormatRead();
//==============================================================================
//===============================================================================
?>
<html>
<head>
<?php if (function_exists('html_header_show')) html_header_show(); ?>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language='JavaScript'>
 var mypcc = '1';
</script>
<?php include_once("{$GLOBALS['srcdir']}/payment_jav.inc.php"); ?>
 <script type="text/JavaScript" src="../../library/js/jquery121.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script LANGUAGE="javascript" TYPE="text/javascript">
function CancelDistribute()
 {//Used in the cancel button.Helpful while cancelling the distribution.
	if(confirm("<?php echo htmlspecialchars( xl('Would you like to Cancel Distribution for this Patient?'), ENT_QUOTES) ?>"))
	 {
		document.getElementById('hidden_patient_code').value='';
		document.getElementById('mode').value='search';
		top.restoreSession();
		document.forms[0].submit();
	 }
	else
	 return false;
 }
function PostPayments()
 {//Used in saving the allocation
 	if(CompletlyBlank())//Checks whether any of the allocation row is filled.
	 {
	  alert("<?php echo htmlspecialchars( xl('Fill the Row.'), ENT_QUOTES) ?>")
	  return false;
	 }
 	if(!CheckPayingEntityAndDistributionPostFor())//Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
	 {
	  return false;
	 }
	PostValue=CheckUnappliedAmount();//Decides TdUnappliedAmount >0, or <0 or =0
	if(PostValue==1)
	 {
	  alert("<?php echo htmlspecialchars( xl('Cannot Post Payments.Undistributed is Negative.'), ENT_QUOTES) ?>")
	  return false;
	 }
	if(confirm("<?php echo htmlspecialchars( xl('Would you like to Post Payments?'), ENT_QUOTES) ?>"))
	 {
		document.getElementById('mode').value='PostPayments';
		top.restoreSession();
		document.forms[0].submit();
	 }
	else
	 return false;
 }
function FinishPayments()
 {//Used in finishig the allocation.Usually done when the amount gets reduced to zero.
 //After this is pressed a confirmation screen comes,where you can edit if needed.
 	if(CompletlyBlank())//Checks whether any of the allocation row is filled.
	 {
	  alert("<?php echo htmlspecialchars( xl('Fill the Row.'), ENT_QUOTES) ?>")
	  return false;
	 }
 	if(!CheckPayingEntityAndDistributionPostFor())//Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
	 {
	  return false;
	 }
 	PostValue=CheckUnappliedAmount();//Decides TdUnappliedAmount >0, or <0 or =0
	if(PostValue==1)
	 {
	  alert("<?php echo htmlspecialchars( xl('Cannot Post Payments.Undistributed is Negative.'), ENT_QUOTES) ?>")
	  return false;
	 }
	if(PostValue==2)
	 {
		if(confirm("<?php echo htmlspecialchars( xl('Would you like to Post and Finish Payments?'), ENT_QUOTES) ?>"))
		 {
			UnappliedAmount=document.getElementById('TdUnappliedAmount').innerHTML*1;
			if(confirm("<?php echo htmlspecialchars( xl('Undistributed is'), ENT_QUOTES) ?>" + ' ' + UnappliedAmount +  '.' + "<?php echo htmlspecialchars('\n');echo htmlspecialchars( xl('Would you like the balance amount to apply to Global Account?'), ENT_QUOTES) ?>"))
			 {
				document.getElementById('mode').value='FinishPayments';
				document.getElementById('global_amount').value='yes';
				top.restoreSession();
				document.forms[0].submit();
			 }
			else
			 {
				document.getElementById('mode').value='FinishPayments';
				top.restoreSession();
				document.forms[0].submit();
			 }
		 }
		else
		 return false;
	 }
	else
	 {
		if(confirm("<?php echo htmlspecialchars( xl('Would you like to Post and Finish Payments?'), ENT_QUOTES) ?>"))
		 {
			document.getElementById('mode').value='FinishPayments';
			top.restoreSession();
			document.forms[0].submit();
		 }
		else
		 return false;
	 }

 }
function CompletlyBlank()
 {//Checks whether any of the allocation row is filled.
  for(RowCount=1;;RowCount++)
   {
	  if(!document.getElementById('Payment'+RowCount))
	   break;
	  else
	   {
		   if(document.getElementById('Allowed'+RowCount).value=='' && document.getElementById('Payment'+RowCount).value=='' && document.getElementById('AdjAmount'+RowCount).value=='' && document.getElementById('Deductible'+RowCount).value=='' && document.getElementById('Takeback'+RowCount).value=='' && document.getElementById('FollowUp'+RowCount).checked==false)
			{

			}
		    else
			 return false;
	   }
   }
  return true;
 }
function OnloadAction()
 {//Displays message after saving to master table.
  after_value=document.getElementById("after_value").value;
  payment_id=document.getElementById('payment_id').value;
  if(after_value=='distribute')
   {
   }
  else if(after_value=='new_payment')
   {
	if(document.getElementById('TablePatientPortion'))
	 {
		document.getElementById('TablePatientPortion').style.display='none';
	 }
	if(confirm("<?php echo htmlspecialchars( xl('Successfully Saved.Would you like to Allocate?'), ENT_QUOTES) ?>"))
	 {
		if(document.getElementById('TablePatientPortion'))
		 {
			document.getElementById('TablePatientPortion').style.display='';
		 }
	 }
   }

 }
function ResetForm()
 {//Resets form used in the 'Cancel Changes' button in the master screen.
  document.forms[0].reset();
  document.getElementById('TdUnappliedAmount').innerHTML='0.00';
  document.getElementById('div_insurance_or_patient').innerHTML='&nbsp;';
  CheckVisible('yes');//Payment Method is made 'Check Payment' and the Check box is made visible.
  PayingEntityAction();//Paying Entity is made 'insurance' and Payment Category is 'Insurance Payment'
 }
function FillUnappliedAmount()
 {//Filling the amount
  document.getElementById('TdUnappliedAmount').innerHTML=document.getElementById('payment_amount').value;
 }
</script>
<script language="javascript" type="text/javascript">
document.onclick=HideTheAjaxDivs;
</script>
<style>
.class1{width:125px;}
.class2{width:250px;}
.bottom{border-bottom:1px solid black;}
.top{border-top:1px solid black;}
.left{border-left:1px solid black;}
.right{border-right:1px solid black;}
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
</style>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
</head>
<body class="body_top" onLoad="OnloadAction()"  >
<form name='new_payment' method='post'  action="new_payment.php"  onsubmit='
<?php
 if($payment_id*1==0)
  {
  ?>
top.restoreSession();return SavePayment();
<?php
  }
 else
  {
  ?>
return false;
<?php
  }
  ?>
' style="display:inline" >
<table width="100%" border="0"  cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="3" align="left"><b><?php echo htmlspecialchars( xl('Payments'), ENT_QUOTES) ?></b></td>
  </tr>
  <tr height="15">
    <td colspan="3" align="left" ></td>
  </tr>
  <tr>
    <td colspan="3" align="left">
		<ul class="tabNav">
		 <li class='current'><a href='new_payment.php'><?php echo htmlspecialchars( xl('New Payment'), ENT_QUOTES) ?></a></li>
		 <li><a href='search_payments.php'><?php echo htmlspecialchars( xl('Search Payment'), ENT_QUOTES) ?></a></li>
		 <li><a href='era_payments.php'><?php echo htmlspecialchars( xl('ERA Posting'), ENT_QUOTES) ?></a></li>
		</ul>	</td>
  </tr>
  <tr>
    <td colspan="3" align="left" >
    <?php 
	require_once("payment_master.inc.php"); //Check/cash details are entered here.
	?>
	</td>
  </tr>
</table>
<?php
 if($payment_id*1>0)
  {
  ?>
<table width="999" border="0" cellspacing="0" cellpadding="10" bgcolor="#DEDEDE"><tr><td>
	<table width="979" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td colspan="13" align="left" >
				<!--Distribute section-->
				<?php 
				if($PaymentType=='patient' && $default_search_patient != "default_search_patient")
				 {
				  $default_search_patient = "default_search_patient";
				  $_POST['default_search_patient'] = $default_search_patient;
				  $hidden_patient_code=$TypeCode;
				  $_REQUEST['hidden_patient_code']=$hidden_patient_code;
				  $_REQUEST['RadioPaid']='Show_Paid';
				 }
				require_once("payment_pat_sel.inc.php"); //Patient ajax section and listing of charges.
				?>
			</td>
		  </tr>
		  <tr>
			<td colspan="13" align="left" >
				<?php 
				if($CountIndexBelow>0)
				 {
				?>
				<table border="0" cellspacing="0" cellpadding="0" width="267" align="center" id="AllocateButtons">
				  <tr height="5">
					<td ></td>
					<td ></td>
					<td></td>
				  </tr>
				  <tr>
					<td width="100"><a href="#" onClick="javascript:return PostPayments();"  class="css_button"><span><?php echo htmlspecialchars( xl('Post Payments'), ENT_QUOTES);?></span></a></td>
					<td width="107"><a href="#" onClick="javascript:return FinishPayments();"  class="css_button"><span><?php echo htmlspecialchars( xl('Finish Payments'), ENT_QUOTES);?></span></a></td>
					<td width="60"><a href="#"  onClick="CancelDistribute()" class="css_button"><span><?php echo htmlspecialchars( xl('Cancel'), ENT_QUOTES);?></span></a></td>
				  </tr>
				</table>
				<?php
				 }//if($CountIndexBelow>0)
				?>
		<?php
		 }
		?>		</td>
	  </tr>
	</table>
	</td></tr></table>
<input type="hidden" name="hidden_patient_code" id="hidden_patient_code" value="<?php echo htmlspecialchars($hidden_patient_code);?>"/>
<input type='hidden' name='mode' id='mode' value='' />
<input type='hidden' name='default_search_patient' id='default_search_patient' value='<?php echo $default_search_patient ?>' />
<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
<input type="hidden" name="after_value" id="after_value" value="<?php echo htmlspecialchars($mode);?>"/>
<input type="hidden" name="payment_id" id="payment_id" value="<?php echo htmlspecialchars($payment_id);?>"/>
<input type="hidden" name="hidden_type_code" id="hidden_type_code" value="<?php echo htmlspecialchars($hidden_type_code);?>"/>
<input type='hidden' name='global_amount' id='global_amount' value='' />
</form>
</body>
</html>
