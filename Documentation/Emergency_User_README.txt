Purpose of Emergency Login:
--------------------------
Break glass refers to a quick means for a person who does not have access privileges to certain information to gain access during emergency. Following factors should be considered for break glass users: 

1. Emergency Accounts should be created in advance.
2. Username should meaningful, such as breakglass01/emergency01 with strong password.
3. Auditing should be enabled to log details of the account usage and details.
4. Disable or delete the emergency account(s) that were used to prevent re–use.

Emergency Login In OpenEMR:
---------------------------

Emergency Login acl can be assigned to the users in the following screens:-

1. Administration->Users
2. Administration->ACL->User Membership
3. Administration->ACL->Advanced->Assign ARO Group

Emergency User should be de-activated initally. Activate the Emergency User only during emergency situations.
Emergency Login username should start with "breakglass" or "emergency".

When Emergency User is activated, a notication mail is sent to the email-id configured in $GLOBALS['Emergency_Login_email_id'] 

Emergency Login Activation mail will be sent only if "$GLOBALS['Emergency_Login_email']" and "$GLOBALS['Emergency_Login_email_id']" is configured in globals.php

