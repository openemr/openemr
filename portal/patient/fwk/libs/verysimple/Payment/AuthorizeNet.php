<?php

/** @package    verysimple::Payment */

/**
 * import supporting libraries
 */
require_once("PaymentProcessor.php");

/**
 * AuthorizeNet extends the generic PaymentProcessor object to process
 * a PaymentRequest through the Authorize.NET payment gateway.
 *
 * @package verysimple::Payment
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.0
 */
class AuthorizeNet extends PaymentProcessor
{
    static $AN_RESPONSE_UNKNOWN = 0;
    static $AN_RESPONSE_SUCCESS = 1;
    static $AN_RESPONSE_DECLINED = 2;
    static $AN_RESPONSE_ERROR = 3;
    static $AN_RESPONSE_HOLD_FOR_REVIEW = 4;
    private $test_mode = false;
    private $post_url = "https://secure.authorize.net/gateway/transact.dll";
    private $api_version = "3.1";
    private $delim_data = "True";
    function Init($testmode)
    {
        // set the post url depending on whether we're in test mode or not
        $this->test_mode = $testmode ? "true" : "false";
    }
    function Process(PaymentRequest $req)
    {
        // convert the request object into compatible param list
        $params = array ();
        $params ['x_amount'] = number_format($req->TransactionAmount, 2, ".", "");
        $params ['x_card_num'] = str_replace(array (
                "-",
                " "
        ), array (
                "",
                ""
        ), $req->CCNumber);
        $params ['x_card_code'] = $req->CCSecurityCode;
        $params ['x_exp_date'] = $req->CCExpMonth . "/" . $req->CCExpYear;
        $params ['x_type'] = $req->TransactionType;
        $params ['x_customer_ip'] = $req->CustomerIP;
        $params ['x_test_request'] = $this->test_mode;
        $params ['x_first_name'] = $req->CustomerFirstName;
        $params ['x_last_name'] = $req->CustomerLastName;
        $params ['x_zip'] = $req->CustomerZipCode;

        // set the global settings
        $params ['x_version'] = $this->api_version;
        $params ['x_delim_data'] = $this->delim_data;
        $params ['x_login'] = $this->Username;
        $params ['x_password'] = $this->Password;

        // do the http post
        $resp = new PaymentResponse();
        $resp->RawResponse = $this->CurlPost($this->post_url, $params);

        // parse the results
        $resp->OrderNumber = $req->OrderNumber;
        $resp->ParsedResponse = explode(",", $resp->RawResponse);
        $resp->ResponseCode = $resp->ParsedResponse [0];
        $resp->ResponseMessage = $resp->ParsedResponse [3];
        $resp->TransactionId = $resp->ParsedResponse [37];
        $resp->IsSuccess = $resp->ResponseCode == AuthorizeNet::$AN_RESPONSE_SUCCESS;

        return $resp;
    }
}
