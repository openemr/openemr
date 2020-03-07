<?php
use OpenEMR\Common\Crypto\CryptoGen;

// Call to patient if Allow Voice Message and set reminder sent flag.
if ($hipaa_voice == "YES") {
    // Automated VOIP service provided by Maviq. Please visit http://signup.maviq.com for more information.
    $siteId = $GLOBALS['phone_gateway_username'];
    $cryptoGen = new CryptoGen();
    $token = $cryptoGen->decryptStandard($GLOBALS['phone_gateway_password']);
    $endpoint = $GLOBALS['phone_gateway_url'];
    $client = new MaviqClient($siteId, $token, $endpoint);
    // Set up params.
    $data = array(
        "firstName" => $patientfname,
        "lastName" => $patientlname,
        "phone" => $patientphone,
        // "apptDate" => "$scheduled_date[1]/$scheduled_date[2]/$scheduled_date[0]",
        "timeRange" => "10-18",
        "type" => "reminder",
        "timeZone" => date('P'),
        "greeting" => str_replace("[[sender]]", $sender_name, str_replace("[[patient_name]]", $patientfname, $myrow['reminder_content']))
    );

    // Make the call.
    $response = $client->sendRequest("appointment", "POST", $data);

    if ($response->IsError) {
        // deal with and keep track of this unsuccessful call
        $logging['number_failed_calls'] ++;
    } else {
        // deal with and keep track of this succesful call
        sqlStatementCdrEngine("UPDATE `patient_reminders` SET `voice_status`='1', `date_sent`=NOW() WHERE id=?", array(
            $reminder['id']
        ));
        $logging['number_success_calls'] ++;
    }
}