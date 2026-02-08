<?php

/**
 * sets pid
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");
require_once("$srcdir/pid.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();


if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"], session: $session)) {
    CsrfUtils::csrfNotVerified();
}

if (in_array("set_pid", $_GET, true) && !empty($_GET["set_pid"]) && ($_GET["set_pid"] != $session->get('pid'))) {
    setpid($_GET["set_pid"]);
}

// For gotos from billing manager we are whitelisting pid
if (($_POST['mode'] ?? '') == 'session_key') {
    $key = $_POST['key'] ?? '';
    $allowedKeys = ['pid', 'encounter'];

    if (in_array($key, $allowedKeys, true)) {
        $current = $session->get($key) ?? ($key === 'pid' ? ($pid ?? 0) : 0);
        echo text(js_escape($current));
    }
}
