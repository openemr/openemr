<?php
////////////////////////////////////////////////////////////////////
// Class:   AWS PINPOINT SMS Api
// Usage:
// <code>
// require_once("sms_awspinpoint.php");
// $sms = new sms( "key", "secret" );
// $sms->send("123456789","sender","message");
// Note the only reason sender is included here is because of how cron_SendSMS in cron_functions.php expects to format send requests
// The sender value can be set to anything because it will be ignored.
// </code>
//
// Package: sms_awspinpoint
// Created by: Jacob Mevorach
////////////////////////////////////////////////////////////////////
use Aws\Pinpoint\PinpointClient;
use Aws\Exception\AwsException;
class sms
{
    // init vars
    var $key = "";
    var $secret = "";
    var $applicationID = "";
    var $region = "";

    function __construct($strKey, $strSecret, $strApplicationID)
    {

        // The key is the access key ID of an AWS key pair associated with an IAM user with permissions to send AWS Pinpoint SMS messages
        // The secret is the secret access key of an AWS key pair associated with an IAM user with permissions to send AWS Pinpoint SMS messages

        $this->key = $strKey;
        $this->secret = $strPass;


        // The application ID is the ID of an AWS Pinpoint application with permissions to send SMS message for which the IAM user referenced above has access to
        // You must encode the region you'd like to use by writing the applicationID as <region>:<applicationID>.
        // If there's no region encoded in the application ID then we're going to throw an error.

        // Parse out region from the Application ID

        // Check if the string contains a colon
        if (strpos($strApplicationID, ':') !== false) {

            // Extract the part before the colon
            $beforeColon = substr($string, 0, strpos($string, ':'));

            // Extract the part after the colon
            $afterColon = substr($string, strpos($string, ':') + 1);

        } else {
            die("ERROR: No region encoded in application ID!");
        }

        $this->applicationID = $beforeColon;
        $this->region = $afterColon;
    }

    /**
     * Send sms method
     * @access public
     * @return string response
     */

    function send($phoneNumber, $sender, $message)
    {
        // Create a Pinpoint client
        $pinpointClient = new PinpointClient([
            'region' => $region, // Encoded AWS region
            'version' => '2016-12-01', // Use version '2016-12-01' of the API for Pinpoint.
            'credentials' => [
                'key' => $key, // Your AWS Access Key
                'secret' => $secret, // Your AWS Secret Access Key
            ],
        ]);

        try {
            // Send SMS message using AWS Pinpoint
            $result = $pinpointClient->sendMessages([
                'ApplicationId' => $applicationId,
                'MessageRequest' => [
                    'Addresses' => [
                        $phoneNumber => [
                            'ChannelType' => 'SMS', // Specify the channel type as SMS
                        ],
                    ],
                    'MessageConfiguration' => [
                        'SMSMessage' => [
                            'Body' => $message,
                            'MessageType' => 'TRANSACTIONAL' // other option is "PROMOTIONAL"
                        ],
                    ],
                ],
            ]);
        } catch (AwsException $e) {
            die("Error: " . $e->getMessage() . "\n");
        }

        // Extract the status message response
        $messageResponse = $result['MessageResponse']['Result'][$phoneNumber]['StatusMessage'];

        // Return the status message from the message response
        return $messageResponse;
    }
}
