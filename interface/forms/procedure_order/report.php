<?php

/**
 * Encounter form for entering procedure orders.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010-2013 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc");
require_once($GLOBALS["srcdir"] . "/options.inc.php");
require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");

function procedure_order_report($pid, $encounter, $cols, $id)
{
    generate_order_report($id);
}
