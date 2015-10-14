Transaction Details API
=======================

Basic Overview
--------------

The AuthorizeNetTD class creates a request object for submitting requests
to the Authorize.Net Transaction Details API.

The AuthorizeNetTD class returns a response that uses PHP's bundled SimpleXML
class for accessing it's members.

The AuthorizeNetTD response provides two ways to access response elements:

1.) A SimpleXml object:

$response->xml->transaction->payment->creditCard->cardType

2.) Xpath:

$batches = $response->xpath("batchList/batch");

3.) AuthorizeNet Objects (todo)



Get Transaction Details
-----------------------

$request = new AuthorizeNetTD;
$response = $request->getTransactionDetails($transId);
echo "Amount: {$response->xml->transaction->authAmount}";

Get Settled Batch List
----------------------
$request = new AuthorizeNetTD;
$response = $request->getSettledBatchList();
$batches = $response->xpath("batchList/batch");
echo "Batch 1: {$batches[0]->batchId}";

Get Transaction List
--------------------
$request = new AuthorizeNetTD;
$response = $request->getTransactionList($batch_id);
$transactions = $response->xpath("transactions/transaction")

There are two additional helper methods in the PHP SDK which
will make multiple calls to retrieve a day's worth of 
transactions or a month's worth of batches:

getTransactionsForDay($month, $day, $year = false)
getSettledBatchListForMonth($month , $year)

If you don't pass parameters into these methods they will default
to the current day/month.
