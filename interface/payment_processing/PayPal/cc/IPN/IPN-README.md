IPN Overview :
------------

* PayPal Instant Payment Notification is call back system that initiated once a tranction is completed  
  (eg: When a Pay API completed successfully).
* You will receive the transaction related IPN variables on your call back url that you have specified in your request.
* You have to send this IPN variable back to PayPal system for verification, Upon verification PayPal will send  
  a response string "VERIFIED" or "INVALID".
* PayPal will continuously resend this IPN, if a wrong IPN is sent.

IPN How to use
--------------
* Include 'ipn/PPIPNMessage.php' in your IPN callback URL  
* Initialize IPNMessage constructor with a map containing configuration parameters, as shown below.

		// Array containing configuration parameters. (not required if config file is used)
		$config = array(
		    // values: 'sandbox' for testing
			//		   'live' for production
			"mode" => "sandbox"
			
			// These values are defaulted in SDK. If you want to override default values, uncomment it and add your value.
			// "http.ConnectionTimeOut" => "5000",
			// "http.Retry" => "2",
			);
		$ipnMessage = new PPIPNMessage(null, $config);   
* 'validate()' method validates the IPN message and returns true if 'VERIFIED' or returns false if 'INVALID'  
Ex:
		$result = $ipnMessage->validate();
		  
  Initiating IPN:
* Make a PayPal API call (eg: Pay ), setting the IpnNotificationUrl field of API request   
  to the url of deployed IPNLIstener sample(eg:https://example.com/adaptivepayments-sdk-php/IPN/IPNListener.php)  
  the IpnNotificationUrl field is in 'PayRequestDetailsType' class under API request class  
 (Ex: 'PayRequestDetailsType->IpnNotificationUrl')  
* You will receive IPN call back from PayPal , which will be logged in to log file in case of IPN sample.
* See the included sample for more details.
* To access the IPN received use 'getRawData()' which give an array of received IPN variables  
Ex:
		
		$ipnMessage->getRawData(); 
	       
IPN variables :
--------------
[Transaction]
-------------
* transaction_type
* action_type
* transaction[n].amount
* transaction[n].id
* transaction[n].id_for_sender
* transaction[n].invoiceId
* transaction[n].is_primary_receiver
* transaction[n].receiver
* transaction[n].refund_account_charged
* transaction[n].refund_amount
* transaction[n].refund_id
* transaction[n].status
* transaction[n].status_for _sender_txn,
* transaction[n].id_for_sender_txn 
* transaction[n].pending_reason 
* ipn_notification_url
* verify_sign
* notify_version          
* test_ipn                
* reverse_all_parallel_payments_on_error 
* log_default_shipping_address_in_transaction

[BuyerInfo]
-----------
* sender_email
* fees_payer
* pin_type
    
[DisputeResolution]
-------------------
* reason_code

[RecurringPayment]
------------------
* current_number_of_payments
* current_period_attempts
* current_total_amount_of_all_payments
* date_of_month
* day_of_week
* ending_date
* max_amount_per_payment
* max_number_of_payments
* max_total_amount_of_all_payments
* payment_period
* starting_date
* payment_period
    

[Paymentinfo]
-------------
* pay_key
* payment_request_date
* preapproval_key
* memo
* payment_request_date    
* preapproval_key
* currencyCode
* status
* return_url              
* cancel_url
* approved
* charset
* trackingId
	 
*   For a full list of IPN variables you need to check log file, that IPN Listener is logging into.    

IPN Reference :
--------------
*   You can refer IPN getting started guide at [https://www.x.com/developers/paypal/documentation-tools/ipn/gs_IPN]
