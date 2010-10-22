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
//Payments can be edited here.It includes deletion of an allocation,modifying the 
//same or adding a new allocation.Log is kept for the deleted ones.
//===============================================================================
require_once("../globals.php");
require_once("$srcdir/log.inc");
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
	$screen='edit_payment';
//===============================================================================
// deletion of payment distribution code
//===============================================================================
if (isset($_POST["mode"]))
 {
  if ($_POST["mode"] == "DeletePaymentDistribution")
   {
    $DeletePaymentDistributionId=trim(formData('DeletePaymentDistributionId' ));
	$DeletePaymentDistributionIdArray=split('_',$DeletePaymentDistributionId);
	$payment_id=$DeletePaymentDistributionIdArray[0];
	$PId=$DeletePaymentDistributionIdArray[1];
	$Encounter=$DeletePaymentDistributionIdArray[2];
	$Code=$DeletePaymentDistributionIdArray[3];
	$Modifier=$DeletePaymentDistributionIdArray[4];
	//delete and log that action
	row_delete("ar_activity", "session_id ='$payment_id' and  pid ='$PId' AND " .
	  "encounter='$Encounter' and  code='$Code' and modifier='$Modifier'");
	$Message='Delete';
	//------------------
    $_POST["mode"] = "searchdatabase";
   }
 }
//===============================================================================
//Modify Payment Code.
//===============================================================================
if (isset($_POST["mode"]))
 {
  if ($_POST["mode"] == "ModifyPayments" || $_POST["mode"] == "FinishPayments")
   {
	$payment_id=$_REQUEST['payment_id'];
	//ar_session Code
	//===============================================================================
	if(trim(formData('type_name'   ))=='insurance')
	 {
		$QueryPart="payer_id = '"       . trim(formData('hidden_type_code' )) .
		"', patient_id = '"   . 0 ;
	 }
	elseif(trim(formData('type_name'   ))=='patient')
	 {
		$QueryPart="payer_id = '"       . 0 .
		"', patient_id = '"   . trim(formData('hidden_type_code'   )) ;
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

	  sqlStatement("update ar_session set "    .
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
        "'	where session_id='$payment_id'");
//===============================================================================
	$CountIndexAbove=$_REQUEST['CountIndexAbove'];
	$CountIndexBelow=$_REQUEST['CountIndexBelow'];
	$hidden_patient_code=$_REQUEST['hidden_patient_code'];
	$user_id=$_SESSION['authUserID'];
	$created_time = date('Y-m-d H:i:s');
	//==================================================================
	//UPDATION
	//It is done with out deleting any old entries.
	//==================================================================
	for($CountRow=1;$CountRow<=$CountIndexAbove;$CountRow++)
	 {
	  if (isset($_POST["HiddenEncounter$CountRow"]))
	   {
		  if (isset($_POST["Payment$CountRow"]) && $_POST["Payment$CountRow"]*1>0)
		   {
				if(trim(formData('type_name'   ))=='insurance')
				 {
				  if(trim(formData("HiddenIns$CountRow"   ))==1)
				   {
					  $AccountCode="IPP";
				   }
				  if(trim(formData("HiddenIns$CountRow"   ))==2)
				   {
					  $AccountCode="ISP";
				   }
				  if(trim(formData("HiddenIns$CountRow"   ))==3)
				   {
					  $AccountCode="ITP";
				   }
				 }
				elseif(trim(formData('type_name'   ))=='patient')
				 {
				  $AccountCode="PP";
				 }
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount>0");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . trim(formData("Payment$CountRow"   ))  .
					"', account_code = '" . "$AccountCode"  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount>0");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow"   )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . trim(formData("Payment$CountRow"   ))  .
					"', adj_amount = '"    . 0 .
					"', account_code = '" . "$AccountCode"  .
					"'");
				 }
		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount>0");
		   }
//==============================================================================================================================
		  if (isset($_POST["AdjAmount$CountRow"]) && $_POST["AdjAmount$CountRow"]*1!=0)
		   {
				if(trim(formData('type_name'   ))=='insurance')
				 {
				  $AdjustString="Ins adjust Ins".trim(formData("HiddenIns$CountRow"   ));
				  $AccountCode="IA";
				 }
				elseif(trim(formData('type_name'   ))=='patient')
				 {
				  $AdjustString="Pt adjust";
				  $AccountCode="PA";
				 }
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and adj_amount!=0");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', adj_amount = '"    . trim(formData("AdjAmount$CountRow"   )) .
					"', memo = '" . "$AdjustString"  .
					"', account_code = '" . "$AccountCode"  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and adj_amount!=0");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . 0  .
					"', adj_amount = '"    . trim(formData("AdjAmount$CountRow"   )) .
					"', memo = '" . "$AdjustString"  .
					"', account_code = '" . "$AccountCode"  .
					"'");
				 }

		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and adj_amount!=0");
		   }
//==============================================================================================================================
		  if (isset($_POST["Deductible$CountRow"]) && $_POST["Deductible$CountRow"]*1>0)
		   {
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and memo like 'Deductable%'");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', memo = '"    . "Deductable $".trim(formData("Deductible$CountRow"   )) .
					"', account_code = '" . "Deduct"  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and memo like 'Deductable%'");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . 0  .
					"', adj_amount = '"    . 0 .
					"', memo = '"    . "Deductable $".trim(formData("Deductible$CountRow"   )) .
					"', account_code = '" . "Deduct"  .
					"'");
				 }
		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and memo like 'Deductable%'");
		   }
//==============================================================================================================================
		  if (isset($_POST["Takeback$CountRow"]) && $_POST["Takeback$CountRow"]*1>0)
		   {
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount < 0");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . trim(formData("Takeback$CountRow"   ))*-1  .
					"', account_code = '" . "Takeback"  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount < 0");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . trim(formData("Takeback$CountRow"   ))*-1  .
					"', adj_amount = '"    . 0 .
					"', account_code = '" . "Takeback"  .
					"'");
				 }
		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and pay_amount < 0");
		   }
//==============================================================================================================================
		  if (isset($_POST["FollowUp$CountRow"]) && $_POST["FollowUp$CountRow"]=='y')
		   {
				$resPayment = sqlStatement("SELECT  * from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and follow_up ='y'");
				if(sqlNumRows($resPayment)>0)
				 {
				  sqlStatement("update ar_activity set "    .
					"   post_user = '" . trim($user_id            )  .
					"', modified_time = '"  . trim($created_time					) .
					"', follow_up = '"    . "y" .
					"', follow_up_note = '"    . trim(formData("FollowUpReason$CountRow"   )) .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"' where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and follow_up ='y'");
				 }
				else
				 {
				  sqlStatement("insert into ar_activity set "    .
					"pid = '"       . trim(formData("HiddenPId$CountRow" )) .
					"', encounter = '"     . trim(formData("HiddenEncounter$CountRow"   ))  .
					"', code = '"      . trim(formData("HiddenCode$CountRow"   ))  .
					"', modifier = '"      . trim(formData("HiddenModifier$CountRow"   ))  .
					"', payer_type = '"   . trim(formData("HiddenIns$CountRow"   )) .
					"', post_time = '"  . trim($created_time					) .
					"', post_user = '" . trim($user_id            )  .
					"', session_id = '"    . trim(formData('payment_id')) .
					"', modified_time = '"  . trim($created_time					) .
					"', pay_amount = '" . 0  .
					"', adj_amount = '"    . 0 .
					"', follow_up = '"    . "y" .
					"', follow_up_note = '"    . trim(formData("FollowUpReason$CountRow"   )) .
					"'");
				 }
		   }
		  else
		   {
		    sqlStatement("delete from ar_activity " .
					" where  session_id ='$payment_id' and pid ='" . trim(formData("HiddenPId$CountRow"   ))  .
					"' and  encounter  ='" . trim(formData("HiddenEncounter$CountRow"   ))  .
					"' and  code  ='" . trim(formData("HiddenCode$CountRow"   ))  .
					"' and  modifier  ='" . trim(formData("HiddenModifier$CountRow"   ))  .
					"' and follow_up ='y'");
		   }
//==============================================================================================================================
	   }
	  else
	   break;
	 }
	//=========
	//INSERTION of new entries,continuation of modification.
	//=========
	for($CountRow=$CountIndexAbove+1;$CountRow<=$CountIndexAbove+$CountIndexBelow;$CountRow++)
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
	if($_POST["mode"]=="FinishPayments")
	 {
	  $Message='Finish';
	 }
    $_POST["mode"] = "searchdatabase";
	$Message='Modify';
   }
 }
//==============================================================================
//Search Code
//===============================================================================
$payment_id=$payment_id*1 > 0 ? $payment_id : $_REQUEST['payment_id'];
$ResultSearchSub = sqlStatement("SELECT distinct pid from ar_activity where  session_id ='$payment_id'");
//==============================================================================
$DateFormat=DateFormatRead();
//==============================================================================
//===============================================================================
?>

<html>
<head>
<?php if (function_exists('html_header_show')) html_header_show(); ?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->

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
<script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script language='JavaScript'>
 var mypcc = '1';
</script>
<?php include_once("{$GLOBALS['srcdir']}/payment_jav.inc.php"); ?>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script LANGUAGE="javascript" TYPE="text/javascript">
function ModifyPayments()
 {//Used while modifying the allocation
 	if(!FormValidations())//FormValidations contains the form checks
	 {
	  return false;
	 }
	if(CompletlyBlankAbove())//The distribution rows already in the database are checked.
	 {
	  alert("<?php echo htmlspecialchars( xl('None of the Top Distribution Row Can be Completly Blank.'), ENT_QUOTES);echo htmlspecialchars('\n');echo htmlspecialchars( xl('Use Delete Option to Remove.'), ENT_QUOTES) ?>")
	  return false;
	 }
 	if(!CheckPayingEntityAndDistributionPostFor())//Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
	 {
	  return false;
	 }
 	if(CompletlyBlankBelow())//The newly added distribution rows are checked.
	 {
	  alert("<?php echo htmlspecialchars( xl('Fill any of the Below Row.'), ENT_QUOTES) ?>")
	  return false;
	 }
	PostValue=CheckUnappliedAmount();//Decides TdUnappliedAmount >0, or <0 or =0
	if(PostValue==1)
	 {
	  alert("<?php echo htmlspecialchars( xl('Cannot Modify Payments.Undistributed is Negative.'), ENT_QUOTES) ?>")
	  return false;
	 }
	if(confirm("<?php echo htmlspecialchars( xl('Would you like to Modify Payments?'), ENT_QUOTES) ?>"))
	 {
		document.getElementById('mode').value='ModifyPayments';
		top.restoreSession();
		document.forms[0].submit();
	 }
	else
	 return false;
 }
function FinishPayments()
 {
 	if(!FormValidations())//FormValidations contains the form checks
	 {
	  return false;
	 }
 	if(CompletlyBlankAbove())//The distribution rows already in the database are checked.
	 {
	  alert("<?php echo htmlspecialchars( xl('None of the Top Distribution Row Can be Completly Blank.'), ENT_QUOTES);echo htmlspecialchars('\n');echo htmlspecialchars( xl('Use Delete Option to Remove.'), ENT_QUOTES) ?>")
	  return false;
	 }
 	if(!CheckPayingEntityAndDistributionPostFor())//Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
	 {
	  return false;
	 }
 	if(CompletlyBlankBelow())//The newly added distribution rows are checked.
	 {
	  alert("<?php echo htmlspecialchars( xl('Fill any of the Below Row.'), ENT_QUOTES) ?>")
	  return false;
	 }
 	PostValue=CheckUnappliedAmount();//Decides TdUnappliedAmount >0, or <0 or =0
	if(PostValue==1)
	 {
	  alert("<?php echo htmlspecialchars( xl('Cannot Modify Payments.UNDISTRIBUTED is Negative.'), ENT_QUOTES) ?>")
	  return false;
	 }
	if(PostValue==2)
	 {
		if(confirm("<?php echo htmlspecialchars( xl('Would you like to Modify and Finish Payments?'), ENT_QUOTES) ?>"))
		 {
			UnappliedAmount=document.getElementById('TdUnappliedAmount').innerHTML*1;
			if(confirm("<?php echo htmlspecialchars( xl('UNDISTRIBUTED is'), ENT_QUOTES) ?>" + ' ' + UnappliedAmount + '.' + "<?php echo htmlspecialchars('\n');echo htmlspecialchars( xl('Would you like the balance amount to apply to Global Account?'), ENT_QUOTES) ?>"))
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
		if(confirm("<?php echo htmlspecialchars( xl('Would you like to Modify and Finish Payments?'), ENT_QUOTES) ?>"))
		 {
			document.getElementById('mode').value='FinishPayments';
			top.restoreSession();
			document.forms[0].submit();
		 }
		else
		 return false;
	 }

 }
function CompletlyBlankAbove()
 {//The distribution rows already in the database are checked.
 //It is not allowed to be made completly empty.If needed delete option need to be used.
  CountIndexAbove=document.getElementById('CountIndexAbove').value*1;
  for(RowCount=1;RowCount<=CountIndexAbove;RowCount++)
   {
   if(document.getElementById('Allowed'+RowCount).value=='' && document.getElementById('Payment'+RowCount).value=='' && document.getElementById('AdjAmount'+RowCount).value=='' && document.getElementById('Deductible'+RowCount).value=='' && document.getElementById('Takeback'+RowCount).value=='' && document.getElementById('FollowUp'+RowCount).checked==false)
	{
	 return true;
	}
   }
  return false;
 }
function CompletlyBlankBelow()
 {//The newly added distribution rows are checked.
 //It is not allowed to be made completly empty.
  CountIndexAbove=document.getElementById('CountIndexAbove').value*1;
  CountIndexBelow=document.getElementById('CountIndexBelow').value*1;
  if(CountIndexBelow==0)
   return false;
  for(RowCount=CountIndexAbove+1;RowCount<=CountIndexAbove+CountIndexBelow;RowCount++)
   {
   if(document.getElementById('Allowed'+RowCount).value=='' && document.getElementById('Payment'+RowCount).value=='' && document.getElementById('AdjAmount'+RowCount).value=='' && document.getElementById('Deductible'+RowCount).value=='' && document.getElementById('Takeback'+RowCount).value=='' && document.getElementById('FollowUp'+RowCount).checked==false)
	{

	}
	else
	 return false;
   }
  return true;
 }
function OnloadAction()
 {//Displays message while loading after some action.
  after_value=document.getElementById('ActionStatus').value;
  if(after_value=='Delete')
   {
    alert("<?php echo htmlspecialchars( xl('Successfully Deleted'), ENT_QUOTES) ?>")
	return true;
   }
  if(after_value=='Modify' || after_value=='Finish')
   {
    alert("<?php echo htmlspecialchars( xl('Successfully Modified'), ENT_QUOTES) ?>")
	return true;
   }
  after_value=document.getElementById('after_value').value;
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
	if(confirm("<?php echo htmlspecialchars( xl('Successfully Saved.Would you like to Distribute?'), ENT_QUOTES) ?>"))
	 {
		if(document.getElementById('TablePatientPortion'))
		 {
			document.getElementById('TablePatientPortion').style.display='';
		 }
	 }
   }

 }
function DeletePaymentDistribution(DeleteId)
 {//Confirms deletion of payment distribution.
	if(confirm("<?php echo htmlspecialchars( xl('Would you like to Delete Payment Distribution?'), ENT_QUOTES) ?>"))
	 {
		document.getElementById('mode').value='DeletePaymentDistribution';
		document.getElementById('DeletePaymentDistributionId').value=DeleteId;
		top.restoreSession();
		document.forms[0].submit();
	 }
	else
	 return false;
 }
</script>
<script language="javascript" type="text/javascript">
document.onclick=HideTheAjaxDivs;
</script>
<style>
.class1{width:125px;}
.class2{width:250px;}
.class3{width:100px;}
.bottom{border-bottom:1px solid black;}
.top{border-top:1px solid black;}
.left{border-left:1px solid black;}
.right{border-right:1px solid black;}
#ajax_div_insurance {
	position: absolute;
	z-index:10;
	/*
	left: 20px;
	top: 300px;
	*/
	background-color: #FBFDD0;
	border: 1px solid #ccc;
	padding: 10px;
}
#ajax_div_patient {
	position: absolute;
	z-index:10;
	/*
	left: 20px;
	top: 300px;
	*/
	background-color: #FBFDD0;
	border: 1px solid #ccc;
	padding: 10px;
}
</style>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
</head>
<body class="body_top" onLoad="OnloadAction()"  >
<form name='new_payment' method='post'  action="edit_payment.php" onsubmit='
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
<table width="1024" border="0"  cellspacing="0" cellpadding="0">
<?php
  if($_REQUEST['ParentPage']=='new_payment')
  {
  ?>
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
<?php
  }
 else
  {
  ?>
  <tr height="5">
    <td colspan="3" align="left" ></td>
  </tr>
<?php
  }
  ?>
  <tr>
    <td colspan="3" align="left" >

<?php
 if($payment_id*1>0)
  {
  ?>
    <?php 
	require_once("payment_master.inc.php");  //Check/cash details are entered here.
	?>
<?php
}
?>
	</td>
  </tr>
</table>



<?php
 if($payment_id*1>0)
  {//Distribution rows already in the database are displayed.
  ?>

<table width="1024" border="0" cellspacing="0" cellpadding="10" bgcolor="#DEDEDE"><tr><td>
	<table width="1004" border="0" cellspacing="0" cellpadding="0">

		  <tr>
			<td colspan="13" align="left" >

				<?php //
				$resCount = sqlStatement("SELECT distinct encounter,code,modifier from ar_activity where  session_id ='$payment_id' ");
				$TotalRows=sqlNumRows($resCount);
				$CountPatient=0;
				$CountIndex=0;
				$CountIndexAbove=0;
				if($RowSearchSub = sqlFetchArray($ResultSearchSub))
				 {
					do 
					 {
						$CountPatient++;
						$PId=$RowSearchSub['pid'];
					 	$res = sqlStatement("SELECT fname,lname,mname FROM patient_data	where pid ='$PId'");
						$row = sqlFetchArray($res);
						$fname=$row['fname'];
						$lname=$row['lname'];
						$mname=$row['mname'];
						$NameDB=$lname.' '.$fname.' '.$mname;
						$ResultSearch = sqlStatement("SELECT billing.id,last_level_closed,billing.encounter,form_encounter.`date`,billing.code,billing.modifier,fee
						 FROM billing ,form_encounter
						 where billing.encounter=form_encounter.encounter and code_type!='ICD9' and  code_type!='COPAY' and billing.activity!=0 and 
						 form_encounter.pid ='$PId' and billing.pid ='$PId' and billing.encounter in (SELECT distinct encounter from ar_activity 
						 where  session_id ='$payment_id' )
						  and billing.code in (SELECT distinct code from ar_activity where  session_id ='$payment_id' )
						   and billing.modifier in (SELECT distinct modifier from ar_activity where  session_id ='$payment_id' )
						 ORDER BY form_encounter.`date`,form_encounter.encounter,billing.code,billing.modifier");
						if(sqlNumRows($ResultSearch)>0)
						 {
						if($CountPatient==1)
						 {
						 $Table='yes';
						?>
						<table width="1004"  border="0" cellpadding="0" cellspacing="0" align="center" id="TableDistributePortion">
						  <tr class="text" bgcolor="#dddddd">
						    <td width="25" class="left top" >&nbsp;</td>
						    <td width="144" class="left top" ><?php echo htmlspecialchars( xl('Patient Name'), ENT_QUOTES) ?></td>
							<td width="55" class="left top" ><?php echo htmlspecialchars( xl('Post For'), ENT_QUOTES) ?></td>
							<td width="70" class="left top" ><?php echo htmlspecialchars( xl('Srv Date'), ENT_QUOTES) ?></td>
							<td width="50" class="left top" ><?php echo htmlspecialchars( xl('Encnter'), ENT_QUOTES) ?></td>
							<td width="65" class="left top" ><?php echo htmlspecialchars( xl('CPT Code'), ENT_QUOTES) ?></td>
							<td width="50" class="left top" ><?php echo htmlspecialchars( xl('Charge'), ENT_QUOTES) ?></td>
							<td width="40" class="left top" ><?php echo htmlspecialchars( xl('Copay'), ENT_QUOTES) ?></td>
							<td width="40" class="left top" ><?php echo htmlspecialchars( xl('Remdr'), ENT_QUOTES) ?></td>
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Allowed'), ENT_QUOTES) ?></td>
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Payment'), ENT_QUOTES) ?></td>
							<td width="70" class="left top" ><?php echo htmlspecialchars( xl('Adj Amount'), ENT_QUOTES) ?></td>
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Deductible'), ENT_QUOTES) ?></td>
							<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Takeback'), ENT_QUOTES) ?></td>
							<td width="40" class="left top" ><?php echo htmlspecialchars( xl('Resn'), ENT_QUOTES) ?></td>
							<td width="110" class="left top right" ><?php echo htmlspecialchars( xl('Follow Up Reason'), ENT_QUOTES) ?></td>
						  </tr>
						  <?php
						  }
							while ($RowSearch = sqlFetchArray($ResultSearch))
							 {
								$CountIndex++;
								$CountIndexAbove++;
								$ServiceDateArray=split(' ',$RowSearch['date']);
								$ServiceDate=oeFormatShortDate($ServiceDateArray[0]);
								$Code=$RowSearch['code'];
								$Modifier =$RowSearch['modifier'];
								if($Modifier!='')
								 $ModifierString=", $Modifier";
								else
								 $ModifierString="";
								$Fee=$RowSearch['fee'];
								$Encounter=$RowSearch['encounter'];
								
								$resPayer = sqlStatement("SELECT  payer_type from ar_activity where  session_id ='$payment_id' and
								pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier' ");
								$rowPayer = sqlFetchArray($resPayer);
								$Ins=$rowPayer['payer_type'];
					
								//Always associating the copay to a particular charge.
								$BillingId=$RowSearch['id'];
								$resId = sqlStatement("SELECT id  FROM billing where code_type!='ICD9' and  code_type!='COPAY'  and
								pid ='$PId' and  encounter  ='$Encounter' and billing.activity!=0 order by id");
								$rowId = sqlFetchArray($resId);
								$Id=$rowId['id'];
			
								if($BillingId!=$Id)//multiple cpt in single encounter
								 {
									$Copay=0.00;
								 }
								else
								 {
									$resCopay = sqlStatement("SELECT sum(fee) as copay FROM billing where
									code_type='COPAY' and  pid ='$PId' and  encounter  ='$Encounter' and billing.activity!=0");
									$rowCopay = sqlFetchArray($resCopay);
									$Copay=$rowCopay['copay']*-1;

									$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where
									pid ='$PId'  and  encounter  ='$Encounter' and  payer_type=0 and 
									(code='CO-PAY' or account_code='PCP')");//new fees screen copay gives account_code='PCP'
									//openemr payment screen copay gives code='CO-PAY'
									$rowMoneyGot = sqlFetchArray($resMoneyGot);
									$PatientPay=$rowMoneyGot['PatientPay'];
									
									$Copay=$Copay+$PatientPay;
								 }

									//For calculating Remainder
									if($Ins==0)
									 {//Fetch all values
										$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where
										pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and  !(payer_type=0 and 
										(code='CO-PAY' or account_code='PCP'))");
										//new fees screen copay gives account_code='PCP'
										//openemr payment screen copay gives code='CO-PAY'
										$rowMoneyGot = sqlFetchArray($resMoneyGot);
										$MoneyGot=$rowMoneyGot['MoneyGot'];
	
										$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where
										pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter'");
										$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
										$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];
									 }
									else//Fetch till that much got
									 {
										//Fetch the HIGHEST sequence_no till this session.
										//Used maily in  the case if primary/others pays once more.
										$resSequence = sqlStatement("SELECT  sequence_no from ar_activity where  session_id ='$payment_id' and
										pid ='$PId' and  encounter  ='$Encounter' order by sequence_no desc ");
										$rowSequence = sqlFetchArray($resSequence);
										$Sequence=$rowSequence['sequence_no'];

										$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where
										pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and  
										payer_type > 0 and payer_type <='$Ins' and sequence_no<='$Sequence'");
										$rowMoneyGot = sqlFetchArray($resMoneyGot);
										$MoneyGot=$rowMoneyGot['MoneyGot'];
	
										$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where
										pid ='$PId' and  code='$Code' and modifier='$Modifier'   and  encounter  ='$Encounter' and  
										payer_type > 0 and payer_type <='$Ins' and sequence_no<='$Sequence'");
										$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
										$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];
									 }
									$Remainder=$Fee-$Copay-$MoneyGot-$MoneyAdjusted;
									
									//For calculating RemainderJS.Used while restoring back the values.
									if($Ins==0)
									 {//Got just before Patient
										$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where
										pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and  payer_type !=0");
										$rowMoneyGot = sqlFetchArray($resMoneyGot);
										$MoneyGot=$rowMoneyGot['MoneyGot'];
	
										$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where
										pid ='$PId' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and payer_type !=0");
										$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
										$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];
									 }
									else
									 {//Got just before the previous
										//Fetch the LOWEST sequence_no till this session.
										//Used maily in  the case if primary/others pays once more.
										$resSequence = sqlStatement("SELECT  sequence_no from ar_activity where  session_id ='$payment_id' and
										pid ='$PId' and  encounter  ='$Encounter' order by sequence_no  ");
										$rowSequence = sqlFetchArray($resSequence);
										$Sequence=$rowSequence['sequence_no'];

										$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where
										pid ='$PId' and  code='$Code' and modifier='$Modifier'   and  encounter  ='$Encounter' 
										and payer_type > 0  and payer_type <='$Ins' and sequence_no<'$Sequence'");
										$rowMoneyGot = sqlFetchArray($resMoneyGot);
										$MoneyGot=$rowMoneyGot['MoneyGot'];
	
										$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where
										pid ='$PId' and  code='$Code' and modifier='$Modifier'   and  encounter  ='$Encounter' 
										and payer_type <='$Ins' and sequence_no<'$Sequence' ");
										$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
										$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];
									 }
									//Stored in hidden so that can be used while restoring back the values.
									$RemainderJS=$Fee-$Copay-$MoneyGot-$MoneyAdjusted;

									$resPayment = sqlStatement("SELECT  pay_amount from ar_activity where  session_id ='$payment_id' and
									pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and pay_amount>0");
									$rowPayment = sqlFetchArray($resPayment);
									$PaymentDB=$rowPayment['pay_amount']*1;
									$PaymentDB=$PaymentDB == 0 ? '' : $PaymentDB;

									$resPayment = sqlStatement("SELECT  pay_amount from ar_activity where  session_id ='$payment_id' and
									pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and pay_amount<0");
									$rowPayment = sqlFetchArray($resPayment);
									$TakebackDB=$rowPayment['pay_amount']*-1;
									$TakebackDB=$TakebackDB == 0 ? '' : $TakebackDB;

									$resPayment = sqlStatement("SELECT  adj_amount from ar_activity where  session_id ='$payment_id' and
									pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and adj_amount!=0");
									$rowPayment = sqlFetchArray($resPayment);
									$AdjAmountDB=$rowPayment['adj_amount']*1;
									$AdjAmountDB=$AdjAmountDB == 0 ? '' : $AdjAmountDB;

									$resPayment = sqlStatement("SELECT  memo from ar_activity where  session_id ='$payment_id' and
									pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and memo like 'Deductable%'");
									$rowPayment = sqlFetchArray($resPayment);
									$DeductibleDB=$rowPayment['memo'];
									$DeductibleDB=str_replace('Deductable $','',$DeductibleDB);

									$resPayment = sqlStatement("SELECT  follow_up,follow_up_note from ar_activity where  session_id ='$payment_id' and
									pid ='$PId' and  encounter  ='$Encounter' and  code='$Code' and modifier='$Modifier'  and follow_up = 'y'");
									$rowPayment = sqlFetchArray($resPayment);
									$FollowUpDB=$rowPayment['follow_up'];
									$FollowUpReasonDB=$rowPayment['follow_up_note'];

								if($CountIndex==$TotalRows)
								 {
									$StringClass=' bottom left top ';
								 }
								else
								 {
									$StringClass=' left top ';
								 }

								if($Ins==1)
								 {
									$bgcolor='#ddddff';
								 }
								elseif($Ins==2)
								 {
									$bgcolor='#ffdddd';
								 }
								elseif($Ins==3)
								 {
									$bgcolor='#F2F1BC';
								 }
								elseif($Ins==0)
								 {
									$bgcolor='#AAFFFF';
								 }
						  ?>
						  <tr class="text"  bgcolor='<?php echo $bgcolor; ?>' id="trCharges<?php echo $CountIndex; ?>">
						    <td align="left" class="<?php echo $StringClass; ?>" ><a href="#" onClick="javascript:return DeletePaymentDistribution('<?php echo  htmlspecialchars($payment_id.'_'.$PId.'_'.$Encounter.'_'.$Code.'_'.$Modifier); ?>');" ><img src="../pic/Delete.gif" border="0"/></a></td>
						    <td align="left" class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars($NameDB); ?><input name="HiddenPId<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($PId); ?>" type="hidden"/></td>
							<td align="left" class="<?php echo $StringClass; ?>" ><input name="HiddenIns<?php echo $CountIndex; ?>" id="HiddenIns<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Ins); ?>" type="hidden"/><?php echo generate_select_list("payment_ins$CountIndex", "payment_ins", "$Ins", "Insurance/Patient",'','','ActionOnInsPat("'.$CountIndex.'")'); ?></td>
							<td class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars($ServiceDate); ?></td>
							<td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenEncounter<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Encounter); ?>" type="hidden"/><?php echo htmlspecialchars($Encounter); ?></td>
							<td class="<?php echo $StringClass; ?>" ><input name="HiddenCode<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Code); ?>" type="hidden"/><?php echo htmlspecialchars($Code.$ModifierString); ?><input name="HiddenModifier<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Modifier); ?>" type="hidden"/></td>
							<td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenChargeAmount<?php echo $CountIndex; ?>" id="HiddenChargeAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Fee); ?>" type="hidden"/><?php echo htmlspecialchars($Fee); ?></td>
							<td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenCopayAmount<?php echo $CountIndex; ?>" id="HiddenCopayAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Copay); ?>" type="hidden"/><?php echo htmlspecialchars(number_format($Copay,2)); ?></td>
							<td align="right"   id="RemainderTd<?php echo $CountIndex; ?>"  class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars(round($Remainder,2)); ?></td>
							<input name="HiddenRemainderTd<?php echo $CountIndex; ?>" id="HiddenRemainderTd<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars(round($RemainderJS,2)); ?>" type="hidden"/>
							<td class="<?php echo $StringClass; ?>" ><input  name="Allowed<?php echo $CountIndex; ?>" id="Allowed<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"  autocomplete="off"  value=""  onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);RestoreValues(<?php echo $CountIndex; ?>)"   type="text"   style="width:60px;text-align:right; font-size:12px" disabled /></td>
							<td class="<?php echo $StringClass; ?>" ><input   type="text"  name="Payment<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"  id="Payment<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($PaymentDB); ?>"  onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);RestoreValues(<?php echo $CountIndex; ?>)"  style="width:60px;text-align:right; font-size:12px" /></td>
							<td class="<?php echo $StringClass; ?>" ><input  name="AdjAmount<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"  id="AdjAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($AdjAmountDB); ?>"   onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);RestoreValues(<?php echo $CountIndex; ?>)"  type="text"   style="width:70px;text-align:right; font-size:12px" /></td>
							<td class="<?php echo $StringClass; ?>" ><input  name="Deductible<?php echo $CountIndex; ?>"  id="Deductible<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);"  value="<?php echo htmlspecialchars($DeductibleDB); ?>"   autocomplete="off"   type="text"   style="width:60px;text-align:right; font-size:12px" /></td>
							<td class="<?php echo $StringClass; ?>" ><input  name="Takeback<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"   id="Takeback<?php echo $CountIndex; ?>"   value="<?php echo htmlspecialchars($TakebackDB); ?>"   onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);RestoreValues(<?php echo $CountIndex; ?>)"   type="text"   style="width:60px;text-align:right; font-size:12px" /></td>
							<td align="center" class="<?php echo $StringClass; ?>" ><input type="checkbox" id="FollowUp<?php echo $CountIndex; ?>"  name="FollowUp<?php echo $CountIndex; ?>" value="y" onClick="ActionFollowUp(<?php echo $CountIndex; ?>)" <?php echo $FollowUpDB=='y' ? ' checked ' : ''; ?> /></td>
							<td class="<?php echo $StringClass; ?> right" ><input  onKeyDown="PreventIt(event)" id="FollowUpReason<?php echo $CountIndex; ?>"    name="FollowUpReason<?php echo $CountIndex; ?>"  <?php echo $FollowUpDB=='y' ? '' : ' readonly '; ?>  type="text"  value="<?php echo htmlspecialchars($FollowUpReasonDB); ?>"    style="width:110px;font-size:12px" /></td>
						  </tr>
						<?php
								
								
							 }//while ($RowSearch = sqlFetchArray($ResultSearch))
						?>
						<?php
						 }//if(sqlNumRows($ResultSearch)>0)

						 }while ($RowSearchSub = sqlFetchArray($ResultSearchSub));
						if($Table=='yes')
						 {
						?>
						</table>
						<?php
						}
						echo '<br/>';

				}//if($RowSearchSub = sqlFetchArray($ResultSearchSub))
				?>		    </td>
		  </tr>
		  <tr>
		    <td colspan="13" align="left" >
				<?php 
				require_once("payment_pat_sel.inc.php"); //Patient ajax section and listing of charges.
				?>
			</td>
	      </tr>
		  <tr>
			<td colspan="13" align="left" >
				<table border="0" cellspacing="0" cellpadding="0" width="217" align="center">
				  <tr height="5">
					<td ></td>
					<td ></td>
					<td></td>
				  </tr>
				  <tr>
					<td width="110"><a href="#" onClick="javascript:return ModifyPayments();"  class="css_button"><span><?php echo htmlspecialchars( xl('Modify Payments'), ENT_QUOTES);?></span></a>
					</td>
					<td width="107"><a href="#" onClick="javascript:return FinishPayments();"  class="css_button"><span><?php echo htmlspecialchars( xl('Finish Payments'), ENT_QUOTES);?></span></a>
					</td>
				  </tr>
				</table>

		<?php
		 }//if($payment_id*1>0)
		?>		</td>
	  </tr>
	</table>
	</td></tr></table>

<input type="hidden" name="hidden_patient_code" id="hidden_patient_code" value="<?php echo htmlspecialchars($hidden_patient_code);?>"/>
<input type='hidden' name='mode' id='mode' value='' />
<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
<input type="hidden" name="after_value" id="after_value" value="<?php echo htmlspecialchars($_POST["mode"]);?>"/>
<input type="hidden" name="payment_id" id="payment_id" value="<?php echo htmlspecialchars($payment_id);?>"/>
<input type="hidden" name="hidden_type_code" id="hidden_type_code" value="<?php echo htmlspecialchars($TypeCode);?>"/>
<input type='hidden' name='global_amount' id='global_amount' value='' />
<input type='hidden' name='DeletePaymentDistributionId' id='DeletePaymentDistributionId' value='' />
<input type="hidden" name="ActionStatus" id="ActionStatus" value="<?php echo htmlspecialchars($Message);?>"/>
<input type='hidden' name='CountIndexAbove' id='CountIndexAbove' value='<?php echo htmlspecialchars($CountIndexAbove*1);?>' />
<input type='hidden' name='CountIndexBelow' id='CountIndexBelow' value='<?php echo htmlspecialchars($CountIndexBelow*1);?>' />
<input type="hidden" name="ParentPage" id="ParentPage" value="<?php echo htmlspecialchars($_REQUEST['ParentPage']);?>"/>
</form>

</body>
</html>
