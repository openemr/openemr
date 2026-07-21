<?php

 /**
  * Dash Board Header.
  *
  * @package   OpenEMR
  * @link      https://www.open-emr.org
  * @author    Ranganath Pathak <pathak@scrs1.org>
  * @author    Brady Miller <brady.g.miller@gmail.com>
  * @author    Robert Down <robertdown@live.com>
  * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
  * @copyright Copyright (c) 2018-2020 Brady Miller <brady.g.miller@gmail.com>
  * @copyright Copyright (c) 2022-2023 Robert Down <robertdown@live.com>
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

require_once(\OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/display_help_icon_inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;

$twigContainer = new TwigContainer();
$t = $twigContainer->getTwig();

$session = SessionWrapperFactory::getInstance()->getActiveSession();
// This file is included by other patient_file pages that set $oemr_ui and $pid
// in their local scope; fall back to safe defaults so PHPStan can analyze it.
$pid ??= ($session->get('pid') ?? 0);
$viewArgs = [
    'pageHeading' => isset($oemr_ui) ? $oemr_ui->pageHeading() : '',
    'pid' => $pid,
    'csrf' => CsrfUtils::collectCsrfToken(session: $session),
];

echo $t->render('patient/dashboard_header.html.twig', $viewArgs);
