<?php

/**
 * Billing notes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\FormReportRenderer;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

require_once("../../globals.php");
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/forms.inc.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

$ptid     = $_GET['ptid'] + 0;
$encid    = $_GET['encid'] + 0;
$formname = strtr(
    $_GET['formname'],
    ['.' => '', '\\' => '', '/' => '', '\'' => '', '"' => '', "\r" => '', "\n" => '']
);
$formid   = $_GET['formid'] + 0;

if (!hasFormPermission($formname)) {
    exit;
}
$formReportRenderer = new FormReportRenderer();
$formReportRenderer->renderReport($formname, "encounters_ajax.php", $ptid, $encid, 2, $formid);
