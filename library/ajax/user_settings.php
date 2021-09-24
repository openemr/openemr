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

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../user.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use Symfony\Component\HttpFoundation\Response;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//If 'mode' is either a 1 or 0 and 'target' ends with _expand
//  Then will update the appropriate user _expand flag
if ((isset($_POST['mode']) && ( $_POST['mode'] == 1 || $_POST['mode'] == 0 )) && ( substr($_POST['target'], -7, 7) == "_expand" )) {
  //set the user setting
    setUserSetting($_POST['target'], $_POST['mode']);
}

//mdsupport : Generic user setting
if ((isset($_POST['lab'])) && (isset($_POST['val']))) {
    setUserSetting($_POST['lab'], $_POST['val']);
}

// even more generic
if ((isset($_POST['target'])) && (isset($_POST['setting']))) {
    setUserSetting($_POST['target'], $_POST['setting']);
}

// @todo This is crude, but if we make it here thre should be a proper response, so for now send a 200 but really we need better Response handling
$res = new Response();
$res->send();
