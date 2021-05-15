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
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Shiqiang Tao <StrongTSQ@gmail.com>
 * @copyright Copyright (c) 2021 Ben Marte <benmarte@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("verify_session.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("lib/portal_mail.inc");
require_once(__DIR__ . "/../library/appointments.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (isset($_SESSION['register']) && $_SESSION['register'] === true) {
    require_once(__DIR__ . "/../src/Common/Session/SessionUtil.php");
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

if (!isset($_SESSION['portal_init'])) {
    $_SESSION['portal_init'] = true;
}

$whereto = $_SESSION['whereto'] ?? 'documentscard';

$user = isset($_SESSION['sessionUser']) ? $_SESSION['sessionUser'] : 'portal user';
$result = getPatientData($pid);

$msgs = getPortalPatientNotes($_SESSION['portal_username']);
$msgcnt = count($msgs);
$newcnt = 0;
foreach ($msgs as $i) {
    if ($i['message_status'] == 'New') {
        $newcnt += 1;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . '/../templates/portal');
$twig = new Environment($loader);

$styles = Header::setupHeader(['no_main-theme', 'datetime-picker', 'patientportal-style']);

$navItems = [
    [
        'url' => '#',
        'label' => xlt('Profile'),
        'icon' => 'fa-user',
        'dropdownID' => 'test',
        'dataToggle' => 'collapse'
    ],
    [
        'url' => 'https://google.com',
        'label' => xlt('Lists'),
        'icon' => 'fa-list',
        'dropdownID' => 'test',
        'dataToggle' => 'collapse'
    ],
    [
        'url' => 'https://google.com',
        'label' => xlt('Patient Documents'),
        'icon' => 'fa-file-medical',
        'dropdownID' => 'test',
        'dataToggle' => 'collapse'
    ],
    [
        'url' => 'https://google.com',
        'label' => xlt("Appointment"),
        'icon' => 'fa-calendar-check',
        'dropdownID' => 'test',
        'dataToggle' => 'collapse'
    ],
    [
        'url' => 'https://google.com',
        'label' => xlt('Accountings'),
        'icon' => 'fa-file-invoice-dollar',
        'isDropDown' => 'dropdown',
        'dropdownID' => 'accounting',
        'dataToggle' => 'dropdown',
        'children' => [
            [
                'url' => "#ledgercard",
                'label' => xlt('Ledger'),
                'icon' => 'fa-folder-open',
            ]
        ]
    ],
    [
        'url' => '#',
        'label' => xlt('Reports'),
        'icon' => 'fa-book-medical',
        'isDropDown' => 'dropdown',
        'dropdownID' => 'reports',
        'dataToggle' => 'dropdown',
        'children' => [
            [
                'url' => $GLOBALS['web_root'] . '' . "/ccdaservice/ccda_gateway.php?action=startandrun",
                'label' => xlt('View CCD'),
                'icon' => 'fa-envelope',
            ],
            [
                'url' => '#reportcard',
                'label' => xlt('Report Content'),
                'icon' => 'fa-folder-open',
            ],
            [
                'url' => '#downloadcard',
                'label' => xlt('Download Lab Documents'),
                'icon' => 'fa-download',
            ]
        ]
    ],
    [
        'url' => '#secure-msgs-card',
        'label' => xlt('Messages'),
        'icon' => 'fa-envelope',
        'dropdownID' => '#cardgroup',
        'dataToggle' => 'collapse',
        'messageCount' => $newcnt,
    ],
    [
        'url' => '#messagescard',
        'label' => xlt('Chat'),
        'icon' => 'fa-comment-medical',
        'dataToggle' => 'collapse',
        'dataType' => '#cardgroup'
    ],
    [
        'url' => '#openSignModal',
        'label' => xlt('Signature on File'),
        'icon' => 'fa-file-signature',
        'dataToggle' => 'modal',
        'dataType' => 'patient-signature'
    ]
    ];

echo $twig->render('home.html.twig', [
    'user' => $user,
    'whereto' => $whereto,
    'result' => $result,
    'msgs' => $msgs,
    'msgcnt' => $msgcnt,
    'newcnt' => text($newcnt),
    'globals' => $GLOBALS,
    'youHave' => xlt('You have'),
    'navItems' => $navItems,
    'styles' => $styles,
    'pagetitle' => xlt('Home') . ' | ' . xlt('OpenEMR Portal'),
    'jsVersion' => $v_js_includes,
    ]);


?>