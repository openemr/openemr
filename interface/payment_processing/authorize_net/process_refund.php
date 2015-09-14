<?php

require_once 'coffee_store_settings.php';

/**
* Demonstrates how to void a charge using the Authorize.Net SDK.
*/
$transaction = new AuthorizeNetAIM;
$response = $transaction->void($_POST['transaction_id']);

if ($response->approved)
{
  // Transaction approved! Do your logic here.
  header('Location: refund_page.php?transaction_id=' . $response->transaction_id);
}
else
{
  header('Location: error_page.php?response_reason_code='.$response->response_reason_code.'&response_code='.$response->response_code.'&response_reason_text=' .$response->response_reason_text);
}