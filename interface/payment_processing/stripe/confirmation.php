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


require_once('config.php'); 

$a = $_POST['amount'];

$remove_decimal = explode('.', $a);
$amount = $remove_decimal[0].$remove_decimal[1];
 

?>
<center>
<h3>Confirm amount $<?php echo $a; ?></h3>
<form action="charge.php" method="post">
  <script src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
          data-key="<?php echo $stripe['publishable_key']; ?>"
          data-amount="<?php echo $amount; ?>" 
          data-zip-code=""
          data-description="Patient Payment"></script>
          <input type="hidden" value="<?php echo $amount; ?>" name="chargeAmount">
</form>

<button><a href="index.php">Back</a></button>

</center>