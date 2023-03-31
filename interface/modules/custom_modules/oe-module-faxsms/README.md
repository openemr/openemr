# module-faxsms
Fax and SMS feature for OpenEMR
- To turn on and setup accounts goto the top menu Manage Modules and install then enable the oe-module-faxsms module.
- Once installed, click the config icon for the config panel then turn on needed vendors and add the appropriate account credentials.


 This module uses an abstract class to arbitrate and dispatch
 API calls to different vendor services for both the fax and sms type on a per-call basis.
 To add new vendors, just follow and use the existing dispatching flow
 for an existing service type and vendor service.


The module uses the GPL-3 license.
