<?php
//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once("../../globals.php");
?>
<html>
<title>CC Processing</title>
<head>

</head>
<body>
<br>
<center>
<h3>Enter payment amount</h3>
<form method = "post" action="confirmation.php">
<input type="text" size="5" name="amount" autocomplete="off" >
<input type="submit" value="Next">

</form>
</center>
</body>
</html>