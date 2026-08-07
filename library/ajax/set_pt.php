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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();


CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

if (!empty($_GET['set_pid']) && ($_GET["set_pid"] != $session->get('pid'))) {
    // The only live caller of this branch is the Messages Center document-attach
    // flow in interface/main/messages/templates/linked_documents.php, which
    // requires patients/docs. Widen if future callers legitimately need it.
    if (!AclMain::aclCheckCore('patients', 'docs')) {
        http_response_code(403);
        exit;
    }
    setpid($_GET["set_pid"]);
}

// Session-key read used by the tab-bar getSessionValue() helper; returns the
// user's own session state (pid/encounter). CSRF + session auth are sufficient.
if (($_POST['mode'] ?? '') == 'session_key') {
    $key = $_POST['key'] ?? '';
    $allowedKeys = ['pid', 'encounter'];

    if (in_array($key, $allowedKeys, true)) {
        $current = $session->get($key) ?? ($key === 'pid' ? ($pid ?? 0) : 0);
        echo text(js_escape($current));
    }
}
