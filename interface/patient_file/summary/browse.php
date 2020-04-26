<?php

/**
 * Patient selector for insurance gui
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

//the maximum number of patient records to display:
$M = 100;

$browsenum = (is_numeric($_REQUEST['browsenum'])) ? $_REQUEST['browsenum'] : 1;
?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'opener']); ?>

    <script>
        $(function () {
            $('[name="findBy"').on('change', function () {
                if($(this).val() === 'DOB'){
                    $('#searchparm').datetimepicker({
                        <?php $datetimepicker_timepicker = false; ?>
                        <?php $datetimepicker_showseconds = false; ?>
                        <?php $datetimepicker_formatInput = true; ?>
                        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                    });
                } else {
                    $('#searchparm').datetimepicker("destroy");
                }
            });
        });
    </script>

</head>

<body class="body_top">

<a href="javascript:window.close();"><span class="title"><?php echo xlt('Browse for Record'); ?></span><span class="back"><?php echo text($tback);?></span></a>

<form border='0' method='post' name="find_patient" action="browse.php?browsenum=<?php echo attr_url($browsenum); ?>">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div class="form-row">
<div class="col-auto">
    <input type='entry' size='10' class='form-control form-control-sm' name='patient' id='searchparm' />
</div>
<div class="col-auto">
    <select name="findBy" size='1' class="form-control form-control-sm">
     <option value="ID"><?php echo xlt('ID'); ?></option>
     <option value="Last" selected><?php echo xlt('Last Name'); ?></option>
     <option value="SSN"><?php echo xlt('SSN'); ?></option>
     <option value="DOB"><?php echo xlt('DOB'); ?></option>
    </select>
</div>
<div class="col-auto">
    <a href="javascript:document.find_patient.submit();" role="button" class="btn btn-primary btn-sm"><?php echo xlt('Find'); ?></a>
</div>
<div class="col-auto">
    <a href="javascript:auto_populate_employer_address();" role="button" class="btn btn-primary btn-sm"><?php echo xlt('Copy Values'); ?></a>
</div>
</div>
</form>

<?php
if (isset($_GET['set_pid'])) {
    if (!isset($_POST['insurance'])) {
        $insurance = "primary";
    } else {
        $insurance = $_POST['insurance'];
    }

    $result = getPatientData($_GET['set_pid']);
  // $result2 = getEmployerData($_GET['set_pid']); // not used!
    $result3 = getInsuranceData($_GET['set_pid'], $insurance);
    ?>

<script>
<!--
function auto_populate_employer_address(){
 var df = opener.document.demographics_form;
 df.i<?php echo attr($browsenum);?>subscriber_fname.value=<?php echo js_escape($result3['subscriber_fname']);?>;
 df.i<?php echo attr($browsenum);?>subscriber_mname.value=<?php echo js_escape($result3['subscriber_mname']);?>;
 df.i<?php echo attr($browsenum);?>subscriber_lname.value=<?php echo js_escape($result3['subscriber_lname']);?>;
 df.i<?php echo attr($browsenum);?>subscriber_street.value=<?php echo js_escape($result3['subscriber_street']);?>;
 df.i<?php echo attr($browsenum);?>subscriber_city.value=<?php echo js_escape($result3['subscriber_city']);?>;
 df.form_i<?php echo attr($browsenum);?>subscriber_state.value=<?php echo js_escape($result3['subscriber_state']);?>;
 df.i<?php echo attr($browsenum);?>subscriber_postal_code.value=<?php echo js_escape($result3['subscriber_postal_code']);?>;
 if (df.form_i<?php echo attr($browsenum);?>subscriber_country) // in case this is commented out
  df.form_i<?php echo attr($browsenum);?>subscriber_country.value=<?php echo js_escape($result3['subscriber_country']);?>;
 df.i<?php echo attr($browsenum);?>subscriber_phone.value=<?php echo js_escape($result3['subscriber_phone']);?>;
 df.i<?php echo attr($browsenum);?>subscriber_DOB.value=<?php echo js_escape(oeFormatShortDate($result3['subscriber_DOB']));?>;
 df.i<?php echo attr($browsenum);?>subscriber_ss.value=<?php echo js_escape($result3['subscriber_ss']);?>;
 df.form_i<?php echo attr($browsenum);?>subscriber_sex.value=<?php echo js_escape($result3['subscriber_sex']);?>;

 df.i<?php echo attr($browsenum);?>plan_name.value=<?php echo js_escape($result3['plan_name']);?>;
 df.i<?php echo attr($browsenum);?>policy_number.value=<?php echo js_escape($result3['policy_number']);?>;
 df.i<?php echo attr($browsenum);?>group_number.value=<?php echo js_escape($result3['group_number']);?>;
 df.i<?php echo attr($browsenum);?>provider.value=<?php echo js_escape($result3['provider']);?>;

 // One clinic comments out the subscriber employer stuff.
 if (df.i<?php echo attr($browsenum);?>subscriber_employer) {
  df.i<?php echo attr($browsenum);?>subscriber_employer.value=<?php echo js_escape($result3['subscriber_employer']);?>;
  df.i<?php echo attr($browsenum);?>subscriber_employer_street.value=<?php echo js_escape($result3['subscriber_employer_street']);?>;
  df.i<?php echo attr($browsenum);?>subscriber_employer_city.value=<?php echo js_escape($result3['subscriber_employer_city']);?>;
  df.form_i<?php echo attr($browsenum);?>subscriber_employer_state.value=<?php echo js_escape($result3['subscriber_employer_state']);?>;
  df.i<?php echo attr($browsenum);?>subscriber_employer_postal_code.value=<?php echo js_escape($result3['subscriber_employer_postal_code']);?>;
  df.form_i<?php echo attr($browsenum);?>subscriber_employer_country.value=<?php echo js_escape($result3['subscriber_employer_country']);?>;
 }
}
//-->
</script>

<form method="post" name="insurance_form" action="browse.php?browsenum=<?php echo attr_url($browsenum); ?>&set_pid=<?php echo attr_url($_GET['set_pid']); ?>">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type="hidden" name="browsenum" value="<?php echo attr($browsenum); ?>">
<span class='bold'> <?php echo xlt('Insurance Provider'); ?>:</span>
<select name='insurance' onchange="javascript:document.insurance_form.submit();">
    <option value="primary" <?php echo ($insurance == "primary") ? "selected" : ""?>><?php echo xlt('Primary'); ?></option>
    <option value="secondary" <?php echo ($insurance == "secondary") ? "selected" : ""?>><?php echo xlt('Secondary'); ?></option>
    <option value="tertiary" <?php echo ($insurance == "tertiary") ? "selected" : ""?>><?php echo xlt('Tertiary'); ?></option>
</select>

</form>
<table class="table">
<tr>
<td><span class='text'><?php echo xlt('First Name'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_fname']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Middle Name'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_mname']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Last Name'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_lname']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Address'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_street']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('City'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_city']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('State'); ?>:</span></td>
<td><span class='text'>
    <?php
  //Modified 7/2009 by BM to incorporate data types
    echo generate_display_field(array('data_type' => $GLOBALS['state_data_type'],'list_id' => $GLOBALS['state_list']), $result3['subscriber_state']);
    ?>
</span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Zip Code'); ?>:</span></td>
<td><span class='text'><?php echo htmlspecialchars($result3['subscriber_postal_code']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Country'); ?>:</span></td>
<td><span class='text'>
    <?php
  //Modified 7/2009 by BM to incorporate data types
    echo generate_display_field(array('data_type' => $GLOBALS['country_data_type'],'list_id' => $GLOBALS['country_list']), $result3['subscriber_country']);
    ?>
</span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Phone'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_phone']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('DOB'); ?>:</span></td>
<td><span class='text'><?php echo text(oeFormatShortDate($result3['subscriber_DOB']));?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('SS'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_ss']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Primary Insurance Provider'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['provider_name']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Plan Name'); ?>:</span>
</td><td><span class='text'><?php echo text($result3['plan_name']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Group Number'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['group_number']);?></span></td>
</tr>
<tr>
<tr>
<td><span class='text'><?php echo xlt('Policy Number'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['policy_number']);?></span></td>
</tr>

    <?php if (empty($GLOBALS['omit_employers'])) { ?>
<tr>
<td><span class='text'><?php echo xlt('Subscriber Employer'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_employer']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Subscriber Employer Address'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_employer_street']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Subscriber Employer Zip Code'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_employer_postal_code']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Subscriber Employer City'); ?>:</span></td>
<td><span class='text'><?php echo text($result3['subscriber_employer_city']);?></span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Subscriber Employer State'); ?>:</span></td>
<td><span class='text'>
        <?php
      //Modified 7/2009 by BM to incorporate data types
        echo generate_display_field(array('data_type' => $GLOBALS['state_data_type'],'list_id' => $GLOBALS['state_list']), $result3['subscriber_employer_state']);
        ?>
</span></td>
</tr>
<tr>
<td><span class='text'><?php echo xlt('Subscriber Employer Country'); ?>:</span></td>
<td><span class='text'>
        <?php
       //Modified 7/2009 by BM to incorporate data types
        echo generate_display_field(array('data_type' => $GLOBALS['country_data_type'],'list_id' => $GLOBALS['country_list']), $result3['subscriber_employer_country']);
        ?>
</span></td>
</tr>

    <?php } ?>

<tr>
<td><span class='text'><?php echo xlt('Subscriber Sex'); ?>:</span></td>
<td><span class='text'><?php echo generate_display_field(array('data_type' => '1','list_id' => 'sex'), $result3['subscriber_sex']); ?></span></td>
</tr>
</table>

<br />
<a href="javascript:auto_populate_employer_address();" class='btn btn-primary btn-sm'><?php echo xlt('Copy Values'); ?></a>

    <?php
} else {
    ?>

<table class="table">
<tr>
<thead>
    <th>
        <span class='bold'><?php echo xlt('Name'); ?></span>
    </th>
    <th>
        <span class='bold'><?php echo xlt('SS'); ?></span>
    </th>
    <th>
        <span class='bold'><?php echo xlt('DOB'); ?></span>
    </th>
    <th>
        <span class='bold'><?php echo xlt('ID'); ?></span>
    </th>
</thead>
</tr>
    <?php

    $count = 0;
    $total = 0;

    $findby = $_POST['findBy'];
    $patient = $_POST['patient'];
    if ($findby == "Last" && $result = getPatientLnames("$patient", "*")) {
        foreach ($result as $iter) {
            if ($total >= $M) {
                break;
            }

            print "<tr><td><a class='text' target='_top' href='browse.php?browsenum=" .
                    attr_url($browsenum) . "&set_pid=" .
                    attr_url($iter["pid"]) . "'>" .
                    text($iter["lname"] . ", " . $iter["fname"]) .
                    "</td></a>\n";
            print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                    attr_url($browsenum) . "&set_pid=" .
                    attr_url($iter["pid"]) . "'>" .
                    text($iter["ss"]) . "</a></td>";
            if ($iter["DOB"] != "0000-00-00 00:00:00") {
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                        attr_url($browsenum) . "&set_pid=" .
                        attr_url($iter["pid"]) . "'>" .
                        text(oeFormatShortDate($iter["DOB"])) . "</a></td>";
            } else {
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                        attr_url($browsenum) . "&set_pid=" .
                        attr_url($iter["pid"]) . "'>&nbsp;</a></td>";
            }

            print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                    attr_url($browsenum) . "&set_pid=" .
                    attr_url($iter["pid"]) . "'>" .
                    text($iter["pubpid"]) . "</a></td>";

            $total++;
        }
    }

    if ($findby == "ID" && $result = getPatientId("$patient", "*")) {
        foreach ($result as $iter) {
            if ($total >= $M) {
                break;
            }

            print "<tr><td><a class='text' target='_top' href='browse.php?browsenum=" .
                    attr_url($browsenum) . "&set_pid=" .
                    attr_url($iter["pid"]) . "'>" .
                    text($iter["lname"] . ", " . $iter["fname"]) .
                    "</td></a>\n";
            print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                    attr_url($browsenum) . "&set_pid=" .
                    attr_url($iter["pid"]) . "'>" .
                    text($iter["ss"]) . "</a></td>";
            if ($iter["DOB"] != "0000-00-00 00:00:00") {
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                        attr_url($browsenum) . "&set_pid=" .
                        attr_url($iter["pid"]) . "'>" .
                        text(oeFormatShortDate($iter["DOB"])) . "</a></td>";
            } else {
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                        attr_url($browsenum) . "&set_pid=" .
                        attr_url($iter["pid"]) . "'>&nbsp;</a></td>";
            }

            print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                    attr_url($browsenum) . "&set_pid=" .
                    attr_url($iter["pid"]) . "'>" .
                    text($iter["pubpid"]) . "</a></td>";

            $total++;
        }
    }

    if ($findby == "DOB" && $result = getPatientDOB(DateToYYYYMMDD($patient), "*")) {
        foreach ($result as $iter) {
            if ($total >= $M) {
                break;
            }

                print "<tr><td><a class='text' target='_top' href='browse.php?browsenum=" .
                        attr_url($browsenum) . "&set_pid=" .
                        attr_url($iter["pid"]) . "'>" .
                        text($iter["lname"] . ", " . $iter["fname"]) .
                        "</td></a>\n";
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                        attr_url($browsenum) . "&set_pid=" .
                        attr_url($iter["pid"]) . "'>" .
                        text($iter["ss"]) . "</a></td>";
            if ($iter["DOB"] != "0000-00-00 00:00:00") {
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                attr_url($browsenum) . "&set_pid=" .
                attr_url($iter["pid"]) . "'>" .
                text(oeFormatShortDate($iter["DOB"])) . "</a></td>";
            } else {
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                attr_url($browsenum) . "&set_pid=" .
                attr_url($iter["pid"]) . "'>&nbsp;</a></td>";
            }

                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                    attr_url($browsenum) . "&set_pid=" .
                    attr_url($iter["pid"]) . "'>" .
                    text($iter["pubpid"]) . "</a></td>";

            $total++;
        }
    }

    if ($findby == "SSN" && $result = getPatientSSN("$patient", "*")) {
        foreach ($result as $iter) {
            if ($total >= $M) {
                break;
            }

                print "<tr><td><a class='text' target='_top' href='browse.php?browsenum=" .
                        attr_url($browsenum) . "&set_pid=" .
                        attr_url($iter["pid"]) . "'>" .
                        text($iter["lname"] . ", " . $iter["fname"]) .
                        "</td></a>\n";
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                        attr_url($browsenum) . "&set_pid=" .
                        attr_url($iter["pid"]) . "'>" .
                        text($iter["ss"]) . "</a></td>";
            if ($iter["DOB"] != "0000-00-00 00:00:00") {
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                attr_url($browsenum) . "&set_pid=" .
                attr_url($iter["pid"]) . "'>" .
                text(oeFormatShortDate($iter["DOB"])) . "</a></td>";
            } else {
                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                attr_url($browsenum) . "&set_pid=" .
                attr_url($iter["pid"]) . "'>&nbsp;</a></td>";
            }

                print "<td><a class='text' target='_top' href='browse.php?browsenum=" .
                    attr_url($browsenum) . "&set_pid=" .
                    attr_url($iter["pid"]) . "'>" .
                    text($iter["pubpid"]) . "</a></td>";

            $total++;
        }
    }
    ?>
</table>
    <?php
}
?>
</body>
</html>
