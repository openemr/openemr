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
require_once("$srcdir/registry.inc");
require_once("../../library/acl.inc");
require_once("batchcom.inc.php");

use OpenEMR\Core\Header;

if (!acl_check('admin', 'batchcom')) {
    echo "<html>\n<body>\n<h1>";
    echo xlt('You are not authorized for this.');
    echo "</h1>\n</body>\n</html>";
    exit();
}

// menu arrays (done this way so it's easier to validate input on validate selections)
$process_choices = array(xlt('Download CSV File'), xlt('Send Emails'), xlt('Phone call list'));
$gender_choices = array(xlt('Any'), xlt('Male'), xlt('Female'));
$hipaa_choices = array(xlt('No'), xlt('Yes'));
$sort_by_choices = array(xlt('Zip Code')=>'patient_data.postal_code', xlt('Last Name')=>'patient_data.lname', xlt('Appointment Date')=>'last_ap' );

// process form
if ($_POST['form_action']=='process') {
    //validation uses the functions in batchcom.inc.php
    //validate dates
    if (!check_date_format($_POST['app_s'])) {
        $form_err.= xlt('Date format for "appointment start" is not valid');
    }
    if (!check_date_format($_POST['app_e'])) {
        $form_err .= xlt('Date format for "appointment end" is not valid');
    }
    if (!check_date_format($_POST['seen_since'])) {
        $form_err .= xlt('Date format for "seen since" is not valid');
    }
    if (!check_date_format($_POST['seen_before'])) {
        $form_err .= xlt('Date format for "seen before" is not valid');
    }
    // validate numbers
    if (!check_age($_POST['age_from'])) {
        $form_err .= xlt('Age format for "age from" is not valid');
    }
    if (!check_age($_POST['age_upto'])) {
        $form_err .= xlt('Age format for "age up to" is not valid');
    }
    // validate selections
    if (!check_select($_POST['gender'], $gender_choices)) {
        $form_err .= xlt('Error in "Gender" selection');
    }
    if (!check_select($_POST['process_type'], $process_choices)) {
        $form_err .= xlt('Error in "Process" selection');
    }
    if (!check_select($_POST['hipaa_choice'], $hipaa_choices)) {
        $form_err .= xlt('Error in "HIPAA" selection');
    }
    if (!check_select($_POST['sort_by'], $sort_by_choices)) {
        $form_err.=xlt('Error in "Sort By" selection');
    }
    // validates and or
    if (!check_and_or($_POST['and_or_gender'])) {
        $form_err .= xlt('Error in and/or option');
    }
    if (!check_and_or($_POST['and_or_app_within'])) {
        $form_err .= xlt('Error in and/or option');
    }
    if (!check_and_or($_POST['and_or_seen_within'])) {
        $form_err .= xlt('Error in and/or option');
    }

    //process sql
    if (!$form_err) {
         $sql="select patient_data.*, cal_events.pc_eventDate as next_appt,cal_events.pc_startTime as appt_start_time,cal_date.last_appt,forms.last_visit from patient_data left outer join openemr_postcalendar_events as cal_events on patient_data.pid=cal_events.pc_pid and curdate() < cal_events.pc_eventDate left outer join (select pc_pid,max(pc_eventDate) as last_appt from openemr_postcalendar_events where curdate() >= pc_eventDate group by pc_pid ) as cal_date on cal_date.pc_pid=patient_data.pid left outer join (select pid,max(date) as last_visit from forms where curdate() >= date group by pid) as forms on forms.pid=patient_data.pid where 1=1";
        //appointment dates
        if ($_POST['app_s']!=0 and $_POST['app_s']!='') {
            $sql .= " and cal_events.pc_eventDate >= '".$_POST['app_s']."'";
        }
        if ($_POST['app_e']!=0 and $_POST['app_e']!='') {
            $sql .= " and cal_events.pc_endDate <= '".$_POST['app_e']."'";
        }
        // encounter dates
        if ($_POST['seen_since']!=0 and $_POST['seen_since']!='') {
            $sql .= " and forms.date >= '".$_POST['seen_since']."' " ;
        }
        if ($_POST['seen_before']!=0 and $_POST['seen_before']!='') {
            $sql .= " and forms.date <= '".$_POST['seen_before']."' " ;
        }
        // age
        if ($_POST['age_from']!=0 and $_POST['age_from']!='') {
            $sql .= " and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 >= '".$_POST['age_from']."' ";
        }
        if ($_POST['age_upto']!=0 and $_POST['age_upto']!='') {
            $sql .= " and DATEDIFF( CURDATE( ), patient_data.DOB )/ 365.25 <= '".$_POST['age_upto']."' ";
        }
        // gender
        if ($_POST['gender']!='Any') {
            $sql .= " and patient_data.sex='".$_POST['gender']."' ";
        }
        // hipaa overwrite
        if ($_POST['hipaa_choice'] != $hipaa_choices[0]) {
             $sql .= " and patient_data.hipaa_mail='YES' ";
        }

        switch ($_POST['process_type']) :
            case $choices[1]: // Email
                $sql.=" and patient_data.email IS NOT NULL ";
                break;
        endswitch;

        // sort by
        $sql.=' ORDER BY '.$_POST['sort_by'];
        // send query for results.
        //echo $sql;exit();
        $res = sqlStatement($sql);

        if (sqlNumRows($res)==0) {
            $form_err = xlt('No results found, please try again.');
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
<title><?php echo xlt('BatchCom'); ?></title>
<?php Header::setupHeader(['datetime-picker']); ?>
<style>
.datepicker {
    width: 100%;
}
</style>
</head>

<body class="body_top container">
<header class="row">
    <?php require_once("batch_navigation.php");?>
    <h1 class="col-md-6 col-md-offset-3 text-center">
        <?php echo xlt('Batch Communication Tool')?>
    </h1>    
</header>
<main>
    <?php
    if ($form_err) {
        echo "<div class=\"alert alert-danger\">" . xlt("The following errors occurred") . ": $form_err</div>";
    }
    ?>
    <form name="select_form" method="post" action="">
        <div class="row">
            <div class="col-md-3 well">
                <label for="process_type"><?php echo xlt("Process") . ":"; ?></label>
                <select name="process_type">
                    <?php
                    foreach ($process_choices as $choice) {
                        echo "<option>$choice</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 well">
                <label for="hipaa_choice"><?php echo xlt("Override HIPAA choice") . ":"; ?></label>
                <select name="hipaa_choice">
                    <?php
                    foreach ($hipaa_choices as $choice) {
                        echo "<option>$choice</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 well">
                <label for="sort_by"><?php echo xlt("Sort by"); ?></label>
                <select name="sort_by">
                    <?php
                    foreach ($sort_by_choices as $choice => $sorting_code) {
                        echo "<option value=\"$sorting_code\">$choice</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 well">
                <label for="age_from"><?php echo xlt("Age Range") . ":"; ?></label>
                <input name="age_from" size="2" type="num" placeholder="<?php echo xla("any"); ?>">
                -
                <input name="age_upto" size="2" type="num" placeholder="<?php echo xla("any"); ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 well">
                <select name="and_or_gender">
                    <option value="AND"><?php echo xlt('And') ?></option>
                    <option value="OR"><?php echo xlt('Or') ?></option>
                </select>
                <label for="gender"><?php echo xlt('Gender') ?>:</label>
                <select name="gender">
                    <?php
                    foreach ($gender_choices as $choice) {
                        echo "<option>$choice</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 well">
                <select name="and_or_app_within">
                    <option value="AND"><?php echo xlt('And') ?></option>
                    <option value="OR"><?php echo xlt('Or') ?></option>
                    <option value="AND NOT"><?php echo xlt('And not') ?></option>
                    <option value="OR NOT"><?php echo xlt('Or not') ?></option>
                </select>
                <label for="app_s"><?php echo xlt('Appointment within') ?>:</label>
                    <input type="text" class="datepicker" name="app_s" placeholder="any date">
                    <div class="text-center"><?php echo xlt('to'); ?></div>
                    <input type="text" class="datepicker" name="app_e" placeholder="any date">
            </div>
            <!-- later gator    <br>Insurance: <SELECT multiple NAME="insurance" Rows="10" cols="20"></SELECT> -->
            <div class="col-md-3 well">
                <select name="and_or_seen_within">
                    <option value="AND"><?php echo xlt('And'); ?></option>
                    <option value="OR"><?php echo xlt('Or'); ?></option>
                    <option value="AND NOT"><?php echo xlt('And not'); ?></option>
                    <option value="OR NOT"><?php echo xlt('Or not'); ?></option>
                </select>
                <label for="app_s"><?php echo xlt('Seen within')?>:</label>
                    <input type="text" class="datepicker" name="seen_since" placeholder="any date">
                    <div class="text-center"><?php echo xlt('to'); ?></div>
                    <input type="text" class="datepicker" name="seen_before" placeholder="any date">
            </div>
        </div>
        <div class="email row">
            <div class="col-md-6 col-md-offset-3 well">
                <div class="col-md-6">
                    <label for="email_sender"><?php echo xlt('Email Sender'); ?>:</label>
                    <input type="text" name="email_sender" placeholder="your@email.email">
                </div>
                
                <div class="col-md-6">
                    <label for="email_subject"><?php echo xlt('Email Subject'); ?>:</label>
                    <input type="text" name="email_subject" placeholder="From your clinic">
                </div>
                <div class="col-md-12">
                    <label for="email_subject"><?php echo xlt('Email Text, Usable Tag: ***NAME*** , i.e. Dear ***NAME***{{Do Not translate the ***NAME*** elements of this constant.}}'); ?>:</label>
                </div>
                <div class="col-md-12">
                    <textarea name="email_body" id="" cols="40" rows="8"></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center">
                <input type="hidden" name="form_action" value="process">
                <input type="submit" name="submit" class="btn btn-primary" value="<?php echo xla("Process (can take some time)"); ?>">
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
            if (process.value !== '<?php echo $process_choices[1]; ?>') { email.style.display = 'none'; } else { email.style.display = ''; }
        }
        process.addEventListener('change', hideEmail);
        hideEmail();
        $('.datepicker').datetimepicker({
            timepicker: false,
            format: 'Y-m-d'
        });
    })();
</script>
</html>