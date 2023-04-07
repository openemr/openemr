# module-faxsms
Fax and SMS feature for OpenEMR that currently supports Twilio SMS and etherFAX for faxing.
## Install Module

- To turn on and setup vendor accounts goto the top menu Modules->Manage Modules then click Unregistered button to display all currently unregistered modules. 
- Find the FaxSMS Module item and click the Register button.
- From the Registered tab find and click the Install button then Enable the module.
- Once installed, click the far right config icon to display the config panel.
- Select the appropriate vendors for SMS and/or Fax. Tick the Individual User Accounts if you want to have the vendor account(s) associated with the user that is setting up the account.
- Once a vendor is selected an account credential setup button will display.
- Click the Setup SMS/Fax Account button and input the appropriate account information received when setting up the vendor accounts.
- Log out and log back in to OpenEMR.

- You will find the module in the menu under Modules.
### Tips
- You are required to create and use Twilio Api Sid and API Secret.
- etherFax allows using either account user and password or the preferred API key.


 This module uses an abstract class to arbitrate and dispatch
 API calls to different vendor services for both the fax and sms type on a per-call basis.
 To add new vendors, just follow and use the existing dispatching flow
 for an existing service type and vendor service.


### License
This module uses the GPL-3 license.
