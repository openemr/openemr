<?php
/**
 * The Chart Tracker feature facilitates the old traditional paper charts updates.
 * This feature requires a new list:
 * <pre>
 *   INSERT INTO list_options VALUES ('lists','chartloc','Chart Storage Locations',51,0,0);
 * </pre>
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @Copyright (C) 2008-2012 Rod Roark <rod@sunsetsystems.com>
 * @Copyright (C) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @Copyright (C) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 */


require_once("../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Entities\ChartTracker;
use OpenEMR\Services\ChartTrackerService;
use OpenEMR\Services\UserService;

$form_newid   = isset($_POST['form_newid'  ]) ? trim($_POST['form_newid'  ]) : '';
$form_curpid  = isset($_POST['form_curpid' ]) ? trim($_POST['form_curpid' ]) : '';
$form_curid   = isset($_POST['form_curid'  ]) ? trim($_POST['form_curid'  ]) : '';
$form_newloc  = isset($_POST['form_newloc' ]) ? trim($_POST['form_newloc' ]) : '';
$form_newuser = isset($_POST['form_newuser']) ? trim($_POST['form_newuser']) : '';

if ($form_newuser) {
    $form_newloc = '';
} else {
    $form_newuser = 0;
}
?>
<html>

<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Chart Tracker'); ?></title>

<script language="JavaScript">

function locationSelect() {
 var f = document.forms[0];
 var i = f.form_newloc.selectedIndex;
 if (i > 0) {
  f.form_newuser.selectedIndex = 0;
 }
}

function userSelect() {
 var f = document.forms[0];
 var i = f.form_newuser.selectedIndex;
 if (i > 0) {
  f.form_newloc.selectedIndex = 0;
 }
}

</script>

</head>

<body class="body_top">
<div class="container">

    <div class="row">
        <div class="col-xs-12">
            <div class="page-header">
                <h1><?php echo xlt('Chart Tracker'); ?></h1>
            </div>
         </div>
    </div>

<form method='post' action='chart_tracker.php' class='form-horizontal' onsubmit='return top.restoreSession()'>

<?php
// This is the place for status messages.

if ($form_newloc || $form_newuser) {
    $tracker = new ChartTracker();
    $tracker->setPid($form_curpid);
    $tracker->setWhen(new \DateTime(date('Y-m-d H:i:s')));
    $tracker->setUserId($form_newuser);
    $tracker->setLocation($form_newloc);
    $chartTrackerService = new ChartTrackerService();
    $chartTrackerService->trackPatientLocation($tracker);
    echo "<div class='alert alert-success'>" . xlt('Save Successful for chart ID') . " " . "'" . text($form_curid) . "'.</div>";
}

$row = array();

if ($form_newid) {
  // Find out where the chart is now.
    $query = "SELECT pd.pid, pd.pubpid, pd.fname, pd.mname, pd.lname, " .
    "pd.ss, pd.DOB, ct.ct_userid, ct.ct_location, ct.ct_when " .
    "FROM patient_data AS pd " .
    "LEFT OUTER JOIN chart_tracker AS ct ON ct.ct_pid = pd.pid " .
    "WHERE pd.pubpid = ? " .
    "ORDER BY pd.pid ASC, ct.ct_when DESC LIMIT 1";
    $row = sqlQuery($query, array($form_newid));
    if (empty($row)) {
        echo "<div class='alert alert-danger'>" . xlt('Chart ID') . " " . "'" . text($form_newid) . "' " . xlt('not found') . "!</div>";
    }
}
?>

<?php
if (!empty($row)) {
    $userService = new UserService();
    $ct_userid   = $row['ct_userid'];
    $ct_location = $row['ct_location'];
    $current_location = xlt('Unassigned');
    if ($ct_userid) {
        $user = $userService->getUser($ct_userid);
        $current_location = text($user->getLname() . ", " . $user->getFname() . " " . $user->getMname() . " " . oeFormatDateTime($row['ct_when'], "global", true));
    } else if ($ct_location) {
        $current_location = generate_display_field(array('data_type'=>'1','list_id'=>'chartloc'), $ct_location);
    }
?>

    <div class="row">
        <div class="col-sm-6 well">
            <div class="form-group">
                <label for="form_pat_id" class='control-label col-sm-3'><?php echo xlt('Patient ID') . ":"; ?></label>
                <div class='col-sm-9'>
                    <p class="form-control-static"><?php echo text($row['pid']) ?></p>
                    <input type='hidden' name='form_curpid' value='<?php echo attr($row['pid']); ?>'  />
                    <input type='hidden' name='form_curid' value='<?php echo attr($row['pubpid']); ?>' />
                </div>
            </div>
            <div class="form-group">
                <label for="form_pat_id" class='control-label col-sm-3'><?php echo xlt('Name') . ":"; ?></label>
                <div class='col-sm-9'>
                    <p class="form-control-static"><?php echo text($row['lname'] . ", " . $row['fname'] . " " . $row['mname']) ?></p>
                </div>
            </div>
            <div class="form-group">
                <label for="form_pat_id" class='control-label col-sm-3'><?php echo xlt('DOB') . ":"; ?></label>
                <div class='col-sm-9'>
                    <p class="form-control-static"><?php echo text(oeFormatShortDate($row['DOB'])) ?></p>
                </div>
              </div>
            <div class="form-group">
                <label for="form_pat_id" class='control-label col-sm-3'><?php echo xlt('SSN') . ":"; ?></label>
                <div class='col-sm-9'>
                    <p class="form-control-static"><?php echo text($row['ss']) ?></p>
                </div>
            </div>
            <div class="form-group">
                <label for="form_pat_id" class='control-label col-sm-3'><?php echo xlt('Current Location') . ":"; ?></label>
                <div class='col-sm-9'>
                    <p class="form-control-static"><?php echo text($current_location) ?></p>
                </div>
            </div>
            <div class="form-group">
                <label for="form_curr_loc" class='control-label col-sm-3'><?php echo xlt('Check In To') . ":"; ?></label>
                <div class='col-sm-9'>
                    <?php generate_form_field(array('data_type'=>1,'field_id'=>'newloc','list_id'=>'chartloc','empty_title'=>''), ''); ?>
                </div>
            </div>
            <div class="form-group">
                <label for="form_out_to" class='control-label col-sm-3'><?php echo xlt('Our Out To') . ":"; ?></label>
                <div class='col-sm-9'>
                    <select name='form_newuser' class='form-control' onchange='userSelect()'>
                        <option value=''></option>
                        <?php
                        $users = $userService->getActiveUsers();

                        foreach ($users as $activeUser) {
                            echo "    <option value='" . attr($activeUser->getId()) . "'";
                            echo ">" . text($activeUser->getLname()) . ', ' . text($activeUser->getFname()) . ' ' . text($activeUser->getMname()) .
                            "</option>\n";
                        }
                        ?>
                    </select>
                </div>
            </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                    <button type='submit' class='btn btn-default btn-save' name='form_save'><?php echo xlt("Save"); ?></button>
            </div>
        </div>
    </div>


<?php
}
?>
    <div class="row">
        <div class="col-sm-6 well">
            <div class="form-group">
                <label for='form_newid' class='control-label col-sm-3'><?php echo xlt('New Patient ID') . ":"; ?></label>
                <div class='col-sm-9'>
                   <input type='text' name='form_newid' id='form_newid' class='form-control' title='<?php echo xla('Type or scan the patient identifier here'); ?>'>
                </div>
            </div>
            <div class="form-group">
            <div class='col-sm-offset-3 col-sm-9'>
                <button type='submit' class='btn btn-default btn-search' name='form_lookup'><?php echo xlt("Look Up"); ?></button>
            </div>
        </div>
    </div>
</form>

</div>

</body>
</html>
