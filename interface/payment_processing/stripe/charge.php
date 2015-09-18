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

  $token  = $_POST['stripeToken'];
  $email  = $_POST['stripeEmail'];
  $amount = $_POST['chargeAmount'];
  echo $email . $amount;
  
  $customer = \Stripe\Customer::create(array(
      'email' => $email,
      'card'  => $token
  ));

  $charge = \Stripe\Charge::create(array(
      'customer' => $customer->id,
      'amount'   => $amount,
      'currency' => 'usd',

      
  ));
  
   echo '<h1>Successfully charged</h1>';
    echo "Charge Code: " .$charge->id . "<br>";

   echo "Amount Charged: ". sprintf('%.2f', $charge['amount']/100) . "<br>"  
     . "Patient ID: ". $GLOBALS['pid'] . "<br>";
   
   echo "Person running Charge: ". $_SESSION['authUser'] . "<br>" ;
   
   
  ?>
  <button><a href="index.php">New Charge</a></button>