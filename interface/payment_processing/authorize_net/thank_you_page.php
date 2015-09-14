<?php 
//SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once('../../globals.php');



require_once 'coffee_store_settings.php';?>

<!doctype html>
<html>
  <head>
    <title>Your Store</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h1>Your Store</h1>
    <h2>Thank You</h2>
    <h3>Your transaction ID:</h3>
    <div class="id"><?php echo htmlentities($_GET['transaction_id'])?></div>
    <?php
    if ($METHOD_TO_USE == "AIM")
    {?>
      <h3>Coffee not hot enough?</h3>
      Request a
      <form action="process_refund.php" method="post">
        <input type="hidden" name="transaction_id" value="<?php echo htmlentities($_GET['transaction_id'])?>">
        <input type="submit" class="submit refund" value="Refund">
      </form>
    <?php
    }
    ?>
    <form method="get" action="index.php">
      <input type="submit" class="submit" value="Start Over">
    </form>
  </body>
</html>
