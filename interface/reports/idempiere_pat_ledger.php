<?php
/**
 * This is a report to create a patient ledger of charges with payments
 * applied.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    WMT
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Rich Genandt <rgenandt@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/appointments.inc.php');

require_once($GLOBALS['OE_SITE_DIR'] . "/odbcconf.php");
require_once("./idempiere_pat_ledger_fun.php");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;
use OpenEMR\Common\Acl\AclMain;

$facilityService = new FacilityService();

$enc_units = $total_units = 0;
$enc_chg = $total_chg = 0;
$enc_pmt = $total_pmt = 0;
$enc_adj = $total_adj = 0;
$enc_bal = $total_bal = 0;
$bgcolor = "#FFFFDD";
$orow = 0;

$pat_pid = $_GET['patient_id'];
$type_form = $_GET['form'];

if (! AclMain::aclCheckCore('acct', 'rep')) {
    die(xlt("Unauthorized access."));
}


function User_Id_Look($thisField)
{
    if (!$thisField) {
        return '';
    }

    $ret = '';
    $rlist= sqlStatement("SELECT lname, fname, mname FROM users WHERE id=?", array($thisField));
    $rrow= sqlFetchArray($rlist);
    if ($rrow) {
        $ret = $rrow{'lname'}.', '.$rrow{'fname'}.' '.$rrow{'mname'};
    }

    return $ret;
}


if (!isset($_REQUEST['form_facility'])) {
    $_REQUEST['form_facility'] = '';
}

if (!isset($_REQUEST['form_provider'])) {
    $_REQUEST['form_provider'] = '';
}

if ($type_form=='0') {
    if (!isset($_REQUEST['form_patient'])) {
        $_REQUEST['form_patient'] = '';
    }

    if (!isset($_REQUEST['form_pid'])) {
        $_REQUEST['form_pid'] = '';
    }
} else {
    if (!isset($_REQUEST['form_patient'])) {
        $_REQUEST['form_patient'] = $pat_pid;
    }

    if (!isset($_REQUEST['form_pid'])) {
        $_REQUEST['form_pid'] = $pat_pid;
    }
}

if (!isset($_REQUEST['form_csvexport'])) {
    $_REQUEST['form_csvexport'] = '';
}

if (!isset($_REQUEST['form_refresh'])) {
    $_REQUEST['form_refresh'] = '';
}

if (!isset($_REQUEST['$form_dob'])) {
    $_REQUEST['$form_dob'] = '';
}

if (substr($GLOBALS['ledger_begin_date'], 0, 1) == 'Y') {
    $last_year = mktime(0, 0, 0, date('m'), date('d'), date('Y')-3);
} elseif (substr($GLOBALS['ledger_begin_date'], 0, 1) == 'M') {
    $ledger_time = substr($GLOBALS['ledger_begin_date'], 1, 1);
    $last_year = mktime(0, 0, 0, date('m')-$ledger_time, date('d'), date('Y'));
} elseif (substr($GLOBALS['ledger_begin_date'], 0, 1) == 'D') {
    $ledger_time = substr($GLOBALS['ledger_begin_date'], 1, 1);
    $last_year = mktime(0, 0, 0, date('m'), date('d')-$ledger_time, date('Y'));
}

$form_from_date = date('Y-m-d', $last_year);
if ($_REQUEST['form_from_date']) {
    $form_from_date = DateToYYYYMMDD($_POST['form_from_date']);
}

$form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_REQUEST['form_facility'];
$form_provider  = $_REQUEST['form_provider'];
$form_patient   = $_REQUEST['form_patient'];
$form_pid       = $_REQUEST['form_pid'];
$form_dob       = $_REQUEST['form_dob'];

$form_extra_payment_filter       = $_REQUEST['form_extra_payment_filter'] ? $_REQUEST['form_extra_payment_filter'] : '';
$form_extra_case_filter       = $_REQUEST['form_extra_case_filter'] ? $_REQUEST['form_extra_case_filter'] : '';

$chartNumber = getChartNumber($form_patient);
$caseList = getCaseDropdown($idempiere_connection, $chartNumber);

if ($_REQUEST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=svc_financial_report_".attr($form_from_date)."--".attr($form_to_date).".csv");
    header("Content-Description: File Transfer");
} else {
?>
<html>
<head>

    <title><?php echo xlt('Patient Ledger by Date'); ?></title>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
    <?php Header::setupHeader(['opener', 'jquery', 'jquery-ui-base', 'datetime-picker', 'datatables', 'datatables-colreorder', 'datatables-bs']); ?>

    <script type="text/javascript">
        var pt_name;
        var pt_id;
        var balancesAjax;
        var dataTableAjax;
        var printAjax;

        function checkSubmit() {
            $("#pageLoading").addClass('show');

            if(balancesAjax) {
                balancesAjax.abort();
            }

            if(dataTableAjax) {
                dataTableAjax.abort();
            }

            if(printAjax) {
                printAjax.abort();
            }

            var pat = document.forms[0].elements['form_patient'].value;
            if(!pat || pat == 0) {
                alert('<?php echo xls('A Patient Must Be Selected to Generate This Report') ?>');
                return false;
            }
            document.forms[0].elements['form_refresh'].value = true;
            document.forms[0].elements['form_csvexport'].value = '';
            document.forms[0].submit();
        }
        function setpatient(pid, lname, fname, dob) {
          document.forms[0].elements['form_patient'].value = lname + ', ' + fname;
          document.forms[0].elements['form_pid'].value = pid;
          document.forms[0].elements['form_dob'].value = dob;
        }
        function sel_patient() {
            dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
        }
    </script>

    <style type="text/css">
        td.details-control:before {
            font-family: "Font Awesome 6 Free";
            content: "\f078" !important;
            display: inline-block;
            vertical-align: middle;
            font-weight: 900;
            padding-right: 3px;
            vertical-align: middle;
            background-color: transparent !important;
            color: #000;
            border-radius: 0px;
            width: 100%;
            height: auto;
            box-shadow: none;
            margin: auto;
            text-align: center;
            border: 0px;
            padding-top: 4px;
        }
        tr.details td.details-control:before {
            font-family: "Font Awesome 6 Free";
            content: "\f077" !important;
            display: inline-block;
            vertical-align: middle;
            font-weight: 900;
            padding-right: 3px;
            vertical-align: middle;
            background-color: transparent !important;
            color: #000;
            border-radius: 0px;
            width: 100%;
            height: auto;
            box-shadow: none;
            margin: auto;
            text-align: center;
            border: 0px;
            padding-top: 4px;
        }
        .ledger_result_wrapper {
            margin-top: 20px;
        }
        #report_results {
            display: none;
        }
        /*tr.row-details {
        }
        tr.row-details:hover {
            background: none!important;
            background-color: transparent!important;
        }*/
        .green {
            color: green;
        }
        .red {
            color: red;
        }
        .childTable {
            margin-bottom: 10px;
        }
        .balance-label {
            font-size: 14px;
        }
        .balance-container {
            margin-left: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 370px;
        }
        .emptyRow {
            padding: 5px;
        }
        .loader-container {
            text-align: center;
            vertical-align: middle;
            padding: 80px 30px;
        }
        .loader-container .text {
            margin-bottom: 5px;
        }
        .documentationText {
            cursor: pointer;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            width: 120px;
            display: block;
        }
        td .tooltip .tooltip-inner {
            text-align:left!important;
        }
        .page-loading {
            top: 0;
            left: 0;
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: 10001;
            background: rgba(255,255,255,0.5);
            align-items: center;
            justify-content: center;
            text-align: center;
            display: none;
        }
        .page-loading.show {
            display: flex!important;
        }
        .page-loading .inner-content {
            padding: 8px 20px;
            background: #fff;
        }

        table.printTable tr th,
        table.printTable tr td {
            border: 1px solid black;
        }

        td.rowDetails .subViewTitle {
            display: none!important;
        }

        td.rowDetails {
            padding: 0px!important;
        }
        td.rowDetails .subTableContainer {
            border: 0px solid !important;
        }
        td.rowDetails table.subTableContainer > tbody > tr:first-child > td {
            border-top: 0px solid !important;
        }
        td.rowDetails table.subTableContainer > tbody > tr > td {
            padding: 0px!important;
        }
        td.rowDetails table {
            border-collapse: collapse;
            margin-bottom: 0px!important;
            margin-top: 0px!important;
            border: 0px solid !important; 
        }
        td.rowDetails table td,td.rowDetails table th {
            border: 1px solid black;
        }
        td.rowDetails table tr:first-child th {
            border-top: 0!important;
        }
        td.rowDetails table tr:last-child td {
            border-bottom: 0!important;
        }
        td.rowDetails table tr td:first-child,
        td.rowDetails table tr th:first-child {
            border-left: 0!important;
        }
        td.rowDetails table tr td:last-child,
        td.rowDetails table tr th:last-child {
            border-right: 0!important;
        }
        .headerDetailsContainer {
            font-size: 16px;
        }
        .dateContainer {
            text-align: right;
            vertical-align: text-top;
        }
        #printBalances .balance-label {
            font-size: 16px;
        }
        #printBalances .balance-label > span > b {
            font-weight: normal;
        }
        table.printTable {
            color: black !important;
        }

        table.printTable tr td, table.printTable tr th {
            color: black !important;
        }

        table.printTable td.rowDetails table.subTableContainer .childTable > thead > tr > th {
            border-color: black !important;
            background-color: transparent !important;
        }
    </style>

    <style type="text/css">
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }
            #report_results {
                margin-top: 30px;
            }
            #report_header {
                visibility: visible;
                display: inline;
            }
            #title {
                visibility: hidden;
                display: none;
            }
            .ledger_result_wrapper {
                display: none;
                visibility: hidden;
            }
            #report_results {
                display: block;
            }
        }
        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
            #report_header {
                visibility: hidden;
                display: none;
            }
            #title {
                visibility: visible;
                display: inline;
            }
        }
    </style>

    <script language="JavaScript">
        $(document).ready(function() {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });
    </script>
</head>
<body class="body_top">
<?php if ($type_form == '0') { ?>
<span class='title' id='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Patient Ledger by Date'); ?></span>
<?php } else { ?>
<span class='title' id='title'><?php echo xlt('Patient Ledger'); ?></span>
<?php } ?>
<form method='post' action='idempiere_pat_ledger.php?form=<?php echo attr($type_form);?>&patient_id=<?php echo attr($form_pid);?>' id='theform' onsubmit='return top.restoreSession()'>
<div id="report_parameters" class="alert alert-secondary">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
    <?php if ($type_form == '1') { ?>
    <td width='70%'>
    <?php } else { ?>
  <td width='30%'>
    <?php } ?>
    <div style='float:left'>
    <table class='text'>
        <tr>
        <?php if ($type_form == '0') { ?>
            <td class='control-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
            <?php dropdown_facility($form_facility, 'form_facility', true); ?>
            </td>
      <td class='control-label'><?php echo xlt('Provider'); ?>:</td>
      <td><?php
        $query = "SELECT id, lname, fname FROM users WHERE ".
                "authorized=1 AND active!=0 ORDER BY lname, fname";
        $ures = sqlStatement($query);
        echo "   <select name='form_provider' class='form-control'>\n";
        echo "    <option value=''>-- " . xlt('All') . " --\n";
        while ($urow = sqlFetchArray($ures)) {
            $provid = $urow['id'];
            echo "    <option value='" . attr($provid) ."'";
            if ($provid == $_REQUEST['form_provider']) {
                echo " selected";
            }

            echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
        }

        echo "   </select>\n";
        ?></td>
        </tr><tr>
        <?php } ?>
      <td class='control-label'>
        <?php echo xlt('From'); ?>:&nbsp;&nbsp;&nbsp;&nbsp;
      </td>
      <td>
        <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
      </td>
      <td class='control-label' class='control-label'>
        <?php echo xlt('To'); ?>:
      </td>
      <td>
        <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
      </td>
      <td>
        <select name='form_extra_payment_filter' class='form-control' style="min-width: 110px;">
            <option value='charge' <?php echo $form_extra_payment_filter == "charge" ? "selected" : "" ?>>Charge</option>
            <option value='payment' <?php echo $form_extra_payment_filter == "payment" ? "selected" : "" ?>>Payment</option>
        </select>
      </td>
      <td>
        <select name='form_extra_case_filter' class='form-control' style="width: 180px;">
            <option value=''>Please Select</option>
            <?php
                $highestCaseNumber = getHighestCaseNumber($caseList, $_REQUEST['form_extra_case_filter']); 
                foreach ($caseList as $key => $list) {
                    $selectedOp = $highestCaseNumber == $list['value'] ? "selected" : "";
                    echo '<option value="'.$list['value'].'" '.$selectedOp.'>'.$list['name'].'</option>';
                }
            ?>
        </select>
      </td>
      <td>
        <div id="balance_data" class='balance-container'>
            <br/>
            <div style='margin-left:10px' class='text'><img src='../pic/ajax-loader.gif'/></div><br/>
        </div>
      </td>
        <?php if ($type_form == '0') { ?>
      <td><span class='control-label'><?php echo xlt('Patient'); ?>:&nbsp;&nbsp;</span></td>
      <td>
        <input type='text' size='20' name='form_patient' class='form-control' style='width:100%;cursor:pointer;cursor:hand' id='form_patient' value='<?php echo ($form_patient) ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
        <?php } else { ?>
        <input type='hidden' name='form_patient' value='<?php echo attr($form_patient); ?>' />
        <?php } ?>
        <input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
        <input type='hidden' name='form_dob' value='<?php echo attr($form_dob); ?>' />

      </td>
        </tr>
    </table>
    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                      <a href='#' class='btn btn-secondary btn-save' onclick="checkSubmit();" >
                        <?php echo xlt('Submit'); ?>
            </a>
                    <?php if ($_REQUEST['form_refresh'] || $_REQUEST['form_csvexport']) { ?>
              <a href='#' class='btn btn-secondary btn-print' id='med_printbutton'>
                <?php echo xlt('Print Ledger'); ?>
              </a>
              <a href='#' id='printbutton' style="display: none;"></a>
                <?php if ($type_form == '1') { ?>
                <a href="../patient_file/summary/demographics.php" class="btn btn-secondary btn-transmit" onclick="top.restoreSession()">
                    <?php echo xlt('Back To Patient');?>
                </a>
                <?php } ?>
                    <?php } ?>
          </div>
                </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

<?php
} // end not export
  $from_date = $form_from_date . ' 00:00:00';
  $to_date = $form_to_date . ' 23:59:59';
if ($_REQUEST['form_refresh'] || $_REQUEST['form_csvexport']) {
    if (!$form_facility) {
        $form_facility = '3';
    }

    $facility = $facilityService->getById($form_facility);
    $patient = sqlQuery("SELECT * from patient_data WHERE pid=?", array($form_patient));
    $pat_dob = $patient['DOB'];
    $pat_name = $patient['fname']. ' ' . $patient['lname'];
?>
<div id="report_header">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr><td colspan="2">&nbsp;</td></tr>
  <tr>
    <td colspan="2" class="title" align="center" ><?php echo $GLOBALS['openemr_name']; ?></td>
  </tr>
  <tr><td colspan="2">&nbsp;</td></tr>
  <tr><td colspan="2">&nbsp;</td></tr>
  <tr>
    <td class="headerDetailsContainer">
        <?php echo '<div>'; ?>
        <?php echo xlt('Patient Name: ')?>:
        <?php if ($type_form == '1') { ?>
            <?php echo text($pat_name); ?>
        <?php } else { ?>
            <?php echo text($form_patient); ?>
        <?php } ?>
        <?php echo '</div>'; ?>

        <?php echo '<div>'; ?>
        <?php echo xlt('DOB: ')?>:
        <?php if ($type_form == '1') { ?>
            <?php echo text($pat_dob);?>
        <?php } else { ?>
            <?php echo text($form_dob); ?>
        <?php } ?>
        <?php echo '</div>'; ?>

        <?php //echo '<div>'; ?>
        <?php //echo xlt('Phone: ')?>
        <?php //echo text($facility{'phone'}); ?>
        <?php //echo '</div>'; ?>
    </td>
    <td class="dateContainer">
        <?php
            $header_form_to_date = xl('For Dates: ') . ': ' . oeFormatShortDate($form_from_date) . ' - ' . oeFormatShortDate($form_to_date);
            echo text($header_form_to_date);
        ?>
    </td>
  </tr>
  <tr><td colspan="2">&nbsp;</td></tr>
  <tr>
    <td colspan="2">
        <div id="printBalances"></div>
    </td>
  </tr>
</table>
<br/>
</div>

<div id="report_results">
</div>

<div id="ledger_datatable" class="ledger_result_wrapper table-responsive">
    <div class='loader-container'><br/><div class='text'><img src='../pic/ajax-loader.gif'/></div><div>Loading...</div><br/></div>
</div>

<script type="text/javascript">
    var form_from_date = '<?php echo $form_from_date; ?>';
    var form_to_date = '<?php echo $form_to_date; ?>';
    var chartNumber = '<?php echo $chartNumber; ?>';
    var form_extra_payment_filter = '<?php echo $form_extra_payment_filter; ?>';
    var form_extra_case_filter = '<?php echo $form_extra_case_filter; ?>';

    dataTableAjax = $.ajax({
        type: 'GET',
        url: "idempiere_pat_ledger_ajax.php?page=datatable&form_from_date="+form_from_date+"&form_to_date="+form_to_date+"&chartNumber="+chartNumber+"&form_extra_payment_filter="+form_extra_payment_filter+"&form_extra_case_filter="+form_extra_case_filter+"",
        success: function(result){

            $("#ledger_datatable").html(result);

            jQuery('[data-toggle="tooltip"]').tooltip({
               position: {
                  my: "right center",
                  at: "left-10 left"
               }
            });

            var table = jQuery('#ledger_result').DataTable({});

            jQuery('#ledger_result').on('click', 'td.details-control', function () {
              var tr = jQuery(this).closest('tr');
              var row = table.row(tr);

              if (row.child.isShown()) {
                  // This row is already open - close it
                  row.child.hide();
                  tr.removeClass('details');
              } else {
                  // Open this row
                  row.child(format(tr.data('child-value')), "row-details").show();
                  tr.addClass('details');
              }
            });
        }
    });

</script>
<?php } 

if (!$_REQUEST['form_refresh'] && !$_REQUEST['form_csvexport']) { ?>
    <div class='text'>
        <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
    </div><?php
} ?>

</form>

<div id="pageLoading" class="page-loading"><div class="inner-content">Please Wait...</div></div>

<script type="text/javascript">
    $(document).ready(function(){
        var tchartNumber = '<?php echo $chartNumber; ?>';
        var tform_extra_case_filter = '<?php echo $form_extra_case_filter; ?>';
        balancesAjax = $.ajax({
            type: 'GET',
            url: "idempiere_pat_ledger_ajax.php?page=balances&chartNumber="+tchartNumber+"&form_extra_case_filter="+tform_extra_case_filter+"",
            success: function(result){
                $("#balance_data").html(result);
                $("#printBalances").html(result);
            }
        });

        $('#med_printbutton').click(function(){
            $("#pageLoading").addClass('show');
            printAjax = $.ajax({
                type: 'GET',
                url: "idempiere_pat_ledger_ajax.php?page=print&form_from_date="+form_from_date+"&form_to_date="+form_to_date+"&chartNumber="+chartNumber+"&form_extra_payment_filter="+form_extra_payment_filter+"&form_extra_case_filter="+form_extra_case_filter+"",
                success: function(result){
                    $("#report_results").html(result);
                    $("#pageLoading").removeClass('show');
                    $('#printbutton').trigger( "click" );
                },
                error: function() {
                    $("#pageLoading").removeClass('show');
                }
            });
        });
    });
</script>

</body>
</html>
<?php

?>