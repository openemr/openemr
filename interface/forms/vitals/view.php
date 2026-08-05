<?php

/**
 * vitals view.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$srcdir = \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir();

require_once("$srcdir/api.inc.php");
require_once "C_FormVitals.class.php";

$c = new C_FormVitals();
$c->setFormId($_GET['id']);
echo $c->default_action();
