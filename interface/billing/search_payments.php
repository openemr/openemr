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
require_once("$srcdir/options.inc.php");
require_once("$srcdir/payment.inc.php");

use OpenEMR\Core\Header;

//===============================================================================
//Deletion of payment and its corresponding distributions.
//===============================================================================
set_time_limit(0);
if (isset($_POST["mode"])) {
    if ($_POST["mode"] == "DeletePayments") {
        $DeletePaymentId=trim(formData('DeletePaymentId'));
        $ResultSearch = sqlStatement("SELECT distinct encounter,pid from ar_activity where  session_id ='$DeletePaymentId'");
        if (sqlNumRows($ResultSearch)>0) {
            while ($RowSearch = sqlFetchArray($ResultSearch)) {
                $Encounter=$RowSearch['encounter'];
                $PId=$RowSearch['pid'];
                sqlStatement("update form_encounter set last_level_closed=last_level_closed - 1 where pid ='$PId' and encounter='$Encounter'");
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
    if ($_POST["mode"] == "SearchPayment") {
        $FromDate=trim(formData('FromDate'));
        $ToDate=trim(formData('ToDate'));
        $PaymentMethod=trim(formData('payment_method'));
        $CheckNumber=trim(formData('check_number'));
        $PaymentAmount=trim(formData('payment_amount'));
        $PayingEntity=trim(formData('type_name'));
        $PaymentCategory=trim(formData('adjustment_code'));
        $PaymentFrom=trim(formData('hidden_type_code'));
        $PaymentStatus=trim(formData('PaymentStatus'));
        $PaymentSortBy=trim(formData('PaymentSortBy'));
        $PaymentDate=trim(formData('payment_date'));
        $QueryString.="Select * from  ar_session where  ";
        $And='';

        if ($PaymentDate=='date_val') {
            $PaymentDateString=' check_date ';
        } elseif ($PaymentDate=='post_to_date') {
            $PaymentDateString=' post_to_date ';
        } elseif ($PaymentDate=='deposit_date') {
            $PaymentDateString=' deposit_date ';
        }

        if ($FromDate!='') {
             $QueryString.=" $And $PaymentDateString >='".DateToYYYYMMDD($FromDate)."'";
             $And=' and ';
        }

        if ($ToDate!='') {
             $QueryString.=" $And $PaymentDateString <='".DateToYYYYMMDD($ToDate)."'";
             $And=' and ';
        }

        if ($PaymentMethod!='') {
             $QueryString.=" $And payment_method ='".$PaymentMethod."'";
             $And=' and ';
        }

        if ($CheckNumber!='') {
             $QueryString.=" $And reference like '%".$CheckNumber."%'";
             $And=' and ';
        }

        if ($PaymentAmount!='') {
             $QueryString.=" $And pay_total ='".$PaymentAmount."'";
             $And=' and ';
        }

        if ($PayingEntity!='') {
            if ($PayingEntity=='insurance') {
                $QueryString.=" $And payer_id !='0'";
            }

            if ($PayingEntity=='patient') {
                $QueryString.=" $And payer_id ='0'";
            }

             $And=' and ';
        }

        if ($PaymentCategory!='') {
             $QueryString.=" $And adjustment_code ='".$PaymentCategory."'";
             $And=' and ';
        }

        if ($PaymentFrom!='') {
            if ($PayingEntity=='insurance' || $PayingEntity=='') {
               //-------------------
                $res = sqlStatement("SELECT insurance_companies.name FROM insurance_companies
					where insurance_companies.id ='$PaymentFrom'");
                $row = sqlFetchArray($res);
                $div_after_save=$row['name'];
               //-------------------

                $QueryString.=" $And payer_id ='".$PaymentFrom."'";
            }

            if ($PayingEntity=='patient') {
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

        if ($PaymentStatus!='') {
            $QsString="select ar_session.session_id,pay_total,global_amount,sum(pay_amount) sum_pay_amount from ar_session,ar_activity
				where ar_session.session_id=ar_activity.session_id group by ar_activity.session_id,ar_session.session_id
				having pay_total-global_amount-sum_pay_amount=0 or pay_total=0";
            $rs= sqlStatement("$QsString");
            while ($rowrs=sqlFetchArray($rs)) {
                $StringSessionId.=$rowrs['session_id'].',';
            }

            $QsString="select ar_session.session_id from ar_session	where  pay_total=0";
            $rs= sqlStatement("$QsString");
            while ($rowrs=sqlFetchArray($rs)) {
                $StringSessionId.=$rowrs['session_id'].',';
            }

             $StringSessionId=substr($StringSessionId, 0, -1);
            if ($PaymentStatus=='fully_paid') {
                $QueryString.=" $And session_id in($StringSessionId) ";
            } elseif ($PaymentStatus=='unapplied') {
                $QueryString.=" $And session_id not in($StringSessionId) ";
            }

             $And=' and ';
        }

        if ($PaymentSortBy!='') {
             $SortFieldOld=trim(formData('SortFieldOld'));
             $Sort=trim(formData('Sort'));
            if ($SortFieldOld==$PaymentSortBy) {
                if ($Sort=='DESC' || $Sort=='') {
                    $Sort='ASC';
                } else {
                    $Sort='DESC';
                }
            } else {
                $Sort='ASC';
            }

            $QueryString.=" order by $PaymentSortBy $Sort";
        }

         $ResultSearch = sqlStatement($QueryString);
    }
}

//===============================================================================
?>
<!DOCTYPE html>
<html>
<head>

    <?php Header::setupHeader(['jquery-ui', 'datetime-picker']); ?>

    <?php include_once("{$GLOBALS['srcdir']}/payment_jav.inc.php"); ?>
    <?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>

<script type='text/javascript'>

$(document).ready(function() {
    $(".medium_modal").on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dlgopen('', '', 1050, 350, '', '', {
            buttons: [
                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            onClosed: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>
<script language='JavaScript'>
 var mypcc = '1';
</script>
<script language='JavaScript'>
 function SearchPayment()
  {//Search  validations.
    if(document.getElementById('FromDate').value=='' && document.getElementById('ToDate').value=='' && document.getElementById('PaymentStatus').selectedIndex==0 && document.getElementById('payment_method').selectedIndex==0 && document.getElementById('type_name').selectedIndex==0 && document.getElementById('adjustment_code').selectedIndex==0 && document.getElementById('check_number').value==''  && document.getElementById('payment_amount').value==''  && document.getElementById('hidden_type_code').value=='' )
     {
        alert("<?php echo htmlspecialchars(xl('Please select any Search Option.'), ENT_QUOTES) ?>");
        return false;
     }
    if(document.getElementById('FromDate').value!='' && document.getElementById('ToDate').value!='')
     {
        if(!DateCheckGreater(document.getElementById('FromDate').value,document.getElementById('ToDate').value,'<?php echo DateFormatRead();?>'))
         {
            alert("<?php echo htmlspecialchars(xl('From Date Cannot be Greater than To Date.'), ENT_QUOTES) ?>");
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
    if(confirm("<?php echo htmlspecialchars(xl('Would you like to Delete Payments?'), ENT_QUOTES) ?>"))
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
    alert("<?php echo htmlspecialchars(xl('Successfully Deleted'), ENT_QUOTES) ?>")
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
.bottom {
    border-bottom: 1px solid black;
}
.top {
    border-top: 1px solid black;
}
.left {
    border-left: 1px solid black;
}
.right {
    border-right: 1px solid black;
}
.form-group {
    margin-bottom: 5px;
}
@media only screen and (max-width: 768px) {
    [class*="col-"] {
        width: 100%;
        text-align: left!Important;
    }
    .navbar-toggle>span.icon-bar {
        background-color: #68171A ! Important;
    }
    .navbar-default .navbar-toggle {
        border-color: #4a4a4a;
    }
    .navbar-default .navbar-toggle:focus, .navbar-default .navbar-toggle:hover {
        background-color: #f2f2f2 !Important;
        font-weight: 900 !Important;
        color: #000000 !Important;
    }
    .navbar-color {
        background-color: #E5E5E5;
    }
    .icon-bar {
        background-color: #68171A;
    }
    .navbar-header {
        float: none;
    }
    .navbar-toggle {
        display: block;
        background-color: #f2f2f2;
    }
    .navbar-nav {
        float: none!important;
    }
    .navbar-nav>li {
        float: none;
    }
    .navbar-collapse.collapse.in {
        z-index: 100;
        background-color: #dfdfdf;
        font-weight: 700;
        color: #000000 !Important;
    }
}
.table {
    margin: auto;
    width: 90% !important;
}
@media (min-width: 992px) {
    .modal-lg {
        width: 1000px !Important;
    }
}
.oe-modal-dialog {
    width: 65% !Important;
}
.oe-modal-content {
    padding: 20px 0px 20px 0px;
}
.modalclass {
    overflow-x: hidden !Important;
}
</style>
</head>
<body class="body_top" onload="OnloadAction()">
    <div class="container">
        <div class="row">
            <div class="page-header">
                <h2><?php echo xlt('Payments'); ?></h2>
            </div>
        </div>
        <div class="row">
            <nav class="navbar navbar-default navbar-color navbar-static-top" >
                <div class="container">
                    <div class="navbar-header">
                        <button class="navbar-toggle" data-target="#myNavbar" data-toggle="collapse" type="button"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
                    </div>
                    <div class="collapse navbar-collapse" id="myNavbar" >
                        <ul class="nav navbar-nav" >
                            <li class="oe-bold-black">
                                <a href='new_payment.php' style="font-weight:700; color:#000000"><?php echo xlt('New Payment'); ?></a>
                            </li>
                            <li class="active oe-bold-black" >
                                <a href='search_payments.php' style="font-weight:700; color:#000000"><?php echo xlt('Search Payment'); ?></a>
                            </li>
                            <li class="oe-bold-black">
                                <a href='era_payments.php' style="font-weight:700; color:#000000"><?php echo xlt('ERA Posting'); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
        <div class="row">
            <form id="new_payment" method='post' name='new_payment' style="display:inline">
                <fieldset>
                    <div class="col-xs-12 h3">
                    <?php echo xlt('Payment List'); ?>
                    </div>
                    <div class="col-xs-12 oe-custom-line">
                        <div class="forms col-xs-2">
                            <label class="control-label" for="payment_date"><?php echo xlt('Payment date'); ?>:</label>
                            <?php echo generate_select_list("payment_date", "payment_date", "$PaymentDate", "Payment Date", "", "");?>
                        </div>
                        <div class="forms col-xs-2">
                            <label class="control-label" for="FromDate"><?php echo xlt('From'); ?>:</label>
                            <input class="form-control datepicker" id='FromDate' name='FromDate'  type='text' value='<?php echo attr($FromDate); ?>'>
                        </div>
                        <div class="forms col-xs-2">
                            <label class="control-label" for="ToDate"><?php echo xlt('To'); ?>:</label>
                            <input class="form-control datepicker" id='ToDate' name='ToDate' type='text' value='<?php echo attr($ToDate); ?>'>
                        </div>
                        <div class="forms col-xs-3">
                            <label class="control-label" for="payment_method"><?php echo xlt('Payment Method'); ?>:</label>
                            <?php  echo generate_select_list("payment_method", "payment_method", "$PaymentMethod", "Payment Method", " ", "");?>
                        </div>
                        <div class="forms col-xs-3">
                            <label class="control-label" for="check_number"><?php echo xlt('Check Number'); ?>:</label>
                            <input autocomplete="off" class="form-control" id="check_number" name="check_number" type="text" value="<?php echo htmlspecialchars(formData('check_number'));?>">
                        </div>
                    </div>
                    <div class="col-xs-12 oe-custom-line">
                        <div class="forms col-xs-4">
                            <label class="control-label" for="payment_method"><?php echo xlt('Payment Amount'); ?>:</label>
                            <input autocomplete="off" class="form-control" id="payment_amount" name="payment_amount" onkeyup="ValidateNumeric(this);"  type="text" value="<?php echo htmlspecialchars(formData('payment_amount'));?>">
                        </div>
                        <div class="forms col-xs-2">
                            <label class="control-label" for="type_name"><?php echo xlt('Paying Entity'); ?>:</label>
                            <?php  echo generate_select_list("type_name", "payment_type", "$type_name", "Paying Entity", " ", "", "SearchPayingEntityAction()");?>
                        </div>
                        <div class="forms col-xs-3">
                            <label class="control-label" for="adjustment_code"><?php echo xlt('Payment Category'); ?>:</label>
                            <?php  echo generate_select_list("adjustment_code", "payment_adjustment_code", "$adjustment_code", "Paying Category", " ", "");?>
                        </div>
                        <div class="forms col-xs-3">
                            <label class="control-label" for="PaymentStatus"><?php echo xlt('Pay Status'); ?>:</label>
                            <?php echo generate_select_list("PaymentStatus", "payment_status", "$PaymentStatus", "Pay Status", " ", "");?>
                        </div>
                    </div>
                    <div class="col-xs-12 oe-custom-line">
                        <div class="forms col-xs-4">
                            <label class="control-label" for="type_code"><?php echo xlt('Payment From'); ?>:</label>
                            <input id="hidden_ajax_close_value" type="hidden" value="<?php echo htmlspecialchars($div_after_save);?>">
                            <input autocomplete="off" class="form-control" id='type_code' name='type_code' onkeydown="PreventIt(event)" type="text" value="<?php echo htmlspecialchars($div_after_save);?>">
                            <!--onKeyUp="ajaxFunction(event,'non','search_payments.php');"-->
                            <div id='ajax_div_insurance_section'>
                                <div id='ajax_div_insurance_error'></div>
                                <div id="ajax_div_insurance" style="display:none;"></div>
                            </div>
                        </div>
                        <div class="forms col-xs-2">
                            <label class="control-label" for="div_insurance_or_patient"><?php echo xlt('Payor ID'); ?>:</label>
                            <div class="form-control" id="div_insurance_or_patient"><?php echo htmlspecialchars(formData('hidden_type_code'));?></div>
                            <input id="description" name="description" type="hidden">
                            <input id="deposit_date" name="deposit_date" style="display:none" type="text">
                        </div>
                        <div class="forms col-xs-3">
                            <label class="control-label" for="PaymentSortBy"><?php echo xlt('Sort Result by'); ?>:</label>
                            <?php echo generate_select_list("PaymentSortBy", "payment_sort_by", "$PaymentSortBy", "Sort Result by", " ", "");?>
                        </div>
                    </div>
                </fieldset><!--End of Search-->
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                <div class="form-group clearfix">
                    <div class="col-sm-12 text-left position-override">
                        <div class="btn-group" role="group">
                        <a class="btn btn-default btn-search" href="#" onclick="javascript:return SearchPayment();"><span><?php echo xlt('Search');?></span></a>
                        </div>
                    </div>
                </div>
                <?php
                if ($_POST["mode"] == "SearchPayment") {
                    echo "&nbsp;" ."<br>"; // do not remove else below div will not display !!
                ?>
                <div class = "table-responsive">
              <table class="table">
            <?php
            if (sqlNumRows($ResultSearch)>0) {
                ?>
              <thead bgcolor="#DDDDDD" class="">
                <td class="left top" width="25">&nbsp;</td>
                <td class="left top"><?php echo htmlspecialchars(xl('ID'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Date'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Paying Entity'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Payer'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Ins Code'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Payment Method'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Check Number'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Pay Status'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Payment'), ENT_QUOTES) ?></td>
                    <td class="left top right" ><?php echo htmlspecialchars(xl('Undistributed'), ENT_QUOTES) ?></td>
                    </thead>
                    <?php
                    $CountIndex=0;
                    while ($RowSearch = sqlFetchArray($ResultSearch)) {
                             $Payer='';
                        if ($RowSearch['payer_id']*1 >0) {
                           //-------------------
                            $res = sqlStatement("SELECT insurance_companies.name FROM insurance_companies
                                                where insurance_companies.id ='{$RowSearch['payer_id']}'");
                            $row = sqlFetchArray($res);
                            $Payer=$row['name'];
                      //-------------------
                        } elseif ($RowSearch['patient_id']*1 >0) {
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
                        if ($CountIndex==sqlNumRows($ResultSearch)) {
                            $StringClass=' bottom left top ';
                        } else {
                            $StringClass=' left top ';
                        }
                        if ($CountIndex%2==1) {
                            $bgcolor='#ddddff';
                        } else {
                            $bgcolor='#ffdddd';
                        }
                        ?>
                        <tr bgcolor='<?php echo $bgcolor; ?>' class="text">
                        <td class="<?php echo $StringClass; ?>">
                            <!--<a href="#" onclick="javascript:return DeletePayments(&lt;?php echo htmlspecialchars($RowSearch['session_id']); ?&gt;);"><img border="0" src="../pic/Delete.gif"></a>-->
                                 
                            <a href="#" onclick="javascript:return DeletePayments(<?php echo htmlspecialchars($RowSearch['session_id']); ?>);"><img border="0" src="../pic/Delete.gif"></a>
                        </td>
                        <td class="<?php echo $StringClass; ?>">
                            <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')"><?php echo htmlspecialchars($RowSearch['session_id']); ?></a>
                        </td>
                        <td class="<?php echo $StringClass; ?>">
                            <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')"><?php echo $RowSearch['check_date']=='0000-00-00' ? '&nbsp;' : htmlspecialchars(oeFormatShortDate($RowSearch['check_date'])); ?></a>
                        </td>
                        <td class="<?php echo $StringClass; ?>">
                            <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')">
                            <?php
                            $frow['data_type']=1;
                            $frow['list_id']='payment_type';
                            $PaymentType='';
                            if ($RowSearch['payment_type']=='insurance' || $RowSearch['payer_id']*1 >0) {
                                $PaymentType='insurance';
                            } elseif ($RowSearch['payment_type']=='patient' || $RowSearch['patient_id']*1 >0) {
                                $PaymentType='patient';
                            } elseif (($RowSearch['payer_id']*1 == 0 && $RowSearch['patient_id']*1 == 0)) {
                                $PaymentType='';
                            }
                            generate_print_field($frow, $PaymentType);
                            ?></a>
                            </td>
                            <td class="<?php echo $StringClass; ?>">
                            <!--<a class='iframe medium_modal' href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"><?php echo  $Payer=='' ? '&nbsp;' : htmlspecialchars($Payer) ;?></a>-->
                            <a class="" data-target="#myModal" data-toggle="modal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')"><?php echo  $Payer=='' ? '&nbsp;' : htmlspecialchars($Payer) ;?></a><!--link to iframe-->
                            </td>
                            <td class="<?php echo $StringClass; ?>">
                            <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')"><?php echo $RowSearch['payer_id']*1 >0 ? htmlspecialchars($RowSearch['payer_id']) : '&nbsp;'; ?></a>
                            </td>
                            <td align="left" class="<?php echo $StringClass; ?>">
                            <!--<a class='iframe medium_modal' href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"><?php
                                                        $frow['data_type']=1;
                                                        $frow['list_id']='payment_method';
                                                        generate_print_field($frow, $RowSearch['payment_method']);
                                            ?></a>-->
                                    <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')">
                                    <?php
                                    $frow['data_type']=1;
                                    $frow['list_id']='payment_method';
                                    generate_print_field($frow, $RowSearch['payment_method']);
                                    ?></a>
                                </td>
                                <td align="left" class="<?php echo $StringClass; ?>">
                                <!--<a class='iframe medium_modal' href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"><?php echo $RowSearch['reference']=='' ? '&nbsp;' : htmlspecialchars($RowSearch['reference']); ?></a>-->
                                <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')"><?php echo $RowSearch['reference']=='' ? '&nbsp;' : htmlspecialchars($RowSearch['reference']); ?></a>
                                </td>
                                <td align="left" class="<?php echo $StringClass; ?>">
                                   <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')"><?php
                                                        $rs= sqlStatement("select pay_total,global_amount from ar_session where session_id='".$RowSearch['session_id']."'");
                                                        $row=sqlFetchArray($rs);
                                                        $pay_total=$row['pay_total'];
                                                        $global_amount=$row['global_amount'];
                                                        $rs= sqlStatement("select sum(pay_amount) sum_pay_amount from ar_activity where session_id='".$RowSearch['session_id']."'");
                                                        $row=sqlFetchArray($rs);
                                                        $pay_amount=$row['sum_pay_amount'];
                                                        $UndistributedAmount=$pay_total-$pay_amount-$global_amount;
                                                        echo $UndistributedAmount*1==0 ? htmlspecialchars(xl('Fully Paid'), ENT_QUOTES) : htmlspecialchars(xl('Unapplied'), ENT_QUOTES); ?></a>
                                </td>
                                <td align="right" class="<?php echo $StringClass; ?>">
                                    <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')"><?php echo htmlspecialchars($RowSearch['pay_total']); ?></a>
                                </td>
                                <td align="right" class="<?php echo $StringClass; ?>right">
                                    <a class="" data-toggle="modal"  data-target="#myModal" onclick="loadiframe('edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>')"><?php echo htmlspecialchars(number_format($UndistributedAmount, 2)); ?></a>
                                </td>
                            </tr>
                            <?php
                    }//End of while ($RowSearch = sqlFetchArray($ResultSearch))
            } //End of if(sqlNumRows($ResultSearch)>0)
            else {
                    ?>
                  <tr>
                  <td class="text" colspan="11"><?php echo htmlspecialchars(xl('No Result Found, for the above search criteria.'), ENT_QUOTES) ?></td>
                        </tr>
                        <?php
            }// End of else
            ?>
              </table>
          </div><!--End of table-responsive div-->
            <?php
                }// End of if ($_POST["mode"] == "SearchPayment")
                ?>
                <div class="row">
                    <input id='mode' name='mode' type='hidden' value=''>
                    <input id='ajax_mode' name='ajax_mode' type='hidden' value=''>
                    <input id="hidden_type_code" name="hidden_type_code" type="hidden" value="<?php echo htmlspecialchars(formData('hidden_type_code'));?>">
                    <input id='DeletePaymentId' name='DeletePaymentId' type='hidden' value=''>
                    <input id='SortFieldOld' name='SortFieldOld' type='hidden' value='<?php echo attr($PaymentSortBy);?>'>
                    <input id='Sort' name='Sort' type='hidden' value='<?php echo attr($Sort);?>'>
                    <input id="after_value" name="after_value" type="hidden" value="<?php echo htmlspecialchars($Message);?>">
                </div>
            </form>
        </div>
        
    </div>
    <div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog oe-modal-dialog modal-lg">
                <div class="modal-content oe-modal-content">
                    <!--<div class="modal-header" style="border:hidden"></div>-->
                    <div class="modal-body">
                        <iframe src="" id="targetiframe" style="height:650px; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe> 
                    </div>
                    <div class="modal-footer" style="margin-top:0px;">
                       <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    function loadiframe(htmlHref) { //load iframe
         document.getElementById('targetiframe').src = htmlHref;
    }
    </script>
</body>
</html>
