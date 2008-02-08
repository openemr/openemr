<?php
 // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
 //
 // This program may be run after phpGACL has been installed, and will
 // create the Access Control Objects and their sections as required
 // by OpenEMR.  See openemr/library/acl.inc file for the list of
 // currently supported Access Control Objects(ACO), which this
 // script will install.  This script also creates several
 // ARO groups, an "admin" ARO, and some reasonable ACL entries for
 // the groups.
 //   ARO groups include:
 //      Administrators
 //      Physicians     (Doctors)
 //      Clinicians     (Nurses, Physician Assistants, etc.)
 //      Front Office   (Receptionist)
 //      Accounting
 //
 // Upgrade Howto
 // If you have previously installed phpGACL, and have since upgraded
 // to a new version of OpenEMR, then should consider upgrading
 // the phpGACL database with the acl_upgrade.php script to ensure
 // the database includes all the required Access Control Objects(ACO).
 //

 include_once('library/acl.inc');

 if (! $phpgacl_location) die("You must first set up library/acl.inc to use phpGACL!");

 include_once("$phpgacl_location/gacl_api.class.php");

 $gacl = new gacl_api();

 // Create the ACO sections.  Every ACO must have a section.
 //
 if ($gacl->add_object_section('Accounting', 'acct', 10, 0, 'ACO') === FALSE) {
  die("<br>This is not working.  Make sure you have:<br>" .
   "* Set the correct phpgacl database name, user and password in gacl.ini.php<br>" .
   "* Done the same in gacl.class.php<br>" .
   "* Run setup.php from the phpGACL distribution<br>" .
   "* Not already run this script successfully<br>");
 }
 $gacl->add_object_section('Administration', 'admin'        , 10, 0, 'ACO');
 $gacl->add_object_section('Encounters'    , 'encounters'   , 10, 0, 'ACO');
 $gacl->add_object_section('Patients'      , 'patients'     , 10, 0, 'ACO');
 $gacl->add_object_section('Squads'        , 'squads'       , 10, 0, 'ACO');
 $gacl->add_object_section('Sensitivities' , 'sensitivities', 10, 0, 'ACO');

 // Create Accounting ACOs.
 //
 $gacl->add_object('acct', 'Billing (write optional)'           , 'bill' , 10, 0, 'ACO');
 $gacl->add_object('acct', 'EOB Data Entry'                     , 'eob'  , 10, 0, 'ACO');
 $gacl->add_object('acct', 'Financial Reporting - my encounters', 'rep'  , 10, 0, 'ACO');
 $gacl->add_object('acct', 'Financial Reporting - anything'     , 'rep_a', 10, 0, 'ACO');

 // Create Administration ACOs.
 //
 $gacl->add_object('admin', 'Superuser'                       , 'super'    , 10, 0, 'ACO');
 $gacl->add_object('admin', 'Calendar Settings'               , 'calendar' , 10, 0, 'ACO');
 $gacl->add_object('admin', 'Database Reporting'              , 'database' , 10, 0, 'ACO');
 $gacl->add_object('admin', 'Forms Administration'            , 'forms'    , 10, 0, 'ACO');
 $gacl->add_object('admin', 'Practice Settings'               , 'practice' , 10, 0, 'ACO');
 $gacl->add_object('admin', 'Superbill Codes Administration'  , 'superbill', 10, 0, 'ACO');
 $gacl->add_object('admin', 'Users/Groups/Logs Administration', 'users'    , 10, 0, 'ACO');
 $gacl->add_object('admin', 'Batch Communication Tool'        , 'batchcom' , 10, 0, 'ACO');
 $gacl->add_object('admin', 'Language Interface Tool'         , 'language' , 10, 0, 'ACO');
 $gacl->add_object('admin', 'Pharmacy Dispensary'             , 'drugs'    , 10, 0, 'ACO');
 $gacl->add_object('admin', 'ACL Administration'              , 'acl'      , 10, 0, 'ACO');

 // Create ACOs for encounters.
 //
 $gacl->add_object('encounters', 'Authorize - my encounters'                        , 'auth'    , 10, 0, 'ACO');
 $gacl->add_object('encounters', 'Authorize - any encounters'                       , 'auth_a'  , 10, 0, 'ACO');
 $gacl->add_object('encounters', 'Coding - my encounters (write,wsome optional)'    , 'coding'  , 10, 0, 'ACO');
 $gacl->add_object('encounters', 'Coding - any encounters (write,wsome optional)'   , 'coding_a', 10, 0, 'ACO');
 $gacl->add_object('encounters', 'Notes - my encounters (write,addonly optional)'   , 'notes'   , 10, 0, 'ACO');
 $gacl->add_object('encounters', 'Notes - any encounters (write,addonly optional)'  , 'notes_a' , 10, 0, 'ACO');
 $gacl->add_object('encounters', 'Fix encounter dates - any encounters'             , 'date_a'  , 10, 0, 'ACO');
 $gacl->add_object('encounters', 'Less-private information (write,addonly optional)', 'relaxed' , 10, 0, 'ACO');

 // Create ACOs for patients.
 //
 $gacl->add_object('patients', 'Appointments (write optional)'           , 'appt' , 10, 0, 'ACO');
 $gacl->add_object('patients', 'Demographics (write,addonly optional)'   , 'demo' , 10, 0, 'ACO');
 $gacl->add_object('patients', 'Medical/History (write,addonly optional)', 'med'  , 10, 0, 'ACO');
 $gacl->add_object('patients', 'Transactions (write optional)'           , 'trans', 10, 0, 'ACO');
 $gacl->add_object('patients', 'Documents (write,addonly optional)'      , 'docs' , 10, 0, 'ACO');
 $gacl->add_object('patients', 'Patient Notes (write,addonly optional)'  , 'notes', 10, 0, 'ACO');

 // Create ACOs for sensitivities.
 //
 $gacl->add_object('sensitivities', 'Normal', 'normal', 10, 0, 'ACO');
 $gacl->add_object('sensitivities', 'High'  , 'high'  , 20, 0, 'ACO');

 // Create ARO groups.
 //
 $users = $gacl->add_group('users', 'OpenEMR Users' , 0     , 'ARO');
 $admin = $gacl->add_group('admin', 'Administrators', $users, 'ARO');
 $clin  = $gacl->add_group('clin' , 'Clinicians'    , $users, 'ARO');
 $doc   = $gacl->add_group('doc'  , 'Physicians'    , $users, 'ARO');
 $front = $gacl->add_group('front', 'Front Office'  , $users, 'ARO');
 $back  = $gacl->add_group('back' , 'Accounting'    , $users, 'ARO');

 // Create a Users section for the AROs (humans).
 //
 $gacl->add_object_section('Users', 'users', 10, 0, 'ARO');

 // Create the Administrator in the above-created "users" section
 // and add him/her to the above-created "admin" group.
 //
 $gacl->add_object('users', 'Administrator', 'admin' ,10, 0, 'ARO');
 $gacl->add_group_object($admin, 'users', 'admin', 'ARO');

 // Set permissions for administrators.
 //
 $gacl->add_acl(
  array(
   'acct'=>array('bill', 'eob', 'rep', 'rep_a'),
   'admin'=>array('calendar', 'database', 'forms', 'practice', 'superbill', 'users', 'batchcom', 'language', 'super', 'drugs', 'acl'),
   'encounters'=>array('auth_a', 'coding_a', 'notes_a', 'date_a'),
   'patients'=>array('appt', 'demo', 'med', 'trans', 'docs', 'notes'),
   'sensitivities'=>array('normal', 'high')
  ),
  NULL, array($admin), NULL, NULL,
  1, 1, 'write', 'Administrators can do anything'
 );

 // Set permissions for physicians.
 //
 $gacl->add_acl(
  array(
   'acct'=>array('rep'),
   'admin'=>array('drugs'),
   'encounters'=>array('auth_a', 'coding_a', 'notes_a', 'date_a'),
   'patients'=>array('appt', 'demo', 'med', 'trans', 'docs', 'notes'),
   'sensitivities'=>array('normal', 'high')
  ),
  NULL, array($doc), NULL, NULL,
  1, 1, 'write', 'Things that physicians can read and modify'
 );

 // Set permissions for clinicians.
 //
 $gacl->add_acl(
  array(
   'encounters'=>array('notes', 'relaxed'),
   'patients'=>array('demo', 'med', 'docs', 'notes'),
   'sensitivities'=>array('normal')
  ),
  NULL, array($clin), NULL, NULL,
  1, 1, 'addonly', 'Things that clinicians can read and enter but not modify'
 );
 $gacl->add_acl(
  array(
   'admin'=>array('drugs'),
   'encounters'=>array('coding'),
   'patients'=>array('appt')
  ),
  NULL, array($clin), NULL, NULL,
  1, 1, 'write', 'Things that clinicians can read and modify'
 );

 // Set permissions for front office staff.
 //
 $gacl->add_acl(
  array(
   'patients'=>array('appt', 'demo', 'trans', 'notes')
  ),
  NULL, array($front), NULL, NULL,
  1, 1, 'write', 'Things that front office can read and modify'
 );

 // Set permissions for back office staff.
 //
 $gacl->add_acl(
  array(
   'acct'=>array('bill', 'eob', 'rep', 'rep_a'),
   'admin'=>array('practice', 'superbill'),
   'encounters'=>array('auth_a', 'coding_a', 'date_a'),
   'patients'=>array('appt', 'demo')
  ),
  NULL, array($back), NULL, NULL,
  1, 1, 'write', 'Things that back office can read and modify'
 );

?>
<html>
<head>
<title>OpenEMR ACL Setup</title>
<link rel=STYLESHEET href="interface/themes/style_blue.css">
</head>
<body>
<span class="title">OpenEMR ACL Setup</span>
<br><br>
<span class="text">

All done!

</span>

</body>
</html>
