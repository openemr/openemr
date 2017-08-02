<?php
/**
 * The Chart Tracker feature facilitates the old traditional paper charts updates.
 * This feature requires a new list:
 * <pre>
 *   INSERT INTO list_options VALUES ('lists','chartloc','Chart Storage Locations',51,0,0);
 * </pre>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @Copyright (C) 2008-2012 Rod Roark <rod@sunsetsystems.com>
 * @Copyright (C) 2011-2017 Brady Miller <brady.g.miller@gmail.com>
 * @Copyright (C) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 */




require_once("../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

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
         <h3><?php echo xlt('Chart Tracker'); ?></h3>
      </div>
   </div>
<!--<center> -->
&nbsp;<br />
<form method='post' action='chart_tracker.php' class='form-horizontal' onsubmit='return top.restoreSession()'>

<?php
// This is the place for status messages.

if ($form_newloc || $form_newuser) {
    $tracker = new \entities\ChartTracker();
    $tracker->setPid($form_curpid);
    $tracker->setWhen(new \DateTime(date('Y-m-d H:i:s')));
    $tracker->setUserId($form_newuser);
    $tracker->setLocation($form_newloc);
    $chartTrackerService = new \services\ChartTrackerService();
    $chartTrackerService->trackPatientLocation($tracker);
    echo "<font color='green'>" . xlt('Save Successful for chart ID') . " " . "'" . text($form_curid) . "'.</font><br />";
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
        echo "<font color='red'>" . xlt('Chart ID') . " " . "'" . text($form_newid) . "' " . xlt('not found') . "!</font><br />";
    }
}
?>

<?php
if (!empty($row)) {
    $userService = new \services\UserService();
    $ct_userid   = $row['ct_userid'];
    $ct_location = $row['ct_location'];
    $current_location = xlt('Unassigned');
    if ($ct_userid) {
        $user = $userService->getUser($ct_userid);
        $current_location = text($user->getLname() . ", " . $user->getFname() . " " . $user->getMname() . " " . $row['ct_when']);
    } else if ($ct_location) {
        $current_location = generate_display_field(array('data_type'=>'1','list_id'=>'chartloc'), $ct_location);
    }
?>

   <div class="row">
      <div class="col-xs-12">
         <div class="form-group">
            <label for="form_pat_id" class='control-label col-sm-6'><?php echo xlt('Patient ID') . ":"; ?></label>
            <div class='col-sm-2'>
               <p class="form-control-static"><?php echo text($row['pid']) ?></p>
               <?php
               echo
               "<input type='hidden' name='form_curpid' value='" . attr($row['pid']) . "' />" .
               "<input type='hidden' name='form_curid' value='" . attr($row['pubpid']) . "' /></td>\n";
               ?>
            </div>
         </div>
         <div class="form-group">
            <label for="form_pat_id" class='control-label col-sm-6'><?php echo xlt('Name') . ":"; ?></label>
            <div class='col-sm-2'>
               <p class="form-control-static"><?php echo text($row['lname'] . ", " . $row['fname'] . " " . $row['mname']) ?></p>
            </div>
         </div>
         <div class="form-group">
            <label for="form_pat_id" class='control-label col-sm-6'><?php echo xlt('DOB') . ":"; ?></label>
            <div class='col-sm-2'>
               <p class="form-control-static"><?php echo text($row['DOB']) ?></p>
            </div>
         </div>
         <div class="form-group">
            <label for="form_pat_id" class='control-label col-sm-6'><?php echo xlt('SSN') . ":"; ?></label>
            <div class='col-sm-2'>
               <p class="form-control-static"><?php echo text($row['ss']) ?></p>
            </div>
         </div>
         <div class="form-group">
            <label for="form_pat_id" class='control-label col-sm-6'><?php echo xlt('Current Location') . ":"; ?></label>
            <div class='col-sm-2'>
               <p class="form-control-static"><?php echo text($current_location) ?></p>
            </div>
         </div>
         <div class="form-group">
            <label for="form_curr_loc" class='control-label col-sm-6'><?php echo xlt('Check In To') . ":"; ?></label>
            <div class='col-sm-2'>
                  <?php
                  echo " <td class='text'>";                   
                  generate_form_field(array('data_type'=>1,'field_id'=>'newloc','list_id'=>'chartloc','empty_title'=>''), '');
                  echo " </td>\n";
                  ?>
            </div>
         </div>
         <div class="form-group">
            <label for="form_out_to" class='control-label col-sm-6'><?php echo xlt('Our Out To') . ":"; ?></label>
            <div class='col-sm-2'>
                  <?php
                  echo "  <td class='text'><select name='form_newuser' onchange='userSelect()'>\n";
                  echo "   <option value=''></option>";

                  $users = $userService->getActiveUsers();

                  foreach ($users as $activeUser) {
                   echo "    <option value='" . attr($activeUser->getId()) . "'";
                   echo ">" . text($activeUser->getLname()) . ', ' . text($activeUser->getFname()) . ' ' . text($activeUser->getMname()) .
                        "</option>\n";
                         }

                  echo "  </select></td>\n";
                  ?> 
            </div>
         </div>
         <div class="form-group">
               <div class='col-sm-offset-6 col-sm-10'>
                  <button type='submit' class='btn btn-default btn-search' name='form_save'><?php echo xlt("Save"); ?></button>
               </div>

         </div>
      </div>
   </div>


<?php	
} 
?>  
   <div class="row">
      <div class="col-xs-12">
         <div class="form-group">
            <label for='form_newid' class='control-label col-sm-6'><?php echo xlt('New Patient ID') . ":"; ?></label>            
            <div class='col-sm-2'>
               <input type='text' name='form_newid' id='form_newid' class='form-control col-sm-6' title='<?php echo xla('Type or scan the patient identifier here'); ?>'>
            </div>
            <div class="form-group">
               <div class='col-sm-offset-6 col-sm-10'>
                  <button type='submit' class='btn btn-default btn-search' name='form_lookup'><?php echo xlt("Look Up"); ?></button>
               </div>
            </div>   
         </div>
      </div>  
   </div>
</form>

</div>  

</body>
</html>
