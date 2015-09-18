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

require_once("../../globals.php");

$pid = $GLOBALS['pid'];
if($pid == 0){
 echo "Please select a patient first.";
 exit;
}
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