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
use Ramsey\Uuid\Uuid;
use OpenEMR\Admin\Service\SettingsService;
use Symfony\Component\HttpFoundation\Request;

/** @var Request */
$request = Request::createFromGlobals();

// Grab Twig environment and add the admin templates dir with "admin" namespace
$twig = $GLOBALS['twig'];
$twig->getLoader()->addPath("../../src/Admin/templates/globals", "admin");

global $GLOBALS_METADATA;
global $USER_SPECIFIC_TABS;
global $USER_SPECIFIC_GLOBALS;

// Set up crypto object
$cryptoGen = new CryptoGen();

$settingsService = new SettingsService($GLOBALS_METADATA, $USER_SPECIFIC_TABS, $USER_SPECIFIC_GLOBALS, $cryptoGen);

/** @var bool $isUser */
$isUser = ($request->query->get("user") === "user") ? true : false;
$isUser = (array_key_exists('mode', $_GET) && $_GET['mode'] == 'user');

if (!$isUser) {
  // Check authorization.
    $thisauth = acl_check('admin', 'super');
    if (!$thisauth) {
        die(xlt('Not authorized'));
    }
}

$includeReload = false;
$includeWebServiceFailure = false;

// If we are saving user_specific globals.
if ($request->request->has('form_save') && $isUser) {
    $settingsService->saveUserSettings($request->request->all());
    $includeReload = true;
}

if ($request->request->has('form_download')) {
    //verify csrf
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $client = portal_connection();
    try {
        global $credentials;
        $response = $client->getPortalConnectionFiles($credentials);
    } catch (SoapFault $e) {
        error_log('SoapFault Error');
        error_log(errorLogEscape(var_dump(get_object_vars($e))));
    } catch (Exception $e) {
        error_log('Exception Error');
        error_log(errorLogEscape(var_dump(get_object_vars($e))));
    }

    if (array_key_exists('status', $response) && $response['status'] == "1") { //WEBSERVICE RETURNED VALUE SUCCESSFULLY
        $tmpfilename = realpath(sys_get_temp_dir())."/".date('YmdHis').".zip";
        $fp = fopen($tmpfilename, "wb");
        fwrite($fp, base64_decode($response['value']));
        fclose($fp);
        $practice_filename = $response['file_name']; //practicename.zip
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
    } else { //WEBSERVICE CALL FAILED AND RETURNED AN ERROR MESSAGE
        ob_end_clean();
        $includeWebServiceFailure = true;
        $webserviceResponseValue = $response['value'];
    }
}

// If we are saving main globals.
if ($request->request->has('form_save') && !$isUser) {
    $settingsService->saveGlobalSettings($request->request->all());
    $includeReload = true;
}

$formAction = "edit_globals.php";
$formAction .= ($isUser) ? "?mode=user" : "";

$viewVars = [
    "title" => ($isUser) ? xlt("User Settings") : xlt("Global Settings"),
    "csrfToken" => js_escape(CsrfUtils::collectCsrfToken()),
    'page_title' => ($isUser) ? xlt("Edit User Settings") : xlt("Edit Global Settings"),
    'search_query' => $request->request->get('search_query'),
    'menu' => $settingsService->getSectionNames(),
    'isUser' => $isUser,
    'user_tabs' => $settingsService->getSectionNames(true),
    'form_action' => $formAction,
    'csrf' => CsrfUtils::collectCsrfToken(),
    'valid_text_inputs' => ['text', 'num', 'if_empty_create_random_uuid', 'encrypted', 'pwd', 'pass', 'lang', 'all_code_types', 'm_lang', 'color_code'],
    'valid_array_inputs' => ['default_visit_category', 'tabs', 'tabs_css', 'css'],
    'includeReload' => $includeReload,
    'includeWebServiceFailure' => $includeWebServiceFailure,
];

if ($includeWebServiceFailure) {
    $viewVars['value'] = $response['value'];
}

$srch_item = 0;
$sections = [];

foreach ($settingsService->getAllSections() as $sectionName => $sectionFields) {
    if ($isUser && !$settingsService->isUserSection($sectionName)) {
        continue;
    }

    $sectionVars = [
        'sectionName' => $sectionName,
        'addendum' => ($sectionName == "Appearance") ? xl("required logout/login") : false,
        'fields' => [],
    ];

    $fields = [];
    $i = 0;

    foreach ($sectionFields as $fieldID => $fieldDetails) {
        $tmp = [];
        if ($isUser && !$settingsService->isUserField($fieldID)) {
            continue;
        }

        list($name, $type, $definition, $description) = $fieldDetails;

        if ($request->request->has("search_query") && (stristr("{name}{$description}", $request->request->get("search_query")) !== false)) {
            $tmp['search_class'] = 'srch';
            $srch_item++;
        }

        $glres = sqlStatement("SELECT gl_index, gl_value FROM globals WHERE gl_name = ? ORDER BY gl_index", array($fieldID));
        $glarr = array();
        while ($glrow = sqlFetchArray($glres)) {
            $glarr[] = $glrow;
        }

        $fieldValue = count($glarr) ? $glarr[0]['gl_value'] : $definition;

        // Collect user specific setting if mode set to user
        $userSetting = "";
        $settingDefault = "checked='checked'";
        if ($isUser) {
            $userSettingArray = sqlQuery("SELECT * FROM user_settings WHERE setting_user=? AND setting_label=?", array($_SESSION['authId'],"global:".$fieldID));
            $userSetting = $userSettingArray['setting_value'];
            $globalValue = $fieldValue;
            if (!empty($userSettingArray)) {
                $fldvalue = $userSetting;
                $settingDefault = "";
            }
        }

        // Display the title of the setting
        $tmp = [
            'label' => text($name),
            'title' => $description,
            'name' => "form_{$i}",
            'type' => $type,
            'value' => $fieldValue,
        ];

        switch ($type) {
            case "bool":
                if ($isUser) {
                    $globalTitle = ($globalValue == 1) ? xlt("Checked") : xlt("Not Checked");
                }
                $vars = array_merge($tmp, [
                    'checked' => ($fldvalue) ? "checked" : "",
                ]);
                break;
            case "num":
                if ($isUser) {
                    $globalTitle = $globalValue;
                }
                $vars = array_merge($tmp, ['maxlength' => '15']);
                break;
            case "text":
                if ($isUser) {
                    $globalTitle = $globalValue;
                }
                break;
            case "if_empty_create_random_uuid":
                if ($isUser) {
                    $globalTitle = $globalValue;
                }
                if (empty($fldvalue)) {
                    $uuid = Uuid::uuid4();
                    $tmp["value"] = $uuid->toString();
                }
                break;
            case "encrypted":
                if ($isUser) {
                    if (empty($tmp)) {
                        $globalTitle = '';
                    } elseif ($cryptoGen->cryptCheckStandard($globalValue)) {
                        // normal behavior when not empty
                        $globalTitle = $cryptoGen->decryptStandard($globalValue);
                    } else {
                        // this is used when value has not yet been encrypted (only happens once when upgrading)
                        $globalTitle = $globalValue;
                    }
                }
                if (empty($fieldValue)) {
                    // empty value
                    $fldvalueDecrypted = '';
                } elseif ($cryptoGen->cryptCheckStandard($fldvalue)) {
                    // normal behavior when not empty
                    $fldvalueDecrypted = $cryptoGen->decryptStandard($fldvalue);
                } else {
                    // this is used when value has not yet been encrypted (only happens once when upgrading)
                    $fldvalueDecrypted = $fldvalue;
                }
                $tmp['value'] = $fldvalueDecrypted;
                $fldvalueDecrypted = '';
                break;
            case "pwd":
                if ($isUser) {
                    $globalTitle = $globalValue;
                }
                $tmp['value'] = "";
                $tmp['type'] = "password";
                break;
            case "pass":
                if ($isUser) {
                    $globalTitle = $globalValue;
                }
                $tmp['type'] = "password";
                break;
            case "lang":
                $res = sqlStatement("SELECT * FROM lang_languages ORDER BY lang_description");
                $languages = [];
                while ($row = sqlFetchArray($res)) {
                    $languages[$row['lang_description']] = xlt($row['lang_description']);
                }
                $tmp['value'] = $fieldValue;
                $tmp['options'] = $languages;
                break;
            case 'all_code_types':
                global $code_types;
                $options = [];
                foreach (array_keys($code_types) as $code_key) {
                    $options[$code_key] = xlt($code_types[$code_key]['label']);
                }
                $tmp['value'] = $fieldValue;
                $tmp['options'] = $options;
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
                $tmp['value'] = $selected;
                $tmp['options'] = $languages;
                $tmp['classes'] = 'multi-lang';
                $tmp['multi'] = 'multiple';
                break;
            case "color_code":
                if ($isUser) {
                    $globalTitle = $globalValue;
                }
                $tmp['classes'] = ['jscolor', '{hash:true'];
                $tmp['maxlength'] = '15';
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
                $tmp['options'] = $options;
                break;
            case "tabs":
            case "tabs_css":
            case "css":
                if ($isUser) {
                    $globalTitle = $globalValue;
                }
                $themedir = "$webserver_root/public/themes";
                $dh = opendir($themedir);
                if ($dh) {
                    // Collect styles
                    $styleArray = array();
                    while (false !== ($tfname = readdir($dh))) {
                        // Only show files that contain tabs_style_ or style_ as options
                        $patternStyle = ($type == "tabs_css") ? "tabs_style" : "style_";
                        $drop = ($type == "tabs_css") ? 11 : 6;
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
                    $tmp['options'] = $styleArray;
                }
                closedir($dh);
                break;
            case "hour":
                if ($isUser) {
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
                $tmp['options'] = $options;
                break;
        }

        if (is_array($type)) {
            foreach ($type as $key => $value) {
                if ($isUser && ($globalValue == $key)) {
                    $globalTitle = $value;
                }
            }

            $tmp['options'] = $type;
            $tmp['type'] = 'array';
        }

        if (trim(strtolower($fieldID)) == 'portal_offsite_address_patient_link' && !empty($GLOBALS['portal_offsite_enable']) && !empty($GLOBALS['portal_offsite_providerid'])) {
            $tmp['include_offsite_portal'] = true;
        }

        $tmp['globalTitle'] = $globalTitle;
        $tmp['globalValue'] = $globalValue;
        $tmp['description'] = $description;
        $tmp['definition'] = $definition;

        $fields[] = $tmp;
        $i++;
    }

    $sectionVars['fields'] = $fields;
    $sections[] = $sectionVars;
}

if ($request->request->has('search_query') && $srch_item == 0) {
    $viewVars['noSearchResults'] = true;
}

$viewVars['sections'] = $sections;
echo $twig->render("@admin/globals.html.twig", $viewVars);
