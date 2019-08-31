<?php
/**
 * Script for the globals editor.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once "../globals.php";
require_once "../../custom/code_types.inc.php";
require_once "$srcdir/acl.inc";
require_once "$srcdir/globals.inc.php";
require_once "$srcdir/user.inc";
require_once "../../myportal/soap_service/portal_connectivity.php";

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;
use Ramsey\Uuid\Uuid;
use OpenEMR\Admin\Service\SettingsService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @var Request
 */
$request = Request::createFromGlobals();

//Add the appropriate templates directory to the twig loader
$twig->getLoader()->addPath("../../src/Admin/templates/globals", "admin");

// Set up crypto object
$cryptoGen = new CryptoGen();

$settingsService = new SettingsService();

$userMode = (array_key_exists('mode', $_GET) && $_GET['mode'] == 'user');

if (!$userMode) {
  // Check authorization.
    $thisauth = acl_check('admin', 'super');
    if (!$thisauth) {
        die(xlt('Not authorized'));
    }
}

/**
 * @todo Move to Admin Service class
 */
function checkCreateCDB() {
    $sql = "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN 
            ('couchdb_host','couchdb_user','couchdb_pass','couchdb_port','couchdb_dbase','document_storage_method')";
    $globalsres = sqlStatement($sql);
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
            echo "<script type='text/javascript'>alert(" . xlj("CouchDB Connection Failed.") . ");</script>";
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
 * @todo Move to Admin Service class
 */
function updateBackgroundService($name, $active, $interval) {
    //order important here: next_run change dependent on _old_ value of execute_interval so it comes first
    $sql = "UPDATE background_services SET active=?, next_run = next_run + INTERVAL (? - execute_interval) MINUTE, execute_interval=? WHERE name=?";
    return sqlStatement($sql, [$active, $interval, $interval, $name]);
}

/**
 * @todo Move to Admin Service class
 * @todo Reformat this docblock
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
function checkBackgroundServices() {
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

// If we are saving user_specific globals.
if ($request->request->has('form_save') && $userMode) {
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
                    $label = "global:".$fldid;
                    if ($fldtype == "encrypted") {
                        if (empty(trim($_POST["form_$i"]))) {
                            $fldvalue = '';
                        } else {
                            $fldvalue = $cryptoGen->encryptStandard(trim($_POST["form_$i"]));
                        }
                    } else {
                        $fldvalue = trim($_POST["form_$i"]);
                    }
                    setUserSetting($label, $fldvalue, $_SESSION['authId'], false);
                    if ($_POST["toggle_$i"] == "YES") {
                        removeUserSetting($label);
                    }

                    ++$i;
                }
            }
        }
    }
    echo <<<EOD
        <script>
            if (parent.left_nav.location) {
              parent.left_nav.location.reload();
              parent.Title.location.reload();
              if(self.name=='RTop'){
              parent.RBot.location.reload();
              }else{
              parent.RTop.location.reload();
              }
            }
            self.location.href='edit_globals.php?mode=user&unique=yes';
        </script>
    EOD;
}

if ($request->request->has('form_download')) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $client = portal_connection();
    try {
        $response = $client->getPortalConnectionFiles($credentials);
    } catch (SoapFault $e) {
        error_log('SoapFault Error');
        error_log(errorLogEscape(var_dump(get_object_vars($e))));
    } catch (Exception $e) {
        error_log('Exception Error');
        error_log(errorLogEscape(var_dump(get_object_vars($e))));
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
        <script>
            alert(<?php echo xlj('Offsite Portal web Service Failed') ?> + ":\\n" + <?php echo js_escape($response['value']); ?>);
        </script>
        <?php
    }
}

// If we are saving main globals.
if ($request->request->has('form_save') && !$userMode) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
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

                if ($fldtype == 'encrypted') {
                    if (empty(trim($fldvalue))) {
                        $fldvalue = '';
                    } else {
                        $fldvalue = $cryptoGen->encryptStandard($fldvalue);
                    }
                }

                // We rely on the fact that set of keys in globals.inc === set of keys in `globals`  table!

                if (!isset($old_globals[$fldid]) || (isset($old_globals[$fldid]) && $old_globals[ $fldid ]['gl_value'] !== $fldvalue )) {
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
    $auditLogStatusNew = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'enable_auditlog'");
    $auditLogStatusFieldNew = $auditLogStatusNew['gl_value'];
    if ($auditLogStatusFieldOld != $auditLogStatusFieldNew) {
        EventAuditLogger::instance()->auditSQLAuditTamper($auditLogStatusFieldNew);
    }

    echo <<<EOD
        <script>
            if (parent.left_nav.location) {
                parent.left_nav.location.reload();
                parent.Title.location.reload();
                if(self.name=='RTop'){
                    parent.RBot.location.reload();
                }else{
                    parent.RTop.location.reload();
                }
            }
            self.location.href='edit_globals.php?unique=yes';
        </script>
    EOD;
}

$head_view_vars = [
    "title" => ($userMode) ? "User Settings" : "Global Settings",
    "csrfToken" => js_escape(CsrfUtils::collectCsrfToken()),
];

$header_view_vars = array(
    'page_title' => ($userMode) ? xlt("Edit User Settings") : xlt("Edit Global Settings"),
);

$sidebar_view_vars = [
    'menu' => array_keys($GLOBALS_METADATA),
    'user_mode' => $userMode,
    'user_tabs' => $USER_SPECIFIC_TABS,
];

$footer_view_vars = [
    'user_mode' => ($userMode) ? "yes" : "no"
];

$formAction = "edit_globals.php";
$formAction .= ($userMode) ? "?mode=user" : "";

$viewVars = array_merge($head_view_vars, $header_view_vars, $sidebar_view_vars, [
    'form_action' => $formAction,
    'csrf' => CsrfUtils::collectCsrfToken(),
]);
echo $twig->render("@admin/base.html.twig", $viewVars);

$i = 0;
$srch_item = 0;
foreach ($GLOBALS_METADATA as $grpname => $grparr) {
    if (!$userMode || in_array($grpname, $USER_SPECIFIC_TABS)) {
        $current = ($i) ? "" : "active";
        $addendum = ($grpname == 'Appearance') ? ' ('. xl("requires logout/login") .')' : '';
        ?>
        <div class="tab-pane <?php echo $current;?>" id="<?php echo $grpname;?>" role="tabpanel">
            <div class="page-header">
                <h1><?php echo xlt($grpname);?>&nbsp;<small><?php echo text($addendum);?></small></h1>
            </div>

            <?php if ($userMode): ?>
                <div class='row'>
                    <div class='col-sm-4 col-sm-offset-4'><b><?php echo xlt('User Specific Setting'); ?></b></div>
                    <div class='col-sm-2'><b><?php echo xlt('Default Setting');?></b></div>
                    <div class='col-sm-2'><b><?php echo xlt('Default');?></b></div>
                </div>
            <?php endif;

            foreach ($grparr as $fldid => $fldarr) {
                if (!$userMode || in_array($fldid, $USER_SPECIFIC_GLOBALS)) {
                    list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                    $srch_cl = '';

                    if ($request->request->has("search_query") && (stristr("{$fldname}{$flddesc}", $request->request->get("search_query")) !== false)) {
                        $srch_cl = ' srch';
                        $srch_item++;
                    }

                    // Most parameters will have a single value, but some will be arrays.
                    // Here we cater to both possibilities.
                    $glres = sqlStatement("SELECT gl_index, gl_value FROM globals WHERE gl_name = ? ORDER BY gl_index", array($fldid));
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

                    // Display the title of the setting
                    $divClass = ($userMode) ? "col-sm-4" : "col-sm-6";
                    $label = text($fldname);
                    $title = attr($flddesc);
                    echo "<div class='row form-group {$srch_cl}'><div class='{$divClass} control-label'>{$label}</div><div class='col-sm-6'  title='$title'>\n";

                    $globalVars = [
                        "field_name" => "form_{$i}",
                        "field_type" => $fldtype,
                        "value" => $fldvalue,
                    ];
                    $vars = [];

                    switch ($fldtype) {
                        case "bool":
                            if ($userMode) {
                                $globalTitle = ($globalValue == 1) ? xlt("Checked") : xlt("Not Checked");
                            }
                            $vars = array_merge($globalVars, [
                                'checked' => ($fldvalue) ? "checked" : "",
                            ]);
                            $template = "partials/bool.html.twig";
                            break;
                        case "num":
                            if ($userMode) {
                                $globalTitle = $globalValue;
                            }
                            $vars = array_merge($globalVars, ['maxlength' => '15']);
                            $template = "partials/text.html.twig";
                            break;
                        case "text":
                            if ($userMode) {
                                $globalTitle = $globalValue;
                            }
                            $vars = $globalVars;
                            $template = "partials/text.html.twig";
                            break;
                        case "if_empty_create_random_uuid":
                            if ($userMode) {
                                $globalTitle = $globalValue;
                            }
                            if (empty($fldvalue)) {
                                $uuid = Uuid::uuid4();
                                $globalVars["value"] = $uuid->toString();
                            }
                            $vars = $globalVars;
                            $template = "partials/text.html.twig";
                            break;
                        case "encrypted":
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
                            $globalVars['value'] = $fldvalueDecrypted;
                            $vars = $globalVars;
                            $template = "partials/text.html.twig";
                            $fldvalueDecrypted = '';
                            break;
                        case "pwd":
                            if ($userMode) {
                                $globalTitle = $globalValue;
                            }
                            $globalVars['value'] = "";
                            $vars = array_merge($globalVars, ['type' => 'password']);
                            $template = "partials/text.html.twig";
                            break;
                        case "pass":
                            if ($userMode) {
                                $globalTitle = $globalValue;
                            }
                            $vars = array_merge($globalVars, ['type' => 'password']);
                            $template = "partials/text.html.twig";
                            break;
                        case "lang":
                            $res = sqlStatement("SELECT * FROM lang_languages ORDER BY lang_description");
                            $languages = [];
                            while ($row = sqlFetchArray($res)) {
                                $languages[$row['lang_description']] = xlt($row['lang_description']);
                            }
                            $vars = array_merge($globalVars, ['field_value' => $fldvalue, 'options' => $languages]);
                            $template = "partials/array.html.twig";
                            break;
                        case 'all_code_types':
                            global $code_types;
                            $options = [];
                            foreach (array_keys($code_types) as $code_key) {
                                var_dump($code_key);
                                $options[$code_key] = xlt($code_types[$code_key]['label']);
                            }
                            $vars = array_merge($globalVars, ['field_values' => $fldvalue, 'options' => $options]);
                            $template = "partials/array.html.twig";
                            break;
                        case "m_lang":
                            $res = sqlStatement("SELECT * FROM lang_languages  ORDER BY lang_description");
                            $languages = [];
                            $selected = [];
                            while ($row = sqlFetchArray($res)) {
                                $languages[$row['lang_description']] = xlt($row['lang_description']);
                                foreach ($glarr as $glrow) {
                                    if ($glrow['gl_value'] == $row['lang_description']) {
                                        $selected[] = $row['lang_description'];
                                        break;
                                    }
                                }
                            }
                            $vars = array_merge($globalVars, [
                                'field_value' => $selected,
                                'options' => $languages,
                                'classes' => "multi-lang",
                                'multi' => 'multiple',
                            ]);
                            $template = "partials/array.html.twig";
                            break;
                        case "color_code":
                            if ($userMode) {
                                $globalTitle = $globalValue;
                            }
                            $vars = array_merge($globalVars, [
                                'classes' => 'jscolor {hash:true}',
                                'maxlength' => '15',
                                'field_def' => $flddef,
                            ]);
                            $template = "partials/text.html.twig";
                            break;
                        case "default_visit_category":
                            $sql = "SELECT pc_catid, pc_catname, pc_cattype
                                    FROM openemr_postcalendar_categories
                                    WHERE pc_active = 1 ORDER BY pc_seq";
                            $result = sqlStatement($sql);
                            $options[] = [
                                '_blank' => xlt("None"),
                            ];
                            while ($row = sqlFetchArray($result)) {
                                if ($row['pc_catid'] < 9 && $row['pc_catid'] != "5") {
                                    continue;
                                }

                                if ($row['pc_cattype'] == 3 && !$GLOBALS['enable_group_therapy']) {
                                    continue;
                                }

                                $options[$row['pc_catid']] = text(xl_appt_category($row['pc_catname']));
                            }
                            $vars = array_merge($globalVars, [
                                'field_value' => $fldvalue,
                                'options' => $options,
                            ]);
                            $template = "partials/array.html.twig";
                            break;
                        case "tabs":
                        case "tabs_css":
                        case "css":
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
                                    $patternStyle = ($fldtype == "tabs_css") ? "tabs_style" : "style_";
                                    $drop = ($fldtype == "tabs_css") ? 11 : 6;
                                    if ($tfname == 'style_blue.css' ||
                                        $tfname == 'style_pdf.css' ||
                                        !preg_match("/^" . $patternStyle . ".*\.css$/", $tfname)) {
                                        continue;
                                    }

                                    // Replace "tabs_css_" or "style_" from name
                                    $styleDisplayName = str_replace("_", " ", substr($tfname, $drop));
                                    // Strip the ".css" and uppercase the first character
                                    $styleDisplayName = ucfirst(str_replace(".css", "", $styleDisplayName));
                                    $styleArray[$tfname] = $styleDisplayName;
                                }
                                // Alphabetize styles
                                asort($styleArray);
                                $vars = array_merge($globalVars, [
                                    'options' => $styleArray,
                                    'field_value' => $fldvalue,
                                ]);
                                $template = "partials/array.html.twig";
                            }
                            closedir($dh);
                            break;
                        case "hour":
                            if ($userMode) {
                                $globalTitle = $globalValue;
                            }
                            $options = [];
                            for ($h = 0; $h < 24; ++$h) {
                                if ($h == 0) {
                                    $val = "12 AM";
                                } elseif ($h < 12) {
                                    $val = "{$h} AM";
                                } elseif ($h == 12) {
                                    $val = "12 PM";
                                } else {
                                    $val = ($h - 12) . " PM";
                                }
                                $options[$val] = $val;
                            }
                            $vars = array_merge($globalVars, ['field_value' => $fldvalue, 'options' => $options]);
                            break;
                    }

                    if (is_array($fldtype)) {
                        foreach ($fldtype as $key => $value) {
                            if ($userMode && ($globalValue == $key)) {
                                $globalTitle = $value;
                            }
                        }

                        $vars = array_merge($globalVars, [
                            'field_value' => $fldvalue,
                            'options' => $fldtype,
                        ]);
                        $template = "partials/array.html.twig";
                    }

                    if (!empty($template)) {
                        echo $twig->render("@admin/{$template}", $vars);
                        $template = "";
                        $vars = [];
                    }

                    if ($userMode) {
                        echo " </div>\n";
                        echo "<div class='col-sm-2 text-danger'>" . text($globalTitle) . "</div>\n";
                        echo "<div class='col-sm-2 '><input type='checkbox' value='YES' name='toggle_" . $i . "' id='toggle_" . $i . "' " . $settingDefault . "/></div>\n";
                        if ($fldtype == 'encrypted') {
                            echo "<input type='hidden' id='globaldefault_" . $i . "' value='" . attr($globalTitle) . "'>\n";
                        } else {
                            echo "<input type='hidden' id='globaldefault_" . $i . "' value='" . attr($globalValue) . "'>\n";
                        }
                        echo "</div>\n";
                    } else {
                        echo " </div></div>\n";
                    }
                    ++$i;
                }

                if (trim(strtolower($fldid)) == 'portal_offsite_address_patient_link' && !empty($GLOBALS['portal_offsite_enable']) && !empty($GLOBALS['portal_offsite_providerid'])) {
                    echo $twig->render("@admin/partials/portal_offside.html.twig");
                }
            }

            echo "<div><div class='oe-pull-away oe-margin-t-10' style=''>". xlt($grpname) ." &nbsp;<i class='fa fa-lg fa-arrow-circle-up oe-help-redirect scroll' aria-hidden='true'></i></div><div class='clearfix'></div></div>";
            echo "</div>";
    }
}
?>
            </div>
            </form>
        </div>
    </div>
</div><!--End of container div-->
</div>
</body>
<?php
$post_srch_desc = $_POST['srch_desc'];
if (!empty($post_srch_desc) && $srch_item == 0) {
    echo "<script>alert(" . js_escape($post_srch_desc." - ".xl('search term was not found, please try another search')) . ");</script>";
}

echo $twig->render("@admin/partials/footer.html.twig", $footer_view_vars);
?>
</html>
