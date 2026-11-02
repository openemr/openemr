<?php

/**
 * This file contains functions that manage custom user
 * settings
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");
// library/globals.inc.php defines $USER_SPECIFIC_GLOBALS used below. It is not
// auto-included by interface/globals.php, so pull it in explicitly here.
require_once(__DIR__ . "/../globals.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use Symfony\Component\HttpFoundation\Response;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

/** @var list<string> $USER_SPECIFIC_GLOBALS */
$allowList = $USER_SPECIFIC_GLOBALS;

/**
 * Permit per-user UI settings but refuse `global:*` keys unless the suffix
 * is in the explicit allowlist used by the admin globals editor.
 */
$isUserSettingKeyAllowed = static function (mixed $key) use ($allowList): bool {
    if (!is_string($key) || $key === '') {
        return false;
    }
    if (str_starts_with($key, 'global:')) {
        return in_array(substr($key, strlen('global:')), $allowList, true);
    }
    return true;
};

//If 'mode' is either a 1 or 0 and 'target' ends with _expand
//  Then will update the appropriate user _expand flag
if ((isset($_POST['mode']) && ( $_POST['mode'] == 1 || $_POST['mode'] == 0 )) && ( str_ends_with((string) $_POST['target'], "_expand") )) {
    $expandTarget = $_POST['target'];
    if ($isUserSettingKeyAllowed($expandTarget)) {
        setUserSetting($expandTarget, $_POST['mode']);
    }
}

//mdsupport : Generic user setting
if ((isset($_POST['lab'])) && (isset($_POST['val']))) {
    $labKey = $_POST['lab'];
    if ($isUserSettingKeyAllowed($labKey)) {
        setUserSetting($labKey, $_POST['val']);
    }
}

// even more generic
if ((isset($_POST['target'])) && (isset($_POST['setting']))) {
    $genericTarget = $_POST['target'];
    if ($isUserSettingKeyAllowed($genericTarget)) {
        setUserSetting($genericTarget, $_POST['setting']);
    }
}

// @todo This is crude, but if we make it here there should be a proper response, so for now send a 200 but really we need better Response handling
$res = new Response();
$res->send();
