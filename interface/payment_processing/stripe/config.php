<?php

/**
 * To collect credit card payments in openEMR without assigning to an encounter
 * using stripe.com
 *
 * sets the public and secret keys for the secure stripe transaction
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 * 
 * @package OpenEMR
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2016, 2017 Sherwin Gaddis, Ranganath Pathak
 * @version 3.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://www.open-emr.org 
 */

require_once('../../../vendor/autoload.php'); // loads stripe classes  via composer
require_once("../../globals.php");

$stripe = array(
  "secret_key"      => $GLOBALS['s_key_stripe'],
  "publishable_key" => $GLOBALS['pk_key_stripe']
);

\Stripe\Stripe::setApiKey($stripe['secret_key']);
