<?php

/**
 * prior auth form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$srcdir = OEGlobalsBag::getInstance()->getSrcDir();

require_once("$srcdir/api.inc.php");
require_once("C_FormPriorAuth.class.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

$_POST['date_from']  = (!empty($_POST['date_from'])) ? DateToYYYYMMDD($_POST['date_from']) : null;
$_POST['date_to']  = (!empty($_POST['date_to'])) ? DateToYYYYMMDD($_POST['date_to']) : null;

$c = new C_FormPriorAuth();
echo $c->default_action_process();
@formJump();
