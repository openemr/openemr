# ClaimRev Connect Module for OpenEMR
This module creates the connetion the the Claim Revolution, LLC clearinghouse. Using rest API's the module will send your claim file in for processing and download the 999/277 files to the appropriate location on the OpenEMR system. 
The module also handles the real-time eligibility of your patients. There are multiple ways this can be accomplished.
<ul>
<li> Manual – In the patient card a new card is created that lets the user manually check for eligibility from Claim Revolution.  </li>
<li> Automatic – When the patient has a visit scheduled the system will go out and pull eligibility and will be available on the patient card. </li>
</ul>

Under the menu option for the module the user can do a simple search for claims submitted to the clearinghouse and view the statuses attached to those claims.
## Getting Started
Please to go to www.claimrev.com to get the required information to setup the module. A ClientID and Client Secret are required to get the module to do something. Once those are setup, you should see your account number listed in debug tab on the module's system menu. 

The module will create a new table to store the eligibility request and response JSON. Entries are created in the background_services table to enable:</br>

<ul>
<li>Sending the claim files</li>
<li>Getting reports</li>
<li>Send and Receive eligibility </li>
</ul>	

## Contributing
If you would like to help with improving this module post an issue on Github or send a pull request.

 
