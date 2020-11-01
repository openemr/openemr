<?php

/** @package    verysimple::Payment */

/**
 * import supporting libraries
 */
require_once("PaymentProcessor.php");

/**
 * SkipJack extends the generic PaymentProcessor object to process
 * a PaymentRequest through the SkipJack payment gateway.
 *
 * @package verysimple::Payment
 * @author VerySimple Inc.
 * @copyright 1997-2012 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.1
 */
class SkipJack extends PaymentProcessor
{
    private $liveUrl = "https://www.skipjackic.com/scripts/evolvcc.dll?AuthorizeApi";
    private $testUrl = "https://developer.skipjackic.com/scripts/evolvcc.dll?AuthorizeAPI";
    private $url = "";

    /**
     * Called on contruction
     *
     * @param bool $test
     *          set to true to enable test mode. default = false
     */
    function Init($testmode)
    {
        // set the post url depending on whether we're in test mode or not
        $this->url = $testmode ? $this->testUrl : $this->liveUrl;
    }

    /**
     *
     * @see PaymentProcessor::Refund()
     */
    function Refund(RefundRequest $req)
    {
        throw new Exception("Refund not implemented for this gateway");
    }

    /**
     * Process a PaymentRequest
     *
     * @param PaymentRequest $req
     *          Request object to be processed
     * @return PaymentResponse
     */
    function Process(PaymentRequest $req)
    {
        if ($this->_testMode) {
            if ($req->SerialNumber == "" || $req->DeveloperSerialNumber == "") {
                throw new Exception("SkipJack requires  a SerialNumber and DeveloperSerialNumber for test transactions.  Free developer accounts can be obtained through SkipJack.com");
            }
        } else {
            if ($req->SerialNumber == "") {
                throw new Exception("SkipJack requires a SerialNumber for live transactions");
            }
        }

        // skipjack requires a funky formatted order string
        if (! $req->OrderString) {
            $req->OrderString = "1~None~0.00~0~N~||";
        }

        $resp = new PaymentResponse();
        $resp->OrderNumber = $req->OrderNumber;

        // post to skipjack service
        $resp->RawResponse = $this->CurlPost($this->url, $this->GetPostData($req));

        // response is two lines - first line is field name, 2nd line is values
        $lines = explode("\r\n", $resp->RawResponse);

        // strip off the beginning and ending doublequote
        $lines [0] = substr($lines [0], 1, strlen($lines [0]) - 2);
        $lines [1] = substr($lines [1], 1, strlen($lines [1]) - 2);

        // split the fields and values
        $fields = explode("\",\"", $lines [0]);
        $vals = explode("\",\"", $lines [1]);

        // convert these two lines into a hash so we can get individual values
        for ($i = 0; $i < count($fields); $i++) {
            $resp->ParsedResponse [$fields [$i]] = $vals [$i];
        }

        // convert these codes into a generic response object
        $resp->ResponseCode = $resp->ParsedResponse ["szReturnCode"];
        $resp->TransactionId = $resp->ParsedResponse ["AUTHCODE"];

        // figure out if the transaction was a total success or not
        $verifyOK = $resp->ParsedResponse ["szReturnCode"] == "1";
        $approvedOK = $resp->ParsedResponse ["szIsApproved"] == "1";
        $authOK = $resp->ParsedResponse ["AUTHCODE"] != "EMPTY" && $resp->ParsedResponse ["AUTHCODE"] != "" && $resp->ParsedResponse ["szAuthorizationResponseCode"] != "";
        $resp->IsSuccess = ($verifyOK && $approvedOK && $authOK);

        // dependin on the status, get the best description we can
        if ($resp->IsSuccess) {
            $resp->ResponseMessage = $this->GetMessage($resp->ParsedResponse ["szReturnCode"]);
        } elseif (! $verifyOK) {
            // verification failed
            $resp->ResponseMessage = $this->GetMessage($resp->ParsedResponse ["szReturnCode"]);
        } elseif (! $authOK) {
            // verification was ok, but the processor didn't process the transaction
            $resp->ResponseMessage = $resp->ParsedResponse ["szAuthorizationDeclinedMessage"];
        } else {
            // we don't know why it so just display all the possible error messages
            $resp->ResponseMessage = $resp->ParsedResponse ["szAuthorizationDeclinedMessage"] . " " . $resp->ParsedResponse ["szAVSResponseMessage"] . " " . $resp->ParsedResponse ["szCVV2ResponseMessage"];
        }

        return $resp;
    }
    private function GetPostData($req)
    {
        $data = array ();
        $data ["orderstring"] = $req->OrderString;
        $data ["serialnumber"] = $req->SerialNumber;
        $data ["developerserialnumber"] = $req->DeveloperSerialNumber;
        $data ["sjname"] = $req->CustomerName;
        $data ["streetaddress"] = $req->CustomerStreetAddress;
        $data ["city"] = $req->CustomerCity;
        $data ["state"] = $req->CustomerState;
        $data ["zipcode"] = $req->CustomerZipCode;
        $data ["shiptophone"] = $req->CustomerPhone;
        $data ["email"] = $req->CustomerEmail;
        $data ["ordernumber"] = "CC" . substr(md5(time()), 0, 20);
        $data ["transactionamount"] = number_format($req->TransactionAmount, 2, ".", "");
        $data ["accountnumber"] = str_replace(array (
                "-",
                " "
        ), array (
                "",
                ""
        ), $req->CCNumber);
        $data ["month"] = $req->CCExpMonth;
        $data ["year"] = $req->CCExpYear;
        $data ["cvv2"] = $req->CCSecurityCode;
        $data ["country"] = $req->CustomerCountry;
        $data ["comment"] = $req->Comment;
        return $data;
    }

    /**
     * Returns a text description based on the return code
     *
     * @param string $code
     *          the skipjack response code
     * @return string
     */
    private function GetMessage($code)
    {
        $errors = array ();
        $errors ["-1"] = "Error in request Data was not by received intact by Skipjack Transaction Network.";
        $errors ["0"] = "Communication Failure Error in Request and Response at IP level. Use Get Transaction Status before retrying transaction.";
        $errors ["1"] = "Success";
        $errors ["-35"] = "Credit card number does not comply with the Mod10 check. Retry with correct credit card number.";
        $errors ["-37"] = "Skipjack is unable to communicate with payment Processor.  Please Retry.";
        $errors ["-39"] = "Check HTML Serial Number length and that it is a correct/valid number. Confirm you are sending to the correct environment (Development or Production)";
        $errors ["-51"] = "Length or value of zip code The value or length for billing zip code is incorrect.";
        $errors ["-52"] = "The value or length for shipping zip code is incorrect.";
        $errors ["-53"] = "The value or length for credit card expiration month is incorrect.";
        $errors ["-54"] = "The value or length of the month or year of the credit card account number was incorrect.";
        $errors ["-55"] = "The value or length or billing street address is incorrect.";
        $errors ["-56"] = "The value or length of the shipping address is incorrect.";
        $errors ["-57"] = "The length of the transaction amount must be at least 3 digits long (excluding the decimal place).";
        $errors ["-58"] = "Merchant Name associated with Skipjack account is misconfigured or invalid";
        $errors ["-59"] = "Merchant Address associated with Skipjack account is misconfigured or invalid Skipjack Financial Services Skipjack Integration GuidePage 52 of 251";
        $errors ["-60"] = "Merchant State associated with Skipjack account is misconfigured or invalid";
        $errors ["-61"] = "The value or length for shipping state/province is empty.";
        $errors ["-62"] = "The value for length orderstring is empty.";
        $errors ["-64"] = "The value for the phone number is incorrect.";
        $errors ["-65"] = "Error empty sjname The value or length for billing name is empty.";
        $errors ["-66"] = "The value or length for billing e-mail is empty.";
        $errors ["-67"] = "The value or length for billing street address is empty.";
        $errors ["-68"] = "The value or length for billing city is empty.";
        $errors ["-69"] = "The value or length for billing state is empty.";
        $errors ["-70"] = "Zip Code field is empty.";
        $errors ["-71"] = "Ordernumber field is empty.";
        $errors ["-72"] = "Account number field is empty";
        $errors ["-73"] = "Month field is empty.";
        $errors ["-74"] = "Year field is empty.";
        $errors ["-75"] = "Serial number field is empty.";
        $errors ["-76"] = "Transaction amount field is empty.";
        $errors ["-77"] = "Orderstring field is empty.";
        $errors ["-78"] = "Shiptophone field is empty.";
        $errors ["-79"] = "Length or value sjname The value or length for billing name is empty.";
        $errors ["-80"] = "Error in the length or value of shiptophone.";
        $errors ["-81"] = "Length or value of Customer location";
        $errors ["-82"] = "The value or length for billing state is empty.";
        $errors ["-83"] = "The value or length for shipping phone is empty.";
        $errors ["-84"] = "There is already an existing pending transaction in the register sharing the posted Order Number.";
        $errors ["-85"] = "Airline leg field value is invalid or empty.";
        $errors ["-86"] = "Airline ticket info field is invalid or empty";
        $errors ["-87"] = "Point of Sale check routing number is invalid or empty.";
        $errors ["-88"] = "Point of Sale check account number is invalid or empty.";
        $errors ["-89"] = "Point of Sale check MICR invalid or empty.";
        $errors ["-90"] = "Point of Sale check number missingor invalid Point of Sale check number invalid or empty.";
        $errors ["-91"] = "\"Make CVV a required field feature\" enabled in the Merchant Account Setup interface but no CVV code was sent in the transaction data.";
        $errors ["-92"] = "Approval Code Invalid. Approval Code is a 6 digit code.";
        $errors ["-93"] = "Blind Credits Request Refused \"Allow Blind Credits\" option must be enabled on the Skipjack Merchant Account.";
        $errors ["-94"] = "BlindCreditsFailed Skipjack Financial Services Skipjack Integration GuidePage 53 of 251";
        $errors ["-95"] = "Voice Authorization Request Refused Voice Authorization option must be enabled on the Skipjack";
        $errors ["-96"] = "Voice Authorizations Failed";
        $errors ["-97"] = "Fraud Rejection Violates Velocity Settling.";
        $errors ["-98"] = "Invalid Discount Amount";
        $errors ["-99"] = "POS PIN Debit Pin BlockDebit-specific";
        $errors ["-100"] = "POS PIN Debit Invalid Key Serial Number Debit-specific";
        $errors ["-101"] = "Data for Verified by Visa/MC Secure Code is invalid.";
        $errors ["-102"] = "Authentication Data Not Allowed";
        $errors ["-103"] = "POS checkdateofbirth variable contains a birth date in an incorrect format. Use MM/DD/YYYY format for this variable.";
        $errors ["-104"] = "POS checkidentificationtype variable contains a identificationtype value which is invalid. Use the single digit value where Social Security Number=1, Drivers License=2 for this variable.";
        $errors ["-105"] = "Track Data is in invalid format.";
        $errors ["-106"] = "POS Check Invalid Account Type";
        $errors ["-107"] = "POS PIN Debit Invalid Sequence Number";
        $errors ["-108"] = "Invalid Transaction ID";
        $errors ["-109"] = "Invalid From Account Type";
        $errors ["-110"] = "Pos Error Invalid To Account Type";
        $errors ["-112"] = "Pos Error Invalid Auth Option";
        $errors ["-113"] = "Pos Error Transaction Failed";
        $errors ["-114"] = "Pos Error Invalid Incoming Eci";
        $errors ["-115"] = "POS Check Invalid Check Type";
        $errors ["-116"] = "POS Check lane or cash register number is invalid. Use a valid lane or cash register number that has been configured in the Skipjack Merchant Account.";
        $errors ["-117"] = "POS Check Invalid Cashier Number";

        return (isset($errors [$code])) ? $errors [$code] : "Unknown Error";
    }
}
