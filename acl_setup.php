<?php
/**
 * This program is run by the OpenEMR setup.php script to install phpGACL
 * and creates the Access Control Objects and their sections.
 * See openemr/library/acl.inc file for the list of
 * currently supported Access Control Objects(ACO), which this
 * script will install.  This script also creates several
 * ARO groups, an "admin" ARO, and some reasonable ACL entries for
 * the groups.
 *   ARO groups include:
 *      Administrators
 *      Physicians     (Doctors)
 *      Clinicians     (Nurses, Physician Assistants, etc.)
 *      Front Office   (Receptionist)
 *      Accounting
 *
 * Upgrade Howto
 * When upgrading to a new version of OpenEMR, run the acl_upgrade.php
 * script to update the phpGACL access controls.  This is required to
 * ensure the database includes all the required Access Control
 * Objects(ACO).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__).'/library/acl.inc');

if (empty($phpgacl_location)) {
    die("You must first set up library/acl.inc to use phpGACL!");
}

require_once("$phpgacl_location/gacl_api.class.php");

$gacl = new gacl_api();

// Create the ACO sections.  Every ACO must have a section.
//
if ($gacl->add_object_section('Accounting', 'acct', 10, 0, 'ACO') === false) {
    echo "Unable to create the access controls for OpenEMR.  You have likely already run this script (acl_setup.php) successfully.<br>Other possible problems include php-GACL configuration file errors (gacl.ini.php or gacl.class.php).<br>";
    return;
}
// xl('Accounting')
$gacl->add_object_section('Administration', 'admin', 10, 0, 'ACO');
// xl('Administration')
$gacl->add_object_section('Encounters', 'encounters', 10, 0, 'ACO');
// xl('Encounters')
$gacl->add_object_section('Lists', 'lists', 10, 0, 'ACO');
// xl('Lists')
$gacl->add_object_section('Patients', 'patients', 10, 0, 'ACO');
// xl('Patients')
$gacl->add_object_section('Squads', 'squads', 10, 0, 'ACO');
// xl('Squads')
$gacl->add_object_section('Sensitivities', 'sensitivities', 10, 0, 'ACO');
// xl('Sensitivities')
$gacl->add_object_section('Placeholder', 'placeholder', 10, 0, 'ACO');
// xl('Placeholder')
$gacl->add_object_section('Nation Notes', 'nationnotes', 10, 0, 'ACO');
// xl('Nation Notes')
$gacl->add_object_section('Patient Portal', 'patientportal', 10, 0, 'ACO');
// xl('Patient Portal')
$gacl->add_object_section('Menus', 'menus', 10, 0, 'ACO');
// xl('Menus')
$gacl->add_object_section('Groups', 'groups', 10, 0, 'ACO');
// xl('Groups')



// Create Accounting ACOs.
//
$gacl->add_object('acct', 'Billing (write optional)', 'bill', 10, 0, 'ACO');
// xl('Billing (write optional)')
$gacl->add_object('acct', 'Price Discounting', 'disc', 10, 0, 'ACO');
// xl('Price Discounting')
$gacl->add_object('acct', 'EOB Data Entry', 'eob', 10, 0, 'ACO');
// xl('EOB Data Entry')
$gacl->add_object('acct', 'Financial Reporting - my encounters', 'rep', 10, 0, 'ACO');
// xl('Financial Reporting - my encounters')
$gacl->add_object('acct', 'Financial Reporting - anything', 'rep_a', 10, 0, 'ACO');
// xl('Financial Reporting - anything')

// Create Administration ACOs.
//
$gacl->add_object('admin', 'Superuser', 'super', 10, 0, 'ACO');
// xl('Superuser')
$gacl->add_object('admin', 'Calendar Settings', 'calendar', 10, 0, 'ACO');
// xl('Calendar Settings')
$gacl->add_object('admin', 'Database Reporting', 'database', 10, 0, 'ACO');
// xl('Database Reporting')
$gacl->add_object('admin', 'Forms Administration', 'forms', 10, 0, 'ACO');
// xl('Forms Administration')
$gacl->add_object('admin', 'Practice Settings', 'practice', 10, 0, 'ACO');
// xl('Practice Settings')
$gacl->add_object('admin', 'Superbill Codes Administration', 'superbill', 10, 0, 'ACO');
// xl('Superbill Codes Administration')
$gacl->add_object('admin', 'Users/Groups/Logs Administration', 'users', 10, 0, 'ACO');
// xl('Users/Groups/Logs Administration')
$gacl->add_object('admin', 'Batch Communication Tool', 'batchcom', 10, 0, 'ACO');
// xl('Batch Communication Tool')
$gacl->add_object('admin', 'Language Interface Tool', 'language', 10, 0, 'ACO');
// xl('Language Interface Tool')
$gacl->add_object('admin', 'Pharmacy Dispensary', 'drugs', 10, 0, 'ACO');
// xl('Pharmacy Dispensary')
$gacl->add_object('admin', 'ACL Administration', 'acl', 10, 0, 'ACO');
// xl('ACL Administration')
$gacl->add_object('admin', 'Multipledb', 'multipledb', 10, 0, 'ACO');
// xl('Multipledb')
$gacl->add_object('admin', 'Menu', 'menu', 10, 0, 'ACO');
// xl('Menu')
$gacl->add_object('admin', 'Manage modules', 'manage_modules', 10, 0, 'ACO');
// xl('Manage modules')


// Create ACOs for encounters.
//
$gacl->add_object('encounters', 'Authorize - my encounters', 'auth', 10, 0, 'ACO');
// xl('Authorize - my encounters')
$gacl->add_object('encounters', 'Authorize - any encounters', 'auth_a', 10, 0, 'ACO');
// xl('Authorize - any encounters')
$gacl->add_object('encounters', 'Coding - my encounters (write,wsome optional)', 'coding', 10, 0, 'ACO');
// xl('Coding - my encounters (write,wsome optional)')
$gacl->add_object('encounters', 'Coding - any encounters (write,wsome optional)', 'coding_a', 10, 0, 'ACO');
// xl('Coding - any encounters (write,wsome optional)')
$gacl->add_object('encounters', 'Notes - my encounters (write,addonly optional)', 'notes', 10, 0, 'ACO');
// xl('Notes - my encounters (write,addonly optional)')
$gacl->add_object('encounters', 'Notes - any encounters (write,addonly optional)', 'notes_a', 10, 0, 'ACO');
// xl('Notes - any encounters (write,addonly optional)')
$gacl->add_object('encounters', 'Fix encounter dates - any encounters', 'date_a', 10, 0, 'ACO');
// xl('Fix encounter dates - any encounters')
$gacl->add_object('encounters', 'Less-private information (write,addonly optional)', 'relaxed', 10, 0, 'ACO');
// xl('Less-private information (write,addonly optional)')

// Create ACOs for lists.
//
$gacl->add_object('lists', 'Default List (write,addonly optional)', 'default', 10, 0, 'ACO');
// xl('Default List (write,addonly optional)')
$gacl->add_object('lists', 'State List (write,addonly optional)', 'state', 10, 0, 'ACO');
// xl('State List (write,addonly optional)')
$gacl->add_object('lists', 'Country List (write,addonly optional)', 'country', 10, 0, 'ACO');
// xl('Country List (write,addonly optional)')
$gacl->add_object('lists', 'Language List (write,addonly optional)', 'language', 10, 0, 'ACO');
// xl('Language List (write,addonly optional)')
$gacl->add_object('lists', 'Ethnicity-Race List (write,addonly optional)', 'ethrace', 10, 0, 'ACO');
// xl('Ethnicity-Race List (write,addonly optional)')

// Create ACOs for patientportal.
//
$gacl->add_object('patientportal', 'Patient Portal', 'portal', 10, 0, 'ACO');
// xl('Patient Portal')

// Create ACOs for modules.
//
$gacl->add_object('menus', 'Modules', 'modle', 10, 0, 'ACO');
// xl('Modules')

// Create ACOs for patients.
//
$gacl->add_object('patients', 'Appointments (write,wsome optional)', 'appt', 10, 0, 'ACO');
// xl('Appointments (write,wsome optional)')
$gacl->add_object('patients', 'Demographics (write,addonly optional)', 'demo', 10, 0, 'ACO');
// xl('Demographics (write,addonly optional)')
$gacl->add_object('patients', 'Medical/History (write,addonly optional)', 'med', 10, 0, 'ACO');
// xl('Medical/History (write,addonly optional)')
$gacl->add_object('patients', 'Transactions (write optional)', 'trans', 10, 0, 'ACO');
// xl('Transactions (write optional)')
$gacl->add_object('patients', 'Documents (write,addonly optional)', 'docs', 10, 0, 'ACO');
// xl('Documents (write,addonly optional)')
$gacl->add_object('patients', 'Documents Delete', 'docs_rm', 10, 0, 'ACO');
// xl('Documents Delete')
$gacl->add_object('patients', 'Patient Notes (write,addonly optional)', 'notes', 10, 0, 'ACO');
// xl('Patient Notes (write,addonly optional)')
$gacl->add_object('patients', 'Sign Lab Results (write,addonly optional)', 'sign', 10, 0, 'ACO');
// xl('Sign Lab Results (write,addonly optional)')
$gacl->add_object('patients', 'Patient Reminders (write,addonly optional)', 'reminder', 10, 0, 'ACO');
// xl('Patient Reminders (write,addonly optional)')
$gacl->add_object('patients', 'Clinical Reminders/Alerts (write,addonly optional)', 'alert', 10, 0, 'ACO');
// xl('Clinical Reminders/Alerts (write,addonly optional)')
$gacl->add_object('patients', 'Disclosures (write,addonly optional)', 'disclosure', 10, 0, 'ACO');
// xl('Disclosures (write,addonly optional)')
$gacl->add_object('patients', 'Prescriptions (write,addonly optional)', 'rx', 10, 0, 'ACO');
// xl('Prescriptions (write,addonly optional)')
$gacl->add_object('patients', 'Amendments (write,addonly optional)', 'amendment', 10, 0, 'ACO');
// xl('Amendments (write,addonly optional)')
$gacl->add_object('patients', 'Lab Results (write,addonly optional)', 'lab', 10, 0, 'ACO');
// xl('Lab Results (write,addonly optional)')


$gacl->add_object('groups', 'View/Add/Update groups', 'gadd', 10, 0, 'ACO');
// xl('View/Add/Update groups')
$gacl->add_object('groups', 'View/Create/Update groups appointment in calendar', 'gcalendar', 10, 0, 'ACO');
// xl('View/Create/Update groups appointment in calendar')
$gacl->add_object('groups', 'Group encounter log', 'glog', 10, 0, 'ACO');
// xl('Group encounter log')
$gacl->add_object('groups', 'Group detailed log of appointment in patient record', 'gdlog', 10, 0, 'ACO');
// xl('Group detailed log of appointment in patient record')
$gacl->add_object('groups', 'Send message from the permanent group therapist to the personal therapist', 'gm', 10, 0, 'ACO');
// xl('Send message from the permanent group therapist to the personal therapist')

// Create ACOs for sensitivities.
//
$gacl->add_object('sensitivities', 'Normal', 'normal', 10, 0, 'ACO');
// xl('Normal')
$gacl->add_object('sensitivities', 'High', 'high', 20, 0, 'ACO');
// xl('High')

// Create ACO for placeholder.
//
$gacl->add_object('placeholder', 'Placeholder (Maintains empty ACLs)', 'filler', 10, 0, 'ACO');
// xl('Placeholder (Maintains empty ACLs)')

// Create ACO for nationnotes.
//
$gacl->add_object('nationnotes', 'Nation Notes Configure', 'nn_configure', 10, 0, 'ACO');
// xl('Nation Notes Configure')

// Create ARO groups.
//
$users = $gacl->add_group('users', 'OpenEMR Users', 0, 'ARO');
// xl('OpenEMR Users')
$admin = $gacl->add_group('admin', 'Administrators', $users, 'ARO');
// xl('Administrators')
$clin  = $gacl->add_group('clin', 'Clinicians', $users, 'ARO');
// xl('Clinicians')
$doc   = $gacl->add_group('doc', 'Physicians', $users, 'ARO');
// xl('Physicians')
$front = $gacl->add_group('front', 'Front Office', $users, 'ARO');
// xl('Front Office')
$back  = $gacl->add_group('back', 'Accounting', $users, 'ARO');
// xl('Accounting')
$breakglass  = $gacl->add_group('breakglass', 'Emergency Login', $users, 'ARO');
// xl('Emergency Login')


// Create a Users section for the AROs (humans).
//
$gacl->add_object_section('Users', 'users', 10, 0, 'ARO');
// xl('Users')

// Create the Administrator in the above-created "users" section
// and add him/her to the above-created "admin" group.
// If this script is being used by OpenEMR's setup, then will
//   incorporate the installation values. Otherwise will
//    hardcode the 'admin' user.
if (isset($this) && isset($this->iuser)) {
    $gacl->add_object('users', $this->iuname, $this->iuser, 10, 0, 'ARO');
    $gacl->add_group_object($admin, 'users', $this->iuser, 'ARO');
} else {
    $gacl->add_object('users', 'Administrator', 'admin', 10, 0, 'ARO');
    $gacl->add_group_object($admin, 'users', 'admin', 'ARO');
}

// Declare return terms for language translations
//  xl('write') xl('wsome') xl('addonly') xl('view')

// Set permissions for administrators.
//
$gacl->add_acl(
    array(
        'acct'=>array('bill', 'disc', 'eob', 'rep', 'rep_a'),
        'admin'=>array('calendar', 'database', 'forms', 'practice', 'superbill', 'users', 'batchcom', 'language', 'super', 'drugs', 'acl','multipledb','menu','manage_modules'),
        'encounters'=>array('auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'),
        'lists'=>array('default','state','country','language','ethrace'),
        'patients'=>array('appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab', 'docs_rm'),
        'sensitivities'=>array('normal', 'high'),
        'nationnotes'=>array('nn_configure'),
        'patientportal'=>array('portal'),
        'menus'=>array('modle'),
        'groups'=>array('gadd','gcalendar','glog','gdlog','gm')
    ),
    null,
    array($admin),
    null,
    null,
    1,
    1,
    'write',
    'Administrators can do anything'
);
// xl('Administrators can do anything')

// Set permissions for physicians.
//
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($doc),
    null,
    null,
    1,
    1,
    'view',
    'Things that physicians can only read'
);
// xl('Things that physicians can only read')
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($doc),
    null,
    null,
    1,
    1,
    'addonly',
    'Things that physicians can read and enter but not modify'
);
// xl('Things that physicians can read and enter but not modify')
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($doc),
    null,
    null,
    1,
    1,
    'wsome',
    'Things that physicians can read and partly modify'
);
// xl('Things that physicians can read and partly modify')
$gacl->add_acl(
    array(
        'acct'=>array('disc', 'rep'),
        'admin'=>array('drugs'),
        'encounters'=>array('auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'),
        'patients' => array('appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert',
        'disclosure', 'rx', 'amendment', 'lab'),
        'sensitivities'=>array('normal', 'high'),
        'groups'=>array('gcalendar','glog')
    ),
    null,
    array($doc),
    null,
    null,
    1,
    1,
    'write',
    'Things that physicians can read and modify'
);
// xl('Things that physicians can read and modify')

// Set permissions for clinicians.
//
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($clin),
    null,
    null,
    1,
    1,
    'view',
    'Things that clinicians can only read'
);
// xl('Things that clinicians can only read')
$gacl->add_acl(
    array(
        'encounters'=>array('notes', 'relaxed'),
        'patients'=>array('demo', 'med', 'docs', 'notes','trans', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab'),
        'sensitivities'=>array('normal')
    ),
    null,
    array($clin),
    null,
    null,
    1,
    1,
    'addonly',
    'Things that clinicians can read and enter but not modify'
);
// xl('Things that clinicians can read and enter but not modify')
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($clin),
    null,
    null,
    1,
    1,
    'wsome',
    'Things that clinicians can read and partly modify'
);
// xl('Things that clinicians can read and partly modify')
$gacl->add_acl(
    array(
        'admin'=>array('drugs'),
        'encounters'=>array('coding'),
        'patients'=>array('appt'),
        'groups'=>array('gcalendar','glog')
    ),
    null,
    array($clin),
    null,
    null,
    1,
    1,
    'write',
    'Things that clinicians can read and modify'
);
// xl('Things that clinicians can read and modify')

// Set permissions for front office staff.
//
$gacl->add_acl(
    array(
        'patients'=>array('alert')
    ),
    null,
    array($front),
    null,
    null,
    1,
    1,
    'view',
    'Things that front office can only read'
);
// xl('Things that front office can only read')
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($front),
    null,
    null,
    1,
    1,
    'addonly',
    'Things that front office can read and enter but not modify'
);
// xl('Things that front office can read and enter but not modify')
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($front),
    null,
    null,
    1,
    1,
    'wsome',
    'Things that front office can read and partly modify'
);
// xl('Things that front office can read and partly modify')
$gacl->add_acl(
    array(
        'patients'=>array('appt', 'demo', 'trans', 'notes'),
        'groups'=>array('gcalendar')
    ),
    null,
    array($front),
    null,
    null,
    1,
    1,
    'write',
    'Things that front office can read and modify'
);
// xl('Things that front office can read and modify')

// Set permissions for back office staff.
//
$gacl->add_acl(
    array(
        'patients'=>array('alert')
    ),
    null,
    array($back),
    null,
    null,
    1,
    1,
    'view',
    'Things that back office can only read'
);
// xl('Things that back office can only read')
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($back),
    null,
    null,
    1,
    1,
    'addonly',
    'Things that back office can read and enter but not modify'
);
// xl('Things that back office can read and enter but not modify')
$gacl->add_acl(
    array(
        'placeholder'=>array('filler')
    ),
    null,
    array($back),
    null,
    null,
    1,
    1,
    'wsome',
    'Things that back office can read and partly modify'
);
// xl('Things that back office can read and partly modify')
$gacl->add_acl(
    array(
        'acct'=>array('bill', 'disc', 'eob', 'rep', 'rep_a'),
        'admin'=>array('practice', 'superbill'),
        'encounters'=>array('auth_a', 'coding_a', 'date_a'),
        'patients'=>array('appt', 'demo')
    ),
    null,
    array($back),
    null,
    null,
    1,
    1,
    'write',
    'Things that back office can read and modify'
);
// xl('Things that back office can read and modify')

// Set permissions for Emergency Login.
//
$gacl->add_acl(
    array(
        'acct'=>array('bill', 'disc', 'eob', 'rep', 'rep_a'),
        'admin'=>array('calendar', 'database', 'forms', 'practice', 'superbill', 'users', 'batchcom', 'language', 'super', 'drugs', 'acl','multipledb','menu','manage_modules'),
        'encounters'=>array('auth_a', 'auth', 'coding_a', 'coding', 'notes_a', 'notes', 'date_a', 'relaxed'),
        'lists'=>array('default','state','country','language','ethrace'),
        'patients'=>array('appt', 'demo', 'med', 'trans', 'docs', 'notes', 'sign', 'reminder', 'alert', 'disclosure', 'rx', 'amendment', 'lab', 'docs_rm'),
        'sensitivities'=>array('normal', 'high'),
        'nationnotes'=>array('nn_configure'),
        'patientportal'=>array('portal'),
        'menus'=>array('modle'),
        'groups'=>array('gadd','gcalendar','glog','gdlog','gm')
    ),
    null,
    array($breakglass),
    null,
    null,
    1,
    1,
    'write',
    'Emergency Login user can do anything'
);
// xl('Emergency Login user can do anything')

?>
<html>
<head>
<title>OpenEMR ACL Setup</title>
<link rel=STYLESHEET href="interface/themes/style_blue.css">
</head>
<body>
<b>OpenEMR ACL Setup</b>
<br>
All done configuring and installing access controls (php-GACL)!
</body>
</html>
