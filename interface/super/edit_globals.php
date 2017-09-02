<?php
/**
 * Script for the globals editor.
 *
 * Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
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
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */




require_once("../globals.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/globals.inc.php");
require_once("$srcdir/user.inc");
require_once(dirname(__FILE__)."/../../myportal/soap_service/portal_connectivity.php");

$userMode = (array_key_exists('mode', $_GET) && $_GET['mode'] == 'user');

if (!$userMode) {
  // Check authorization.
    $thisauth = acl_check('admin', 'super');
    if (!$thisauth) {
        die(xlt('Not authorized'));
    }
}

function checkCreateCDB()
{
    $globalsres = sqlStatement("SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN
  ('couchdb_host','couchdb_user','couchdb_pass','couchdb_port','couchdb_dbase','document_storage_method')");
    $options = array();
    while ($globalsrow = sqlFetchArray($globalsres)) {
        $GLOBALS[$globalsrow['gl_name']] = $globalsrow['gl_value'];
    }

    $directory_created = false;
    if (!empty($GLOBALS['document_storage_method'])) {
        // /documents/temp/ folder is required for CouchDB
        if (!is_dir($GLOBALS['OE_SITE_DIR'] . '/documents/temp/')) {
            $directory_created = mkdir($GLOBALS['OE_SITE_DIR'] . '/documents/temp/', 0777, true);
            if (!$directory_created) {
                echo htmlspecialchars(xl("Failed to create temporary folder. CouchDB will not work."), ENT_NOQUOTES);
            }
        }

        $couch = new CouchDB();
        if (!$couch->check_connection()) {
            echo "<script type='text/javascript'>alert('".addslashes(xl("CouchDB Connection Failed."))."');</script>";
            return false;
        }

        if ($GLOBALS['couchdb_host'] || $GLOBALS['couchdb_port'] || $GLOBALS['couchdb_dbase']) {
            $couch->createDB($GLOBALS['couchdb_dbase']);
            $couch->createView($GLOBALS['couchdb_dbase']);
        }
    }

    return true;
}

/**
 * Update background_services table for a specific service following globals save.
 * @author EMR Direct
 */
function updateBackgroundService($name, $active, $interval)
{
   //order important here: next_run change dependent on _old_ value of execute_interval so it comes first
    $sql = 'UPDATE background_services SET active=?, '
    . 'next_run = next_run + INTERVAL (? - execute_interval) MINUTE, execute_interval=? WHERE name=?';
    return sqlStatement($sql, array($active,$interval,$interval,$name));
}

/**
 * Make any necessary changes to background_services table when globals are saved.
 * To prevent an unexpected service call during startup or shutdown, follow these rules:
 * 1. Any "startup" operations should occur _before_ the updateBackgroundService() call.
 * 2. Any "shutdown" operations should occur _after_ the updateBackgroundService() call. If these operations
 * would cause errors in a running service call, it would be best to make the shutdown function itself is
 * a background service that is activated here, does nothing if active=1 or running=1 for the
 * parent service.  Then it deactivates itself by setting active=0 when it is done shutting the parent service
 * down. This will prevent non-responsiveness to the user by waiting for a service to finish.
 * 3. If any "previous" values for globals are required for startup/shutdown logic, they need to be
 * copied to a temp variable before the while($globalsrow...) loop.
 * @author EMR Direct
 */
function checkBackgroundServices()
{
  //load up any necessary globals
    $bgservices = sqlStatement("SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN
  ('phimail_enable','phimail_interval')");
    while ($globalsrow = sqlFetchArray($bgservices)) {
        $GLOBALS[$globalsrow['gl_name']] = $globalsrow['gl_value'];
    }

  //Set up phimail service
    $phimail_active = empty($GLOBALS['phimail_enable']) ? '0' : '1';
    $phimail_interval = max(0, (int) $GLOBALS['phimail_interval']);
    updateBackgroundService('phimail', $phimail_active, $phimail_interval);
}
function handleAltServices($this_serviceid, $gln = '', $sinterval = 1)
{
    $bgservices = sqlStatement("SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name = '$gln'");
    while ($globalsrow = sqlFetchArray($bgservices)) {
        $GLOBALS[$globalsrow['gl_name']] = $globalsrow['gl_value'];
    }

    $bs_active = empty($GLOBALS[$gln]) ? '0' : '1';
    $bs_interval = max(0, (int) $sinterval);
    updateBackgroundService($this_serviceid, $bs_active, $bs_interval);
    if (!$bs_active && $this_serviceid == 'ccdaservice') {
        require_once(dirname(__FILE__)."/../../ccdaservice/ssmanager.php");
        
        service_shutdown(0);
    }
}
?>

<html>

<head>
<?php

html_header_show();

// If we are saving user_specific globals.
//
if (array_key_exists('form_save', $_POST) && $_POST['form_save'] && $userMode) {
    $i = 0;
    foreach ($GLOBALS_METADATA as $grpname => $grparr) {
        if (in_array($grpname, $USER_SPECIFIC_TABS)) {
            foreach ($grparr as $fldid => $fldarr) {
                if (in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
                    list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                    $label = "global:".$fldid;
                    $fldvalue = trim($_POST["form_$i"]);
                    setUserSetting($label, $fldvalue, $_SESSION['authId'], false);
                    if ($_POST["toggle_$i"] == "YES") {
                        removeUserSetting($label);
                    }

                    ++$i;
                }
            }
        }
    }

    echo "<script type='text/javascript'>";
    echo "if (parent.left_nav.location) {";
    echo "  parent.left_nav.location.reload();";
    echo "  parent.Title.location.reload();";
    echo "  if(self.name=='RTop'){";
    echo "  parent.RBot.location.reload();";
    echo "  }else{";
    echo "  parent.RTop.location.reload();";
    echo "  }";
    echo "}";
    echo "self.location.href='edit_globals.php?mode=user&unique=yes';";
    echo "</script>";
}

if (array_key_exists('form_download', $_POST) && $_POST['form_download']) {
    $client = portal_connection();
    try {
        $response = $client->getPortalConnectionFiles($credentials);
    } catch (SoapFault $e) {
        error_log('SoapFault Error');
        error_log(var_dump(get_object_vars($e)));
    } catch (Exception $e) {
        error_log('Exception Error');
        error_log(var_dump(get_object_vars($e)));
    }

    if (array_key_exists('status', $response) && $response['status'] == "1") {//WEBSERVICE RETURNED VALUE SUCCESSFULLY
        $tmpfilename  = realpath(sys_get_temp_dir())."/".date('YmdHis').".zip";
        $fp           = fopen($tmpfilename, "wb");
        fwrite($fp, base64_decode($response['value']));
        fclose($fp);
        $practice_filename    = $response['file_name'];//practicename.zip
        ob_clean();
        // Set headers
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$practice_filename);
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        // Read the file from disk
        readfile($tmpfilename);
        unlink($tmpfilename);
        exit;
    } else {//WEBSERVICE CALL FAILED AND RETURNED AN ERROR MESSAGE
        ob_end_clean();
        ?>
      <script type="text/javascript">
        alert('<?php echo xls('Offsite Portal web Service Failed').":\\n".text($response['value']);?>');
    </script>
        <?php
    }
}
?>
<html>
<head>
<?php

// If we are saving main globals.
//
if (array_key_exists('form_save', $_POST) && $_POST['form_save'] && !$userMode) {
    $force_off_enable_auditlog_encryption = true;
  // Need to force enable_auditlog_encryption off if the php mycrypt module
  // is not installed.
    if (extension_loaded('mcrypt')) {
        $force_off_enable_auditlog_encryption = false;
    }

  // Aug 22, 2014: Ensoftek: For Auditable events and tamper-resistance (MU2)
  // Check the current status of Audit Logging
    $auditLogStatusFieldOld = $GLOBALS['enable_auditlog'];

  /*
   * Compare form values with old database values.
   * Only save if values differ. Improves speed.
   */

  // Get all the globals from DB
    $old_globals = sqlGetAssoc('SELECT gl_name, gl_index, gl_value FROM `globals` ORDER BY gl_name, gl_index', false, true);

    $i = 0;
    foreach ($GLOBALS_METADATA as $grpname => $grparr) {
        foreach ($grparr as $fldid => $fldarr) {
            list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
            if ($fldtype == 'pwd') {
                $pass = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = ?", array($fldid));
                $fldvalueold = $pass['gl_value'];
            }

            /* Multiple choice fields - do not compare , overwrite */
            if (!is_array($fldtype) && substr($fldtype, 0, 2) == 'm_') {
                if (isset($_POST["form_$i"])) {
                    $fldindex = 0;

                    sqlStatement("DELETE FROM globals WHERE gl_name = ?", array( $fldid ));

                    foreach ($_POST["form_$i"] as $fldvalue) {
                        $fldvalue = trim($fldvalue);
                        sqlStatement('INSERT INTO `globals` ( gl_name, gl_index, gl_value ) VALUES ( ?,?,?)', array( $fldid, $fldindex, $fldvalue ));
                        ++$fldindex;
                    }
                }
            } else {
                /* check value of single field. Don't update if the database holds the same value */
                if (isset($_POST["form_$i"])) {
                    $fldvalue = trim($_POST["form_$i"]);
                } else {
                    $fldvalue = "";
                }

                if ($fldtype=='pwd') {
                    $fldvalue = $fldvalue ? SHA1($fldvalue) : $fldvalueold; // TODO: salted passwords?
                }

                // We rely on the fact that set of keys in globals.inc === set of keys in `globals`  table!

                if (!isset($old_globals[$fldid]) // if the key not found in database - update database
                    ||
                   ( isset($old_globals[$fldid]) && $old_globals[ $fldid ]['gl_value'] !== $fldvalue ) // if the value in database is different
                ) {
                      // Need to force enable_auditlog_encryption off if the php mcrypt module
                      // is not installed.
                    if ($force_off_enable_auditlog_encryption && ($fldid  == "enable_auditlog_encryption")) {
                          error_log("OPENEMR ERROR: UNABLE to support auditlog encryption since the php mcrypt module is not installed", 0);
                          $fldvalue=0;
                    }

                      // special treatment for some vars
                    switch ($fldid) {
                        case 'first_day_week':
                            // update PostCalendar config as well
                            sqlStatement("UPDATE openemr_module_vars SET pn_value = ? WHERE pn_name = 'pcFirstDayOfWeek'", array($fldvalue));
                            break;
                    }

                      // Replace old values
                      sqlStatement('DELETE FROM `globals` WHERE gl_name = ?', array( $fldid ));
                      sqlStatement('INSERT INTO `globals` ( gl_name, gl_index, gl_value ) VALUES ( ?, ?, ? )', array( $fldid, 0, $fldvalue ));
                } else {
                    //error_log("No need to update $fldid");
                }
            }

            ++$i;
        }
    }

    checkCreateCDB();
    checkBackgroundServices();
    handleAltServices('ccdaservice', 'ccda_alt_service_enable', 1);
  
  // July 1, 2014: Ensoftek: For Auditable events and tamper-resistance (MU2)
  // If Audit Logging status has changed, log it.
    $auditLogStatusNew = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'enable_auditlog'");
    $auditLogStatusFieldNew = $auditLogStatusNew['gl_value'];
    if ($auditLogStatusFieldOld != $auditLogStatusFieldNew) {
         auditSQLAuditTamper($auditLogStatusFieldNew);
    }

    echo "<script type='text/javascript'>";
    echo "if (parent.left_nav.location) {";
    echo "  parent.left_nav.location.reload();";
    echo "  parent.Title.location.reload();";
    echo "  if(self.name=='RTop'){";
    echo "  parent.RBot.location.reload();";
    echo "  }else{";
    echo "  parent.RTop.location.reload();";
    echo "  }";
    echo "}";
    echo "self.location.href='edit_globals.php?unique=yes';";
    echo "</script>";
}
?>

<!-- supporting javascript code -->
<script type="text/javascript" src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-3-2/index.js"></script>
<script type="text/javascript" src="../../library/js/common.js"></script>
<script type="text/javascript" src="../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jscolor-1-4-5/jscolor.js"></script>
<link rel="stylesheet" type="text/css" href="../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<?php if ($userMode) { ?>
  <title><?php  echo xlt('User Settings'); ?></title>
<?php } else { ?>
  <title><?php echo xlt('Global Settings'); ?></title>
<?php } ?>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
td        { font-size:10pt; }
input     { font-size:10pt; }
</style>
<script type="text/javascript">
  function validate_file(){
    $.ajax({
      type: "POST",
      url: "<?php echo $GLOBALS['webroot']?>/library/ajax/offsite_portal_ajax.php",
      data: {
    action: 'check_file',
      },
      cache: false,
      success: function( message )
      {
    if(message == 'OK'){
      document.getElementById('form_download').value = 1;
      document.getElementById('file_error_message').innerHTML = '';
      document.forms[0].submit();
    }
    else{
      document.getElementById('form_download').value = 0;
      document.getElementById('file_error_message').innerHTML = message;
      return false;
    }
      }
    });
  }
</script>
</head>

<body class="body_top">

<?php if ($userMode) { ?>
  <form method='post' name='theform' id='theform' action='edit_globals.php?mode=user' onsubmit='return top.restoreSession()'>
<?php } else { ?>
  <form method='post' name='theform' id='theform' action='edit_globals.php' onsubmit='return top.restoreSession()'>
<?php } ?>

<?php if ($userMode) { ?>
  <p><b><?php echo xlt('Edit User Settings'); ?></b>
<?php } else { ?>
  <p><b><?php echo xlt('Edit Global Settings'); ?></b>
<?php } ?>

<?php // mdsupport - Optional server based searching mechanism for large number of fields on this screen. ?>
<span style='float: right;'>
    <input name='srch_desc' size='20'
        value='<?php echo (!empty($_POST['srch_desc']) ? htmlspecialchars($_POST['srch_desc']) : '') ?>' />
    <input type='submit' name='form_search' value='<?php echo xla('Search'); ?>' />
</span>

<ul class="tabNav">
<?php
$i = 0;
foreach ($GLOBALS_METADATA as $grpname => $grparr) {
    if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
        echo " <li" . ($i ? "" : " class='current'") .
        "><a href='#'>" .
        xlt($grpname) . "</a></li>\n";
        ++$i;
    }
}
?>
</ul>

<div class="tabContainer">
<?php
$i = 0;
foreach ($GLOBALS_METADATA as $grpname => $grparr) {
    if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
        echo " <div class='tab" . ($i ? "" : " current") .
          "' style='height:auto;width:97%;'>\n";

        echo " <table>";

        if ($userMode) {
            echo "<tr>";
            echo "<th>&nbsp</th>";
            echo "<th>" . htmlspecialchars(xl('User Specific Setting'), ENT_NOQUOTES) . "</th>";
            echo "<th>" . htmlspecialchars(xl('Default Setting'), ENT_NOQUOTES) . "</th>";
            echo "<th>&nbsp</th>";
            echo "<th>" . htmlspecialchars(xl('Set to Default'), ENT_NOQUOTES) . "</th>";
            echo "</tr>";
        }

        foreach ($grparr as $fldid => $fldarr) {
            if (!$userMode || in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
                list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                // mdsupport - Check for matches
                $srch_cl = '';
                if (!empty($_POST['srch_desc']) && (stristr(($fldname.$flddesc), $_POST['srch_desc']) !== false)) {
                    $srch_cl = 'class="srch"';
                }

                // Most parameters will have a single value, but some will be arrays.
                // Here we cater to both possibilities.
                $glres = sqlStatement("SELECT gl_index, gl_value FROM globals WHERE " .
                  "gl_name = ? ORDER BY gl_index", array($fldid));
                $glarr = array();
                while ($glrow = sqlFetchArray($glres)) {
                    $glarr[] = $glrow;
                }

                // $fldvalue is meaningful only for the single-value cases.
                $fldvalue = count($glarr) ? $glarr[0]['gl_value'] : $flddef;

                // Collect user specific setting if mode set to user
                $userSetting = "";
                $settingDefault = "checked='checked'";
                if ($userMode) {
                           $userSettingArray = sqlQuery("SELECT * FROM user_settings WHERE setting_user=? AND setting_label=?", array($_SESSION['authId'],"global:".$fldid));
                           $userSetting = $userSettingArray['setting_value'];
                           $globalValue = $fldvalue;
                    if (!empty($userSettingArray)) {
                        $fldvalue = $userSetting;
                        $settingDefault = "";
                    }
                }

                echo " <tr $srch_cl title='" . attr($flddesc) . "'><td valign='top'><b>" . text($fldname) . "</b></td><td valign='top'>\n";

                if (is_array($fldtype)) {
                              echo "  <select name='form_$i' id='form_$i'>\n";
                    foreach ($fldtype as $key => $value) {
                        if ($userMode) {
                            if ($globalValue == $key) {
                                $globalTitle = $value;
                            }
                        }

                        echo "   <option value='" . attr($key) . "'";

                        //Casting value to string so the comparison will be always the same type and the only thing that will check is the value
                        //Tried to use === but it will fail in already existing variables
                        if ((string)$key == (string)$fldvalue) {
                            echo " selected";
                        }

                        echo ">";
                        echo text($value);
                        echo "</option>\n";
                    }

                              echo "  </select>\n";
                } else if ($fldtype == 'bool') {
                    if ($userMode) {
                        if ($globalValue == 1) {
                            $globalTitle = htmlspecialchars(xl('Checked'), ENT_NOQUOTES);
                        } else {
                            $globalTitle = htmlspecialchars(xl('Not Checked'), ENT_NOQUOTES);
                        }
                    }

                              echo "  <input type='checkbox' name='form_$i' id='form_$i' value='1'";
                    if ($fldvalue) {
                        echo " checked";
                    }

                              echo " />\n";
                } else if ($fldtype == 'num') {
                    if ($userMode) {
                        $globalTitle = $globalValue;
                    }

                              echo "  <input type='text' name='form_$i' id='form_$i' " .
                                "size='6' maxlength='15' value='" . attr($fldvalue) . "' />\n";
                } else if ($fldtype == 'text') {
                    if ($userMode) {
                        $globalTitle = $globalValue;
                    }

                              echo "  <input type='text' name='form_$i' id='form_$i' " .
                                "size='50' maxlength='255' value='" . attr($fldvalue) . "' />\n";
                } else if ($fldtype == 'pwd') {
                    if ($userMode) {
                        $globalTitle = $globalValue;
                    }

                              echo "  <input type='password' name='form_$i' " .
                                "size='50' maxlength='255' value='' />\n";
                } else if ($fldtype == 'pass') {
                    if ($userMode) {
                        $globalTitle = $globalValue;
                    }

                              echo "  <input type='password' name='form_$i' " .
                                "size='50' maxlength='255' value='" . attr($fldvalue) . "' />\n";
                } else if ($fldtype == 'lang') {
                              $res = sqlStatement("SELECT * FROM lang_languages ORDER BY lang_description");
                              echo "  <select name='form_$i' id='form_$i'>\n";
                    while ($row = sqlFetchArray($res)) {
                        echo "   <option value='" . attr($row['lang_description']) . "'";
                        if ($row['lang_description'] == $fldvalue) {
                            echo " selected";
                        }

                        echo ">";
                        echo xlt($row['lang_description']);
                        echo "</option>\n";
                    }

                              echo "  </select>\n";
                } else if ($fldtype == 'all_code_types') {
                              global $code_types;
                              echo "  <select name='form_$i' id='form_$i'>\n";
                    foreach (array_keys($code_types) as $code_key) {
                        echo "   <option value='" . attr($code_key) . "'";
                        if ($code_key == $fldvalue) {
                            echo " selected";
                        }

                        echo ">";
                        echo xlt($code_types[$code_key]['label']);
                        echo "</option>\n";
                    }

                              echo "  </select>\n";
                } else if ($fldtype == 'm_lang') {
                              $res = sqlStatement("SELECT * FROM lang_languages  ORDER BY lang_description");
                              echo "  <select multiple name='form_{$i}[]' id='form_{$i}[]' size='3'>\n";
                    while ($row = sqlFetchArray($res)) {
                        echo "   <option value='" . attr($row['lang_description']) . "'";
                        foreach ($glarr as $glrow) {
                            if ($glrow['gl_value'] == $row['lang_description']) {
                                echo " selected";
                                break;
                            }
                        }

                        echo ">";
                        echo xlt($row['lang_description']);
                        echo "</option>\n";
                    }

                              echo "  </select>\n";
                } else if ($fldtype == 'color_code') {
                    if ($userMode) {
                        $globalTitle = $globalValue;
                    }

                              echo "  <input type='text' class='color {hash:true}' name='form_$i' id='form_$i' " .
                                "size='6' maxlength='15' value='" . attr($fldvalue) . "' />" .
                                "<input type='button' value='" . xla('Default'). "' onclick=\"document.forms[0].form_$i.color.fromString('" . attr($flddef) . "')\">\n";
                } else if ($fldtype == 'default_visit_category') {
                                $sql = "SELECT pc_catid, pc_catname, pc_cattype 
                FROM openemr_postcalendar_categories
                WHERE pc_active = 1 ORDER BY pc_seq";
                                $result = sqlStatement($sql);
                                echo "<select name=\"form_{$i}\" id=\"form_{$i}\">\n";
                                echo "<option value='_blank'>" . xlt('None') . "</option>";
                    while ($row = sqlFetchArray($result)) {
                        $catId = $row['pc_catid'];
                        $name = $row['pc_catname'];
                        if ($catId < 9 && $catId != "5") {
                            continue;
                        }

                        if ($row['pc_cattype'] == 3 && !$GLOBALS['enable_group_therapy']) {
                            continue;
                        }

                        $optionStr = '<option value="%pc_catid%"%selected%>%pc_catname%</option>';
                        $optionStr = str_replace("%pc_catid%", attr($catId), $optionStr);
                        $optionStr = str_replace("%pc_catname%", text(xl_appt_category($name)), $optionStr);
                        $selected = ($fldvalue == $catId) ? " selected" : "";
                        $optionStr = str_replace("%selected%", $selected, $optionStr);
                        echo $optionStr;
                    }

                                echo "</select>";
                } else if ($fldtype == 'css') {
                    if ($userMode) {
                        $globalTitle = $globalValue;
                    }

                              $themedir = "$webserver_root/interface/themes";
                              $dh = opendir($themedir);
                    if ($dh) {
                        echo "  <select name='form_$i' id='form_$i'>\n";
                        while (false !== ($tfname = readdir($dh))) {
                            // Only show files that contain style_ as options
                            //  Skip style_blue.css since this is used for
                            //  lone scripts such as setup.php
                            //  Also skip style_pdf.css which is for PDFs and not screen output
                            if (!preg_match("/^style_.*\.css$/", $tfname) ||
                              $tfname == 'style_blue.css' || $tfname == 'style_pdf.css') {
                                continue;
                            }

                            echo "<option value='" . attr($tfname) . "'";
                            // Drop the "style_" part and any replace any underscores with spaces
                            $styleDisplayName = str_replace("_", " ", substr($tfname, 6));
                            // Strip the ".css" and uppercase the first character
                            $styleDisplayName = ucfirst(str_replace(".css", "", $styleDisplayName));
                            if ($tfname == $fldvalue) {
                                echo " selected";
                            }

                            echo ">";
                            echo text($styleDisplayName);
                            echo "</option>\n";
                        }

                        closedir($dh);
                        echo "  </select>\n";
                    }
                } else if ($fldtype == 'tabs_css') {
                    if ($userMode) {
                        $globalTitle = $globalValue;
                    }

                              $themedir = "$webserver_root/interface/themes";
                              $dh = opendir($themedir);
                    if ($dh) {
                        echo "  <select name='form_$i' id='form_$i'>\n";
                        while (false !== ($tfname = readdir($dh))) {
                            // Only show files that contain tabs_style_ as options
                            if (!preg_match("/^tabs_style_.*\.css$/", $tfname)) {
                                continue;
                            }

                            echo "<option value='" . attr($tfname) . "'";
                            // Drop the "tabs_style_" part and any replace any underscores with spaces
                            $styleDisplayName = str_replace("_", " ", substr($tfname, 11));
                            // Strip the ".css" and uppercase the first character
                            $styleDisplayName = ucfirst(str_replace(".css", "", $styleDisplayName));
                            if ($tfname == $fldvalue) {
                                echo " selected";
                            }

                            echo ">";
                            echo text($styleDisplayName);
                            echo "</option>\n";
                        }

                        closedir($dh);
                        echo "  </select>\n";
                    }
                } else if ($fldtype == 'hour') {
                    if ($userMode) {
                        $globalTitle = $globalValue;
                    }

                              echo "  <select name='form_$i' id='form_$i'>\n";
                    for ($h = 0; $h < 24; ++$h) {
                        echo "<option value='$h'";
                        if ($h == $fldvalue) {
                            echo " selected";
                        }

                        echo ">";
                        if ($h ==  0) {
                            echo "12 AM";
                        } else if ($h <  12) {
                            echo "$h AM";
                        } else if ($h == 12) {
                            echo "12 PM";
                        } else {
                            echo ($h - 12) . " PM";
                        }

                        echo "</option>\n";
                    }

                              echo "  </select>\n";
                }

                if ($userMode) {
                              echo " </td>\n";
                              echo "<td align='center' style='color:red;'>" . attr($globalTitle) . "</td>\n";
                              echo "<td>&nbsp</td>";
                              echo "<td align='center'><input type='checkbox' value='YES' name='toggle_" . $i . "' id='toggle_" . $i . "' " . attr($settingDefault) . "/></td>\n";
                              echo "<input type='hidden' id='globaldefault_" . $i . "' value='" . attr($globalValue) . "'>\n";
                              echo "</tr>\n";
                } else {
                              echo " </td></tr>\n";
                }

                ++$i;
            }

            if (trim(strtolower($fldid)) == 'portal_offsite_address_patient_link' && !empty($GLOBALS['portal_offsite_enable']) && !empty($GLOBALS['portal_offsite_providerid'])) {
                echo "<input type='hidden' name='form_download' id='form_download'>";
                echo "<tr><td><input onclick=\"return validate_file()\" type='button' value='".xla('Download Offsite Portal Connection Files')."' /></td><td id='file_error_message' style='color:red'></td></tr>";
            }
        }

        echo " </table>\n";
        echo " </div>\n";
    }
}
?>
</div>

<p>
 <input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />
</p>
</center>

</form>

</body>

<script language="JavaScript">

$(document).ready(function(){
  tabbify();
  enable_modals();

    <?php // mdsupport - Highlight search results ?>
  $('.srch td').wrapInner("<mark></mark>");
  $('.tab > table').find('tr.srch:first').each(function() {
      var srch_div = $(this).closest('div').prevAll().length + 1;
      $('.tabNav > li:nth-child('+srch_div+') a').wrapInner("<mark></mark>");
  });
  // Use the counter ($i) to make the form user friendly for user-specific globals use
    <?php if ($userMode) { ?>
    <?php for ($j = 0; $j <= $i; $j++) { ?>
      $("#form_<?php echo $j ?>").change(function() {
        $("#toggle_<?php echo $j ?>").attr('checked',false);
      });
      $("#toggle_<?php echo $j ?>").change(function() {
        if ($('#toggle_<?php echo $j ?>').attr('checked')) {
          var defaultGlobal = $("#globaldefault_<?php echo $j ?>").val();
          $("#form_<?php echo $j ?>").val(defaultGlobal);
        }
      });
    <?php } ?>
    <?php } ?>

});

</script>

</html>

