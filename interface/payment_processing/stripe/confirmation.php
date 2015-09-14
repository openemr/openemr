<?php 
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
          data-amount="<?php echo $amount ?>" data-description="Patient Payment"></script>
</form>

<button><a href="index.php">Back</a></button>

</center>