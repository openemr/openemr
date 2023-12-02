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
 * @copyright Copyright (c) 2021-2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// prevent UI redressing
Header("X-Frame-Options: DENY");
Header("Content-Security-Policy: frame-ancestors 'none'");

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\LogoService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

$ignoreAuth = true;
// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;
require_once("../globals.php");

$twig = new TwigContainer(null, $GLOBALS["kernel"]);
$t = $twig->getTwig();

$logoService = new LogoService();
$primaryLogo = $logoService->getLogo("core/login/primary");
$secondaryLogo = $logoService->getLogo("core/login/secondary");
$smallLogoOne = $logoService->getLogo("core/login/small_logo_1");
$smallLogoTwo = $logoService->getLogo("core/login/small_logo_2");

$layout = $GLOBALS['login_page_layout'];

// mdsupport - Add 'App' functionality for user interfaces without standard menu and frames
// If this script is called with app parameter, validate it without showing other apps.
//
// Build a list of valid entries
// Original merge v5.0.1
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
        $opt_htm = '';
        foreach ($emr_app as $opt_disp => $opt_value) {
            $opt_htm .= sprintf(
                '<option value="%s" %s>%s</option>\n',
                attr($opt_disp),
                ($opt_disp == ($emr_app_def ?? '') ? 'selected="selected"' : ''),
                text(xl_list_label($opt_disp))
            );
        }

        $div_app = sprintf(
            '
            <div id="divApp" class="form-group row">
                <label for="appChoice" class="col-form-label col-sm-4">%s:</label>
                <div class="col">
                    <select class="form-control" id="selApp" name="appChoice" size="1">%s</select>
                </div>
            </div>',
            xlt('App'),
            $opt_htm
        );
    }
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
        if (!$GLOBALS['allow_debug_language'] && $row['lang_description'] == 'dummy') {
            continue; // skip the dummy language
        }

        if ($GLOBALS['language_menu_showall']) {
            $langList[] = $row;
        } else {
            if (in_array($row['lang_description'], $GLOBALS['language_menu_show'])) {
                $langList[] = $row;
            }
        }
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
$displaySmallLogo = false;
if ($t1 && !$t2) {
    $displaySmallLogo = 1;
} if ($t2 && !$t1) {
    $displaySmallLogo = 2;
} if ($t1 && $t2) {
    $displaySmallLogo = 3;
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
    'displayLanguage' => $GLOBALS["language_menu_login"] && (count($languageList) != 1),
    'defaultLangID' => $defaultLanguage['id'],
    'defaultLangName' => $defaultLanguage['language'],
    'languageList' => $languageList,
    'relogin' => $relogin,
    'loginFail' => isset($_SESSION["loginfailure"]) && $_SESSION["loginfailure"] == 1,
    'displayFacilities' => (bool)$GLOBALS["login_into_facility"],
    'facilityList' => $facilities,
    'facilitySelected' => $facilitySelected,
    'displayGoogleSignin' => !empty($GLOBALS['google_signin_enabled']) && !empty($GLOBALS['google_signin_client_id']),
    'googleSigninClientID' => $GLOBALS['google_signin_client_id'],
    'displaySmallLogo' => $displaySmallLogo,
    'smallLogoOne' => $smallLogoOne,
    'smallLogoTwo' => $smallLogoTwo,
    'showTitleOnLogin' => $GLOBALS['show_label_login'],
    'displayTagline' => $GLOBALS['show_tagline_on_login'],
    'tagline' => $GLOBALS['login_tagline_text'],
    'displayAck' => $GLOBALS['display_acknowledgements_on_login'],
    'hasSession' => (bool)session_name(),
    'cookieText' => $cookie,
    'regTranslations' => $regTranslations,
    'regConstants' => json_encode(['webroot' => $GLOBALS['webroot']]),
    'siteID' => $_SESSION['site_id'],
    'showLabels' => $GLOBALS['show_labels_on_login_form'],
    'displayPrimaryLogo' => $GLOBALS['show_primary_logo'],
    'primaryLogo'   => $primaryLogo,
    'primaryLogoWidth' => $GLOBALS['primary_logo_width'],
    'logoPosition' => $GLOBALS['logo_position'],
    'secondaryLogoWidth' => $GLOBALS['secondary_logo_width'],
    'displaySecondaryLogo' => $GLOBALS['extra_logo_login'],
    'secondaryLogo' => $secondaryLogo,
    'secondaryLogoPosition' => $GLOBALS['secondary_logo_position'],
];

/**
 * @var EventDispatcher;
 */
$ed = $GLOBALS['kernel']->getEventDispatcher();

$templatePageEvent = new TemplatePageEvent('login/login.php', [], $layout, $viewArgs);
$event = $ed->dispatch($templatePageEvent, TemplatePageEvent::RENDER_EVENT);

try {
    echo $t->render($event->getTwigTemplate(), $event->getTwigVariables());
} catch (LoaderError | RuntimeError | SyntaxError $e) {
    echo "<p style='font-size:24px; color: red;'>" . text($e->getMessage()) . '</p>';
}
