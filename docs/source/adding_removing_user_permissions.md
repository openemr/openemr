# Editing User Permissions

The Administrator of an OpenEMR instance adds new users to a set of
groups when they add a user to the system.

To edit a user's permissions, log in to openemr as a user with 'admin' rights, and open the `administration --> ACLs` link in the left navigation bar.

This will pull up the `Access Control List Administration` page. By default, the checkbox next to `User Memberships` should be selected, and you should see a list of all your users, with a blue 'Edit' link next to them.

To add/remove a permission for a given user, hit 'edit' next to their name. this opens a window with two sides, and two buttons with arrows, for moving items from one side to the other.

To add a permission, select it on the right hand `Inactive` pane, and hit the `<<` button at the bottom of the window. The item will now be on the side labeled `Active`.

To remove a permission, select it on the left hand `Active` pane, and
hit the `>>` button at the bottom of the window. The item will now be
on the side labeled `Inactive`.

## List of Permissions

The list and descriptions of [Permissions can
be found here](access_control.html).
