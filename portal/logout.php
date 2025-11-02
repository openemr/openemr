<?php

/**
 * portal/logout.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("verify_session.php");
require_once(__DIR__ . "/lib/appsql.class.php");

use OpenEMR\Common\Session\SessionUtil;
$session = SessionUtil::portalSessionStart();
$logit = new ApplicationTable();
$logit->portalLog('logout', $session->get('pid'), ($session->get('portal_username') . ': ' . $session->get('ptName') . ':success'));

//log out by killing the session
SessionUtil::portalSessionCookieDestroy();

//redirect to pretty login/logout page
// $landingpage is defined in above verify_session.php script
header('Location: ' . $landingpage . '&logout');
//
