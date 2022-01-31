<?php

/**
 * OpenEMR About Page
 *
 * This Displays an About page for OpenEMR Displaying Version Number, Support Phone Number
 * If it have been entered in Globals along with the Manual and On Line Support Links
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021-2022 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// @TODO: jQuery UI Removal


require_once("../globals.php");

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Services\VersionService;

$twig = new TwigContainer();
$t = $twig->getTwig();

$versionService = new VersionService();

$registrationTranslation = json_encode(array(
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

// Auto-generate the link if no override is specified. This is tied directly to the OpenEMR Wiki
$userManual = ($GLOBALS['user_manual_link'] === '')
    ? "https://open-emr.org/wiki/index.php/OpenEMR_" . $versionService->asString(false, false) . "_Users_Guide"
    : $GLOBALS['user_manual_link'];

$viewArgs = [
    'onlineSupportHref' => $GLOBALS["online_support_link"],
    'ackHref' => "../../acknowledge_license_cert.html",
    'registrationTranslations' => $registrationTranslation,
    'applicationTitle' => $openemr_name,
    'versionNumber' => $versionService->asString(),
    'supportPhoneNumber' => $GLOBALS['support_phone_number'] ?? false,
    'theUUID' => UniqueInstallationUuid::getUniqueInstallationUuid(),
    'userManualHref' => $userManual,
    'onlineSupportLink' => $GLOBALS['online_support_link'] ?? false,
    'displayAcknowledgements' => $GLOBALS['display_acknowledgements'],
    'displayDonations' => $GLOBALS['display_review_link'],
    'displayReview' => $GLOBALS['display_donations_link'],
];

echo $t->render('core/about.html.twig', $viewArgs);
