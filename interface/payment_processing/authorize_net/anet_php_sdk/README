By:
    Authorize.Net
    http://developer.authorize.net
    http://community.developer.authorize.net

License:
    See License.pdf

About:
    A PHP library for working with all Authorize.Net APIs.

Files:
    - AuthorizeNet.php                       -> Includes all classes. Include this file in your project.
    - lib/AuthorizeNetAIM.php                -> AIM API.
    - lib/AuthorizeNetARB.php                -> ARB API.
    - lib/AuthorizeNetCIM.php                -> CIM API.
    - lib/AuthorizeNetSIM.php                -> SIM API.
    - lib/AuthorizeNetTD.php                 -> Reporting API.
    - lib/AuthorizeNetCP.php                 -> Card Present API.
    - lib/AuthorizeNetDPM.php                -> Direct Post Method Helpers.
    - lib/AuthorizeNetSOAP.php               -> Class to assist with using the SOAP API.
    - lib/shared/AuthorizeNetRequest.php     -> Class to connect to AuthorizeNet.
    - lib/shared/AuthorizeNetTypes.php       -> Classes for AuthorizeNet Datatypes.
    - lib/shared/AuthorizeNetResponse.php    -> Class to parse AuthorizeNet NVP Responses.
    - lib/shared/AuthorizeNetXMLResponse.php -> Class to parse AuthorizeNet XML Responses.
    - lib/ssl/cert.pem                       -> The AuthorizeNet Certificate bundle.
    - tests/                                 -> Tests & examples for each of the API methods.
    - README                                 -> This file.
    - README.html                            -> HTML version of this file.

Requirements:
    - cURL PHP Extension
    - PHP 5.2+
    - An Authorize.Net Merchant Account or Test Account. You can get a 
      free test account at http://developer.authorize.net/testaccount/
    
Install:
    - Include the 'AuthorizeNet.php' file in your application.
    - Use your desired API.
    
Usage Examples:
    See below for basic usage examples. View the tests/ folder for more examples of each API.
      
AuthorizeNetAIM.php Quick Usage Example:
    <?php
    require_once 'anet_php_sdk/AuthorizeNet.php'; 
    define("AUTHORIZENET_API_LOGIN_ID", "YOURLOGIN");
    define("AUTHORIZENET_TRANSACTION_KEY", "YOURKEY");
    define("AUTHORIZENET_SANDBOX", true);
    $sale = new AuthorizeNetAIM;
    $sale->amount = "5.99";
    $sale->card_num = '6011000000000012';
    $sale->exp_date = '04/15';
    $response = $sale->authorizeAndCapture();
    if ($response->approved) {
        $transaction_id = $response->transaction_id;
    }
    ?>
    
AuthorizeNetAIM.php Advanced Usage Example:
    <?php
    require_once 'anet_php_sdk/AuthorizeNet.php'; 
    define("AUTHORIZENET_API_LOGIN_ID", "YOURLOGIN");
    define("AUTHORIZENET_TRANSACTION_KEY", "YOURKEY");
    define("AUTHORIZENET_SANDBOX", true);
    $auth = new AuthorizeNetAIM;
    $auth->amount = "45.00";

    // Use eCheck:
    $auth->setECheck(
        '121042882',
        '123456789123',
        'CHECKING',
        'Bank of Earth',
        'Jane Doe',
        'WEB'
    );
    
    // Set multiple line items:
    $auth->addLineItem('item1', 'Golf tees', 'Blue tees', '2', '5.00', 'N');
    $auth->addLineItem('item2', 'Golf shirt', 'XL', '1', '40.00', 'N');
    
    // Set Invoice Number:
    $auth->invoice_num = time();
    
    // Set a Merchant Defined Field:
    $auth->setCustomField("entrance_source", "Search Engine");
    
    // Authorize Only:
    $response  = $auth->authorizeOnly();

    if ($response->approved) {
        $auth_code = $response->transaction_id;
        
        // Now capture:
        $capture = new AuthorizeNetAIM;
        $capture_response = $capture->priorAuthCapture($auth_code);
        
        // Now void:
        $void = new AuthorizeNetAIM;
        $void_response = $void->void($capture_response->transaction_id);
    }
    ?>

AuthorizeNetARB.php Usage Example:
    <?php
    require_once 'anet_php_sdk/AuthorizeNet.php';
    define("AUTHORIZENET_API_LOGIN_ID", "YOURLOGIN");
    define("AUTHORIZENET_TRANSACTION_KEY", "YOURKEY");
    $subscription                          = new AuthorizeNet_Subscription;
    $subscription->name                    = "PHP Monthly Magazine";
    $subscription->intervalLength          = "1";
    $subscription->intervalUnit            = "months";
    $subscription->startDate               = "2011-03-12";
    $subscription->totalOccurrences        = "12";
    $subscription->amount                  = "12.99");
    $subscription->creditCardCardNumber    = "6011000000000012";
    $subscription->creditCardExpirationDate= "2018-10";
    $subscription->creditCardCardCode      = "123";
    $subscription->billToFirstName         = "Rasmus";
    $subscription->billToLastName          = "Doe";

    // Create the subscription.
    $request = new AuthorizeNetARB;
    $response = $request->createSubscription($subscription);
    $subscription_id = $response->getSubscriptionId();
    ?>

AuthorizeNetCIM.php Usage Example:
    <?php
    require_once 'anet_php_sdk/AuthorizeNet.php';
    define("AUTHORIZENET_API_LOGIN_ID", "YOURLOGIN");
    define("AUTHORIZENET_TRANSACTION_KEY", "YOURKEY");
    $request = new AuthorizeNetCIM;
    // Create new customer profile
    $customerProfile                    = new AuthorizeNetCustomer;
    $customerProfile->description       = "Description of customer";
    $customerProfile->merchantCustomerId= time();
    $customerProfile->email             = "test@domain.com";
    $response = $request->createCustomerProfile($customerProfile);
    if ($response->isOk()) {
        $customerProfileId = $response->getCustomerProfileId();
    }
    ?>

AuthorizeNetSIM.php Usage Example:
    <?php
    require_once 'anet_php_sdk/AuthorizeNet.php';
    define("AUTHORIZENET_API_LOGIN_ID", "YOURLOGIN");
    define("AUTHORIZENET_MD5_SETTING", "");
    $message = new AuthorizeNetSIM;
    if ($message->isAuthorizeNet()) {
        $transactionId = $message->transaction_id;
    }
    ?>
    
AuthorizeNetDPM.php Usage Example:
    <?php // Filename: direct_post.php
    require_once 'anet_php_sdk/AuthorizeNet.php'; // The SDK
    $url = "http://YOUR_DOMAIN.com/direct_post.php";
    $api_login_id = 'YOUR_API_LOGIN_ID';
    $transaction_key = 'YOUR_TRANSACTION_KEY';
    $md5_setting = 'YOUR_MD5_SETTING'; // Your MD5 Setting
    $amount = "5.99";
    AuthorizeNetDPM::directPostDemo($url, $api_login_id, $transaction_key, $amount, $md5_setting);
    ?>

AuthorizeNetCP.php Usage Example:
    <?php
    require_once 'anet_php_sdk/AuthorizeNet.php';
    define("AUTHORIZENET_API_LOGIN_ID", "YOURLOGIN");
    define("AUTHORIZENET_TRANSACTION_KEY", "YOURKEY");
    define("AUTHORIZENET_MD5_SETTING", "");
    $sale = new AuthorizeNetCP;
    $sale->amount = '59.99';
    $sale->device_type = '4';
    $sale->setTrack1Data('%B4111111111111111^CARDUSER/JOHN^1803101000000000020000831000000?');
    $response = $sale->authorizeAndCapture();
    $trans_id = $response->transaction_id;
    ?>

AuthorizeNetTD.php Usage Example:
    <?php
    require_once 'anet_php_sdk/AuthorizeNet.php';
    define("AUTHORIZENET_API_LOGIN_ID", "YOURLOGIN");
    define("AUTHORIZENET_TRANSACTION_KEY", "YOURKEY");
    $request = new AuthorizeNetTD;
    $response = $request->getTransactionDetails("12345");
    echo $response->xml->transaction->transactionStatus;
    ?>
    
Test Credit Card Numbers:
    - Set the expiration date to anytime in the future.
    - American Express Test Card=> 370000000000002
    - Discover Test Card        => 6011000000000012
    - Visa Test Card            => 4007000000027
    - Second Visa Test Card     => 4012888818888
    - JCB                       => 3088000000000017
    - Diners Club/ Carte Blanche=> 38000000000006

PHPDoc:
  To autogenerate PHPDocs run:
  phpdoc -t phpdocs/ -f AuthorizeNet.php -d lib

Release Notes
    Version 1.1.8
    - Fixed an issue with validationMode in CIM::updateCustomerPaymentProfile.
      Note: The behavior where validationMode persisted across transactions using the same request object
      has been removed. This was unsupported behavior.
    - Removed an unused validationMode argument in CIM::updateCustomerProfile. The parameter used to be ignored, now its removed.
    - Enhanced the tests with checks for single digit months in expiration date support and SSL certificate validity.
    Version 1.1.7
    - Added getBatchStatisticsRequest and getUnsettledTransactionListRequest support to the SDK.
    Version 1.1.6
    - Added the HTML version of the README to the distributed bundle. It was missing in version 1.1.5.
    Version 1.1.5
    - Added HTML version of README.
    Version 1.1.4
    - Updated the cert.pem bundle to include the new secure.authorize.net SSL certificate.
    Version 1.1.3
    - Added more documentation
    - Improved support for all Transaction Details API methods.
    - Added support for the Card Present API.
    - Added easier xpath support to XML Response class.
    - Added ability to use DPM sample app with production account.
    Version 1.1.2
    - Added getValidationResponses method to CIM Response for parsing the validation results when validating payment profiles.
    - Added support for UpdateSplitTenderGroup method to CIM request.
    - Bug fix. In CIM requests using validation mode the the validation mode element should have been added to the end of the request.
    - Bug fix. In AIM Response class where $response->account_number was returning the wrong value.