<?php
/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */

$sanitize_all_escapes = true;		// SANITIZE ALL ESCAPES

$fake_register_globals = false;		// STOP FAKE REGISTER GLOBALS

require_once('../globals.php');

?>
<!DOCTYPE html>
<html>
<head>
<title><?php print xlt("Weno Sample Code") ?></title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-ui-1-10-4/themes/sunny/jquery-ui.min.css" type="text/css" />

</head>

<body>

<h3><?php print xlt("Convert a JavaScript object into a JSON string, and send it to the server.") ?></h3>

  <div id="confirm">
  <br><br>
      <input type='submit' id='confirm_btn' value='<?php print xlt("Confirm") ?>' >
  </div>
   <div id="transmit">
      <input type='submit' id='order' value='<?php print xlt("Transmit Order") ?>' >
  </div>
    <div id="success"></div>
	
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>

  
<script>

$(document).ready(function(){


    $("#transmit").hide();
    $("#confirm_btn").click(function(){
     //
     //build a process that will create the code below for transmit.
	 //each drug transmitted has to be separate
	 //
        $("#transmit").show();

      });
   });
   
   
$(document).ready(function(){
	$('#order').click(function(){
		$('#success').html("<img src='img/progress.gif'>");
		var patient = { "patient": {
					 "lname" : "Ike",
					 "fname" : "Turner",
					 "street" : "123 Franklin Blvd",
					 "city" : "Chesapeake",
					 "postal" : 23323,
					 "DOB" : "1951-01-03",
					 "Sex" : "M"
		}};
		 
		 var provider = {"provider": {
					 "provlname" : "Mark", 
					 "provfname" : "East", 
					 "provnpi" : 1033137377,
                     "facilityphone" : 7573331212,					 
					 "facilityfax" : 7574441212,
					 "facilityname" : "East Cardiology",
					 "facilitystreet" : "127 Albert Dr.",
					 "facilitycity" : "Chesapeake",
					 "facilitystate" : "VA",
					 "facilityzip" : 23320,
					 "qualifier" : "D91539:C29729",
					 "wenoAccountId" : "111",
					 "wenoAccountPass" : "A80B97AB1A80B92084CB86DE61A1F82A6979990B",
					 "wenoClinicId" : "D91539:C29729"
		 }};
		var	pharmacy = {"pharmacy": {
					 "storename":"Test Direct Pharmacy",
					 "storenpi":321,
					 "pharmacy":1234567,
					 "pharmacyPhone":2109128143,
					 "pharmacyFax":5128525926							
		}};

		var	script = {"script": {
					 "drugName" : "tylonal ES TABS",
					 "drug_NDC" : 405012301,
					 "dateAdded" : "2017-02-02",
					 "quantity" : 50,
					 "refills" : 3,
					 "dateModified" : "2017-02-15",
					 "note" : "add not to pharmacy",
					 "take" : "once a day"							
		}};
			
		
		 var sendPatient = JSON.stringify(patient);
		 var sendProvider = JSON.stringify(provider);
		 var sendPharmacy = JSON.stringify(pharmacy);
		 var sendScript = JSON.stringify(script);	
		 var send = '['+sendPatient+','+sendProvider+','+sendPharmacy+','+sendScript+']';
		 
		 $.ajax({
			type: 'POST',
		dataType: 'JSON',
			 url: 'https://apa.openmedpractice.com/apa/interface/weno/receivingrx.php?',
			data: {"scripts": send},

		  success: function(response){
			  console.log(response);
			  
			  $('#success').html('<p>'+response+'</p>');
		  },
			error: function(xhr, status, error){
				 console.log(xhr);
				 console.log(status);
				 console.log(error);
				 console.warn(xhr.responseText);							 
		  }			
		 });
		 
	});
	
});
</script>

</body>
</html>