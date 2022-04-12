<?php

/**
 * add_transaction is a misnomer, as this script will now also edit
 * existing transactions.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/amc.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

// This can come from the URL if it's an Add.
$title   = empty($_REQUEST['title']) ? 'LBTref' : $_REQUEST['title'];
$form_id = $title;

// Plugin support.
$fname = $GLOBALS['OE_SITE_DIR'] . "/LBF/" . convert_safe_file_dir_name($form_id) . ".plugin.php";
if (file_exists($fname)) {
    include_once($fname);
}

$transid = empty($_REQUEST['transid']) ? 0 : $_REQUEST['transid'] + 0;
$mode    = empty($_POST['mode' ]) ? '' : $_POST['mode' ];
// $inmode    = $_GET['inmode'];
$body_onload_code = "";

// Load array of properties for this layout and its groups.
$grparr = array();
getLayoutProperties($form_id, $grparr);

if ($mode) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $sets = "title = ?, user = ?, groupname = ?, authorized = ?, date = NOW()";
    $sqlBindArray = array($form_id, $_SESSION['authUser'], $_SESSION['authProvider'], $userauthorized);

    if ($transid) {
        array_push($sqlBindArray, $transid);
        sqlStatement("UPDATE transactions SET $sets WHERE id = ?", $sqlBindArray);
    } else {
        array_push($sqlBindArray, $pid);
        $sets .= ", pid = ?";
        $newid = sqlInsert("INSERT INTO transactions SET $sets", $sqlBindArray);
    }

    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = ? AND uor > 0 AND field_id != '' " .
    "ORDER BY group_id, seq", array($form_id));

    while ($frow = sqlFetchArray($fres)) {
        $data_type = $frow['data_type'];
        $field_id  = $frow['field_id'];
        $value = get_layout_form_value($frow);

        if ($transid) { // existing form
            if ($value === '') {
                $query = "DELETE FROM lbt_data WHERE " .
                "form_id = ? AND field_id = ?";
                sqlStatement($query, array($transid, $field_id));
            } else {
                $query = "REPLACE INTO lbt_data SET field_value = ?, " .
                "form_id = ?, field_id = ?";
                sqlStatement($query, array($value, $transid, $field_id));
            }
        } else { // new form
            if ($value !== '') {
                sqlStatement(
                    "INSERT INTO lbt_data " .
                    "( form_id, field_id, field_value ) VALUES ( ?, ?, ? )",
                    array($newid, $field_id, $value)
                );
            }
        }
    }

    if (!$transid) {
        $transid = $newid;
    }

  // Set the AMC sent records flag
    if (!(empty($_POST['send_sum_flag']))) {
        // add the sent records flag
        processAmcCall('send_sum_amc', true, 'add', $pid, 'transactions', $transid);
        if (!(empty($_POST['send_sum_elec_flag']))) {
            processAmcCall('send_sum_elec_amc', true, 'add', $pid, 'transactions', $transid);
        } else {
            processAmcCall('send_sum_elec_amc', true, 'remove', $pid, 'transactions', $transid);
        }

        if (!(empty($_POST['send_sum_amc_confirmed']))) {
            processAmcCall('send_sum_amc_confirmed', true, 'add', $pid, 'transactions', $transid);
        } else {
            processAmcCall('send_sum_amc_confirmed', true, 'remove', $pid, 'transactions', $transid);
        }
    } else {
        // remove the sent records flags
        processAmcCall('send_sum_amc', true, 'remove', $pid, 'transactions', $transid);
        processAmcCall('send_sum_elec_amc', true, 'remove', $pid, 'transactions', $transid);
        processAmcCall('send_sum_amc_confirmed', true, 'remove', $pid, 'transactions', $transid);
    }

    $body_onload_code = "javascript:location.href='transactions.php';";
}

$CPR = 4; // cells per row

function end_cell()
{
    global $item_count, $cell_count;
    if ($item_count > 0) {
        echo "</td>";
        $item_count = 0;
    }
}

function end_row()
{
    global $cell_count, $CPR;
    end_cell();
    if ($cell_count > 0) {
        for (; $cell_count < $CPR; ++$cell_count) {
            echo "<td></td>";
        }

        echo "</tr>\n";
        $cell_count = 0;
    }
}

function end_group()
{
    global $last_group;
    if (strlen($last_group) > 0) {
        end_row();
        echo " </table>\n";
        echo "</div>\n";
    }
}

// If we are editing a transaction, get its ID and data.
$trow = $transid ? getTransById($transid) : array();
?>
<html>
<head>

<title><?php echo xlt('Add/Edit Patient Transaction'); ?></title>

<?php Header::setupHeader(['common','datetime-picker','select2']); ?>

<?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

<script>
$(function () {
  if(window.tabbify){
    tabbify();
  }
  if (window.checkSkipConditions) {
    checkSkipConditions();
  }
});

var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

$(function () {
  $("#send_sum_flag").click(function() {
    if ( $('#send_sum_flag').prop('checked') ) {
      // Enable the send_sum_elec_flag checkbox
      $("#send_sum_elec_flag").removeAttr("disabled");
      $("#send_sum_amc_confirmed").removeAttr("disabled");
    }
    else {
      //Disable the send_sum_elec_flag checkbox (also uncheck it if applicable)
      $("#send_sum_elec_flag").attr("disabled", true);
      $("#send_sum_elec_flag").prop("checked", false);
      $("#send_sum_amc_confirmed").attr("disabled", true);
      $("#send_sum_amc_confirmed").prop("checked", false);
    }
  });

  $(".select-dropdown").select2({
    theme: "bootstrap4",
    <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
  });
  if (typeof error !== 'undefined') {
    if (error) {
        alertMsg(error);
    }
  }

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = false; ?>
    <?php $datetimepicker_maxDate = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datetimepicker').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = false; ?>
    <?php $datetimepicker_maxDate = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datepicker-past').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = false; ?>
    <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datetimepicker-past').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = false; ?>
    <?php $datetimepicker_maxDate = '+1970/01/01'; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datepicker-future').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = '-1970/01/01'; ?>
    <?php $datetimepicker_maxDate = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
  $('.datetimepicker-future').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php $datetimepicker_minDate = '-1970/01/01'; ?>
    <?php $datetimepicker_maxDate = false; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
});

function titleChanged() {
 var sel = document.forms[0].title;
 // Layouts must not interfere with each other. Reload the document in Add mode.
 top.restoreSession();
 location.href = 'add_transaction.php?title=' + encodeURIComponent(sel.value);
 return true;
}

function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

// The ID of the input element to receive a found code.
var current_sel_name = '';

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
 var frc = document.forms[0][current_sel_name];
 var s = frc.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 frc.value = s;
}

// This invokes the find-code popup.
function sel_related(e) {
    current_sel_name = e.name;
    dlgopen('../encounter/find_code_popup.php<?php
    if ($GLOBALS['ippf_specific']) {
        echo '?codetype=REF';
    } ?>', '_blank', 500, 400);
}

// Process click on $view link.
function deleteme() {
// onclick='return deleteme()'
 dlgopen('../deleter.php?transaction=' + <?php echo js_url($transid); ?> + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450);
 return false;
}

// Called by the deleteme.php window on a successful delete.
function imdeleted() {
 top.restoreSession();
 location.href = 'transaction/transactions.php';
}

// Compute the length of a string without leading and trailing spaces.
function trimlen(s) {
 var i = 0;
 var j = s.length - 1;
 for (; i <= j && s.charAt(i) == ' '; ++i);
 for (; i <= j && s.charAt(j) == ' '; --j);
 if (i > j) return 0;
 return j + 1 - i;
}

// Validation logic for form submission.
function validate(f) {
 var errCount = 0;
 var errMsgs = new Array();

    <?php generate_layout_validation($form_id); ?>

 var msg = "";
 msg += <?php echo xlj('The following fields are required'); ?> + ":\n\n";
 for ( var i = 0; i < errMsgs.length; i++ ) {
    msg += errMsgs[i] + "\n";
 }
 msg += "\n" + <?php echo xlj('Please fill them in before continuing.'); ?>;

 if ( errMsgs.length > 0 ) {
    alert(msg);
 }

 return errMsgs.length < 1;
}

function submitme() {
 var f = document.forms['new_transaction'];
 if (validate(f)) {
  top.restoreSession();
  f.submit();
 }
}

<?php if (function_exists($form_id . '_javascript')) {
    call_user_func($form_id . '_javascript');
} ?>

</script>

<style>
.form-control {
    width: auto;
    display: inline;
    height: auto;
}
div.tab {
    height: auto;
    width: auto;
}
</style>
<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Add/Edit Patient Transaction'),
    'include_patient_name' => true,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "back",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "transactions.php",//only for actions - reset, link and back
    'show_help_icon' => true,
    'help_file_name' => "add_edit_transactions_dashboard_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>

</head>
<body onload="<?php echo $body_onload_code; ?>" >
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?> mt-3">
        <form name='new_transaction' method='post' action='add_transaction.php?transid=<?php echo attr_url($transid); ?>' onsubmit='return validate(this)'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type='hidden' name='mode' value='add' />
            <div class="row">
                <div class="col-sm-12">
                    <?php require_once("$include_root/patient_file/summary/dashboard_header.php"); ?>
                </div>
                <br />
                <br />
                <div class="col-sm-12">
                    <div class="btn-group">
                        <a href="#" class="btn btn-primary btn-save" onclick="submitme();">
                            <?php echo xlt('Save'); ?>
                        </a>
                        <a href="transactions.php" class="btn btn-secondary btn-cancel" onclick="top.restoreSession()">
                            <?php echo xlt('Cancel'); ?>
                        </a>
                    </div>
                </div>
                <div class="col-sm-12 mt-3">
                    <fieldset>
                        <legend><?php echo xlt('Select Transaction Type'); ?></legend>
                        <div class="forms col-sm-7">
                            <label class="control-label" for="title"><?php echo xlt('Transaction Type'); ?>:</label>
                            <?php
                            $ttres = sqlStatement("SELECT grp_form_id, grp_title " .
                              "FROM layout_group_properties WHERE " .
                              "grp_form_id LIKE 'LBT%' AND grp_group_id = '' ORDER BY grp_seq, grp_title");
                            echo "<select name='title' id='title' class='form-control' onchange='titleChanged()'>\n";
                            while ($ttrow = sqlFetchArray($ttres)) {
                                $thisid = $ttrow['grp_form_id'];
                                echo "<option value='" . attr($thisid) . "'";
                                if ($thisid == $form_id) {
                                    echo ' selected';
                                }
                                echo ">" . text($ttrow['grp_title']) . "</option>\n";
                            }
                            echo "</select>\n";
                            ?>
                        </div>
                        <div class="forms col-sm-5">
                            <?php
                            if ($GLOBALS['enable_amc_prompting'] && 'LBTref' == $form_id) { ?>
                                <div class='oe-pull-away' style='margin-right:25px;border-style:solid;border-width:1px;'>
                                    <div style='margin:5px 5px 5px 5px;'>

                                        <?php // Display the send records checkboxes (AMC prompting)
                                            $itemAMC = amcCollect("send_sum_amc", $pid, 'transactions', $transid);
                                            $itemAMC_elec = amcCollect("send_sum_elec_amc", $pid, 'transactions', $transid);
                                            $itemAMC_confirmed = amcCollect("send_sum_amc_confirmed", $pid, 'transactions', $transid);
                                        ?>

                                        <?php if (!(empty($itemAMC))) { ?>
                                            <input type="checkbox" id="send_sum_flag" name="send_sum_flag" checked>
                                        <?php } else { ?>
                                            <input type="checkbox" id="send_sum_flag" name="send_sum_flag">
                                        <?php } ?>

                                        <span class="text"><?php echo xlt('Sent Summary of Care?') ?></span><br />

                                        <?php if (!(empty($itemAMC)) && !(empty($itemAMC_elec))) { ?>
                                            &nbsp;&nbsp;<input type="checkbox" id="send_sum_elec_flag" name="send_sum_elec_flag" checked>
                                        <?php } elseif (!(empty($itemAMC))) { ?>
                                            &nbsp;&nbsp;<input type="checkbox" id="send_sum_elec_flag" name="send_sum_elec_flag">
                                        <?php } else { ?>
                                            &nbsp;&nbsp;<input type="checkbox" id="send_sum_elec_flag" name="send_sum_elec_flag" disabled>
                                        <?php } ?>
                                        <span class="text"><?php echo xlt('Sent Summary of Care Electronically?') ?><br />

                                        <?php if (!(empty($itemAMC)) && !(empty($itemAMC_confirmed))) { ?>
                                            &nbsp;&nbsp;<input type="checkbox" id="send_sum_amc_confirmed" name="send_sum_amc_confirmed" checked>
                                        <?php } elseif (!(empty($itemAMC))) { ?>
                                            &nbsp;&nbsp;<input type="checkbox" id="send_sum_amc_confirmed" name="send_sum_amc_confirmed">
                                        <?php } else { ?>
                                            &nbsp;&nbsp;<input type="checkbox" id="send_sum_amc_confirmed" name="send_sum_amc_confirmed" disabled>
                                        <?php } ?>
                                        <span class="text"><?php echo xlt('Confirmed Recipient Received Summary of Care?') ?>
                                    </div>
                                </div>
                                <?php
                            } ?>
                        </div>
                    </fieldset>
                </div>
            </div>

            <div id='referdiv'>
                <div id="DEM">
                    <ul class="tabNav">
                        <?php
                        $fres = sqlStatement("SELECT * FROM layout_options " .
                          "WHERE form_id = ? AND uor > 0 " .
                          "ORDER BY group_id, seq", array($form_id));
                        $last_group = '';

                        while ($frow = sqlFetchArray($fres)) {
                            $this_group = $frow['group_id'];
                            // Handle a data category (group) change.
                            if (strcmp($this_group, $last_group) != 0) {
                                $group_seq  = substr($this_group, 0, 1);
                                $group_name = $grparr[$this_group]['grp_title'];
                                $last_group = $this_group;
                                if ($group_seq == 1) {
                                    echo "<li class='current'>";
                                } else {
                                    echo "<li class=''>";
                                }
                                echo "<a href='#' id='div_" . attr($group_seq) . "'>" .
                                text(xl_layout_label($group_name)) . "</a></li>";
                            }
                        }
                        ?>
                    </ul>
                    <div class="tabContainer">
                        <?php
                        $fres = sqlStatement("SELECT * FROM layout_options " .
                          "WHERE form_id = ? AND uor > 0 " .
                          "ORDER BY group_id, seq", array($form_id));

                        $last_group = '';
                        $cell_count = 0;
                        $item_count = 0;
                        $display_style = 'block';
                        $condition_str = '';

                        while ($frow = sqlFetchArray($fres)) {
                            $this_group = $frow['group_id'];
                            $titlecols  = $frow['titlecols'];
                            $datacols   = $frow['datacols'];
                            $data_type  = $frow['data_type'];
                            $field_id   = $frow['field_id'];
                            $list_id    = $frow['list_id'];

                            // Accumulate action conditions into a JSON expression for the browser side.
                            accumActionConditions($frow, $condition_str);

                            $currvalue  = '';
                            if (isset($trow[$field_id])) {
                                $currvalue = $trow[$field_id];
                            }

                            // Handle special-case default values.
                            if (!$currvalue && !$transid && $form_id == 'LBTref') {
                                if ($field_id == 'refer_date') {
                                    $currvalue = date('Y-m-d');
                                } elseif ($field_id == 'body' && $transid > 0) {
                                     $tmp = sqlQuery("SELECT reason FROM form_encounter WHERE " .
                                      "pid = ? ORDER BY date DESC LIMIT 1", array($pid));
                                    if (!empty($tmp)) {
                                        $currvalue = $tmp['reason'];
                                    }
                                }
                            }

                            // Handle a data category (group) change.
                            if (strcmp($this_group, $last_group) != 0) {
                                end_group();
                                $group_seq  = substr($this_group, 0, 1);
                                $group_name = $grparr[$this_group]['grp_title'];
                                $last_group = $this_group;
                                if ($group_seq == 1) {
                                    echo "<div class='tab current' id='div_" . attr($group_seq) . "'>";
                                } else {
                                    echo "<div class='tab' id='div_" . attr($group_seq) . "'>";
                                }

                                echo " <table border='0' cellpadding='0'>\n";
                                $display_style = 'none';
                            }

                            // Handle starting of a new row.
                            if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
                                end_row();
                                echo " <tr>";
                            }

                            if ($item_count == 0 && $titlecols == 0) {
                                $titlecols = 1;
                            }

                            // Handle starting of a new label cell.
                            if ($titlecols > 0) {
                                end_cell();
                                echo "<td width='70' valign='top' colspan='" . attr($titlecols) . "'";
                                echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
                                if ($cell_count == 2) {
                                    echo " style='padding-left:10pt'";
                                }

                                // This ID is used by action conditions.
                                echo " id='label_id_" . attr($field_id) . "'";
                                echo ">";
                                $cell_count += $titlecols;
                            }

                            ++$item_count;

                            echo "<b>";

                            // Modified 6-09 by BM - Translate if applicable
                            if ($frow['title']) {
                                echo (text(xl_layout_label($frow['title'])) . ":");
                            } else {
                                echo "&nbsp;";
                            }

                             echo "</b>";

                            // Handle starting of a new data cell.
                            if ($datacols > 0) {
                                end_cell();
                                echo "<td valign='top' colspan='" . attr($datacols) . "' class='text'";
                                // This ID is used by action conditions.
                                echo " id='value_id_" . attr($field_id) . "'";
                                if ($cell_count > 0) {
                                    echo " style='padding-left:5pt'";
                                }

                                echo ">";
                                $cell_count += $datacols;
                            }

                            ++$item_count;
                            generate_form_field($frow, $currvalue);
                            echo "</div>";
                        }

                        end_group();
                        ?>
                    </div><!-- end of tabContainer div -->
                </div><!-- end of DEM div -->
            </div><!-- end of referdiv -->
        </form>

        <!-- include support for the list-add selectbox feature -->
        <?php include $GLOBALS['fileroot'] . "/library/options_listadd.inc"; ?>
    </div> <!--end of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>

<script>

// Array of action conditions for the checkSkipConditions() function.
var skipArray = [
<?php echo $condition_str; ?>
];

<?php echo $date_init; ?>
// titleChanged();
<?php
if (function_exists($form_id . '_javascript_onload')) {
    call_user_func($form_id . '_javascript_onload');
}
?>

</script>

</html>
