<?php
/**
 * Easily use the Authorize.Net Server Integration Method(SIM).
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetSIM
 * @link       http://www.authorize.net/support/SIM_guide.pdf SIM Guide
 */

/**
 * Easily parse an AuthorizeNet SIM Response.
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetSIM
 */
class AuthorizeNetSIM extends AuthorizeNetResponse
{

    // For ARB transactions
    public $subscription_id;
    public $subscription_paynum;

    /**
     * Constructor.
     *
     * @param string $api_login_id
     * @param string $md5_setting For verifying an Authorize.Net message.
     */
    public function __construct($api_login_id = false, $md5_setting = false)
    {
        $this->api_login_id = ($api_login_id ? $api_login_id : (defined('AUTHORIZENET_API_LOGIN_ID') ? AUTHORIZENET_API_LOGIN_ID : ""));
        $this->md5_setting = ($md5_setting ? $md5_setting : (defined('AUTHORIZENET_MD5_SETTING') ? AUTHORIZENET_MD5_SETTING : ""));
        $this->response = $_POST;
        
        // Set fields without x_ prefix
        foreach ($_POST as $key => $value) {
            $name = substr($key, 2);
            $this->$name = $value;
        }
        
        // Set some human readable fields
        $map = array(
            'avs_response' => 'x_avs_code',
            'authorization_code' => 'x_auth_code',
            'transaction_id' => 'x_trans_id',
            'customer_id' => 'x_cust_id',
            'md5_hash' => 'x_MD5_Hash',
            'card_code_response' => 'x_cvv2_resp_code',
            'cavv_response' => 'x_cavv_response',
        );
        foreach ($map as $key => $value) {
            $this->$key = (isset($_POST[$value]) ? $_POST[$value] : "");
        }
        
        $this->approved = ($this->response_code == self::APPROVED);
        $this->declined = ($this->response_code == self::DECLINED);
        $this->error    = ($this->response_code == self::ERROR);
        $this->held     = ($this->response_code == self::HELD);
    }
    
    /**
     * Verify the request is AuthorizeNet.
     *
     * @return bool
     */
    public function isAuthorizeNet()
    {
        return count($_POST) && $this->md5_hash && ($this->generateHash() == $this->md5_hash);
    }
    
    /**
     * Generates an Md5 hash to compare against Authorize.Net's.
     *
     * @return string Hash
     */
    public function generateHash()
    {
        $amount = ($this->amount ? $this->amount : "0.00");
        return strtoupper(md5($this->md5_setting . $this->api_login_id . $this->transaction_id . $amount));
    }

}

/**
 * A helper class for using hosted order page.
 *
 * @package    AuthorizeNet
 * @subpackage AuthorizeNetSIM
 */
class AuthorizeNetSIM_Form
{
    public $x_address;
    public $x_amount;
    public $x_background_url;
    public $x_card_num;
    public $x_city;
    public $x_color_background;
    public $x_color_link;
    public $x_color_text;
    public $x_company;
    public $x_country;
    public $x_cust_id;
    public $x_customer_ip;
    public $x_description;
    public $x_delim_data;
    public $x_duplicate_window;
    public $x_duty;
    public $x_email;
    public $x_email_customer;
    public $x_fax;
    public $x_first_name;
    public $x_footer_email_receipt;
    public $x_footer_html_payment_form;
    public $x_footer_html_receipt;
    public $x_fp_hash;
    public $x_fp_sequence;
    public $x_fp_timestamp;
    public $x_freight;
    public $x_header_email_receipt;
    public $x_header_html_payment_form;
    public $x_header_html_receipt;
    public $x_invoice_num;
    public $x_last_name;
    public $x_line_item;
    public $x_login;
    public $x_logo_url;
    public $x_method;
    public $x_phone;
    public $x_po_num;
    public $x_receipt_link_method;
    public $x_receipt_link_text;
    public $x_receipt_link_url;
    public $x_recurring_billing;
    public $x_relay_response;
    public $x_relay_url;
    public $x_rename;
    public $x_ship_to_address;
    public $x_ship_to_company;
    public $x_ship_to_country;
    public $x_ship_to_city;
    public $x_ship_to_first_name;
    public $x_ship_to_last_name;
    public $x_ship_to_state;
    public $x_ship_to_zip;
    public $x_show_form;
    public $x_state;
    public $x_tax;
    public $x_tax_exempt;
    public $x_test_request;
    public $x_trans_id;
    public $x_type;
    public $x_version;
    public $x_zip;
    
    /**
     * Constructor
     *
     * @param array $fields Fields to set.
     */
    public function __construct($fields = false)
    {
        // Set some best practice fields
        $this->x_relay_response = "FALSE";
        $this->x_version = "3.1";
        $this->x_delim_char = ",";
        $this->x_delim_data = "TRUE";
        
        if ($fields) {
            foreach ($fields as $key => $value) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Get a string of HTML hidden fields for use in a form.
     *
     * @return string
     */
    public function getHiddenFieldString()
    {
        $array = (array)$this;
        $string = "";
        foreach ($array as $key => $value) {
            if ($value) {
                $string .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
            }
        }
        return $string;
    }
    
    /**
     * Generates a fingerprint needed for a hosted order form or DPM.
     *
     * @param string $api_login_id    Login ID.
     * @param string $transaction_key API key.
     * @param string $amount          Amount of transaction.
     * @param string $fp_sequence     An invoice number or random number.
     * @param string $fp_timestamp    Timestamp.
     *
     * @return string The fingerprint.
     */
    public static function getFingerprint($api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp)
    {
        $api_login_id = ($api_login_id ? $api_login_id : (defined('AUTHORIZENET_API_LOGIN_ID') ? AUTHORIZENET_API_LOGIN_ID : ""));
        $transaction_key = ($transaction_key ? $transaction_key : (defined('AUTHORIZENET_TRANSACTION_KEY') ? AUTHORIZENET_TRANSACTION_KEY : ""));
        if (function_exists('hash_hmac')) {
            return hash_hmac("md5", $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^", $transaction_key); 
        }
        return bin2hex(mhash(MHASH_MD5, $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^", $transaction_key));
    }
    
}