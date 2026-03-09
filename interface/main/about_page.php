<?php

/**
 * OpenEMR About Page
 *
 * This Displays an About page for OpenEMR Displaying Version Number, Support Phone Number
 * If it have been entered in Globals along with the Manual and On Line Support Links
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Robert Down <robertdown@live.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021-2023 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// @TODO: jQuery UI Removal


require_once("../globals.php");

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Services\ProductRegistrationService;
use OpenEMR\Services\VersionService;

$twig = new TwigContainer();
$t = $twig->getTwig();

$versionService = new VersionService();

// Auto-generate the link if no override is specified. This is tied directly to the OpenEMR Wiki
$userManual = (OEGlobalsBag::getInstance()->get('user_manual_link') === '')
    ? "https://open-emr.org/wiki/index.php/OpenEMR_" . $versionService->asString(false, false) . "_Users_Guide"
    : OEGlobalsBag::getInstance()->get('user_manual_link');

// Collect registered email, if applicable
$emailRegistered = (new ProductRegistrationService())->getRegistrationEmail() ?? '';

$viewArgs = [
    'onlineSupportHref' => OEGlobalsBag::getInstance()->get("online_support_link"),
    'ackHref' => "../../acknowledge_license_cert.html",
    'applicationTitle' => $openemr_name,
    'versionNumber' => $versionService->asString(),
    'supportPhoneNumber' => OEGlobalsBag::getInstance()->get('support_phone_number') ?? false,
    'theUUID' => UniqueInstallationUuid::getUniqueInstallationUuid(),
    'userManualHref' => $userManual,
    'onlineSupportLink' => OEGlobalsBag::getInstance()->get('online_support_link') ?? false,
    'displayAcknowledgements' => OEGlobalsBag::getInstance()->get('display_acknowledgements'),
    'displayDonations' => OEGlobalsBag::getInstance()->get('display_donations_link'),
    'displayReview' => OEGlobalsBag::getInstance()->get('display_review_link'),
    'emailRegistered' => $emailRegistered
];

$templatePageEvent = new TemplatePageEvent('about_page', [], 'core/about.html.twig', $viewArgs);
$event = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher()->dispatch($templatePageEvent, TemplatePageEvent::RENDER_EVENT);

echo $t->render($event->getTwigTemplate(), $event->getTwigVariables());
