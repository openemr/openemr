<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * Added to be able to process credit cards within the system
 * Sherwin Gaddis sherwingaddis@gmail.com
 * 
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once('class/init.php');
require_once("../../globals.php");

 

$stripe = array(
  "secret_key"      => $GLOBALS['s_key_stripe'],
  "publishable_key" => $GLOBALS['pk_key_stripe']
);

\Stripe\Stripe::setApiKey($stripe['secret_key']);
