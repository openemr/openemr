<?php
//SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once('../../globals.php');

?>

<!doctype html>
<html>
  <head>
    <title>Your Store</title>
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="jquery-1.4.4.min.js"></script>
    <script type="text/javascript" src="jquery.validate.min.js"></script>
    <script type="text/javascript" src="jquery.validate.creditcardtypes.js"></script>
    <script>
      $(document).ready(function(){
        $("#checkout_form").validate();
      });
      </script>
  </head>
  <body>
    <h1>Your Store</h1> 
    <h2>Checkout</h2>
    <?php
    require_once 'coffee_store_settings.php';
    //include 'anet_php_sdk/AuthorizeNet.php';
    
    if ($METHOD_TO_USE == "AIM") {
        ?>
        <form method="post" action="process_sale.php" id="checkout_form">
        <input type="hidden" name="size" value="<?php echo $size?>">
        <?php
    } else {
        ?>
        <form method="post" action="<?php echo (AUTHORIZENET_SANDBOX ? AuthorizeNetDPM::SANDBOX_URL : AuthorizeNetDPM::LIVE_URL)?>" id="checkout_form">
        <?php
        $time = time();
        $fp_sequence = $time;
        $fp = AuthorizeNetDPM::getFingerprint(AUTHORIZENET_API_LOGIN_ID, AUTHORIZENET_TRANSACTION_KEY, $amount, $fp_sequence, $time);
        $sim = new AuthorizeNetSIM_Form(
            array(
            'x_amount'        => $amount,
            'x_fp_sequence'   => $fp_sequence,
            'x_fp_hash'       => $fp,
            'x_fp_timestamp'  => $time,
            'x_relay_response'=> "TRUE",
            'x_relay_url'     => $coffee_store_relay_url,
            'x_login'         => AUTHORIZENET_API_LOGIN_ID,
            'x_test_request'  => TEST_REQUEST,
            
            )
        );
        echo $sim->getHiddenFieldString();
    }
    ?>
      <fieldset>
        <div>
          <label>Credit Card Number</label>
          <input type="text" class="text required creditcard" size="15" name="x_card_num" value="6011000000000012"></input>
        </div>
        <div>
          <label>Exp.</label>
          <input type="text" class="text required" size="4" name="x_exp_date" value="10/15"></input>
        </div>
        <div>
          <label>CCV</label>
          <input type="text" class="text required" size="4" name="x_card_code" value="782"></input>
        </div>
      </fieldset>
      <fieldset>
        <div>
          <label>First Name</label>
          <input type="text" class="text required" size="15" name="x_first_name" value="John"></input>
        </div>
        <div>
          <label>Last Name</label>
          <input type="text" class="text required" size="14" name="x_last_name" value="Doe"></input>
        </div>
      </fieldset>
      <fieldset>
        <div>
          <label>Address</label>
          <input type="text" class="text required" size="26" name="x_address" value="123 Four Street"></input>
        </div>
        <div>
          <label>City</label>
          <input type="text" class="text required" size="15" name="x_city" value="San Francisco"></input>
        </div>
      </fieldset>
      <fieldset>
        <div>
          <label>State</label>
          <input type="text" class="text required" size="4" name="x_state" value="CA"></input>
        </div>
        <div>
          <label>Zip Code</label>
          <input type="text" class="text required" size="9" name="x_zip" value="94133"></input>
        </div>
        <div>
          <label>Country</label>
          <input type="text" class="text required" size="22" name="x_country" value="US"></input>
        </div>
      </fieldset>
      <input type="submit" value="BUY" class="submit buy">
    </form>
  </body>
</html>
