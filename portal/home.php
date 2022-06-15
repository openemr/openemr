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
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Shiqiang Tao <StrongTSQ@gmail.com>
 * @copyright Copyright (c) 2021 Ben Marte <benmarte@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('verify_session.php');
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once('lib/portal_mail.inc');
require_once(__DIR__ . '/../library/appointments.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Events\PatientPortal\RenderEvent;
use OpenEMR\Events\PatientPortal\AppointmentFilterEvent;

if (isset($_SESSION['register']) && $_SESSION['register'] === true) {
    require_once(__DIR__ . '/../src/Common/Session/SessionUtil.php');
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

if (!isset($_SESSION['portal_init'])) {
    $_SESSION['portal_init'] = true;
}

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
if ($newcnt > 0 && $_SESSION['portal_init']) {
    $whereto = $_SESSION['whereto'] = '#secure-msgs-card';
}
$messagesURL = $GLOBALS['web_root'] . '' . '/portal/messaging/messages.php';

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

function buildNav($newcnt, $pid, $result)
{
    $navItems = [
        [
            'url' => '#',
            'label' => $result['fname'] . ' ' . $result['lname'],
            'icon' => 'fa-user',
            'dropdownID' => 'account',
            'messageCount' => $newcnt ?? 0,
            'children' => [
                [
                    'url' => '#profilecard',
                    'label' => xl('My Profile'),
                    'icon' => 'fa-user',
                    'dataToggle' => 'collapse',
                ],

                [
                    'url' => '#secure-msgs-card',
                    'label' => xl('My Messages'),
                    'icon' => 'fa-envelope',
                    'dataToggle' => 'collapse',
                    'messageCount' => $newcnt ?? 0,
                ],
                [
                    'url' => '#documentscard',
                    'label' => xl('My Documents'),
                    'icon' => 'fa-file-medical',
                    'dataToggle' => 'collapse'
                ],
                [
                    'url' => '#lists',
                    'label' => xl('My Dashboard'),
                    'icon' => 'fa-list',
                    'dataToggle' => 'collapse'
                ],
                [
                    'url' => '#openSignModal',
                    'label' => xl('My Signature'),
                    'icon' => 'fa-file-signature',
                    'dataToggle' => 'modal',
                    'dataType' => 'patient-signature'
                ]
            ],
        ],
        [
            'url' => '#',
            'label' => xl('Reports'),
            'icon' => 'fa-book-medical',
            'dropdownID' => 'reports',
            'children' => [
                [
                    'url' => $GLOBALS['web_root'] . '' . '/ccdaservice/ccda_gateway.php?action=view&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken()),
                    'label' => xl('View CCD'),
                    'icon' => 'fa-eye',
                    'target_blank' => 'true',
                ],
                [
                    'url' => $GLOBALS['web_root'] . '' . '/ccdaservice/ccda_gateway.php?action=dl&csrf_token_form=' . urlencode(CsrfUtils::collectCsrfToken()),
                    'label' => xl('Download CCD'),
                    'icon' => 'fa-download',
                ]
            ]
        ]
    ];
    if (($GLOBALS['portal_two_ledger'] || $GLOBALS['portal_two_payments'])) {
        if (!empty($GLOBALS['portal_two_ledger'])) {
            $navItems[] = [
                'url' => '#',
                'label' => xl('Accountings'),
                'icon' => 'fa-file-invoice-dollar',
                'dropdownID' => 'accounting',
                'children' => [
                    [
                        'url' => '#ledgercard',
                        'label' => xl('Ledger'),
                        'icon' => 'fa-folder-open',
                        'dataToggle' => 'collapse'
                    ]
                ]
            ];
        }
    }

    // Build sub nav items

    if (!empty($GLOBALS['allow_portal_chat'])) {
        $navItems[] = [
            'url' => '#messagescard',
            'label' => xl('Chat'),
            'icon' => 'fa-comment-medical',
            'dataToggle' => 'collapse',
            'dataType' => 'cardgroup'
        ];
    }

    for ($i = 0, $iMax = count($navItems); $i < $iMax; $i++) {
        if ($GLOBALS['allow_portal_appointments'] && $navItems[$i]['label'] === ($result['fname'] . ' ' . $result['lname'])) {
            $navItems[$i]['children'][] = [
                'url' => '#appointmentcard',
                'label' => xl('My Appointments'),
                'icon' => 'fa-calendar-check',
                'dataToggle' => 'collapse'
            ];
        }

        if ($navItems[$i]['label'] === ($result['fname'] . ' ' . $result['lname'])) {
            array_push(
                $navItems[$i]['children'],
                [
                    'url' => 'javascript:changeCredentials(event)',
                    'label' => xl('Change Credentials'),
                    'icon' => 'fa-cog fa-fw',
                ],
                [
                    'url' => 'logout.php',
                    'label' => xl('Logout'),
                    'icon' => 'fa-ban fa-fw',
                ]
            );
        }

        if (!empty($GLOBALS['portal_onsite_document_download']) && $navItems[$i]['label'] === xl('Reports')) {
            array_push(
                $navItems[$i]['children'],
                [
                    'url' => '#reportcard',
                    'label' => xl('Report Content'),
                    'icon' => 'fa-folder-open',
                    'dataToggle' => 'collapse'
                ],
                [
                    'url' => '#downloadcard',
                    'label' => xl('Download Charted Documents'),
                    'icon' => 'fa-download',
                    'dataToggle' => 'collapse'
                ]
            );
        }
        if (!empty($GLOBALS['portal_two_payments']) && $navItems[$i]['label'] === xl('Accountings')) {
            $navItems[$i]['children'][] = [
                'url' => '#paymentcard',
                'label' => xl('Make Payment'),
                'icon' => 'fa-credit-card',
                'dataToggle' => 'collapse'
            ];
        }
    }

    return $navItems;
}

$navMenu = buildNav($newcnt, $pid, $result);

$twig = (new TwigContainer('', $GLOBALS['kernel']))->getTwig();
echo $twig->render('portal/home.html.twig', [
    'user' => $user,
    'whereto' => $_SESSION['whereto'] ?? null ?: ($whereto ?? '#documentscard'),
    'result' => $result,
    'msgs' => $msgs,
    'msgcnt' => $msgcnt,
    'newcnt' => $newcnt,
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
    'pagetitle' => xl('Home') . ' | ' . xl('OpenEMR Portal'),
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
    'eventNames' => [
        'sectionRenderPost' => RenderEvent::EVENT_SECTION_RENDER_POST,
        'scriptsRenderPre' => RenderEvent::EVENT_SCRIPTS_RENDER_PRE
    ]
]);
