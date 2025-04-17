             Hints for Using phpGACL with OpenEMR
          by Rod Roark <rod at sunsetsystems dot com>

Installation Instructions

phpGACL access controls are embedded and installed by default in OpenEMR
versions 2.9.0.3 or later.  The administration of the access controls is
within OpenEMR in the Administration->ACL menu.


Upgrading Instructions

After you have upgraded to a new version of OpenEMR, you should
run the acl_upgrade.php program using your web browser
(e.g. http://openemr.location/acl_upgrade.php). This will ensure your
phpGACL database contains all the required OpenEMR Access Control
Objects.


For Developers

If you add a new Access Control Object to the OpenEMR codebase, then
also add it to the following three sites:
1. Header notes of the src/Common/Acl/AclMain.php file
2. library/classes/Installer.class.php script in the install_gacl() function
3. acl_upgrade.php file


Advanced Information

Note that OpenEMR does not use AXOs, so ignore the AXO Group Admin
tab and other references to AXOs.

acl_setup.php creates required Access Control Objects (ACOs, the
things to be protected), several sample Access Request Object (AROs
are the users requesting access), and their corresponding sections.
You may also create such objects yourself using the "ACL Admin"
tab of the phpGACL GUI.

The Access Control Objects (ACOs) for OpenEMR have a very specific
structure.  This is described in the file src/Common/Acl/AclMain.php.

You must manually create an ARO in this "users" section for each
OpenEMR user (except for the "admin" user which the setup program
creates).  The Value column must be the user's OpenEMR login name,
and the Name column can (should) be their full name.

By the way, values in the "Order" columns do not seem to be important.
I just plug in "10" for everything.  Names are cosmetic but should be
meaningful to you.  What really matters is the Value column.

Then you should define or modify groups and assign users (AROs) to
them using the "ARO Group Admin" tab of the GUI.  These can be
structured any way you like.  Here is one example of a group
hierarchy for a clinic setting:

  Users
    Accounting
    Administrators
    Assistants
      Front Office
      Lab
      Medical
    Nurses
    Physicians

To see your access rules, click the "ACL List" tab.  To make corrections
click the corresponding Edit link on the right side; this will open the
ACL Admin tab with the corresponding selections already made, which you
can then change as desired and resubmit.  Note that the ACL List page
also provides for deleting entries.

The ACL Admin tab is also used to assign permissions.  This is
a really confusing "write only" interface, but thankfully you won't
have to use it every day!  Mostly what you will do here is highlight
a group from the box on the right, and also select some ACOs from the
top section by highlighting them and clicking the ">>" button.
Then if "write" or "wsome" or "addonly" access applies, key in that
as the return value, otherwise a return value is not required.  Then
click the Submit button to save that particular access rule.  Repeat
until all your ACL rules are defined.
