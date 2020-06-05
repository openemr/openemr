<?php

/**
 * weno rx confirm
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Rx\Weno\TransmitData;

$date = date("Y-m-d");
$pid = $GLOBALS['pid'];
$uid = $_SESSION['authUserID'];          //username of the person for this session

$tData = new TransmitData();

$send = $tData->getDrugList($pid, $date);
$provider = $tData->getProviderFacility($uid);
$patientPharmacy = $tData->patientPharmacyInfo($pid);
$mailOrder = $tData->mailOrderPharmacy();

?>

<html>
<head>
    <?php Header::setupHeader(); ?>


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

<h2><?php print xlt("Prescription Transmit Review"); ?></h2>
<div class="table-responsive text-center w-75" style="margin-left:10%;">
<table class="table table-sm table-striped">
    <thead>
        <th class='text-center'><?php print xlt("Drug"); ?></th>
        <th class='text-center'><?php print xlt("Quantity"); ?></th>
    </thead>
    <?php
    //List drugs to be sent

    $drug = array(); //list of records that need to updated with pharmacy information
    while ($list = sqlFetchArray($send)) {
        print "<tr class='text-center'><td>" . text($list['drug']) . " </td><td> " . text($list['quantity']) . "</td></tr>";
        $drug[] = $list['id'];
    }


    ?>
</table>
</div>
<?php if (empty($drug)) {
    echo "<br /> <p class='text-danger'><strong> " . xlt("No prescriptions selected") . "</strong></p>";
    exit;
}
?>
<div id="fields">
    <h3><?php echo xlt("Select Pharmacy"); ?></h3>
    <?php echo xlt("Patient Default"); ?> <br />
    <input type = 'radio' name = "pharmacy" id = 'patientPharmacy' value="<?php print attr($patientPharmacy['pharmacy_id']) ?>" checked="checked">
    <?php
    if (!$patientPharmacy['name']) {
        print "<b>" . xlt("Please set pharmacy in patient\'s chart!") . "</b><br /> <br />";
    } else {
        print text($patientPharmacy['name']);
    }
    ?><br /> <br />

    <?php print xlt("Mail Order") ?> <br />
    <input type = 'radio' name = 'pharmacy' id = 'mailOrder' value = "<?php print attr($mailOrder['id']) ?>"><?php print "CCS Medical 	14255 49th Street, North, Clearwater, FL 33762 <br />" ?>

    <div id="confirm" show>
        <br /><br />
        <input type='submit' id='confirm_btn' value='<?php print xla("Approve Order"); ?>' >
    </div>

    <div id="transmit" hidden>
        <br /><br />
        <input type='submit' id='order' value='<?php print xla("Transmit Order"); ?>' >
    </div>
    <div id="success"></div>
</div>

<script>


    $(function () {


        var toTran = <?php echo json_encode($drug); ?>; //pass php array to jquery script
        var jsonArray = [];

        //Updates the order with the pharmacy information
        $("#confirm_btn").click(function(){

            var pharm_Id = $("input[name='pharmacy']:checked").val();

            if($('#patientPharmacy').is(':checked')) {

                pharm_Id;

            }
            if($('#mailOrder').is(':checked')) {

                pharm_Id;

            }
            //Shows the transmit button after selecting approved
            $("#transmit").toggle();
            //Hide the approve button after selection
            $("#confirm").toggle();

            //this is to set the pharmacy for the presciption(s)
            $.ajax({ url: 'markTx.php?arr=' + encodeURIComponent(pharm_Id) + ',' + encodeURIComponent(toTran) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?> });

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
                    url: 'jsonScript.php?getJson=' + encodeURIComponent(pharm_Id) + ',' + encodeURIComponent(value) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>,

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
        $('#order').click(function() {
            $('#success').html("<i class='fa fa-sync fa-spin fa-3x fa-fw'></i>");
            var request = [];
            var responses = [];
            // Lets not talk to user here because most likely won't make to user anyway.
            // So we'll batch the ajax calls with an apply and promise so everyone is happy.
            // This isn't foolproof so look here if not batching large json requests.
            $.each(jsonArray, function(index, value) {
                request.push(
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: 'https://apa.openmedpractice.com/apa/interface/weno/receivingrx.php?',
                        data: {"scripts": value},

                        success: function (response) {
                            responses.push(response);
                        },
                        error: function (xhr, status, error) {
                            console.log(xhr);
                            console.log(status);
                            console.log(error);
                            console.warn(xhr.responseText);
                        }
                    }) //end of ajax push
                );
            }); // end of each loop

            $("#transmit").toggle(); // turn off xmit button for spinner

            // here we apply actual weno server requests and I've been promised
            // a done event to present results to user in one shot.
            $.when.apply(null, request).done(function() {
                // all done with our requests, lets announce what weno says.
                var announce = <?php echo xlj("Send Complete - Prescription(s) Return Status");?>;
                $('#success').html('<p><h4 class="bg-info">' + announce + '</h4></p>');
                $.each(responses, function (index, response) {
                    console.log('result: ' + response);
                    $('#success').append('<p>' + response + '</p>');
                });
            });

        }); // That's it for click event.

    }); //end of doc ready

</script>
<br />
<br />
<br />

<footer>
    <p><?php print xlt("Open Med Practice and its suppliers use their commercially reasonable efforts to provide the most current and complete data available to them concerning prescription histories, drug interactions and formularies, patient allergies and other factors, but by your use of this service you acknowledge that (1) the completeness and accuracy of such data depends upon the completeness and accuracy with which it is entered into connected electronic databases by physicians, physician’s offices, pharmaceutical benefits managers, electronic medical records firms, and other network participants, (2) such data is subject to error or omission in input, storage or retrieval, transmission and display, technical disruption, power or service outages, or other interruptions in electronic communication, any or all of which may be beyond the control of Open Med Practice and its suppliers, and (3) some information may be unavailable due to regulatory, contractual, privacy or other legal restrictions. You are responsible to use your clinical judgment at all times in rendering medical service and advice."); ?></p>
</footer>
</body>
</html>
