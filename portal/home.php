<?php

/**
 * Patient Portal Home
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Shiqiang Tao <StrongTSQ@gmail.com>
 * @author    Ben Marte <benmarte@gmail.com>
 * @copyright Copyright (c) 2016-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Shiqiang Tao <StrongTSQ@gmail.com>
 * @copyright Copyright (c) 2021 Ben Marte <benmarte@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('verify_session.php');
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once('lib/portal_mail.inc.php');
require_once(__DIR__ . '/../library/appointments.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Events\PatientPortal\AppointmentFilterEvent;
use OpenEMR\Events\PatientPortal\RenderEvent;
use OpenEMR\Services\LogoService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

if (isset($_SESSION['register']) && $_SESSION['register'] === true) {
    require_once(__DIR__ . '/../src/Common/Session/SessionUtil.php');
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

if (!isset($_SESSION['portal_init'])) {
    $_SESSION['portal_init'] = true;
}

$logoService = new LogoService();


// Get language definitions for js
$language = $_SESSION['language_choice'] ?? '1'; // defaults english
$sql = "SELECT c.constant_name, d.definition FROM lang_definitions as d
        JOIN lang_constants AS c ON d.cons_id = c.cons_id
        WHERE d.lang_id = ?";
$tarns = sqlStatement($sql, $language);
$language_defs = array();
while ($row = SqlFetchArray($tarns)) {
    $language_defs[$row['constant_name']] = $row['definition'];
}

$whereto = $_SESSION['whereto'] ?? null;

$user = $_SESSION['sessionUser'] ?? 'portal user';
$result = getPatientData($pid);

$msgs = getPortalPatientNotes($_SESSION['portal_username']);
$msgcnt = count($msgs);
$newcnt = 0;
foreach ($msgs as $i) {
    if ($i['message_status'] == 'New') {
        $newcnt += 1;
    }
}

// force to message page if new messages.
/*if ($newcnt > 0 && $_SESSION['portal_init']) {
    $whereto = $_SESSION['whereto'] = '#secure-msgs-card';
}*/
$messagesURL = $GLOBALS['web_root'] . '/portal/messaging/messages.php';

$isEasyPro = $GLOBALS['easipro_enable'] && !empty($GLOBALS['easipro_server']) && !empty($GLOBALS['easipro_name']);

$current_date2 = date('Y-m-d');
$apptLimit = 30;
$appts = fetchNextXAppts($current_date2, $pid, $apptLimit);

$appointments = array();
if ($appts) {
    $stringCM = '(' . xl('Comments field entry present') . ')';
    $stringR = '(' . xl('Recurring appointment') . ')';
    $count = 0;
    foreach ($appts as $row) {
        $status_title = getListItemTitle('apptstat', $row['pc_apptstatus']);
        $count++;
        $dayname = xl(date('l', strtotime($row['pc_eventDate'])));
        $dispampm = 'am';
        $disphour = (int)substr($row['pc_startTime'], 0, 2);
        $dispmin = substr($row['pc_startTime'], 3, 2);
        if ($disphour >= 12) {
            $dispampm = 'pm';
            if ($disphour > 12) {
                $disphour -= 12;
            }
        }

        if ($row['pc_hometext'] != '') {
            $etitle = xl('Comments') . ': ' . $row['pc_hometext'] . "\r\n";
        } else {
            $etitle = '';
        }

        $formattedRecord = [
            'appointmentDate' => $dayname . ', ' . $row['pc_eventDate'] . ' ' . $disphour . ':' . $dispmin . ' ' . $dispampm,
            'appointmentType' => xl('Type') . ': ' . $row['pc_catname'],
            'provider' => xl('Provider') . ': ' . $row['ufname'] . ' ' . $row['ulname'],
            'status' => xl('Status') . ': ' . $status_title,
            'mode' => (int)$row['pc_recurrtype'] > 0 ? 'recurring' : $row['pc_recurrtype'],
            'icon_type' => (int)$row['pc_recurrtype'] > 0,
            'etitle' => $etitle,
            'pc_eid' => $row['pc_eid'],
        ];
        $filteredEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch(new AppointmentFilterEvent($row, $formattedRecord), AppointmentFilterEvent::EVENT_NAME);
        $appointments[] = $filteredEvent->getAppointment() ?? $formattedRecord;
    }
}

$current_theme = sqlQuery("SELECT `setting_value` FROM `patient_settings` WHERE setting_patient = ? AND `setting_label` = ?", array($pid, 'portal_theme'))['setting_value'] ?? '';
function collectStyles(): array
{
    global $webserver_root;
    $theme_dir = "$webserver_root/public/themes";
    $dh = opendir($theme_dir);
    $styleArray = array();
    while (false !== ($tfname = readdir($dh))) {
        if (
            $tfname == 'style_blue.css' ||
            $tfname == 'style_pdf.css' ||
            !preg_match("/^" . 'style_' . ".*\.css$/", $tfname)
        ) {
            continue;
        }
        $styleDisplayName = str_replace("_", " ", substr($tfname, 6));
        $styleDisplayName = ucfirst(str_replace(".css", "", $styleDisplayName));
        $styleArray[$tfname] = $styleDisplayName;
    }
    asort($styleArray);
    closedir($dh);
    return $styleArray;
}

function buildNav($newcnt, $pid, $result): array
{
    $hideLedger = false;
    $hidePayment = false;
    if (empty($GLOBALS['portal_two_ledger'])) {
        $hideLedger = true;
    }

    if (empty($GLOBALS['portal_two_payments'])) {
        $hidePayment = true;
    }
    $navItems = [
        [
            'url' => '#',
            'label' => xl('Menu'),
            'icon' => 'fa-user',
            'dropdownID' => 'account',
            'messageCount' => $newcnt ?? 0,
            'children' => [
                [
                    'url' => '#quickstart-card',
                    'id' => 'quickstart_id',
                    'label' => xl('Dashboard'),
                    'icon' => 'fa-tasks',
                    'dataToggle' => 'collapse',
                ],
                [
                    'url' => '#secure-msgs-card',
                    'label' => xl('Secure Messaging'),
                    'icon' => 'fa-envelope',
                    'dataToggle' => 'collapse',
                    'messageCount' => $newcnt ?? 0,
                ],
                [
                    'url' => $GLOBALS['web_root'] . '/portal/patient/onsitedocuments?pid=' . urlencode($pid),
                    'label' => xl('Forms and Documents'),
                    'icon' => 'fa-file',
                ],
                [
                    'url' => '#profilecard',
                    'label' => xl('Profile'),
                    'icon' => 'fa-user',
                    'dataToggle' => 'collapse',
                ],
                [
                    'url' => '#lists',
                    'label' => xl('Health Snapshot'),
                    'icon' => 'fa-list',
                    'dataToggle' => 'collapse'
                ],
                /*[
                    'url' => '#ledgercard',
                    'label' => xl('Billing Summary'),
                    'icon' => 'fa-folder-open',
                    'dataToggle' => 'collapse',
                    'hide' => $hideLedger
                ],
                [
                    'url' => '#paymentcard',
                    'label' => xl('Make Payment'),
                    'icon' => 'fa-credit-card',
                    'dataToggle' => 'collapse',
                    'hide' => $hidePayment
                ],*/
            ],
        ]
    ];

    // Build sub nav items

    for ($i = 0, $iMax = count($navItems); $i < $iMax; $i++) {
        if ($GLOBALS['allow_portal_appointments'] && $navItems[$i]['label'] === xl('Menu')) {
            $navItems[$i]['children'][] = [
                'url' => '#appointmentcard',
                'label' => xl('Appointments'),
                'icon' => 'fa-calendar-check',
                'dataToggle' => 'collapse'
            ];
        }

        if ($navItems[$i]['label'] === xl('Menu')) {
            array_push(
                $navItems[$i]['children'],
                [
                    'url' => 'javascript:changeCredentials(event)',
                    'label' => xl('Manage Login Credentials'),
                    'icon' => 'fa-cog fa-fw',
                ],
                [
                    'url' => '#openSignModal',
                    'label' => xl('Manage Signature'),
                    'icon' => 'fa-file-signature',
                    'dataToggle' => 'modal',
                    'dataType' => 'patient-signature'
                ],
                [
                    'url' => 'logout.php',
                    'label' => xl('Logout'),
                    'icon' => 'fa-ban fa-fw',
                ]
            );
        }
    }
    return $navItems;
}

// Build our navigation
$navMenu = buildNav($newcnt, $pid, $result);

// Fetch immunization records
$query = "SELECT im.*, cd.code_text, DATE(administered_date) AS administered_date,
    DATE_FORMAT(administered_date,'%m/%d/%Y') AS administered_formatted, lo.title as route_of_administration,
    u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.streetb, u.city, u.state, u.zip, u.phonew1,
    f.name, f.phone, lo.notes as route_code
    FROM immunizations AS im
    LEFT JOIN codes AS cd ON cd.code = im.cvx_code
    JOIN code_types AS ctype ON ctype.ct_key = 'CVX' AND ctype.ct_id=cd.code_type
    LEFT JOIN list_options AS lo ON lo.list_id = 'drug_route' AND lo.option_id = im.route
    LEFT JOIN users AS u ON u.id = im.administered_by_id
    LEFT JOIN facility AS f ON f.id = u.facility_id
    WHERE im.patient_id=?";
$result = sqlStatement($query, array($pid));
$immunRecords = array();
while ($row = sqlFetchArray($result)) {
    $immunRecords[] = $row;
}

// CCDA Alt Service
$ccdaOk = ($GLOBALS['ccda_alt_service_enable'] == 2 || $GLOBALS['ccda_alt_service_enable'] == 3);

// Available Themes
$styleArray = collectStyles();

// Render Home Page
$twig = (new TwigContainer('', $GLOBALS['kernel']))->getTwig();
try {
    echo $twig->render('portal/home.html.twig', [
        'user' => $user,
        'whereto' => $_SESSION['whereto'] ?? null ?: ($whereto ?? '#quickstart-card'),
        'result' => $result,
        'msgs' => $msgs,
        'msgcnt' => $msgcnt,
        'newcnt' => $newcnt,
        'menuLogo' => $logoService->getLogo('portal/menu/primary'),
        'allow_portal_appointments' => $GLOBALS['allow_portal_appointments'],
        'web_root' => $GLOBALS['web_root'],
        'payment_gateway' => $GLOBALS['payment_gateway'],
        'gateway_mode_production' => $GLOBALS['gateway_mode_production'],
        'portal_two_payments' => $GLOBALS['portal_two_payments'],
        'allow_portal_chat' => $GLOBALS['allow_portal_chat'],
        'portal_onsite_document_download' => $GLOBALS['portal_onsite_document_download'],
        'portal_two_ledger' => $GLOBALS['portal_two_ledger'],
        'images_static_relative' => $GLOBALS['images_static_relative'],
        'youHave' => xl('You have'),
        'navMenu' => $navMenu,
        'primaryMenuLogoHeight' => $GLOBALS['portal_primary_menu_logo_height'] ?? '30',
        'pagetitle' => $GLOBALS['openemr_name'] . ' ' . xl('Portal'),
        'messagesURL' => $messagesURL,
        'patientID' => $pid,
        'patientName' => $_SESSION['ptName'] ?? null,
        'csrfUtils' => CsrfUtils::collectCsrfToken(),
        'isEasyPro' => $isEasyPro,
        'appointments' => $appointments,
        'appts' => $appts,
        'appointmentLimit' => $apptLimit,
        'appointmentCount' => $count ?? null,
        'displayLimitLabel' => xl('Display limit reached'),
        'site_id' => $_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'), // one way or another, we will have a site_id.
        'portal_timeout' => $GLOBALS['portal_timeout'] ?? 1800, // timeout is in seconds
        'language_defs' => $language_defs,
        'current_theme' => $current_theme,
        'styleArray' => $styleArray,
        'ccdaOk' => $ccdaOk,
        'allow_custom_report' => $GLOBALS['allow_custom_report'] ?? '0',
        'immunRecords' => $immunRecords,
        'eventNames' => [
            'sectionRenderPost' => RenderEvent::EVENT_SECTION_RENDER_POST,
            'scriptsRenderPre' => RenderEvent::EVENT_SCRIPTS_RENDER_PRE,
            'dashboardInjectCard' => RenderEvent::EVENT_DASHBOARD_INJECT_CARD,
            'dashboardRenderScripts' => RenderEvent::EVENT_DASHBOARD_RENDER_SCRIPTS
        ]
    ]);
} catch (LoaderError | RuntimeError | SyntaxError $e) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    die(text($e->getMessage()));
}
