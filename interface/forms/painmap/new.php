<?php

/**
 * painmap new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @copyright Copyright Medical Information Integration,LLC <info@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* include globals.php, required. */
require_once(dirname(__FILE__) . '/../../globals.php');

/* include api.inc. also required. */
require_once($GLOBALS['srcdir'] . '/api.inc');

/* include our smarty derived controller class. */
require('C_FormPainMap.class.php');

/* Create a form object. */
$c = new C_FormPainMap();

/* Render a 'new form' page. */
echo $c->default_action();
