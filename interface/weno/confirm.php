<?php
/**
 * weno rx confirm
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
 

require_once('../globals.php');
require_once('transmitDataClass.php');
require_once('$srcdir/patient.inc');
use OpenEMR\Core\Header;

$date = date("Y-m-d");
$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];          //username of the person for this session

$tData = new transmitData();

$send = $tData->getDrugList($pid, $date);
$provider = $tData->getProviderFacility($uid);
$patientPharmacy = $tData->patientPharmacyInfo($pid);
$mailOrder = $tData->mailOrderPharmacy();


?>

<html>
<head>
<?php Header::setupHeader(['jquery-ui', 'jquery-ui-sunny']); ?>

<script type="text/javascript" charset="utf-8">
function validate(){
    var pharmacy = document.getElementById('pharm');
    if(text.value.length == 0){
        alert('<?php print xls("Must select a pharmacy first"); ?>');
        return false;
    }
}


</script>
<style>
footer {
    padding: 0.5em;
    font-size: 0.5em;

    clear: left;
    text-align: center;
    top: 200px;
}
</style>
</head>

<body class="body_top">
<h1><?php print xlt("Prescription Transmit Review"); ?></h1>
<table>
<th width="200px"><?php print xlt("Drug"); ?></th>
<th width="100px"><?php print xlt("Quantity"); ?></th>

<?php
//List drugs to be sent 

  $drug = array(); //list of records that need to updated with pharmacy information
while($list = sqlFetchArray($send)){
    print "<tr align='center'><td>". text($list['drug']) . " </td><td> " . text($list['quantity']) . "</td></tr>";
    $drug[] = $list['id'];
}


?>
</table>
<?php if(empty($drug)){
    echo "<br> <strong> <font color='red'>".xlt("No prescriptions selected"). "</strong></font>";
    exit;
}
?>
<div id="fields">
<h3><?php echo xlt("Select Pharmacy"); ?></h3>
        <?php echo xlt("Patient Default"); ?> <br>
        <input type = 'radio' name = "pharmacy" id = 'patientPharmacy' value="<?php print attr($patientPharmacy['pharmacy_id']) ?>" checked="checked">
        <?php if(!$patientPharmacy['name']){
                   print "<b>".xlt("Please set pharmacy in patient\'s chart!")."</b><br> <br>";
}else{
    print text($patientPharmacy['name']);
}
                    ?><br> <br>
                  
        <?php print xlt("Mail Order") ?> <br>
        <input type = 'radio' name = 'pharmacy' id = 'mailOrder' value = "<?php print attr($mailOrder['id']) ?>"><?php print "CCS Medical 	14255 49th Street, North, Clearwater, FL 33762 <br>" ?> 
        <!-- removed from site but has future plans. 
        <input type='text' size='10' name='city' id="city" value='' placeholder='Enter City First' title="type all or three letters of a city name">
        <input type='text' size='30' name='address' id='address' value='' placeholder='Enter 3 #s or Letters of the Address' title="when searching by street name only put in the first three letters of the name"><br>
        <input type='text' size='70' name='pharmacy_id' id="pharm" value='' class='pharmacy' placeholder='Enter City First Then Type Pharmacy' >
        -->

  <div id="confirm">
  <br><br>
      <input type='submit' id='confirm_btn' value='<?php print xla("Approve Order"); ?>' >
  </div>
 
  <div id="transmit">
      <input type='submit' id='order' value='<?php print xla("Transmit Order"); ?>' >
  </div> 
  <div id="success"></div>  
</div>

<script type="text/javascript">

<!-- This is not used right now but dont want to delete yet-->	
$(function() {
    //Pharmacy autocomplete
    $("#pharm").click(function() {
    var city = $("#city").val();
    var address = $("#address").val();
 
    var str = "../../library/ajax/pharmacy_autocomplete/search.php?city="+city+"&address="+address;
        //autocomplete
        $(".pharmacy").autocomplete({
            source: str,
            minLength: 1
        }); 
 
    });
});

$(document).ready(function(){


    var toTran = <?php echo json_encode($drug); ?>; //pass php array to jquery script
    var jsonArray = [];

    //Hides the transmit button until 
    $("#transmit").hide();
    
    //Updates the order with the pharmacy information
    $("#confirm_btn").click(function(){
     
     /*
        var pharmSelect = $("#pharm").val();
        if(pharmSelect.length == 0){
            alert("Must select a pharmacy first");
            return;
        }
        */
        var pharm_Id = $("input[name='pharmacy']:checked").val();
        //var pharm_Id = 3;//pharmId.filter(':checked').val();
        if($('#patientPharmacy').is(':checked')) { 
               
            pharm_Id; 

        }
        if($('#mailOrder').is(':checked')) { 
               
            pharm_Id; 

        }       
        //alert(pharm_Id);
        $("#transmit").show();
        
        //this is to set the pharmacy for the presciption(s)
        $.ajax({ url: 'markTx.php?arr='+pharm_Id+','+toTran });
        
        //Makes the returned ajax call a global variable
        function getReturnJson(x){
            //to process multiple prescriptions. 
            jsonArray.push(returnedJson = x);

        }

          //loop through the prescription to create json code on fly 
        $.each(toTran, function( index, value ) {
        //this is to create the json script to be transmitted
        $.ajax({
                   //feeds the json generator
            url: 'jsonscript.php?getJson='+pharm_Id+','+value, 
            
            success: function(response){
                console.log(response);
               getReturnJson(response); 
            },
            error: function(xhr, status, error){
                 console.log(xhr);
                 console.log(status);
                 console.log(error);
                 console.warn(xhr.responseText);                             
          }
        });
      }); //end of   
    }); //end of confirm button
    
    //Transmit order(s)
  $('#order').click(function(){
     
     $('#success').html("<i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>");
       
         $.each(jsonArray, function(index, value){
         var send = value;
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
         }); //end of ajax call
      }); // end of each loop

         $("#transmit").hide();
    });
    
    
}); //end of doc ready


</script>
<br>
<br>
<br>

<footer>
<p><?php print xlt("Open Med Practice and its suppliers use their commercially reasonable efforts to provide the most current and complete data available to them concerning prescription histories, drug interactions and formularies, patient allergies and other factors, but by your use of this service you acknowledge that (1) the completeness and accuracy of such data depends upon the completeness and accuracy with which it is entered into connected electronic databases by physicians, physician’s offices, pharmaceutical benefits managers, electronic medical records firms, and other network participants, (2) such data is subject to error or omission in input, storage or retrieval, transmission and display, technical disruption, power or service outages, or other interruptions in electronic communication, any or all of which may be beyond the control of Open Med Practice and its suppliers, and (3) some information may be unavailable due to regulatory, contractual, privacy or other legal restrictions. You are responsible to use your clinical judgment at all times in rendering medical service and advice."); ?></p>
</footer>		
</body>
</html>