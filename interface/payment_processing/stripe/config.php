<?php
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
