<?php
/**
* Upgrade script for access controls.
*
* This script will update the phpGACL database, which include
* Access Control Objects(ACO), Groups(ARO), and Access Control
* Lists(ACL) to the most recent version.
* It will display whether each update already exist
* or if it was updated succesfully.
* To avoid reversing customizations, upgrade is done in versions,
* which are recorded in the database. To add another version of
* changes, use the following template:
* <pre>// Upgrade for acl_version <acl_version_here>
* $upgrade_acl = <acl_version_here>;
* if ($acl_version < $upgrade_acl) {
*   echo "<B>UPGRADING ACCESS CONTROLS TO VERSION ".$upgrade_acl.":</B></BR>";
* 
*   //Collect the ACL ID numbers.
*   echo "<B>Checking to ensure all the proper ACL(access control list) are present:</B></BR>";
*
*   //Add new object Sections
*   echo "<BR/><B>Adding new object sections</B><BR/>";
*
*   //Add new Objects
*   echo "<BR/><B>Adding new objects</B><BR/>";
*
*   //Update already existing Objects
*   echo "<BR/><B>Upgrading objects</B><BR/>";
*
*   //Add new ACLs here (will return the ACL ID of newly created or already existant ACL)
*   // (will also place in the appropriate group and CREATE a new group if needed)
*   echo "<BR/><B>Adding ACLs(Access Control Lists) and groups</B><BR/>";
*
*   //Update the ACLs
*   echo "<BR/><B>Updating the ACLs(Access Control Lists)</B><BR/>";
*
*   //DONE with upgrading to this version
*   $acl_version = $upgrade_acl;
* }
* </pre>
*
* Updates included:
*  <pre>---VERSION 1 ACL---
*   2.8.2
*     Section "sensitivities" (Sensitivities):
*       ADD  normal   Normal              (Administrators, Physicians, Clinicians(addonly))
*       ADD  high     High                (Administrators, Physicians)
*     Section "admin"         (Administration):
*       ADD  super    Superuser           (Adminstrators)
*   2.8.4
*     Section "admin"         (Administration):
*       ADD  drugs    Pharmacy Dispensary (Administrators, Physicians, Clinicians(write))
*       ADD  acl      ACL Administration (Administrators)
*     Section "sensitivities" (Sensitivities):
*       EDIT high     High               (ensure the order variable is '20')
*     Section "acct"          (Accounting):
*       ADD  disc     Price Discounting (Administrators, Physicians, Accounting(write))
*   3.0.2
*     ADD Section "lists" (Lists):
*       ADD  default   Default List (write,addonly optional)  (Administrators)
*       ADD  state     State List (write,addonly optional)  (Administrators)
*       ADD  country   Country List (write,addonly optional)  (Administrators)
*       ADD  language  Language List (write,addonly optional)  (Administrators)
*       ADD  ethrace   Ethnicity-Race List (write,addonly optional)  (Administrators)
*     ADD Section "placeholder" (Placeholder):
*       ADD  filler    Placeholder (Maintains empty ACLs)
*     ACL/Group  doc   addonly  "Physicians"   (filler aco)
*     ACL/Group  front addonly  "Front Office" (filler aco)
*     ACL/Group  back  addonly  "Accounting"   (filler aco)
*   3.3.0
*     Section "patients" (Patients):
*       ADD  sign  Sign Lab Results (Physicians)
*     ACL/Group  breakglass  write  "Emergency Login"  (added all aco's to it)
*   4.1.0
*     Section "nationnotes" (Nation Notes)
*       ADD  nn_configure  Nation Notes Configure  (Administrators, Emergency Login)
*     Section "patientportal" (Patient Portal)
*       ADD  portal    Patient Portal     (Administrators, Emergency Login)
*   4.1.1
*     ACL/Group  doc   wsome  "Physicians"   (filler aco)
*     ACL/Group  clin  wsome  "Clinicians"   (filler aco)
*     ACL/Group  front wsome  "Front Office" (filler aco)
*     ACL/Group  back  wsome  "Accounting"   (filler aco)
*     ACL/Group  doc   view   "Physicians"   (filler aco)
*     ACL/Group  clin  view   "Clinicians"   (filler aco)
*     ACL/Group  front view   "Front Office" (filler aco)
*     ACL/Group  back  view   "Accounting"   (filler aco)
* </pre>
*
* Copyright (C) 2012 Brady Miller <brady@sparmy.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Brady Miller <brady@sparmy.com>
* @link      http://www.open-emr.org
*/

$ignoreAuth = true; // no login required

require_once('interface/globals.php');
require_once("$srcdir/acl_upgrade_fx.php");

//Ensure that phpGACL has been installed
include_once('library/acl.inc');
if (isset ($phpgacl_location)) {
	include_once("$phpgacl_location/gacl_api.class.php");
	$gacl = new gacl_api();
}
else {
	die("You must first set up library/acl.inc to use phpGACL!");
}

$acl_version = get_acl_version();
if (empty($acl_version)) {
$acl_version = 0;
}

// Upgrade for acl_version 1
$upgrade_acl = 1;
if ($acl_version < $upgrade_acl) {
  echo "<B>UPGRADING ACCESS CONTROLS TO VERSION ".$upgrade_acl.":</B></BR>";

  //Collect the ACL ID numbers.
  echo "<B>Checking to ensure all the proper ACL(access control list) are present:</B></BR>";
  //Get Administrator ACL ID number
  $admin_write = getAclIdNumber('Administrators', 'write');
  //Get Doctor ACL ID Number
  $doc_write = getAclIdNumber('Physicians', 'write');
  //Get Clinician ACL with write access ID number
  $clin_write = getAclIdNumber('Clinicians', 'write');
  //Get Clinician ACL with addonly access ID number
  $clin_addonly = getAclIdNumber('Clinicians', 'addonly');
  //Get Receptionist ACL ID number
  $front_write = getAclIdNumber('Front Office', 'write');
  //Get Accountant ACL ID number
  $back_write = getAclIdNumber('Accounting', 'write');

  //Add new object Sections
  echo "<BR/><B>Adding new object sections</B><BR/>";
  //Add 'Sensitivities' object section (added in 2.8.2)
  addObjectSectionAcl('sensitivities', 'Sensitivities');
  //Add 'Lists' object section (added in 3.0.2)
  addObjectSectionAcl('lists', 'Lists');
  //Add 'Placeholder' object section (added in 3.0.2)
  addObjectSectionAcl('placeholder', 'Placeholder');
  //Add 'Nation Notes' object section (added in 4.1.0)
  addObjectSectionAcl('nationnotes','Nation Notes');
  //Add 'Patient Portal' object section (added in 4.1.0)
  addObjectSectionAcl('patientportal', 'Patient Portal');
  //Add 'Menus' object section (added in 4.1.0)
  addObjectSectionAcl('menus', 'Menus');

  //Add new Objects
  echo "<BR/><B>Adding new objects</B><BR/>";
  //Add 'Normal' sensitivity object, order variable is default 10 (added in 2.8.2)
  addObjectAcl('sensitivities', 'Sensitivities', 'normal', 'Normal');
  //Add 'High' sensitivity object, order variable is set to 20 (added in 2.8.2)
  addObjectAclWithOrder('sensitivities', 'Sensitivities', 'high', 'High', 20);
  //Add 'Pharmacy Dispensary' object (added in 2.8.4)
  addObjectAcl('admin', 'Administration', 'drugs', 'Pharmacy Dispensary');
  //Add 'ACL Administration' object (added in 2.8.4)
  addObjectAcl('admin', 'Administration', 'acl', 'ACL Administration');
  //Add 'Price Discounting' object (added in 2.8.4)
  addObjectAcl('acct', 'Accounting', 'disc', 'Price Discounting');
  //Add 'Default List (write,addonly optional)' object (added in 3.0.2)
  addObjectAcl('lists', 'Lists', 'default', 'Default List (write,addonly optional)');
  //Add 'State List (write,addonly optional)' object (added in 3.0.2)
  addObjectAcl('lists', 'Lists', 'state', 'State List (write,addonly optional)');
  //Add 'Country List (write,addonly optional)' object (added in 3.0.2)
  addObjectAcl('lists', 'Lists', 'country', 'Country List (write,addonly optional)');
  //Add 'Language List (write,addonly optional)' object (added in 3.0.2)
  addObjectAcl('lists', 'Lists', 'language', 'Language List (write,addonly optional)');
  //Add 'Ethnicity-Race List (write,addonly optional)' object (added in 3.0.2)
  addObjectAcl('lists', 'Lists', 'ethrace', 'Ethnicity-Race List (write,addonly optional)');
  //Add 'Placeholder (Maintains empty ACLs)' object (added in 3.0.2)
  addObjectAcl('placeholder', 'Placeholder', 'filler', 'Placeholder (Maintains empty ACLs)');
  //Add 'Sign Lab Results (write,addonly optional)' object (added in 3.3.0)
  addObjectAcl('patients', 'Patients', 'sign', 'Sign Lab Results (write,addonly optional)');
  //Add 'nationnotes' object (added in 4.1.0)
  addObjectAcl('nationnotes', 'Nation Notes', 'nn_configure', 'Nation Notes Configure');
  //Add 'patientportal' object (added in 4.1.0)
  addObjectAcl('patientportal', 'Patient Portal', 'portal', 'Patient Portal');
  //Add 'modules' object (added in 4.1.0)
  addObjectAcl('menus', 'Menus', 'modle', 'Modules');

  //Update already existing Objects
  echo "<BR/><B>Upgrading objects</B><BR/>";
  //Ensure that 'High' sensitivity object order variable is set to 20
  editObjectAcl('sensitivities', 'Sensitivities', 'high', 'High', 20);

  //Add new ACLs here (will return the ACL ID of newly created or already existant ACL)
  // (will also place in the appropriate group and CREATE a new group if needed)
  echo "<BR/><B>Adding ACLs(Access Control Lists) and groups</B><BR/>";
  //Add 'Physicians' ACL with 'addonly' and collect the ID number (added in 3.0.2)
  $doc_addonly = addNewACL('Physicians', 'doc', 'addonly', 'Things that physicians can read and enter but not modify');
  //Add 'Front Office' ACL with 'addonly' and collect the ID number (added in 3.0.2)
  $front_addonly = addNewACL('Front Office', 'front', 'addonly', 'Things that front office can read and enter but not modify');
  //Add 'Accounting' ACL with 'addonly' and collect the ID number (added in 3.0.2)
  $back_addonly = addNewACL('Accounting', 'back', 'addonly', 'Things that back office can read and enter but not modify');
  //Add 'Emergency Login' ACL with 'write' and collect the ID number (added in 3.3.0)
  $emergency_write = addNewACL('Emergency Login', 'breakglass', 'write', 'Things that can use for emergency login, can read and modify');

  //Update the ACLs
  echo "<BR/><B>Updating the ACLs(Access Control Lists)</B><BR/>";
  //Insert the 'super' object from the 'admin' section into the Administrators group write ACL (added in 2.8.2)
  updateAcl($admin_write, 'Administrators', 'admin', 'Administration', 'super', 'Superuser', 'write');
  //Insert the 'high' object from the 'sensitivities' section into the Administrators group write ACL (added in 2.8.2)
  updateAcl($admin_write, 'Administrators', 'sensitivities', 'Sensitivities', 'high', 'High', 'write');
  //Insert the 'normal' object from the 'sensitivities' section into the Administrators group write ACL (added in 2.8.2)
  updateAcl($admin_write, 'Administrators', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'write');
  //Insert the 'high' object from the 'sensitivities' section into the Physicians group write ACL (added in 2.8.2)
  updateAcl($doc_write, 'Physicians', 'sensitivities', 'Sensitivities', 'high', 'High', 'write');
  //Insert the 'normal' object from the 'sensitivities' section into the Physicians group write ACL (added in 2.8.2)
  updateAcl($doc_write, 'Physicians', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'write');
  //Insert the 'normal' object from the 'sensitivities' section into the Clinicians group  addonly ACL (added in 2.8.2)
  updateAcl($clin_addonly, 'Clinicians', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'addonly');
  //Insert the 'drugs' object from the 'admin' section into the Administrators group write ACL (added in 2.8.4)
  updateAcl($admin_write, 'Administrators', 'admin', 'Administration', 'drugs', 'Pharmacy Dispensary', 'write');
  //Insert the 'drugs' object from the 'admin' section into the Physicians group write ACL (added in 2.8.4)
  updateAcl($doc_write, 'Physicians', 'admin', 'Administration', 'drugs', 'Pharmacy Dispensary', 'write');
  //Insert the 'drugs' object from the 'admin' section into the Clinicians group write ACL (added in 2.8.4)
  updateAcl($clin_write, 'Clinicians', 'admin', 'Administration', 'drugs', 'Pharmacy Dispensary', 'write');
  //Insert the 'acl' object from the 'admin' section into the Administrators group write ACL (added in 2.8.4)
  updateAcl($admin_write, 'Administrators', 'admin', 'Administration', 'acl', 'ACL Administration', 'write');
  //Insert the 'disc' object from the 'acct' section into the Administrators group write ACL (added in 2.8.4)
  updateAcl($admin_write, 'Administrators', 'acct', 'Accounting', 'disc', 'Price Discounting', 'write');
  //Insert the 'disc' object from the 'acct' section into the Accounting group write ACL (added in 2.8.4)
  updateAcl($back_write, 'Accounting', 'acct', 'Accounting', 'disc', 'Price Discounting', 'write');
  //Insert the 'disc' object from the 'acct' section into the Physicians group write ACL (added in 2.8.4)
  updateAcl($doc_write, 'Physicians', 'acct', 'Accounting', 'disc', 'Price Discounting', 'write');
  //Insert the 'default' object from the 'lists' section into the Administrators group write ACL (added in 3.0.2)
  updateAcl($admin_write, 'Administrators', 'lists', 'Lists', 'default', 'Default List (write,addonly optional)', 'write');
  //Insert the 'state' object from the 'lists' section into the Administrators group write ACL (added in 3.0.2)
  updateAcl($admin_write, 'Administrators', 'lists', 'Lists', 'state', 'State List (write,addonly optional)', 'write');
  //Insert the 'country' object from the 'lists' section into the Administrators group write ACL (added in 3.0.2)
  updateAcl($admin_write, 'Administrators', 'lists', 'Lists', 'country', 'Country List (write,addonly optional)', 'write');
  //Insert the 'language' object from the 'lists' section into the Administrators group write ACL (added in 3.0.2)
  updateAcl($admin_write, 'Administrators', 'lists', 'Lists', 'language', 'Language List (write,addonly optional)', 'write');
  //Insert the 'race' object from the 'lists' section into the Administrators group write ACL (added in 3.0.2)
  updateAcl($admin_write, 'Administrators', 'lists', 'Lists', 'ethrace', 'Ethnicity-Race List (write,addonly optional)', 'write');
  //Update ACLs for Emergency Login
  //Insert the 'disc' object from the 'acct' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'acct', 'Accounting', 'disc', 'Price Discounting', 'write');
  //Insert the 'bill' object from the 'acct' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'acct', 'Accounting', 'bill', 'Billing (write optional)', 'write');
  //Insert the 'eob' object from the 'acct' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'acct', 'Accounting', 'eob', 'EOB Data Entry', 'write');
  //Insert the 'rep' object from the 'acct' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'acct', 'Accounting', 'rep', 'Financial Reporting - my encounters', 'write');
  //Insert the 'rep_a' object from the 'acct' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'acct', 'Accounting', 'rep_a', 'Financial Reporting - anything', 'write');
  //Insert the 'calendar' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'calendar', 'Calendar Settings', 'write');
  //Insert the 'database' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'database', 'Database Reporting', 'write');
  //Insert the 'forms' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'forms', 'Forms Administration', 'write');
  //Insert the 'practice' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'practice', 'Practice Settings', 'write');
  //Insert the 'superbill' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'superbill', 'Superbill Codes Administration', 'write');
  //Insert the 'users' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'users', 'Users/Groups/Logs Administration', 'write');
  //Insert the 'batchcom' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'batchcom', 'Batch Communication Tool', 'write');
  //Insert the 'language' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'language', 'Language Interface Tool', 'write');
  //Insert the 'super' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'super', 'Superuser', 'write');
  //Insert the 'drugs' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'drugs', 'Pharmacy Dispensary', 'write');
  //Insert the 'acl' object from the 'admin' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'admin', 'Administration', 'acl', 'ACL Administration', 'write');
  //Insert the 'auth_a' object from the 'encounters' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'encounters', 'Encounters', 'auth_a', 'Authorize - any encounters', 'write');
  //Insert the 'coding_a' object from the 'encounters' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'encounters', 'Encounters', 'coding_a', 'Coding - any encounters (write,wsome optional)', 'write');
  //Insert the 'notes_a' object from the 'encounters' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'encounters', 'Encounters', 'notes_a', 'Notes - any encounters (write,addonly optional)', 'write');
  //Insert the 'date_a' object from the 'encounters' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'encounters', 'Encounters', 'date_a', 'Fix encounter dates - any encounters', 'write');
  //Insert the 'default' object from the 'lists' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'lists', 'Lists', 'default', 'Default List (write,addonly optional)', 'write');
  //Insert the 'state' object from the 'lists' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'lists', 'Lists', 'state', 'State List (write,addonly optional)', 'write');
  //Insert the 'country' object from the 'lists' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'lists', 'Lists', 'country', 'Country List (write,addonly optional)', 'write');
  //Insert the 'language' object from the 'lists' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'lists', 'Lists', 'language', 'Language List (write,addonly optional)', 'write');
  //Insert the 'ethrace' object from the 'lists' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'lists', 'Lists', 'ethrace', 'Ethnicity-Race List (write,addonly optional)', 'write');
  //Insert the 'appt' object from the 'patients' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'patients', 'Patients', 'appt', 'Appointments (write,wsome optional)', 'write');
  //Insert the 'demo' object from the 'patients' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'patients', 'Patients', 'demo', 'Demographics (write,addonly optional)', 'write');
  //Insert the 'med' object from the 'patients' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'patients', 'Patients', 'med', 'Medical/History (write,addonly optional)', 'write');
  //Insert the 'trans' object from the 'patients' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'patients', 'Patients', 'trans', 'Transactions (write optional)', 'write');
  //Insert the 'docs' object from the 'patients' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'patients', 'Patients', 'docs', 'Documents (write,addonly optional)', 'write');
  //Insert the 'notes' object from the 'patients' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'patients', 'Patients', 'notes', 'Patient Notes (write,addonly optional)', 'write');
  //Insert the 'high' object from the 'sensitivities' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'sensitivities', 'Sensitivities', 'high', 'High', 'write');
  //Insert the 'normal' object from the 'sensitivities' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'write');
  //Insert the 'sign' object from the 'patients' section into the Physicians group write ACL (added in 3.3.0)
  updateAcl($doc_write, 'Physicians', 'patients', 'Patients', 'sign', 'Sign Lab Results (write,addonly optional)', 'write');
  //Insert the 'sign' object from the 'nationnotes' section into the Administrators group write ACL (added in 3.3.0)
  updateAcl($admin_write, 'Administrators','nationnotes', 'Nation Notes', 'nn_configure', 'Nation Notes Configure','write');
  //Insert the 'sign' object from the 'nationnotes' section into the Emergency Login group write ACL (added in 3.3.0)
  updateAcl($emergency_write, 'Emergency Login','nationnotes', 'Nation Notes', 'nn_configure', 'Nation Notes Configure','write');
  //Insert the 'patientportal' object from the 'patientportal' section into the Administrators group write ACL (added in 4.1.0)
  updateAcl($admin_write, 'Administrators','patientportal', 'Patient Portal', 'portal', 'Patient Portal','write');
  //Insert the 'patientportal' object from the 'patientportal' section into the Emergency Login group write ACL (added in 4.1.0)
  updateAcl($emergency_write, 'Emergency Login','patientportal', 'Patient Portal', 'portal', 'Patient Portal','write');
  //Insert the 'Modules' object from the 'Menus' section into the Administrators group write ACL (added in 4.1.0)
  updateAcl($admin_write, 'Administrators','menus', 'Menus', 'modle', 'Modules', 'write');
  //Insert the 'Modules' object from the 'Menus' section into the Emergency Login group write ACL (added in 4.1.0)
  updateAcl($emergency_write, 'Emergency Login','menus', 'Menus', 'modle', 'Modules', 'write');

  //DONE with upgrading to this version
  $acl_version = $upgrade_acl;
}

// Upgrade for acl_version 2
$upgrade_acl = 2;
if ($acl_version < $upgrade_acl) {
  echo "<B>UPGRADING ACCESS CONTROLS TO VERSION ".$upgrade_acl.":</B></BR>";

  //Collect the ACL ID numbers.
  echo "<B>Checking to ensure all the proper ACL(access control list) are present:</B></BR>";
  
  //Add new object Sections
  echo "<BR/><B>Adding new object sections</B><BR/>";

  //Add new Objects
  echo "<BR/><B>Adding new objects</B><BR/>";

  //Update already existing Objects
  echo "<BR/><B>Upgrading objects</B><BR/>";

  //Add new ACLs here (will return the ACL ID of newly created or already existant ACL)
  // (will also place in the appropriate group and CREATE a new group if needed)
  echo "<BR/><B>Adding ACLs(Access Control Lists) and groups</B><BR/>";
  addNewACL('Physicians', 'doc', 'wsome', 'Things that physicians can read and partly modify');
  addNewACL('Clinicians', 'clin', 'wsome', 'Things that clinicians can read and partly modify');
  addNewACL('Front Office', 'front', 'wsome', 'Things that front office can read and partly modify');
  addNewACL('Accounting', 'back', 'wsome', 'Things that back office can read and partly modify');
  addNewACL('Physicians', 'doc', 'view', 'Things that physicians can only read');
  addNewACL('Clinicians', 'clin', 'view', 'Things that clinicians can only read');
  addNewACL('Front Office', 'front', 'view', 'Things that front office can only read');
  addNewACL('Accounting', 'back', 'view', 'Things that back office can only read');

  //Update the ACLs
  echo "<BR/><B>Updating the ACLs(Access Control Lists)</B><BR/>";

  //DONE with upgrading to this version
  $acl_version = $upgrade_acl;
}

/* This is a template for a new revision, when needed
// Upgrade for acl_version 3
$upgrade_acl = 3;
if ($acl_version < $upgrade_acl) {
  echo "<B>UPGRADING ACCESS CONTROLS TO VERSION ".$upgrade_acl.":</B></BR>";

  //Collect the ACL ID numbers.
  echo "<B>Checking to ensure all the proper ACL(access control list) are present:</B></BR>";

  //Add new object Sections
  echo "<BR/><B>Adding new object sections</B><BR/>";

  //Add new Objects
  echo "<BR/><B>Adding new objects</B><BR/>";

  //Update already existing Objects
  echo "<BR/><B>Upgrading objects</B><BR/>";

  //Add new ACLs here (will return the ACL ID of newly created or already existant ACL)
  // (will also place in the appropriate group and CREATE a new group if needed)
  echo "<BR/><B>Adding ACLs(Access Control Lists) and groups</B><BR/>";

  //Update the ACLs
  echo "<BR/><B>Updating the ACLs(Access Control Lists)</B><BR/>";

  //DONE with upgrading to this version
  $acl_version = $upgrade_acl;
}
*/

/* This is a template for a new revision, when needed
// Upgrade for acl_version 4
$upgrade_acl = 4;
if ($acl_version < $upgrade_acl) {
  echo "<B>UPGRADING ACCESS CONTROLS TO VERSION ".$upgrade_acl.":</B></BR>";

  //Collect the ACL ID numbers.
  echo "<B>Checking to ensure all the proper ACL(access control list) are present:</B></BR>";

  //Add new object Sections
  echo "<BR/><B>Adding new object sections</B><BR/>";

  //Add new Objects
  echo "<BR/><B>Adding new objects</B><BR/>";

  //Update already existing Objects
  echo "<BR/><B>Upgrading objects</B><BR/>";

  //Add new ACLs here (will return the ACL ID of newly created or already existant ACL)
  // (will also place in the appropriate group and CREATE a new group if needed)
  echo "<BR/><B>Adding ACLs(Access Control Lists) and groups</B><BR/>";

  //Update the ACLs
  echo "<BR/><B>Updating the ACLs(Access Control Lists)</B><BR/>";

  //DONE with upgrading to this version
  $acl_version = $upgrade_acl;
}
*/

//All done
set_acl_version($acl_version);
echo "DONE upgrading access controls";

?>
