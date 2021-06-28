<?php

/**
 * Script for the globals editor.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/globals.inc.php");
require_once("$srcdir/user.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;
use Ramsey\Uuid\Uuid;

// Set up crypto object
$cryptoGen = new CryptoGen();

$userMode = (array_key_exists('mode', $_GET) && $_GET['mode'] == 'user');

if (!$userMode) {
  // Check authorization.
    $thisauth = AclMain::aclCheckCore('admin', 'super');
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
                echo xlt("Failed to create temporary folder. CouchDB will not work.");
            }
        }

        $couch = new CouchDB();
        if (!$couch->check_connection()) {
            echo "<script>alert(" . xlj("CouchDB Connection Failed.") . ");</script>";
            return false;
        }

        if ($GLOBALS['couchdb_host'] || $GLOBALS['couchdb_port'] || $GLOBALS['couchdb_dbase']) {
            $couch->createDB();
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

    /**
     * Setup background services for Weno when it is enabled
     * this is to sync the prescription logs
     */
    $wenoservices = $GLOBALS['weno_rx_enable'] == 1 ? '1' : '0';
    updateBackgroundService('WenoExchange', $wenoservices, 1);
}
?>
<!DOCTYPE html>
<html>
<head>
<?php
// If we are saving user_specific globals.
//
if (array_key_exists('form_save', $_POST) && $_POST['form_save'] && $userMode) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $i = 0;
    foreach ($GLOBALS_METADATA as $grpname => $grparr) {
        if (in_array($grpname, $USER_SPECIFIC_TABS)) {
            foreach ($grparr as $fldid => $fldarr) {
                if (in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
                    list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                    $label = "global:" . $fldid;
                    if ($fldtype == "encrypted") {
                        if (empty(trim($_POST["form_$i"]))) {
                            $fldvalue = '';
                        } else {
                            $fldvalue = $cryptoGen->encryptStandard(trim($_POST["form_$i"]));
                        }
                    } else {
                        $fldvalue = trim($_POST["form_$i"]);
                    }
                    setUserSetting($label, $fldvalue, $_SESSION['authUserID'], false);
                    if ($_POST["toggle_$i"] == "YES") {
                        removeUserSetting($label);
                    }

                    ++$i;
                }
            }
        }
    }

    echo "<script>";
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
?>

<?php
// If we are saving main globals.
//
if (array_key_exists('form_save', $_POST) && $_POST['form_save'] && !$userMode) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

  // Aug 22, 2014: Ensoftek: For Auditable events and tamper-resistance (MU2)
  // Check the current status of Audit Logging
    $auditLogStatusFieldOld = $GLOBALS['enable_auditlog'];
    $forceBreakglassLogStatusFieldOld = $GLOBALS['gbl_force_log_breakglass'];

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

                if ($fldtype == 'encrypted') {
                    if (empty(trim($fldvalue))) {
                        $fldvalue = '';
                    } else {
                        $fldvalue = $cryptoGen->encryptStandard($fldvalue);
                    }
                }

                // We rely on the fact that set of keys in globals.inc === set of keys in `globals`  table!

                if (
                    !isset($old_globals[$fldid]) // if the key not found in database - update database
                    ||
                    ( isset($old_globals[$fldid]) && $old_globals[ $fldid ]['gl_value'] !== $fldvalue ) // if the value in database is different
                ) {
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

    // July 1, 2014: Ensoftek: For Auditable events and tamper-resistance (MU2)
    // If Audit Logging status has changed, log it.
    $auditLogStatusNew = sqlQuery("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'enable_auditlog'");
    $auditLogStatusFieldNew = $auditLogStatusNew['gl_value'];
    if ($auditLogStatusFieldOld != $auditLogStatusFieldNew) {
        EventAuditLogger::instance()->auditSQLAuditTamper('enable_auditlog', $auditLogStatusFieldNew);
    }
    $forceBreakglassLogStatusNew = sqlQuery("SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'gbl_force_log_breakglass'");
    $forceBreakglassLogStatusFieldNew = $forceBreakglassLogStatusNew['gl_value'];
    if ($forceBreakglassLogStatusFieldOld != $forceBreakglassLogStatusFieldNew) {
        EventAuditLogger::instance()->auditSQLAuditTamper('gbl_force_log_breakglass', $forceBreakglassLogStatusFieldNew);
    }

    echo "<script>";
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

<?php if ($userMode) { ?>
  <title><?php  echo xlt('User Settings'); ?></title>
<?php } else { ?>
  <title><?php echo xlt('Global Settings'); ?></title>
<?php } ?>

<?php Header::setupHeader(['common','jscolor']); ?>

<style>
#oe-nav-ul.tabNav {
    display: flex;
    flex-flow: column;
    max-width: 15%;
}
@media (max-width: 576px) {
  #oe-nav-ul.tabNav {
    max-width: inherit;
    width: 100%;
  }
  #globals-div .tabContainer {
    width: 100%;
  }
}
</style>
<?php
if ($userMode) {
    $heading_title = xl('Edit User Settings');
} else {
    $heading_title = xl('Edit Global Settings');
}
$arrOeUiSettings = array(
    'heading_title' => $heading_title,
    'include_patient_name' => false,// use only in appropriate pages
    'expandable' => true,
    'expandable_files' => array("edit_globals_xpd"),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => false,
    'help_file_name' => ""
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>

<body <?php if ($userMode) {
    echo 'style="min-width: 700px;"';
      } ?>>

    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
        <div class="row">
             <div class="col-sm-12">
                <div class="mt-3">
                    <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?php if ($userMode) { ?>
                <form method='post' name='theform' id='theform' class='form-horizontal' action='edit_globals.php?mode=user' onsubmit='return top.restoreSession()'>
                <?php } else { ?>
                <form method='post' name='theform' id='theform' class='form-horizontal' action='edit_globals.php' onsubmit='return top.restoreSession()'>
                <?php } ?>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="clearfix">
                        <div class="btn-group oe-margin-b-10">
                            <button type='submit' class='btn btn-primary btn-save oe-pull-toward' name='form_save' value='<?php echo xla('Save'); ?>'><?php echo xlt('Save'); ?></button>
                        </div>
                        <div class="input-group col-sm-4 oe-pull-away">
                        <?php // mdsupport - Optional server based searching mechanism for large number of fields on this screen.
                        if (!$userMode) {
                            $placeholder = xla('Search global settings');
                        } else {
                            $placeholder = xla('Search user settings');
                        }
                        ?>
                        <input name='srch_desc' id='srch_desc' class='form-control' type='text' placeholder='<?php echo $placeholder; ?>' value='<?php echo (!empty($_POST['srch_desc']) ? attr($_POST['srch_desc']) : '') ?>' />
                        <span class="input-group-append">
                            <button class="btn btn-secondary btn-search" type='submit' id='globals_form_search' name='form_search'><?php echo xlt('Search'); ?></button>
                        </span>
                        </div><!-- /input-group -->
                    </div>
                    <br />
                    <div id="globals-div">
                        <ul class="tabNav tabWidthWide sticky-top" id="oe-nav-ul">
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
                            $srch_item = 0;
                            foreach ($GLOBALS_METADATA as $grpname => $grparr) {
                                if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
                                    echo " <div class='tab w-100 h-auto" . ($i ? "" : " current") . "' style='font-size: 0.9rem'>\n";

                                    echo "<div class=''>";
                                    $addendum = $grpname == 'Appearance' ? ' (*' . xl("need to logout/login after changing these settings") . ')' : '';
                                    echo "<div class='col-sm-12 oe-global-tab-heading'><div class='oe-pull-toward' style='font-size: 1.4rem'>" . xlt($grpname) . " &nbsp;</div><div style='margin-top: 5px'>" . text($addendum) . "</div></div>";
                                    echo "<div class='clearfix'></div>";
                                    if ($userMode) {
                                        echo "<div class='row'>";
                                        echo "<div class='col-sm-4'>&nbsp</div>";
                                        echo "<div class='col-sm-4 font-weight-bold'>" . xlt('User Specific Setting') . "</div>";
                                        echo "<div class='col-sm-2 font-weight-bold'>" . xlt('Default Setting') . "</div>";
                                        echo "<div class='col-sm-2 font-weight-bold'>" . xlt('Default') . "</div>";
                                        echo "</div>";
                                    }

                                    foreach ($grparr as $fldid => $fldarr) {
                                        if (!$userMode || in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
                                            list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                                            // mdsupport - Check for matches
                                            $srch_cl = '';
                                            $highlight_search = false;

                                            if (!empty($_POST['srch_desc']) && (stristr(($fldname . $flddesc), $_POST['srch_desc']) !== false)) {
                                                $srch_cl = ' srch';
                                                $srch_item++;
                                                $highlight_search = true;
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
                                                    $userSettingArray = sqlQuery("SELECT * FROM user_settings WHERE setting_user=? AND setting_label=?", array($_SESSION['authUserID'],"global:" . $fldid));
                                                    $userSetting = $userSettingArray['setting_value'];
                                                    $globalValue = $fldvalue;
                                                if (!empty($userSettingArray)) {
                                                    $fldvalue = $userSetting;
                                                    $settingDefault = "";
                                                }
                                            }

                                            if ($userMode) {
                                                echo " <div class='row form-group" . $srch_cl  . "'><div class='col-sm-4 font-weight-bold'>" . ($highlight_search ? '<mark>' : '') . text($fldname) . ($highlight_search ? '</mark>' : '') . "</div><div class='col-sm-4 oe-input' title='" . attr($flddesc) . "'>\n";
                                            } else {
                                                echo " <div class='row form-group" . $srch_cl . "'><div class='col-sm-6 font-weight-bold'>" . ($highlight_search ? '<mark>' : '') . text($fldname) . ($highlight_search ? '</mark>' : '') . "</div><div class='col-sm-6 oe-input' title='" . attr($flddesc) . "'>\n";
                                            }

                                            if (is_array($fldtype)) {
                                                          echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
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
                                            } elseif ($fldtype == 'bool') {
                                                if ($userMode) {
                                                    if ($globalValue == 1) {
                                                        $globalTitle = xlt('Checked');
                                                    } else {
                                                        $globalTitle = xlt('Not Checked');
                                                    }
                                                }
                                                        echo "  <input type='checkbox' class='checkbox' name='form_$i' id='form_$i' value='1'";
                                                if ($fldvalue) {
                                                    echo " checked";
                                                }
                                                        echo " />\n";
                                            } elseif ($fldtype == 'num') {
                                                if ($userMode) {
                                                    $globalTitle = $globalValue;
                                                }
                                                        echo "  <input type='text' class='form-control' name='form_$i' id='form_$i' " .
                                                            "maxlength='15' value='" . attr($fldvalue) . "' />\n";
                                            } elseif ($fldtype == 'text') {
                                                if ($userMode) {
                                                    $globalTitle = $globalValue;
                                                }
                                                        echo "  <input type='text' class='form-control' name='form_$i' id='form_$i' " .
                                                            "maxlength='255' value='" . attr($fldvalue) . "' />\n";
                                            } elseif ($fldtype == 'if_empty_create_random_uuid') {
                                                if ($userMode) {
                                                    $globalTitle = $globalValue;
                                                }
                                                if (empty($fldvalue)) {
                                                    // if empty, then create a random uuid
                                                    $uuid4 = Uuid::uuid4();
                                                    $fldvalue = $uuid4->toString();
                                                }
                                                echo "  <input type='text' class='form-control' name='form_$i' id='form_$i' " .
                                                    "maxlength='255' value='" . attr($fldvalue) . "' />\n";
                                            } elseif ($fldtype == 'encrypted') {
                                                if (empty($fldvalue)) {
                                                    // empty value
                                                    $fldvalueDecrypted = '';
                                                } elseif ($cryptoGen->cryptCheckStandard($fldvalue)) {
                                                    // normal behavior when not empty
                                                    $fldvalueDecrypted = $cryptoGen->decryptStandard($fldvalue);
                                                } else {
                                                    // this is used when value has not yet been encrypted (only happens once when upgrading)
                                                    $fldvalueDecrypted = $fldvalue;
                                                }
                                                echo "  <input type='password' class='form-control' name='form_$i' id='form_$i' " .
                                                    "maxlength='255' value='" . attr($fldvalueDecrypted) . "' />\n";
                                                if ($userMode) {
                                                    if (empty($globalValue)) {
                                                        // empty value
                                                        $globalTitle = '';
                                                    } elseif ($cryptoGen->cryptCheckStandard($globalValue)) {
                                                        // normal behavior when not empty
                                                        $globalTitle = $cryptoGen->decryptStandard($globalValue);
                                                    } else {
                                                        // this is used when value has not yet been encrypted (only happens once when upgrading)
                                                        $globalTitle = $globalValue;
                                                    }
                                                }
                                                $fldvalueDecrypted = '';
                                            } elseif ($fldtype == 'pass') {
                                                if ($userMode) {
                                                    $globalTitle = $globalValue;
                                                }
                                                echo "  <input type='password' class='form-control' name='form_$i' " .
                                                "maxlength='255' value='" . attr($fldvalue) . "' />\n";
                                            } elseif ($fldtype == 'lang') {
                                                $res = sqlStatement("SELECT * FROM lang_languages ORDER BY lang_description");
                                                echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
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
                                            } elseif ($fldtype == 'all_code_types') {
                                                global $code_types;
                                                echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
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
                                            } elseif ($fldtype == 'm_lang') {
                                                $res = sqlStatement("SELECT * FROM lang_languages  ORDER BY lang_description");
                                                echo "  <select multiple class='form-control' name='form_{$i}[]' id='form_{$i}[]' size='3'>\n";
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
                                            } elseif ($fldtype == 'color_code') {
                                                if ($userMode) {
                                                    $globalTitle = $globalValue;
                                                }
                                                echo "  <input type='text' class='form-control jscolor {hash:true}' name='form_$i' id='form_$i' " .
                                                "maxlength='15' value='" . attr($fldvalue) . "' />" .
                                                "<input type='button' value='" . xla('Default') . "' onclick=\"document.forms[0].form_$i.jscolor.fromString(" . attr_js($flddef) . ")\">\n";
                                            } elseif ($fldtype == 'default_visit_category') {
                                                $sql = "SELECT pc_catid, pc_catname, pc_cattype
                                                FROM openemr_postcalendar_categories
                                                WHERE pc_active = 1 ORDER BY pc_seq";
                                                $result = sqlStatement($sql);
                                                echo "<select class='form-control' name='form_{$i}' id='form_{$i}'>\n";
                                                echo "<option value='_blank'>" . xlt('None{{Category}}') . "</option>";
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
                                            } elseif ($fldtype == 'css' || $fldtype == 'tabs_css') {
                                                if ($userMode) {
                                                    $globalTitle = $globalValue;
                                                }
                                                $themedir = "$webserver_root/public/themes";
                                                $dh = opendir($themedir);
                                                if ($dh) {
                                                    // Collect styles
                                                    $styleArray = array();
                                                    while (false !== ($tfname = readdir($dh))) {
                                                        // Only show files that contain tabs_style_ or style_ as options
                                                        if ($fldtype == 'tabs_css') {
                                                            $patternStyle = 'tabs_style_';
                                                        } else {
                                                            // $fldtype == 'css'
                                                            $patternStyle = 'style_';
                                                        }
                                                        if (
                                                            $tfname == 'style_blue.css' ||
                                                            $tfname == 'style_pdf.css' ||
                                                            !preg_match("/^" . $patternStyle . ".*\.css$/", $tfname)
                                                        ) {
                                                            continue;
                                                        }

                                                        if ($fldtype == 'tabs_css') {
                                                            // Drop the "tabs_style_" part and any replace any underscores with spaces
                                                            $styleDisplayName = str_replace("_", " ", substr($tfname, 11));
                                                        } else { // $fldtype == 'css'
                                                            // Drop the "style_" part and any replace any underscores with spaces
                                                            $styleDisplayName = str_replace("_", " ", substr($tfname, 6));
                                                        }
                                                        // Strip the ".css" and uppercase the first character
                                                        $styleDisplayName = ucfirst(str_replace(".css", "", $styleDisplayName));

                                                        $styleArray[$tfname] = $styleDisplayName;
                                                    }
                                                    // Alphabetize styles
                                                    asort($styleArray);
                                                    // Generate style selector
                                                    echo "<select class='form-control' name='form_$i' id='form_$i'>\n";
                                                    foreach ($styleArray as $styleKey => $styleValue) {
                                                        echo "<option value='" . attr($styleKey) . "'";
                                                        if ($styleKey == $fldvalue) {
                                                            echo " selected";
                                                        }
                                                        echo ">";
                                                        echo text($styleValue);
                                                        echo "</option>\n";
                                                    }
                                                    echo "</select>\n";
                                                }
                                                closedir($dh);
                                            } elseif ($fldtype == 'hour') {
                                                if ($userMode) {
                                                    $globalTitle = $globalValue;
                                                }

                                                echo "  <select class='form-control' name='form_$i' id='form_$i'>\n";
                                                for ($h = 0; $h < 24; ++$h) {
                                                    echo "<option value='$h'";
                                                    if ($h == $fldvalue) {
                                                        echo " selected";
                                                    }

                                                    echo ">";
                                                    if ($h ==  0) {
                                                        echo "12 AM";
                                                    } elseif ($h <  12) {
                                                        echo "$h AM";
                                                    } elseif ($h == 12) {
                                                        echo "12 PM";
                                                    } else {
                                                        echo ($h - 12) . " PM";
                                                    }

                                                    echo "</option>\n";
                                                }

                                                echo "  </select>\n";
                                            }

                                            if ($userMode) {
                                                echo " </div>\n";
                                                echo "<div class='col-sm-2 text-danger'>" . text($globalTitle) . "</div>\n";
                                                echo "<div class='col-sm-2 '><input type='checkbox' value='YES' name='toggle_" . $i . "' id='toggle_" . $i . "' " . $settingDefault . "/></div>\n";
                                                if ($fldtype == 'encrypted') {
                                                    echo "<input type='hidden' id='globaldefault_" . $i . "' value='" . attr($globalTitle) . "' />\n";
                                                } else {
                                                    echo "<input type='hidden' id='globaldefault_" . $i . "' value='" . attr($globalValue) . "' />\n";
                                                }
                                                echo "</div>\n";
                                            } else {
                                                          echo " </div></div>\n";
                                            }
                                            ++$i;
                                        }
                                    }

                                    echo "<div class='btn-group oe-margin-b-10'>" .
                                        "<button type='submit' class='btn btn-primary btn-save oe-pull-toward' name='form_save'" .
                                        "value='" . xla('Save') . "'>" . xlt('Save') . "</button></div>";
                                    echo "<div class='oe-pull-away oe-margin-t-10' style=''>" . xlt($grpname) . " &nbsp;<a href='#' class='text-dark text-decoration-none fa fa-lg fa-arrow-circle-up oe-help-redirect scroll' aria-hidden='true'></a></div><div class='clearfix'></div></div>";
                                    echo " </div>\n";
                                }
                            }
                            ?>
                        </div><!--End of tabContainer div-->
                    </div><!--End of globals-div div-->
                </form>
            </div>
        </div>
    </div><!--End of container div-->
<?php $oemr_ui->oeBelowContainerDiv();?>
</div>
<?php
$post_srch_desc = $_POST['srch_desc'] ?? '';
if (!empty($post_srch_desc) && $srch_item == 0) {
    echo "<script>alert(" . js_escape($post_srch_desc . " - " . xl('search term was not found, please try another search')) . ");</script>";
}
?>

<script>
$(function () {
    tabbify();
    <?php // mdsupport - Highlight search results ?>
    $('.srch div.control-label').wrapInner("<mark></mark>");
    $('.tab .row.srch :first-child').closest('.tab').each(function() {
        $('.tabNav li:nth-child(' + ($(this).index() + 1) + ') a').wrapInner("<mark></mark>");
    });
    // Use the counter ($i) to make the form user friendly for user-specific globals use
    <?php
    if ($userMode) {
        for ($j = 0; $j <= $i; $j++) { ?>
            $("#form_<?php echo $j ?>").change(function() {
                $("#toggle_<?php echo $j ?>").prop('checked', false);
            });
            $("#toggle_<?php echo $j ?>").change(function() {
                if ($('#toggle_<?php echo $j ?>').prop('checked')) {
                    var defaultGlobal = $("#globaldefault_<?php echo $j ?>").val();
                    $("#form_<?php echo $j ?>").val(defaultGlobal);
                }
            });
            <?php
        }
    }
    ?>
    $('#srch_desc').keypress(function (event) {
        if (event.which === 13 || event.keyCode === 13) {
            event.preventDefault();
            $('#globals_form_search').click();
        }
    });
});
$('.scroll').click(function() {
    if ($(window).scrollTop() == 0) {
        alert(<?php echo xlj("Already at the top of the page"); ?>);
    } else {
        window.parent.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    }
    return false;
});
</script>
</body>
</html>
