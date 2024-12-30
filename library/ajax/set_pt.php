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

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if ($_GET["set_pid"] && $_GET["set_pid"] != $_SESSION["pid"]) {
    setpid($_GET["set_pid"]);
}

// For future use, if needed
if (($_POST['mode'] ?? '') == 'session_key') {
    $key = $_POST['key'] ?? '';
    $current = $_SESSION[$key] ?? $pid ?? 0;
    echo(js_escape($current));
}
