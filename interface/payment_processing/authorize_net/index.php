

<?php 
//SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once('../../globals.php');



require_once 'coffee_store_settings.php';


?>


<!doctype html>
<html>
  <head>
    <title>Your Store</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="splash.css">
  </head>
  <body>
    
    <h1>Your Store</h1>
	
    <a href="select_size.php" class="submit clearfix">Buy Coffee</a>
    
  </body>
</html>
<?php 

//var_dump($GLOBALS);
?>