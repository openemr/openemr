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

require_once(dirname(__FILE__) . "/lib/appsql.class.php");

$logit = new ApplicationTable();
$logit->portalLog('logout', $_SESSION['pid'], ($_SESSION['portal_username'] . ': ' . $_SESSION['ptName'] . ':success'));

//log out by killing the session
require_once(dirname(__FILE__) . "/../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();

//redirect to pretty login/logout page
// $landingpage is defined in above verify_session.php script
header('Location: ' . $landingpage . '&logout');
//
