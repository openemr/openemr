<?php

 /**
  * Dash Board Header.
  *
  * @package   OpenEMR
  * @link      http://www.open-emr.org
  * @author    Ranganath Pathak <pathak@scrs1.org>
  * @author    Brady Miller <brady.g.miller@gmail.com>
  * @author    Robert Down <robertdown@live.com>
  * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
  * @copyright Copyright (c) 2018-2020 Brady Miller <brady.g.miller@gmail.com>
  * @copyright Copyright (c) 2022-2023 Robert Down <robertdown@live.com>
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

require_once("$srcdir/display_help_icon_inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;

$twigContainer = new TwigContainer();
$t = $twigContainer->getTwig();

$viewArgs = [
    'pageHeading' => $oemr_ui->pageHeading(),
    'pid' => $pid,
    'csrf' => CsrfUtils::collectCsrfToken(),
];

echo $t->render('patient/dashboard_header.html.twig', $viewArgs);
