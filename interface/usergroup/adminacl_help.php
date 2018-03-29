<?php
/**
 * Access Control List Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ranganath Pathak <pathak01@hotmail.com>
 * @copyright Copyright (c) 2017 Ranganath Pathak <pathak01@hotmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");

use OpenEMR\Core\Header;

?>
<!DOCTYPE HTML>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Access Control List Help");?></title>
    <style>
        .oe-help-heading{
            color:#676666;
            background-color: #E4E2E0;
            border-color: #DADADA;
            padding: 10px 5px;
            border-radius: 5px;
        }
        .oe-help-redirect{
            color:#676666;
        }
        a {
            text-decoration: none !important;
            color:#676666 !important;
            font-weight:700;
        }
        h2 > a {
            font-weight:500;
        }
        @media only screen and (max-width: 768px) {
           [class*="col-"] {
           width: 100%;
           text-align:left!Important;
            }
        }
        @media only screen and (max-width: 1004px) and (min-width: 641px)  {
            .oe-large {
                display: none;
            }
            .oe-small {
                display: inline-block;
            }
        }
    </style>
    </head>
    <body>
        <div class="container">
            <div>
                <center><h2><a name = 'entire_doc'><?php echo xlt("Access Control Lists");?></a></h2></center>
            </div>
            <div class= "row">
                <p><?php echo xlt("A large application like openEMR is used by a multitude of users with varying roles and degrees of responsibility. It is imperative that access to various parts of the program be granted to users on a need to know basis. To achieve this - Access Control Lists (ACL) are used.");?>

                <p><?php echo xlt("These lists are used to determine who can access what in openEMR. They work in a top down manner, i.e. initially everybody is denied access to those parts of the program controlled by the ACL.");?>

                <p><?php echo xlt("Access is then granted selectively to portions of the program on a need to know basis.");?>

                <p><?php echo xlt("The parts of the program to which access can be controlled are called Access Control Objects (ACOs). ");?>

                <p><?php echo xlt("These ACOs are grouped into ten broad categories that are part of the default installation. They are - Administration, Accounting, Patient Information, Encounter Information, Squads, Sensitivities, Lists, Placeholder, Nation Notes and Patient Portal. Each of these categories has one or several sub-categories that provide access to specific parts of the program."); ?>


                <p><?php echo xlt("These sub-categories represent the actual Access Control Objects (ACOs)."); ?>

                <p><?php echo xlt("The entire collection of ACOs forms the Access Control List (ACL)."); ?>

                <p><?php echo xlt("Rather than granting access to each ACO individually for each user the program grants access to groups that request these privileges. These groups are called Access Request Objects (ARO)."); ?>

                <p><?php echo xlt("The default installation has six such groups - Accounting, Administrators, Clinicians, Emergency Login, Front Office and Physicians."); ?>

                <p><?php echo xlt("Each of these groups (AROs) has access to pre-determined parts of the program (ACOs)."); ?>

                <p><?php echo xlt("Individual access can be tailored to fit the needs by assigning a user to one or more groups (AROs). The user will then inherit all the privileges, i.e have access to parts of the program (ACO), of each group (ARO) the user belongs to."); ?>

                <p><?php echo xlt("When a new user is created, access control is granted by the administrator or by a user with similar privileges by selecting which groups (AROs) a user can belong to."); ?>

                <p><?php echo xlt("This is done in Administration > Users."); ?>

                <p><?php echo xlt("If privileges have to be modified then it can be done either one user at a time at Administration > Users or more conveniently on this page i.e. Administration > ACL where all users are listed on one page and more options are available."); ?>

                <p><?php echo xlt("To see to all the ACOs that are available click on the eye icon."); ?>&nbsp <i id="show_hide" class="fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i>

                <div id="aco_list" class='hideaway' style='display: none;'>
                    <ul>
                        <li><strong><?php echo xlt('Administration (admin)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Superuser - can delete patients, encounters, issues (super)');?></li>
                                <li><?php echo xlt('Calendar Settings (calendar)');?></li>
                                <li><?php echo xlt('Database Reporting (database)');?></li>
                                <li><?php echo xlt('Forms Administration (forms)');?></li>
                                <li><?php echo xlt('Practice Settings (practice)');?></li>
                                <li><?php echo xlt('Superbill Codes Administration (superbill)');?></li>
                                <li><?php echo xlt('Users/Groups/Logs Administration (users)');?></li>
                                <li><?php echo xlt('Batch Communication Tool (batchcom)');?></li>
                                <li><?php echo xlt('Language Interface Tool (language)');?></li>
                                <li><?php echo xlt('Pharmacy Dispensary (drugs)');?></li>
                                <li><?php echo xlt('ACL Administration (acl)');?></li>
                            </ul>
                        <li><strong><?php echo xlt('Accounting (acct)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Billing (write optional) (bill)');?></li>
                                <li><?php echo xlt('Allowed to discount prices (in Fee Sheet or Checkout form) (disc)');?></li>
                                <li><?php echo xlt('EOB Data Entry (eob)');?></li>
                                <li><?php echo xlt('Financial Reporting - my encounters (rep)');?></li>
                                <li><?php echo xlt('Financial Reporting - anything (rep_a)');?></li>
                            </ul>
                        <li><strong><?php echo xlt('Patient Information (patients)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Appointments (write,wsome optional) (appt)');?></li>
                                <li><?php echo xlt('Demographics (write,addonly optional) (demo)');?></li>
                                <li><?php echo xlt('Medical Records and History (write,addonly optional) (med)');?></li>
                                <li><?php echo xlt('Transactions, e.g. referrals (write optional) (trans)');?></li>
                                <li><?php echo xlt('Documents (write,addonly optional) (docs)');?></li>
                                <li><?php echo xlt('Patient Notes (write,addonly optional) (notes)');?></li>
                                <li><?php echo xlt('Sign Lab Results (write,addonly optional) (sign)');?></li>
                            </ul>
                        <li><strong><?php echo xlt('Encounter Information (encounters)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Authorize - my encounters (auth)');?></li>
                                <li><?php echo xlt('Authorize - any encounters (auth_a)');?></li>
                                <li><?php echo xlt('Coding - my encounters (write,wsome optional) (coding)');?></li>
                                <li><?php echo xlt('Coding - any encounters (write,wsome optional) (coding_a)');?></li>
                                <li><?php echo xlt('Notes - my encounters (write,addonly optional) (notes)');?></li>
                                <li><?php echo xlt('Notes - any encounters (write,addonly optional) (notes_a)');?></li>
                                <li><?php echo xlt('Fix encounter dates - any encounters (date_a)');?></li>
                                <li><?php echo xlt('Less-private information (write,addonly optional) (relaxed)');?></li>
                            </ul>
                        <li><strong><?php echo xlt('Squads (squads)');?></strong></li>
                        <li><strong><?php echo xlt('Sensitivities (sensitivities)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Normal (normal)');?></li>
                                <li><?php echo xlt('High (high)');?></li>
                            </ul>
                        <li><strong><?php echo xlt('Lists (lists)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Default List (write,addonly optional) (default)');?></li>
                                <li><?php echo xlt('State List (write,addonly optional) (state)');?></li>
                                <li><?php echo xlt('Country List (write,addonly optional) (country)');?></li>
                                <li><?php echo xlt('Language List (write,addonly optional) (language)');?></li>
                                <li><?php echo xlt('Ethnicity-Race List (write,addonly optional) (ethrace)');?></li>
                            </ul>
                        <li><strong><?php echo xlt('Placeholder (placeholder)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Placeholder (Maintains empty ACLs) (filler)');?></li>
                            </ul>
                        <li><strong><?php echo xlt('Nation Notes (nationnotes)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Nation Notes (nn_configure)');?></li>
                            </ul>
                        <li><strong><?php echo xlt('Patient Portal (patientportal)');?></strong></li>
                            <ul>
                                <li><?php echo xlt('Patient Portal (portal)');?></li>
                            </ul>
                    </ul>
                </div>

                <p><?php echo xlt("The ACL page two sections."); ?>
                <ul id="listed_items">
                    <li><a href="#users_section"><?php echo xlt("User Memberships");?></a></li>
                    <li><a href="#groups_section"><?php echo xlt("Groups and Access Controls");?></a></li>
                </ul>
            </div>
            <div class= "row" id="users_section">
                <h4 class="oe-help-heading"><?php echo xlt("User Memberships"); ?><a href="#listed_items"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("By default the User Memberships section is selected and all the active registered users will be listed in alphabetical order by their user names."); ?>

                <p><?php echo xlt("Clicking on the icon next to their name will bring up the 'Edit' window."); ?>

                <p><?php echo xlt("The 'Edit' window is divided into two columns, 'Active' and 'Inactive'. The groups (AROs) that are listed in the active column are those groups that the user belongs to."); ?>

                <p><?php echo xlt("The user's actual privileges are determined by the access to the parts of the program i.e. (ACO) that each group (ARO) has."); ?>

                <p><?php echo xlt("To move the groups from one column to another select one or more items from the column that you need to move them out of and press the relevant button with the double chevrons."); ?> <input class='button_submit' type='button' value=' >> ' >&nbsp;&nbsp;<input class='button_submit' type='button' value=' << ' >

                <p><?php echo xlt("To select multiple groups hold down the 'Shift' or 'Ctrl' keys while clicking."); ?>

                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("Note there is no 'Save' button."); ?></strong>
            </div>
            <div class= "row" id="groups_section">
                <h4 class="oe-help-heading"><?php echo xlt("Groups and Access Controls"); ?><a href="#listed_items"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Checking the Groups and Access Controls checkbox will reveal this section that lists all the categories with sub-categories (ACOs), i.e. the parts of the program controlled by the access control list privileges."); ?>

                <p><?php echo xlt("It also lets you create new groups (AROs) as well as remove existing ones."); ?>

                <p><?php echo xlt("These groups (AROs) can then be given a set of privileges by assigning different categories (ACOs)."); ?>

                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("This section DOES NOT let you create new ACOs."); ?></strong>

                <p><?php echo xlt("There are three actions that can be performed here - edit an existing group (ARO), add a new group (ARO) or delete an existing group (ARO)."); ?>

                <p><strong><?php echo xlt("EDIT EXISTING GROUP"); ?> :</strong>

                <p><?php echo xlt("To edit an existing group (ARO) click on the icon next to the desired group. This will bring up the edit window."); ?>

                <p><?php echo xlt("The items listed in the 'Active' column delineate the privileges of this group (ARO) and constitutes this group's Access Control List (ACL)."); ?>

                <p><?php echo xlt("Move the individual items from 'Active' to 'Inactive' or vice-versa by selecting the items and pressing the relevant button with the double chevron."); ?>  <input class='button_submit' type='button' value=' >> ' >&nbsp;&nbsp;<input class='button_submit' type='button' value=' << ' >

                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("Note there is no 'Save' button."); ?></strong>

                <p><?php echo xlt("Click the slashed-eye icon to close."); ?>

                <p><strong><?php echo xlt("ADD NEW GROUP"); ?> :</strong>

                <p><?php echo xlt("Click the 'Add New Group' button to display the 'New Group Information' section."); ?>

                <p><?php echo xlt("The Title will be the name of the new group (ARO) that you are going to create."); ?>

                <p><?php echo xlt("Use a unique word to identify this group, it has to be a single word, if using two words link them together with an underscore or hyphen."); ?>

                <p><?php echo xlt("Choose one of the four return values that reflect varying degrees of privilege."); ?>
                    <ul>
                        <li><?php echo xlt("view - can only read but not add or modify"); ?></li>
                        <li><?php echo xlt("addonly - can read and add but not modify"); ?></li>
                        <li><?php echo xlt("wsome - can read and partially modify"); ?></li>
                        <li><?php echo xlt("write - can read and fully modify"); ?></li>
                    </ul>

                <p><?php echo xlt("A short description of this group that will appear when you hover over the newly created group (ARO)."); ?>

                <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong><?php echo xlt("Review all the parameters that are entered and only then move to the next step. This is because once you create a group (ARO) you will NOT be able of modify any of the data that you have entered in THIS section. You can only delete the entire group (ARO) and start afresh."); ?></strong>

                <p><?php echo xlt("Click on the 'Add Group' button to create this new group (ARO)."); ?>

                <p><?php echo xlt("The group (ARO) that you created will now appear in alphabetical order in the 'Groups and Access Controls' section."); ?>

                <p><?php echo xlt("If you click on the edit icon next to this newly created group (ARO) you will note that the 'Active' column contains only a single entry - Placeholder (Maintains empty ACLs). As yet this new group (ARO) has NO access to any part of the program as there are no ACOs assigned in the 'Active' column."); ?>

                <p><?php echo xlt("Add desired privileges by moving items (ACOs) from the 'Inactive' column to the 'Active' column."); ?>

                <p><i class="fa fa-exclamation-circle" style="color:orange" aria-hidden="true"></i> <strong><?php echo xlt("Note there is no 'Save' button."); ?></strong>

                <p><?php echo xlt("You can close the edit box by clicking on the 'slashed eye' icon next to the group's name."); ?>

                <p><?php echo xlt("If you click on any user in the 'User Memberships' section you will now see these newly created group (ARO) in the 'Inactive' column. These can now be assigned in the usual fashion as needed."); ?>

                <p><strong><?php echo xlt("REMOVE GROUP"); ?> :</strong>

                <p><?php echo xlt("Click the 'Remove Group' button to display the 'Remove Group Form'."); ?>

                <p><?php echo xlt("Select the group (ARO) that you wish to remove."); ?>

                <p><?php echo xlt("Click the 'Yes' radio button."); ?>

                <p><?php echo xlt("Click the 'Delete Group' button to completely remove this group."); ?>
            </div>
            <div class= "row" id="advanced_acl">
                <h4 class="oe-help-heading"><?php echo xlt("Advanced - Finer Access Control"); ?><a href="#"><i class="fa fa-arrow-circle-up float-right oe-help-redirect" aria-hidden="true"></i></a></h4>
                <p><?php echo xlt("Click on the icon next to the 'Access Control List Administration' title to go to the phpGACL page."); ?> <i id='advanced' class='fa fa-external-link small' aria-hidden='true'></i>

                <p><?php echo xlt("Here you can customize the ACL further."); ?>

                <p><?php echo xlt("You have to have an understanding how the program is structured and the ability and willingness to modify the underlying code."); ?>

                <p><?php echo xlt("Click on the the following link to learn more about what is involved."); ?> <strong><a href="http://www.open-emr.org/wiki/index.php/ACL_Fine_Granular_Control" target="_blank"><?php echo xlt("ACL Fine Granular Control"); ?></a></strong>

                <p><?php echo xlt("Best of Luck."); ?> :)
            </div>
        </div><!--end of container div-->
        <script>
           $('#show_hide').click(function() {
                var elementTitle = $('#show_hide').prop('title');
                var hideTitle = '<?php echo xla('Click to Hide'); ?>';
                var showTitle = '<?php echo xla('Click to Show'); ?>';
                $('.hideaway').toggle('1000');
                $(this).toggleClass('fa-eye-slash fa-eye');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                }
                $('#show_hide').prop('title', elementTitle);
            });
        </script>
    </body>
</html>
