<?php

/**
 * evaluation new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

require("C_FormEvaluation.class.php");

$c = new C_FormEvaluation();
echo $c->view_action($_GET['id']);
