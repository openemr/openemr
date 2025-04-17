<?php

/**
 * Batch Communication Tool for selecting/communicating with subsets of patients
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @link    http://www.open-emr.org
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
require_once("../globals.php");
require_once("$srcdir/registry.inc.php");
require_once("batchcom.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'batchcom')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("BatchCom")]);
    exit;
}

// menu arrays (done this way so it's easier to validate input on validate selections)
$process_choices = array(xl('Download CSV File'), xl('Send Emails'), xl('Phone call list'));
$gender_choices = array(xl('Any{{Gender}}'), xl('Male'), xl('Female'));
$hipaa_choices = array(xl('No'), xl('Yes'));
$sort_by_choices = array(xl('Zip Code') => 'patient_data.postal_code', xl('Last Name') => 'patient_data.lname', xl('Appointment Date') => 'last_appt');

// process form
if (!empty($_POST['form_action']) && ($_POST['form_action'] == 'process')) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    //validation uses the functions in batchcom.inc.php
    //validate dates
    if (!check_date_format($_POST['app_s'])) {
        $form_err .= xl('Date format for "appointment start" is not valid');
    }

    if (!check_date_format($_POST['app_e'])) {
        $form_err .= xl('Date format for "appointment end" is not valid');
    }

    if (!check_date_format($_POST['seen_since'])) {
        $form_err .= xl('Date format for "seen since" is not valid');
    }

    if (!check_date_format($_POST['seen_before'])) {
        $form_err .= xl('Date format for "seen before" is not valid');
    }

    // validate numbers
    if (!check_age($_POST['age_from'])) {
        $form_err .= xl('Age format for "age from" is not valid');
    }

    if (!check_age($_POST['age_upto'])) {
        $form_err .= xl('Age format for "age up to" is not valid');
    }

    // validate selections
    if (!check_select($_POST['gender'], $gender_choices)) {
        $form_err .= xl('Error in "Gender" selection');
    }

    if (!check_select($_POST['process_type'], $process_choices)) {
        $form_err .= xl('Error in "Process" selection');
    }

    if (!check_select($_POST['hipaa_choice'], $hipaa_choices)) {
        $form_err .= xl('Error in "HIPAA" selection');
    }

    if (!check_select($_POST['sort_by'], $sort_by_choices)) {
        $form_err .= xl('Error in "Sort By" selection');
    }

    //process sql
    if (!$form_err) {
        $sql = "select patient_data.*, cal_events.pc_eventDate as next_appt, cal_events.pc_startTime
                    as appt_start_time,cal_date.last_appt,forms.last_visit from patient_data
                    left outer join openemr_postcalendar_events as cal_events on patient_data.pid=cal_events.pc_pid
                    and curdate() < cal_events.pc_eventDate left outer join (select pc_pid,max(pc_eventDate)
                    as last_appt from openemr_postcalendar_events where curdate() >= pc_eventDate group by pc_pid )
                    as cal_date on cal_date.pc_pid=patient_data.pid left outer join (select pid,max(date)
                    as last_visit from forms where curdate() >= date group by pid)
                    as forms on forms.pid=patient_data.pid where 1=1";
        $params = array();

        //appointment dates
        if ($_POST['app_s'] != 0 and $_POST['app_s'] != '') {
            $sql .= " and cal_events.pc_eventDate >= ?";
            array_push($params, $_POST['app_s']);
        }

        if ($_POST['app_e'] != 0 and $_POST['app_e'] != '') {
            $sql .= " and cal_events.pc_endDate <= ?";
            array_push($params, $_POST['app_e']);
        }

        // encounter dates
        if ($_POST['seen_since'] != 0 and $_POST['seen_since'] != '') {
            $sql .= " and forms.date >= ?" ;
            array_push($params, $_POST['seen_since']);
        }

        if ($_POST['seen_before'] != 0 and $_POST['seen_before'] != '') {
            $sql .= " and forms.date <= ?" ;
            array_push($params, $_POST['seen_before']);
        }

        // age
        if ($_POST['age_from'] != 0 and $_POST['age_from'] != '') {
            $sql .= " and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 >= ?";
            array_push($params, $_POST['age_from']);
        }

        if ($_POST['age_upto'] != 0 and $_POST['age_upto'] != '') {
            $sql .= " and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 <= ?";
            array_push($params, $_POST['age_upto']);
        }

        // gender
        if ($_POST['gender'] != 'Any') {
            $sql .= " and patient_data.sex=?";
            array_push($params, $_POST['gender']);
        }

        // hipaa override
        if ($_POST['hipaa_choice'] != $hipaa_choices[0]) {
            $sql .= " and patient_data.hipaa_mail='YES' ";
        }

        switch ($_POST['process_type']) :
            case $choices[1]: // Email
                $sql .= " and patient_data.email IS NOT NULL ";
                break;
        endswitch;

        // sort by
        $sql .= ' ORDER BY ' . escape_identifier($_POST['sort_by'], array_values($sort_by_choices), true);
        // send query for results.
        //echo $sql;exit();
        $res = sqlStatement($sql, $params);

        if (sqlNumRows($res) == 0) {
            $form_err = xl('No results found, please try again.');
        } else {
            switch ($_POST['process_type']) :
                case $process_choices[0]: // CSV File
                    generate_csv($res);
                    exit();
                case $process_choices[1]: // Email
                    require_once('batchEmail.php');
                    exit();
                case $process_choices[2]: // Phone list
                    require_once('batchPhoneList.php');
                    exit();
            endswitch;
        }
    }
}

?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <title><?php echo xlt('BatchCom'); ?></title>
</head>
<body class="body_top container">
<header>
    <?php require_once("batch_navigation.php");?>
    <h1 class="text-center"><?php echo xlt('Batch Communication Tool')?></h1>
</header>
<main class="mx-4">
    <?php
    if (!empty($form_err)) {
        echo '<div class="alert alert-danger">' . xlt('The following errors occurred') . ': ' . text($form_err) . '</div>';
    }
    ?>
    <form name="select_form" method="post" action="">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
        <div class="row">
            <div class="col-md card p-3 m-1 form-group">
                <label for="process_type"><?php echo xlt("Process") . ":"; ?></label>
                <select name="process_type" class="form-control">
                    <?php
                    foreach ($process_choices as $choice) {
                        echo "<option>" . text($choice) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md card p-3 m-1 form-group">
                <label for="hipaa_choice"><?php echo xlt("Override HIPAA choice") . ":"; ?></label>
                <select name="hipaa_choice" class="form-control">
                    <?php
                    foreach ($hipaa_choices as $choice) {
                        echo "<option>" . text($choice) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md card p-3 m-1 form-group">
                <label for="sort_by"><?php echo xlt("Sort by"); ?></label>
                <select name="sort_by" class="form-control">
                    <?php
                    foreach ($sort_by_choices as $choice => $sorting_code) {
                        echo "<option value=\"$sorting_code\">" . text($choice) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md card p-3 m-1 form-group">
                <label for="gender"><?php echo xlt('Gender') ?>:</label>
                <select name="gender" class="form-control">
                    <?php
                    foreach ($gender_choices as $choice) {
                        echo "<option>" . text($choice) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md card p-3 m-1 form-group">
                <label for="age_from"><?php echo xlt("Age Range") . ":"; ?></label>
                <input name="age_from" size="2" type="num" class="form-control" placeholder="<?php echo xla("any"); ?>">
                <label for="age_upto" class="text-center"><?php echo xlt('to{{Range}}'); ?></label>
                <input name="age_upto" size="2" type="num" class="form-control" placeholder="<?php echo xla("any"); ?>">
            </div>
            <div class="col-md card p-3 m-1 form-group">
                <label for="app_s"><?php echo xlt('Appointment within') ?>:</label>
                    <input type="text" class="datepicker form-control" name="app_s" placeholder="<?php echo xla('any date'); ?>" />
                    <div class="text-center"><?php echo xlt('to{{Range}}'); ?></div>
                    <input type="text" class="datepicker form-control" name="app_e" placeholder="<?php echo xla('any date'); ?>" />
            </div>
            <!-- later gator    <br />Insurance: <SELECT multiple NAME="insurance" Rows="10" cols="20"></SELECT> -->
            <div class="col-md card p-3 m-1 form-group">
                <label for="app_s"><?php echo xlt('Seen within')?>:</label>
                    <input type="text" class="datepicker form-control" name="seen_since" placeholder="<?php echo xla('any date'); ?>" />
                    <div class="text-center"><?php echo xlt('to{{Range}}'); ?></div>
                    <input type="text" class="datepicker form-control" name="seen_before" placeholder="<?php echo xla('any date'); ?>" />
            </div>
        </div>
        <div class="email row form-group">
            <div class="col-md-6 offset-md-3 card p-3 m-1">
                <div class="col-md-6">
                    <label for="email_sender"><?php echo xlt('Email Sender'); ?>:</label>
                    <input class="form-control" type="text" name="email_sender" placeholder="your@email.email" />
                </div>

                <div class="col-md-6">
                    <label for="email_subject"><?php echo xlt('Email Subject'); ?>:</label>
                    <input class="form-control" type="text" name="email_subject" placeholder="<?php echo xla('From your clinic'); ?>" />
                </div>
                <div class="col-md-12">
                    <label for="email_subject"><?php echo xlt('Email Text, Usable Tag: ***NAME*** , i.e. Dear ***NAME***{{Do Not translate the ***NAME*** elements of this constant.}}'); ?>:</label>
                </div>
                <div class="col-md-12">
                    <textarea class="form-control" name="email_body" id="" cols="40" rows="8"></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md form-group">
                <input type="hidden" name="form_action" value="process" />
                <button type="submit" name="submit" class="btn btn-secondary btn-save">
                    <?php echo xla("Process"); ?>
                </button>
            </div>
        </div>
    </form>
</main>
</body>

<script>
    (function() {
        var email = document.querySelector('.email');
        var process = document.querySelector('select[name="process_type"]');
        function hideEmail() {
            if (process.value !== <?php echo js_escape($process_choices[1]); ?>) { email.style.display = 'none'; } else { email.style.display = ''; }
        }
        process.addEventListener('change', hideEmail);
        hideEmail();
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    })();
</script>
</html>
