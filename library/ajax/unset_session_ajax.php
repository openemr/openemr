<?php

/**
 * library/ajax/unset_session_ajax.php Clear active patient on the server side.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Visolve <services@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 Visolve <services@visolve.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");
require_once("../pid.inc");
require_once("../group.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//Setpid function is called on receiving an ajax request.
if (($_POST['func'] == "unset_pid")) {
    setpid(0);
}

//Setpid function is called on receiving an ajax request.
if (($_POST['func'] == "unset_gid")) {
    unsetGroup();
}
