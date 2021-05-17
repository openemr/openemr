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

Header::setupHeader(['no_main-theme', 'datetime-picker', 'patientportal-style']);

$navItems = [
    [
        'url' => '#profilecard',
        'label' => xlt('Profile'),
        'icon' => 'fa-user',
        'dropdownID' => 'test',
        'dataToggle' => 'collapse'
    ],
    [
        'url' => '#lists',
        'label' => xlt('Lists'),
        'icon' => 'fa-list',
        'dropdownID' => 'test',
        'dataToggle' => 'collapse'
    ],
    [
        'url' => '#documentscard',
        'label' => xlt('My Documents'),
        'icon' => 'fa-file-medical',
        'dropdownID' => 'test',
        'dataToggle' => 'collapse'
    ],
    [
        'url' => '#appointmentcard',
        'label' => xlt("Appointment"),
        'icon' => 'fa-calendar-check',
        'dropdownID' => 'test',
        'dataToggle' => 'collapse'
    ],
    [
        'url' => '#',
        'label' => xlt('Accountings'),
        'icon' => 'fa-file-invoice-dollar',
        'dropdownID' => 'accounting',
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
        'dropdownID' => 'reports',
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

$messagesURL = $GLOBALS['web_root'] . ''. "/portal/messaging/messages.php";

$isEasyPro = $GLOBALS['easipro_enable'] && !empty($GLOBALS['easipro_server']) && !empty($GLOBALS['easipro_name']);

$current_date2 = date('Y-m-d');
$apptLimit = 30;
$appts = fetchNextXAppts($current_date2, $pid, $apptLimit);


if ($appts) {
$stringCM = "(" . xl("Comments field entry present") . ")";
$stringR = "(" . xl("Recurring appointment") . ")";
$count = 0;
foreach ($appts as $row) {
    $status_title = getListItemTitle('apptstat', $row['pc_apptstatus']);
    $count++;
    $dayname = xl(date("l", strtotime($row['pc_eventDate'])));
    $dispampm = "am";
    $disphour = substr($row['pc_startTime'], 0, 2) + 0;
    $dispmin = substr($row['pc_startTime'], 3, 2);
    if ($disphour >= 12) {
    $dispampm = "pm";
    if ($disphour > 12) {
        $disphour -= 12;
    }
    }

    if ($row['pc_hometext'] != "") {
    $etitle = xlt('Comments') . ": " . $row['pc_hometext'] . "\r\n";
    } else {
    $etitle = "";
    }

    $mode = (int)$row['pc_recurrtype'] > 0 ? text("recurring") : $row['pc_recurrtype'];
    $appt_type_icon = (int)$row['pc_recurrtype'] > 0 ? "<i class='float-right fa fa-edit text-danger bg-light'></i>" : "<i class='float-right fa fa-edit text-success bg-light'></i>";
    echo "<div class='card-header clearfix'><a href='#' onclick='editAppointment(" . attr_js($mode) . "," . attr_js($row['pc_eid']) . ")'" . " title='" . attr($etitle) . "'>" . $appt_type_icon . "</a></div>";
    echo "<div class='body font-weight-bold'><p>" . text($dayname . ", " . $row['pc_eventDate']) . "&nbsp;";
    echo text($disphour . ":" . $dispmin . " " . $dispampm) . "<br />";
    echo xlt("Type") . ": " . text($row['pc_catname']) . "<br />";
    echo xlt("Provider") . ": " . text($row['ufname'] . " " . $row['ulname']) . "<br />";
    echo xlt("Status") . ": " . text($status_title);
    echo "</p></div></div>";
}
if ($count == $apptLimit) {
    echo "<p>" . xlt("Display limit reached") . "<br>" . xlt("More appointments may exist") . "</p>";
}
} else { // if no appts
// echo "<h3 class='text-center'>" . xlt('No Appointments') . "</h3>";
}
echo '</div>';

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
    'pagetitle' => xlt('Home') . ' | ' . xlt('OpenEMR Portal'),
    'jsVersion' => $v_js_includes,
    'messagesURL' => $messagesURL,
    'patientID' => $pid,
    'patientName' => js_escape($_SESSION['ptName']),
    'profileModalTitle' => xlj('Profile Edits Red = Charted Values Blue = Patient Edits'),
    'helpButtonLabel' => xlj('Help'),
    'cancelButtonLabel' => xlj('Cancel'),
    'revertButtonLabel' => xlj('Revert Edits'),
    'reviewButtonLabel' => xlj('Send for Review'),
    'newAppointmentLabel' => xlj('Request New Appointment'),
    'recurringAppointmentLabel' => xlj("A Recurring Appointment. Please contact your appointment desk for any changes."),
    'editAppointmentLabel' => xlj('Edit Appointment'),
    'newCredentialsLabel' => xlj('Please Enter New Credentials'),
    'csrfUtils' => js_escape(CsrfUtils::collectCsrfToken()),
    'finishedAssesmentLabel' => xlj('You have finished the assessment.'),
    'thankYouLabel' => xlj('Thank you'),
    'loadingTextLabel' => xlj('Loading'),
    'startAssesmentLabel' => xlj('Start Assessment'),
    'workingLabel' => xlt('Working!'),
    'pleaseWaitLabel' => xlt('Please wait...'),
    'medicationsLabel' => xlt('Medications'),
    'medicationsAllergyLabel' => xlt('Medications Allergy List'),
    'issuesListLabel' => xlt('Issues List'),
    'ammendmentListLabel' => xlt('Amendment List'),
    'labResultsLabel' => xlt('Lab Results'),
    'appointmentsLabel' => xlt('Appointments'),
    'noAppointmentsLabel' => xlt('No Appointments'),
    'scheduleNewAppointmentLabel'=> xlt('Schedule A New Appointment'),
    'paymentsLabel' => xlt('Payments'),
    'secureChatLabel' => xlt('Secure Chat'),
    'reportsLabel' => xlt('Reports'),
    'downloadDocumentsLabel' => xlt('Download Documents'),
    'downloadAllPatientDocumentsLabel' => xlt('Download all patient documents'),
    'downloadLabel' => xla('Download'),
    'ledgerLabel' => xlt('Ledger'),
    'patientReportedOutcomeLabel' => xlt('Patient Reported Outcomes'),
    'isEasyPro' => $isEasyPro,
    'patientAppointments' => $appts,
    ]);
