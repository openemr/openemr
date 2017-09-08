<?php
/**
 * weno rx ajax sample code
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html>
<head>
<title><?php print xlt("Weno Sample Code") ?></title>
<?php Header::setupHeader(['jquery-ui', 'jquery-ui-sunny']); ?>

</head>

<body>

<h3><?php print xlt("Convert a JavaScript object into a JSON string, and send it to the server.") ?></h3>

  <div id="confirm">
  <br><br>
      <input type='submit' id='confirm_btn' value='<?php print xla("Confirm") ?>' >
  </div>
   <div id="transmit">
      <input type='submit' id='order' value='<?php print xla("Transmit Order") ?>' >
  </div>
    <div id="success"></div>
      
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

    $('#order').click(function(){
        $('#success').html("<i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>");
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
        var pharmacy = {"pharmacy": {
                     "storename":"Test Direct Pharmacy",
                     "storenpi":321,
                     "pharmacy":1234567,
                     "pharmacyPhone":2109128143,
                     "pharmacyFax":5128525926                           
        }};

        var script = {"script": {
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