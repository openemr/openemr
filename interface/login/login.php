<?php

/**
 * Login screen.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Scott Wakefield <scott.wakefield@gmail.com>
 * @author  ViCarePlus <visolve_emr@visolve.com>
 * @author  Julia Longtin <julialongtin@diasp.org>
 * @author  cfapress
 * @author  markleeds
 * @author  Tyler Wrenn <tyler@tylerwrenn.com>
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Daniel Pflieger <daniel@mi-squared.com> <daniel@growlingflea.com>
 * @author  Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@mi-squared.com> <daniel@growlingflea.com>
 * @copyright Copyright (c) 2021-2022 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\FacilityService;

$ignoreAuth = true;
// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;
require_once("../globals.php");

$twig = new TwigContainer(null, $GLOBALS["kernel"]);
$t = $twig->getTwig();

// mdsupport - Add 'App' functionality for user interfaces without standard menu and frames
// If this script is called with app parameter, validate it without showing other apps.
//
// Build a list of valid entries
$emr_app = array();
$sql = "SELECT option_id, title,is_default FROM list_options WHERE list_id=? and activity=1 ORDER BY seq, option_id";
$rs = sqlStatement($sql, ['apps']);
if (sqlNumRows($rs)) {
    while ($app = sqlFetchArray($rs)) {
        $app_req = explode('?', trim($app['title']));
        if (! file_exists('../' . $app_req[0])) {
            continue;
        }

        $emr_app [trim($app ['option_id'])] = trim($app ['title']);
        if ($app ['is_default']) {
            $emr_app_def = $app ['option_id'];
        }
    }
}

$div_app = '';
if (count($emr_app)) {
    // Standard app must exist
    $std_app = 'main/main_screen.php';
    if (!in_array($std_app, $emr_app)) {
        $emr_app['*OpenEMR'] = $std_app;
    }

    if (isset($_REQUEST['app']) && $emr_app[$_REQUEST['app']]) {
        $div_app = sprintf('<input type="hidden" name="appChoice" value="%s">', attr($_REQUEST['app']));
    } else {
        foreach ($emr_app as $opt_disp => $opt_value) {
            $opt_htm .= sprintf(
                '<option value="%s" %s>%s</option>\n',
                attr($opt_disp),
                ($opt_disp == $opt_default ? 'selected="selected"' : ''),
                text(xl_list_label($opt_disp))
            );
        }

        $div_app = sprintf(
            '
            <div id="divApp" class="form-group">
                <label for="appChoice" class="text-right">%s:</label>
                <div>
                    <select class="form-control" id="selApp" name="appChoice" size="1">%s</select>
                </div>
            </div>',
            xlt('App'),
            $opt_htm
        );
    }
}

// This code allows configurable positioning in the login page
$logoarea = "py-2 px-2 py-md-3 px-md-5 order-1 bg-primary";
$formarea = "py-3 px-2 p-sm-5 bg-white order-2";
$loginrow = "row login-row bg-white shadow-lg align-items-center my-sm-5";

// Apply these classes to the logo area if the login page is left or right
$lrArr = ['left', 'right'];
$logoarea .= (in_array($GLOBALS['login_page_layout'], $lrArr)) ? " col-md-6" : " col-md-12";
$formarea .= (in_array($GLOBALS['login_page_layout'], $lrArr)) ? " col-md-6" : " col-md-12";

// More finite control on a per-setting basis
switch ($GLOBALS['login_page_layout']) {
    case 'left':
        $logoarea .= " order-md-2";
        $formarea .= " order-md-1";
        break;

    case 'right':
        $logoarea .= " order-md-1";
        $formarea .= " order-md-2";
        break;

    default:
        $logoarea .= " order-1";
        $formarea .= " col-12";
        $loginrow .= " login-row-center";
        break;
}

function getDefaultLanguage(): array
{
    $sql = "SELECT * FROM lang_languages where lang_description = ?";
    $res = sqlStatement($sql, [$GLOBALS['language_default']]);
    $langs = [];

    while ($row = sqlFetchArray($res)) {
        $langs[] = $row;
    }

    $id = 1;
    $desc = "English";

    if (count($langs) == 1) {
        $id = $langs[0]["lang_id"];
        $desc = $langs[0]["lang_description"];
    }

    return ["id" => $id, "language" => $desc];
}

function getLanguagesList(): array
{
    $mainLangID = empty($_SESSION['language_choice']) ? '1' : $_SESSION['language_choice'];
    $sql = "SELECT ll.lang_id, IF(LENGTH(ld.definition), ld.definition, ll.lang_description) AS trans_lang_description, ll.lang_description
        FROM lang_languages AS ll
        LEFT JOIN lang_constants AS lc ON lc.constant_name = ll.lang_description
        LEFT JOIN lang_definitions AS ld ON ld.cons_id = lc.cons_id AND ld.lang_id = ?
        ORDER BY IF(LENGTH(ld.definition),ld.definition,ll.lang_description), ll.lang_id";
    $res = sqlStatement($sql, [$mainLangID]);
    $langList = [];

    while ($row = sqlFetchArray($res)) {
        $langList[] = $row;
    }

    return $langList;
}

$facilities = [];
$facilitySelected = false;
if ($GLOBALS['login_into_facility']) {
    $facilityService = new FacilityService();
    $facilities = $facilityService->getAllFacility();
    $facilitySelected = ($GLOBALS['set_facility_cookie'] && isset($_COOKIE['pc_facility'])) ? $_COOKIE['pc_facility'] : null;
}

$defaultLanguage = getDefaultLanguage();
$languageList = getLanguagesList();
$_SESSION['language_choice'] = $defaultLanguage['id'];

$relogin = (isset($_SESSION['relogin']) && ($_SESSION['relogin'] == 1)) ? true : false;
if ($relogin) {
    unset($_SESSION["relogin"]);
}

$t1 = $GLOBALS['tiny_logo_1'];
$t2 = $GLOBALS['tiny_logo_2'];
$displayTinyLogo = false;
if ($t1 && !$t2) {
    $displayTinyLogo = 1;
} if ($t2 && !$t1) {
    $displayTinyLogo = 2;
} if ($t1 && $t2) {
    $displayTinyLogo = 3;
}

$regTranslations = json_encode(array(
    'title' => xla('OpenEMR Product Registration'),
    'pleaseProvideValidEmail' => xla('Please provide a valid email address'),
    'success' => xla('Success'),
    'registeredSuccess' => xla('Your installation of OpenEMR has been registered'),
    'submit' => xla('Submit'),
    'noThanks' => xla('No Thanks'),
    'registeredEmail' => xla('Registered email'),
    'registeredId' => xla('Registered id'),
    'genericError' => xla('Error. Try again later'),
    'closeTooltip' => ''
));

$cookie = '';
if (session_name()) {
    $sid = urlencode(session_id());
    $sname = urlencode(session_name());
    $scparams = session_get_cookie_params();
    $domain = $scparams['domain'];
    $path = $scparams['path'];
    $oldDate = gmdate('Y', strtotime("-1 years"));
    $expires = gmdate(DATE_RFC1123, $oldDate);
    $sameSite = empty($scparams['samesite']) ? '' : $scparams['samesite'];
    $cookie = "{$sname}={$sid}; path={$path}; domain={$domain}; expires={$expires}";

    if ($sameSite) {
        $cookie .= "; SameSite={$sameSite}";
    }

    $cookie = json_encode($cookie);
}

$viewArgs = [
    'title' => $openemr_name,
    'displayLanguage' => ($GLOBALS["language_menu_login"] && (count($languageList) != 1)) ? true : false,
    'defaultLangID' => $defaultLanguage['id'],
    'defaultLangName' => $defaultLanguage['language'],
    'languageList' => $languageList,
    'relogin' => $relogin,
    'loginFail' => (isset($_SESSION["loginfailure"]) && $_SESSION["loginfailure"] == 1) ? true : false,
    'displayFacilities' => ($GLOBALS["login_into_facility"]) ? true : false,
    'facilityList' => $facilities,
    'facilitySelected' => $facilitySelected,
    'displayGoogleSignin' => (!empty($GLOBALS['google_signin_enabled']) && !empty($GLOBALS['google_signin_client_id'])) ? true : false,
    'googleSigninClientID' => $GLOBALS['google_signin_client_id'],
    'logoArea' => $logoarea,
    'displayExtraLogo' => $GLOBALS['extra_logo_login'],
    'primaryLogoSrc' => file_get_contents($GLOBALS["images_static_absolute"] . "/login-logo.svg"),
    'logocode' => $logocode,
    'displayLoginLabel' => ($GLOBALS["show_label_login"]) ? true : false,
    'displayTinyLogo' => $displayTinyLogo,
    'tinyLogo1' => $tinylogocode1,
    'tinyLogo2' => $tinylogocode2,
    'displayTagline' => $GLOBALS['show_tagline_on_login'],
    'tagline' => $GLOBALS['login_tagline_text'],
    'displayAck' => $GLOBALS['display_acknowledgements'],
    'hasSession' => (session_name()) ? true : false,
    'cookieText' => $cookie,
    'regTranslations' => $regTranslations,
    'regConstants' => json_encode(['webroot' => $GLOBALS['webroot']]),
    'siteID' => $_SESSION['site_id'],
    'loginRow' => $loginrow,
    'formArea' => $formarea,
    'showLabels' => $GLOBALS['show_labels_on_login_form'],
];
echo $t->render("login/login_core.html.twig", $viewArgs);
