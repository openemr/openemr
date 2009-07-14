Submitted by Medical Information Integration, LLC July 2009

Enhanced Search and Facility based Filters

Changes:

- Added enhanced search filter
This enhancement adds two selections to the left_nav.php "Find" tool.
1) Any - entering data in the search text field and clicking 'Any' will match what's has been entered against
any field in the patient data table it not listed as 'unused' in the layout table
2) Filter - entering date in the search text field and clicking 'Filter' brings up a popup window with a list
fields in the patient data table that are not listed as 'unused' in the layout table.  The user can then select
one or more fields that the search should compare to and the result will be filtered down to that.

- Added patient search restriction to support multiple facilities using the same database instance but needing some
light restrictions on visibility.  Mostly this keeps clinic A from having to look at Clinic B's patient when
doing a name lookup, so it's easier to make sure they get the right patient.  
* The current patient_data table does not have an inherent way to assign a patient to a facility, so we used a configurable
user defined field (userlist3 in the example).  Then we created a cross reference using the "Lists" tool that has a map
of the facility id and name.  This allowed us to limit the selections to a subset of what is in the facility table.
We used the facility record ID as the List ID and entered an short title to match the internal clinic name.
* Search restrictions apply only to the default Name search at this time
* Facility is matched between the custom field identified below and the users default facility in the users table.
* Patients records that do not have a clinic ID in the indentified custom field are not restrictied

- TO DO 
* It could be enhanced to be more secure by adding a gacl role for access by user default facility.
* Filter check boxes should be persistent during a user session and ajax might be prettier than the popup window.
* Filter Search could use a "whole words only" option.  Right now a search for 'male' will match both males and females.
* Admin restrictions are based solely on the user name 'admin'.  This should be based on the gacl role of "administrator"
 
Manual steps:
- To activate patient search restriction, you must add the following lines to interface/globals.php:

// If these options are omitted, patient searches will not be restricted.
$GLOBALS['pt_restrict_field'] = "userlist3"; // Custom map to assigned facility ID#
$GLOBALS['pt_restrict_admin'] = true;        // Should the admin user be restricted as well
$GLOBALS['pt_restrict_by_id'] = false;       // Should lookup by ID (pubpid) be restricted 


