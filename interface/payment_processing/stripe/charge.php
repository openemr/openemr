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

  require_once('config.php');

/* @var $_POST type */
  $token  = filter_input(INPUT_POST, 'stripeToken');
  $email  = filter_input(INPUT_POST, 'stripeEmail');
  $amount = filter_input(INPUT_POST, 'chargeAmount');

  
  $customer = \Stripe\Customer::create(array(
      'email' => $email,
      'card'  => $token
  ));

try{  
  $charge = \Stripe\Charge::create(array(
      'customer' => $customer->id,
      'amount'   => $amount,
      'currency' => 'usd'

      
  ));
} catch(\Stripe\Error\Card $e){
    echo "The card has been declined";
    exit;
}
  $amt = sprintf('%.2f', $charge['amount']/100);
  $pid = $GLOBALS['pid'];
  $user = $_SESSION['authUser'];
  $date = date("Y-m-d");
                //Translate if necessary
   echo '<h1>'. htmlspecialchars( xl_list_label('Successfully charged'), ENT_NOQUOTES) . '</h1>';
    echo "Transaction ID: " .$charge->id . "<br>";

   echo "Amount Charged: ". $amt . "<br>"  
     . "Patient ID: ". $pid . "<br>";
   
   echo "Person Processing Charge: ". $user . "<br>" ;
   
 $sql = "INSERT INTO cc_ledger SET " .
                " id = '' " .
           ", trans_id = '" . $charge->id .
            "', amount = '" . $amt . 
               "', pid = '" . $pid . 
             "', clerk = '" . $user . 
              "', date = '" . $date . "'";
 
    sqlStatement($sql);
 
  ?>
  <button><a href="index.php">New Charge</a></button>