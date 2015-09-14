Direct Post Method
==================

Basic Overview
--------------

The Authorize.Net PHP SDK includes a class that demonstrates one way
of implementing the Direct Post Method.

While it is not necessary to use the AuthorizeNetDPM class to implement
DPM, it may serve as a handy reference.

The AuthorizeNetDPM class extends the AuthorizeNetSIM_Form class.
See the SIM.markdown for additional documentation.

Relay Response Snippet
----------------------

The AuthorizeNetDPM class contains a getRelayResponseSnippet($redirect_url)
which generates a snippet of HTML that will redirect a user back to your
site after submitting a checkout form using DPM/SIM.

Use this method(or just grab the html) if you want to create a checkout
experience where the user only interacts with pages on your site.