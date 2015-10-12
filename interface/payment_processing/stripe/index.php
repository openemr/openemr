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


<script language="javascript">
    //form validation of amount is valid and is a number
function checkvalue() { 
    var amount = document.getElementById('amount').value; 
        
    if(!amount.match(/\S/)) {
        alert ('Empty value is not allowed');
        return false;       
    }
    
    var regex = /^[1-9]\d*(((,\d{3}){1})?(\.\d{0,2})?)$/;
    if(!amount.match(regex)){
        alert ('Numbers only');
        return false;
    }
    
    var twoDecimalPlaces = /\.\d{2}$/g;
    var oneDecimalPlace = /\.\d{1}$/g;
    var noDecimalPlacesWithDecimal = /\.\d{0}$/g;
    
    if(!amount.match(twoDecimalPlaces)){
      alert ('Please add .00 to amount');
      return false;
    }
        
};


        
</script>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
</head>
<body class="body_top">
<br>
<center>
<h3>Enter payment amount</h3>

<form method = "post" action="confirmation.php" onsubmit="return checkvalue(this)">
<input type="text" size="5" id="amount" name="amount" autocomplete="off" value="" />
<input type="submit" value="Next">

</form>
</center>
</body>
</html>