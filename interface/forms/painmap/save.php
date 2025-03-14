<?php

/**
 * painmap save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright Copyright Medical Information Integration,LLC <info@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* include globals.php, required. */
require_once(__DIR__ . "/../../globals.php");

/* include api.inc.php. also required. */
require_once($GLOBALS['srcdir'] . '/api.inc.php');

/* include our smarty derived controller class. */
require('C_FormPainMap.class.php');

/* Create a form object. */
$c = new C_FormPainMap();

/* Save the form contents .*/
echo $c->default_action_process($_POST);

/* return to the encounter. */
@formJump();
