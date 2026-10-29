<?php

/**
 * The logged-in user's main menu as JSON, for top.refreshMainMenu() in
 * templates/interface/main/tabs/menu_json.html.twig. It is the same menu
 * main.php renders when the page loads, built the same way (MainMenuData), so
 * a module whose menu entries depend on something the user just changed can
 * show the new menu without reloading OpenEMR.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once(__DIR__ . '/../../globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Menu\MainMenuData;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_POST, $session, dieOnFail: true);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(
    MainMenuData::forUser(OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher(), $session),
    JSON_THROW_ON_ERROR
);
