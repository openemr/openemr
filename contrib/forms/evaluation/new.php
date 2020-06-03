<?php

/**
 * evaluation new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");

require("C_FormEvaluation.class.php");

$c = new C_FormEvaluation();
echo $c->default_action();
