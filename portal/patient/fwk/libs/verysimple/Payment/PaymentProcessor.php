<?php

/** @package    verysimple::Payment */

/**
 * import supporting libraries
 */
include_once("PaymentRequest.php");
include_once("PaymentResponse.php");
include_once("RefundRequest.php");

/**
 * PaymentProcessor is an abstract base class for processing PaymentRequest
 * objects.
 * The purpose of this API is to allow a common PaymentRequest
 * object to be processed by any class that extends PaymentProcessor
 *
 * @package verysimple::Payment
 * @author VerySimple Inc.
 * @copyright 1997-2012 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 3.0
 */
abstract class PaymentProcessor
{
    public $Username;
    public $Password;
    public $Signature;
    protected $_testMode;

    /**
     * Constructor
     *
     * @param bool $testmode
     *          default = false
     */
    final function __construct($testmode = false, $username = "", $password = "", $signature = "")
    {
        $this->Username = $username;
        $this->Password = $password;
        $this->Signature = $signature;
        $this->_testMode = $testmode;
        $this->Init($testmode);
    }

    /**
     * Init is called by the base object on construction
     *
     * @param bool $testmode
     */
    abstract function Init($testmode);

    /**
     * Process a PaymentRequest
     *
     * @param PaymentRequest $req
     *          Request object to be processed
     * @return PaymentResponse
     */
    abstract function Process(PaymentRequest $req);

    /**
     * Refund a Payment
     *
     * @param RefundRequest $req
     *          object to be processed
     * @return PaymentResponse
     */
    abstract function Refund(RefundRequest $req);

    /**
     * Given a 2-digit year, return the full 4-digit year
     *
     * @param numeric $year
     */
    protected function GetFullYear($year)
    {
        if (strlen($year) < 4) {
            // assume the current century (could be problematic around 2098, 2099, etc)
            $century = substr(date("Y"), 0, 2);
            $year = $century . $year;
        }

        return $year;
    }

    /**
     *
     * @param string $url
     *          url to post
     * @param array $data
     *          array of arguments for post request
     * @param bool $verify_cert
     *          whether to verify an SSL cert. default = false
     * @param bool $use_cookies
     *          whether to store a cookie file. default = false
     */
    protected function CurlPost($url, $data, $verify_cert = false, $use_cookies = false)
    {
        // convert the data array into a url querystring
        $post_data = "";
        $delim = "";
        foreach (array_keys($data) as $key) {
            $post_data .= $delim . $key . "=" . $data [$key];
            $delim = "&";
        }

        $agent = "curl_post.1";
        // $header[] = "Accept: text/vnd.wap.wml,*.*";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0); // ########## debug
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        if ($use_cookies) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, "cook");
            curl_setopt($ch, CURLOPT_COOKIEFILE, "cook");
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verify_cert);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 1);

        $tmp = curl_exec($ch);
        $error = curl_error($ch);

        if ($error != "") {
            $tmp .= $error;
        }

        curl_close($ch);

        return $tmp;
    }
}
