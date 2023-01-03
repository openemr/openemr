<?php

/**
* Payments in database can be searched through this screen and edit popup is also its part.
* Deletion of the payment is done with logging.
*
*
* @package   OpenEMR
* @link      http://www.open-emr.org
* @author    Eldho Chacko <eldho@zhservices.com>
* @author    Paul Simon K <paul@zhservices.com>
* @author    Brady Miller <brady.g.miller@gmail.com>
* @author    Rod Roark <rod@sunsetsystems.com>
* @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
* @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
* @copyright Copyright (c) 2020 Rod Roark <rod@sunsetsystems.com>
* @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once("../globals.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/payment.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

if (!AclMain::aclCheckCore('acct', 'bill', '', 'write') && !AclMain::aclCheckCore('acct', 'eob', '', 'write')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Search Payment")]);
    exit;
}

//===============================================================================
//Deletion of payment and its corresponding distributions.
//===============================================================================
set_time_limit(0);
if (isset($_POST["mode"])) {
    if ($_POST["mode"] == "DeletePayments") {
        $DeletePaymentId = isset($_POST['DeletePaymentId']) ? trim($_POST['DeletePaymentId']) : '';
        $ResultSearch = sqlStatement(
            "SELECT distinct encounter, pid from ar_activity where deleted IS NULL AND session_id = ?",
            [$DeletePaymentId]
        );
        if (sqlNumRows($ResultSearch) > 0) {
            while ($RowSearch = sqlFetchArray($ResultSearch)) {
                $Encounter = $RowSearch['encounter'];
                $PId = $RowSearch['pid'];
                sqlStatement("update form_encounter set last_level_closed=last_level_closed - 1 where pid =? and encounter=?", [$PId, $Encounter]);
            }
        }

    //delete and log that action
        row_delete("ar_session", "session_id ='" . add_escape_custom($DeletePaymentId) . "'");
        row_modify("ar_activity", "deleted = NOW()", "deleted IS NULL AND session_id = '" . add_escape_custom($DeletePaymentId) . "'");
        $Message = 'Delete';
    //------------------
        $_POST["mode"] = "SearchPayment";
    }

//===============================================================================
//Search section.
//===============================================================================
    if ($_POST["mode"] == "SearchPayment") {
        $FromDate = isset($_POST['FromDate']) ? trim($_POST['FromDate']) : '';
        $ToDate = isset($_POST['ToDate']) ? trim($_POST['ToDate']) : '';
        $PaymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
        $CheckNumber = isset($_POST['check_number']) ? trim($_POST['check_number']) : '';
        $PaymentAmount = isset($_POST['payment_amount']) ? trim($_POST['payment_amount']) : '';
        $PayingEntity = isset($_POST['type_name']) ? trim($_POST['type_name']) : '';
        $PaymentCategory = isset($_POST['adjustment_code']) ? trim($_POST['adjustment_code']) : '';
        $PaymentFrom = isset($_POST['hidden_type_code']) ? trim($_POST['hidden_type_code']) : '';
        $PaymentStatus = isset($_POST['PaymentStatus']) ? trim($_POST['PaymentStatus']) : '';
        $PaymentSortBy = isset($_POST['PaymentSortBy']) ? trim($_POST['PaymentSortBy']) : '';
        $PaymentDate = isset($_POST['payment_date']) ? trim($_POST['payment_date']) : '';
        $QueryString = "Select * from  ar_session where  ";
        $And = '';

        $sqlBindArray = array();

        if ($PaymentDate == 'date_val') {
            $PaymentDateString = ' check_date ';
        } elseif ($PaymentDate == 'post_to_date') {
            $PaymentDateString = ' post_to_date ';
        } elseif ($PaymentDate == 'deposit_date') {
            $PaymentDateString = ' deposit_date ';
        }

        if ($FromDate != '') {
            $QueryString .= " $And $PaymentDateString >=?";
            $And = ' and ';
            $sqlBindArray[] = DateToYYYYMMDD($FromDate);
        }

        if ($ToDate != '') {
            $QueryString .= " $And $PaymentDateString <=?";
            $And = ' and ';
            $sqlBindArray[] = DateToYYYYMMDD($ToDate);
        }

        if ($PaymentMethod != '') {
            $QueryString .= " $And payment_method =?";
            $And = ' and ';
            $sqlBindArray[] = $PaymentMethod;
        }

        if ($CheckNumber != '') {
            $QueryString .= " $And reference like ?";
            $And = ' and ';
            $sqlBindArray[] = '%' . $CheckNumber . '%';
        }

        if ($PaymentAmount != '') {
            $QueryString .= " $And pay_total =?";
            $And = ' and ';
            $sqlBindArray[] = $PaymentAmount;
        }

        if ($PayingEntity != '') {
            if ($PayingEntity == 'insurance') {
                $QueryString .= " $And payer_id !='0'";
            }

            if ($PayingEntity == 'patient') {
                $QueryString .= " $And payer_id ='0'";
            }

            $And = ' and ';
        }

        if ($PaymentCategory != '') {
            $QueryString .= " $And adjustment_code =?";
            $And = ' and ';
            $sqlBindArray[] = $PaymentCategory;
        }

        if ($PaymentFrom != '') {
            if ($PayingEntity == 'insurance' || $PayingEntity == '') {
                //-------------------
                $res = sqlStatement("SELECT insurance_companies.name FROM insurance_companies
            where insurance_companies.id =?", [$PaymentFrom]);
                $row = sqlFetchArray($res);
                $div_after_save = $row['name'];
                //-------------------

                $QueryString .= " $And payer_id =?";
                $sqlBindArray[] = $PaymentFrom;
            }

            if ($PayingEntity == 'patient') {
                //-------------------
                $res = sqlStatement("SELECT fname,lname,mname FROM patient_data
            where pid =?", [$PaymentFrom]);
                $row = sqlFetchArray($res);
                $fname = $row['fname'];
                $lname = $row['lname'];
                $mname = $row['mname'];
                $div_after_save = $lname . ' ' . $fname . ' ' . $mname;
                //-------------------

                $QueryString .= " $And patient_id =?";
                $sqlBindArray[] = $PaymentFrom;
            }

            $And = ' and ';
        }

        if ($PaymentStatus != '') {
            $QsString = "select ar_session.session_id, pay_total, global_amount, sum(pay_amount) sum_pay_amount " .
                "from ar_session,ar_activity WHERE " .
                "ar_activity.deleted IS NULL AND ar_session.session_id = ar_activity.session_id " .
                "group by ar_activity.session_id, ar_session.session_id " .
                "having pay_total - global_amount - sum_pay_amount = 0 or pay_total = 0";
            $rs = sqlStatement($QsString);
            while ($rowrs = sqlFetchArray($rs)) {
                $StringSessionId .= $rowrs['session_id'] . ',';
            }

            $QsString = "select ar_session.session_id from ar_session	where pay_total=0";
            $rs = sqlStatement($QsString);
            while ($rowrs = sqlFetchArray($rs)) {
                $StringSessionId .= $rowrs['session_id'] . ',';
            }

            $StringSessionId = substr($StringSessionId, 0, -1);
            if ($PaymentStatus == 'fully_paid') {
                $QueryString .= " $And session_id in(" . add_escape_custom($StringSessionId) . ") ";
            } elseif ($PaymentStatus == 'unapplied') {
                $QueryString .= " $And session_id not in(" . add_escape_custom($StringSessionId) . ") ";
            }

            $And = ' and ';
        }

        if ($PaymentSortBy != '') {
            $SortFieldOld = isset($_POST['SortFieldOld']) ? trim($_POST['SortFieldOld']) : '';
            $Sort = isset($_POST['Sort']) ? trim($_POST['Sort']) : '';
            if ($SortFieldOld == $PaymentSortBy) {
                if ($Sort == 'DESC' || $Sort == '') {
                    $Sort = 'ASC';
                } else {
                    $Sort = 'DESC';
                }
            } else {
                $Sort = 'ASC';
            }

            $QueryString .= " order by " . escape_sql_column_name($PaymentSortBy, ['ar_session']) . " " . escape_sort_order($Sort);
        }

        $ResultSearch = sqlStatement($QueryString, $sqlBindArray);
    }
}

//===============================================================================
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo xlt("Search Payment") ?></title>
<?php Header::setupHeader(['datetime-picker']); ?>

<?php include_once("{$GLOBALS['srcdir']}/payment_jav.inc.php"); ?>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>

<script>
    function refreshSearch() {
        top.restoreSession();
        SearchPayment();
    }

    $(function () {
    <?php if (!empty($_POST['mode']) && ($_POST['mode'] == 'SearchPayment')) { ?>
        $("html").animate({ scrollTop: $("div table.table").offset().top }, 800);
    <?php } ?>

        $(".medium_modal").on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dlgopen('', '', 'modal-full', 800, '', '', {
                buttons: [
                    {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
                ],
                sizeHeight: '',
                onClosed: 'refreshSearch',
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

    var mypcc = '1';

    function SearchPayment() {
        // Search  validations.
        if (document.getElementById('FromDate').value == '' && document.getElementById('ToDate').value == '' && document.getElementById('PaymentStatus').selectedIndex == 0 && document.getElementById('payment_method').selectedIndex == 0 && document.getElementById('type_name').selectedIndex == 0 && document.getElementById('adjustment_code').selectedIndex == 0 && document.getElementById('check_number').value == '' && document.getElementById('payment_amount').value == '' && document.getElementById('hidden_type_code').value == '') {
            alert(<?php echo xlj('Please select any Search Option.'); ?>);
            return false;
        }
        if (document.getElementById('FromDate').value != '' && document.getElementById('ToDate').value != '') {
            if (!DateCheckGreater(document.getElementById('FromDate').value, document.getElementById('ToDate').value, '<?php echo DateFormatRead();?>')) {
                alert(<?php echo xlj('From Date Cannot be Greater than To Date.'); ?>);
                document.getElementById('FromDate').focus();
                return false;
            }
        }
        top.restoreSession();
        document.getElementById('mode').value = 'SearchPayment';
        document.forms[0].submit();
    }

    function DeletePayments(DeleteId) {
        // Confirms deletion of payment and all its distribution.
        if (confirm(<?php echo xlj('Would you like to Delete Payments?'); ?>)) {
            document.getElementById('mode').value = 'DeletePayments';
            document.getElementById('DeletePaymentId').value = DeleteId;
            top.restoreSession();
            document.forms[0].submit();
        } else
            return false;
    }

    function OnloadAction() {
        // Displays message after deletion.
        after_value = document.getElementById('after_value').value;
        if (after_value == 'Delete') {
            alert(<?php echo xlj('Successfully Deleted'); ?>)
        }
    }

    function SearchPayingEntityAction() {
        // Which ajax is to be active(patient,insurance), is decided by the 'Paying Entity' drop down, where this function is called.
        // So on changing some initialization is need.Done below.
        document.getElementById('type_code').value = '';
        document.getElementById('hidden_ajax_close_value').value = '';
        document.getElementById('hidden_type_code').value = '';
        document.getElementById('div_insurance_or_patient').innerHTML = '&nbsp;';
        document.getElementById('description').value = '';
        if (document.getElementById('ajax_div_insurance')) {
            $("#ajax_div_patient_error").empty();
            $("#ajax_div_patient").empty();
            $("#ajax_div_insurance_error").empty();
            $("#ajax_div_insurance").empty();
            $("#ajax_div_insurance").hide();
            document.getElementById('payment_method').style.display = '';
        }
    }

    document.onclick = HideTheAjaxDivs;
</script>

<style>
#ajax_div_insurance {
    position: absolute;
    z-index: 10;
    background-color: #FBFDD0;
    border: 1px solid var(--gray);
    padding: 10px;
}

@media (min-width: 992px) {
    .modal-lg {
        width: 1000px !Important;
    }
}
</style>
<?php
$arrOeUiSettings = array(
'heading_title' => xl('Payments'),
'include_patient_name' => false,// use only in appropriate pages
'expandable' => true,
'expandable_files' => array("search_payments_xpd", "new_payment_xpd", "era_payments_xpd"),//all file names need suffix _xpd
'action' => "",//conceal, reveal, search, reset, link or back
'action_title' => "",
'action_href' => "",//only for actions - reset, link or back
'show_help_icon' => false,
'help_file_name' => ""
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>
<body onload="OnloadAction()">
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?> mt-3">
        <div class="row">
            <div class="col-sm-12">
                <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
            </div>
        </div>
        <nav class="navbar navbar-nav navbar-expand-md navbar-light text-body bg-light mb-4 p-4">
            <button class="navbar-toggler icon-bar" data-target="#myNavbar" data-toggle="collapse" type="button"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" href='new_payment.php'><?php echo xlt('New Payment'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" href='search_payments.php'><?php echo xlt('Search Payment'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" href='era_payments.php'><?php echo xlt('ERA Posting'); ?></a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="row">
            <div class="col-sm-12">
                <form id="new_payment" method='post' name='new_payment'>
                    <fieldset>
                        <div class="jumbotron py-4">
                            <div class="row h3">
                                <?php echo xlt('Payment List'); ?>
                            </div>
                            <div class="row oe-custom-line">
                                <div class="forms col-2">
                                    <label class="control-label" for="payment_date"><?php echo xlt('Payment date'); ?>:</label>
                                    <?php echo generate_select_list("payment_date", "payment_date", ($PaymentDate ?? ''), "Payment Date", "", ""); ?>
                                </div>
                                <div class="forms col-2">
                                    <label class="control-label" for="FromDate"><?php echo xlt('From'); ?>:</label>
                                    <input class="form-control datepicker" id='FromDate' name='FromDate' type='text' value='<?php echo attr($FromDate ?? ''); ?>' autocomplete="off" />
                                </div>
                                <div class="forms col-2">
                                    <label class="control-label" for="ToDate"><?php echo xlt('To{{Range}}'); ?>:</label>
                                    <input class="form-control datepicker" id='ToDate' name='ToDate' type='text' value='<?php echo attr($ToDate ?? ''); ?>' autocomplete="off" />
                                </div>
                                <div class="forms col-3">
                                    <label class="control-label" for="payment_method"><?php echo xlt('Payment Method'); ?>:</label>
                                    <?php echo generate_select_list("payment_method", "payment_method", ($PaymentMethod ?? ''), "Payment Method", " ", ""); ?>
                                </div>
                                <div class="forms col-3">
                                    <label class="control-label" for="check_number"><?php echo xlt('Check Number'); ?>:</label>
                                    <input autocomplete="off" class="form-control" id="check_number" name="check_number" type="text" value="<?php echo attr($_POST['check_number'] ?? ''); ?>" />
                                </div>
                            </div>
                            <div class="row oe-custom-line">
                                <div class="forms col-4">
                                    <label class="control-label" for="payment_method"><?php echo xlt('Payment Amount'); ?>:</label>
                                    <input autocomplete="off" class="form-control" id="payment_amount" name="payment_amount" onkeyup="ValidateNumeric(this);" type="text" value="<?php echo attr($_POST['payment_amount'] ?? ''); ?>" />
                                </div>
                                <div class="forms col-2">
                                    <label class="control-label" for="type_name"><?php echo xlt('Paying Entity'); ?>:</label>
                                    <?php echo generate_select_list("type_name", "payment_type", ($type_name ?? ''), "Paying Entity", " ", "", "SearchPayingEntityAction()"); ?>
                                </div>
                                <div class="forms col-3">
                                    <label class="control-label" for="adjustment_code"><?php echo xlt('Payment Category'); ?>:</label>
                                    <?php echo generate_select_list("adjustment_code", "payment_adjustment_code", ($adjustment_code ?? ''), "Paying Category", " ", ""); ?>
                                </div>
                                <div class="forms col-3">
                                    <label class="control-label" for="PaymentStatus"><?php echo xlt('Pay Status'); ?>:</label>
                                    <?php echo generate_select_list("PaymentStatus", "payment_status", ($PaymentStatus ?? ''), "Pay Status", " ", ""); ?>
                                </div>
                            </div>
                            <div class="row oe-custom-line">
                                <div class="forms col-4">
                                    <label class="control-label" for="type_code"><?php echo xlt('Payment From'); ?>:</label>
                                    <input id="hidden_ajax_close_value" type="hidden" value="<?php echo attr($div_after_save ?? ''); ?>" />
                                    <input autocomplete="off" class="form-control" id='type_code' name='type_code' onkeydown="PreventIt(event)" type="text" value="<?php echo attr($div_after_save ?? ''); ?>" />
                                    <!--onKeyUp="ajaxFunction(event,'non','search_payments.php');"-->
                                    <div id='ajax_div_insurance_section'>
                                        <div id='ajax_div_insurance_error'></div>
                                        <div id="ajax_div_insurance" style="display:none;"></div>
                                    </div>
                                </div>
                                <div class="forms col-2">
                                    <label class="control-label" for="div_insurance_or_patient"><?php echo xlt('Payor ID'); ?>:</label>
                                    <div class="form-control" id="div_insurance_or_patient"><?php echo attr($_POST['hidden_type_code'] ?? ''); ?></div>
                                    <input id="description" name="description" type="hidden" />
                                    <input id="deposit_date" name="deposit_date" style="display:none" type="text" />
                                </div>
                                <div class="forms col-3">
                                    <label class="control-label" for="PaymentSortBy"><?php echo xlt('Sort Result by'); ?>:</label>
                                    <?php echo generate_select_list("PaymentSortBy", "payment_sort_by", ($PaymentSortBy ?? ''), "Sort Result by", " ", ""); ?>
                                </div>
                            </div>
                            <!-- can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets -->
                            <div class="form-group mt-3">
                                <div class="col-sm-12 text-left position-override">
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-primary btn-search" href="#" onclick="javascript:return SearchPayment();"><span><?php echo xlt('Search'); ?></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset><!--End of Search-->
                    <?php
                    if (!empty($_POST["mode"]) && ($_POST["mode"] == "SearchPayment")) {
                        echo "&nbsp;" . "<br />"; // do not remove else below div will not display !!
                        ?>
                        <div class="table-responsive-sm">
                            <table class="table table-sm table-bordered">
                                <?php
                                if (sqlNumRows($ResultSearch) > 0) { ?>
                                <thead class="bg-dark text-light">
                                <tr>
                                <th>&nbsp;</th>
                                <th><?php echo xlt('ID'); ?></th>
                                <th><?php echo xlt('Date'); ?></th>
                                <th><?php echo xlt('Paying Entity'); ?></th>
                                <th><?php echo xlt('Payer'); ?></th>
                                <th><?php echo xlt('Ins Code'); ?></th>
                                <th><?php echo xlt('Payment Method'); ?></th>
                                <th><?php echo xlt('Check Number'); ?></th>
                                <th><?php echo xlt('Pay Status'); ?></th>
                                <th><?php echo xlt('Payment'); ?></th>
                                <th><?php echo xlt('Undistributed'); ?></th>
                                </tr>
                                    </thead>
                                    <?php
                                    $CountIndex = 0;
                                    while ($RowSearch = sqlFetchArray($ResultSearch)) {
                                        $Payer = '';
                                        if ($RowSearch['payer_id'] * 1 > 0) {
                                            //-------------------
                                            $res = sqlStatement("SELECT insurance_companies.name FROM insurance_companies
                                                    where insurance_companies.id =?", [$RowSearch['payer_id']]);
                                            $row = sqlFetchArray($res);
                                            $Payer = $row['name'];
                                            //-------------------
                                        } elseif ($RowSearch['patient_id'] * 1 > 0) {
                                            //-------------------
                                            $res = sqlStatement("SELECT fname,lname,mname FROM patient_data
                                                    where pid =?", [$RowSearch['patient_id']]);
                                            $row = sqlFetchArray($res);
                                            $fname = $row['fname'];
                                            $lname = $row['lname'];
                                            $mname = $row['mname'];
                                            $Payer = $lname . ' ' . $fname . ' ' . $mname;
                                            //-------------------
                                        }
                                        //=============================================
                                        $CountIndex++;
                                        if ($CountIndex % 2 == 1) {
                                            $bgcolor = '#ddddff';
                                        } else {
                                            $bgcolor = '#ffdddd';
                                        }
                                        ?>
                                        <tr bgcolor='<?php echo attr($bgcolor); ?>' class="text">
                                            <td>
                                                <a href="#" onclick="javascript:return DeletePayments(<?php echo attr_js($RowSearch['session_id']); ?>);"><img border="0" src="../pic/Delete.gif"></a>
                                            </td>
                                            <td>
                                                <a class="medium_modal" href="edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>"><?php echo text($RowSearch['session_id']); ?></a>
                                            </td>
                                            <td>
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>'"><?php echo $RowSearch['check_date'] == '0000-00-00' ? '&nbsp;' : text(oeFormatShortDate($RowSearch['check_date'])); ?></a>
                                            </td>
                                            <td>
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>'">
                                                <?php
                                                $frow['data_type'] = 1;
                                                $frow['list_id'] = 'payment_type';
                                                $PaymentType = '';
                                                if ($RowSearch['payment_type'] == 'insurance' || $RowSearch['payer_id'] * 1 > 0) {
                                                    $PaymentType = 'insurance';
                                                } elseif ($RowSearch['payment_type'] == 'patient' || $RowSearch['patient_id'] * 1 > 0) {
                                                    $PaymentType = 'patient';
                                                } elseif (($RowSearch['payer_id'] * 1 == 0 && $RowSearch['patient_id'] * 1 == 0)) {
                                                    $PaymentType = '';
                                                }
                                                generate_print_field($frow, $PaymentType);
                                                ?></a>
                                            </td>
                                            <td>
                                                <!--<a class='iframe medium_modal' href="edit_payment.php?payment_id=<?php echo htmlspecialchars($RowSearch['session_id']); ?>"><?php echo $Payer == '' ? '&nbsp;' : htmlspecialchars($Payer); ?></a>-->
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>')"><?php echo $Payer == '' ? '&nbsp;' : text($Payer); ?></a><!--link to iframe-->
                                            </td>
                                            <td>
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>'><?php echo $RowSearch['payer_id'] * 1 > 0 ? text($RowSearch['payer_id']) : '&nbsp;'; ?></a>
                                            </td>
                                            <td class="text-left">
                                                <!--<a class='iframe medium_modal' href="edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>"><?php
                                                $frow['data_type'] = 1;
                                                $frow['list_id'] = 'payment_method';
                                                generate_print_field($frow, $RowSearch['payment_method']);
                                                ?></a>-->
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>'">
                                                <?php
                                                $frow['data_type'] = 1;
                                                $frow['list_id'] = 'payment_method';
                                                generate_print_field($frow, $RowSearch['payment_method']);
                                                ?></a>
                                            </td>
                                            <td class="text-left">
                                                <!--<a class='iframe medium_modal' href="edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>"><?php echo $RowSearch['reference'] == '' ? '&nbsp;' : text($RowSearch['reference']); ?></a>-->
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>'"><?php echo $RowSearch['reference'] == '' ? '&nbsp;' : text($RowSearch['reference']); ?></a>
                                            </td>
                                            <td class="text-left">
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>'">
                                        <?php
                                                $rs = sqlStatement("select pay_total,global_amount from ar_session where session_id=?", [$RowSearch['session_id']]);
                                                $row = sqlFetchArray($rs);
                                                $pay_total = $row['pay_total'];
                                                $global_amount = $row['global_amount'];
                                                $rs = sqlStatement(
                                                    "select sum(pay_amount) sum_pay_amount from ar_activity where " .
                                                    "deleted IS NULL AND session_id = ?",
                                                    [$RowSearch['session_id']]
                                                );
                                                $row = sqlFetchArray($rs);
                                                $pay_amount = $row['sum_pay_amount'];
                                                $UndistributedAmount = $pay_total - $pay_amount - $global_amount;
                                                echo $UndistributedAmount * 1 == 0 ? xlt('Fully Paid') : xlt('Unapplied'); ?></a>
                                            </td>
                                            <td class="text-right">
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>'"><?php echo text($RowSearch['pay_total']); ?></a>
                                            </td>
                                            <td class="<?php echo attr($StringClass ?? ''); ?>right text-right">
                                                <a class="medium_modal" href='edit_payment.php?payment_id=<?php echo attr_url($RowSearch['session_id']); ?>'"><?php echo text(number_format($UndistributedAmount, 2)); ?></a>
                                            </td>
                                        </tr>
                                        <?php
                                    }// End of while ($RowSearch = sqlFetchArray($ResultSearch))
                                } else { // End of if(sqlNumRows($ResultSearch)>0)
                                    ?>
                                    <tr>
                                        <td class="text" colspan="11"><?php echo xlt('No Result Found, for the above search criteria.'); ?></td>
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
                        <input id='mode' name='mode' type='hidden' value='' />
                        <input id='ajax_mode' name='ajax_mode' type='hidden' value='' />
                        <input id="hidden_type_code" name="hidden_type_code" type="hidden" value="<?php echo attr($_POST['hidden_type_code'] ?? ''); ?>" />
                        <input id='DeletePaymentId' name='DeletePaymentId' type='hidden' value='' />
                        <input id='SortFieldOld' name='SortFieldOld' type='hidden' value='<?php echo attr($PaymentSortBy ?? ''); ?>' />
                        <input id='Sort' name='Sort' type='hidden' value='<?php echo attr($Sort ?? ''); ?>' />
                        <input id="after_value" name="after_value" type="hidden" value="<?php echo attr($Message ?? ''); ?>" />
                    </div>
                </form>
            </div>
        </div>
    </div><!--end of container div-->
<?php $oemr_ui->oeBelowContainerDiv(); ?>
</body>
</html>
