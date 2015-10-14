ARB API
=======

Basic Overview
--------------

The AuthorizeNetARB class creates a request object for submitting transactions
to the AuthorizeNetARB API.


Creating/Updating Subscriptions
-------------------------------

To create or update a subscription first create a subscription object:

$subscription = new AuthorizeNet_Subscription;
$subscription->name = "Short subscription";
$subscription->intervalLength = "1";
$subscription->intervalUnit = "months";
$subscription->startDate = "2011-03-12";
$subscription->totalOccurrences = "14";
$subscription->amount = rand(1,100);
$subscription->creditCardCardNumber = "6011000000000012";
$subscription->creditCardExpirationDate = "2018-10";
$subscription->creditCardCardCode = "123";
$subscription->billToFirstName = "john";
$subscription->billToLastName = "doe";

Then create an AuthorizeNetARB object and call the appropriate method
passing in your subscription object:

$request = new AuthorizeNetARB;
$response = $request->createSubscription($subscription);

   or for updating a subscription:
   
$response = $request->updateSubscription($subscription_id, $subscription);

Getting Subscription Status
---------------------------

Create a new AuthorizeNetARB object and call the getSubscriptionStatus
method with the subscription_id you want the status of as the parameter:

$status_request = new AuthorizeNetARB;
$status_response = $status_request->getSubscriptionStatus($subscription_id);

Canceling a Subscription
------------------------

$cancellation = new AuthorizeNetARB;
$cancel_response = $cancellation->cancelSubscription($subscription_id);
