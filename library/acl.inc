<?
  // If you have installed phpGACL (http://phpgacl.sourceforge.net/)
  // and have configured it for your site, then uncomment the following
  // statement and change it to point to the location where
  // gacl.class.php is intalled.
  //
  // $phpgacl_location = "/var/www/gacl";

  // The following Access Control Objects are currently be supported.
  // These are the "things to be protected":
  //
  // Section "admin" (Administration):
  //   super       Superuser - can delete patients, encounters, issues
  //   calendar    Calendar Settings
  //   database    Database Reporting
  //   forms       Forms Administration
  //   practice    Practice Settings
  //   superbill   Superbill Codes Administration
  //   users       Users/Groups/Logs Administration
  //   batchcom    Batch Communication Tool
  //   language    Language Interface Tool
  //   drugs       Pharmacy Dispensary
  //
  // Section "acct" (Accounting):
  //   bill        Billing (write optional)
  //   eob         EOB Data Entry
  //   rep         Financial Reporting - my encounters
  //   rep_a       Financial Reporting - anything
  //
  // Section "patients" (Patient Information):
  //   appt        Appointments (write optional)
  //   demo        Demographics (write,addonly optional)
  //   med         Medical Records and History (write,addonly optional)
  //   trans       Transactions, e.g. referrals (write optional)
  //   docs        Documents (write,addonly optional)
  //   notes       Patient Notes (write,addonly optional)
  //
  // Section "encounters" (Encounter Information):
  //   auth        Authorize - my encounters
  //   auth_a      Authorize - any encounters
  //   coding      Coding - my encounters (write,wsome optional)
  //   coding_a    Coding - any encounters (write,wsome optional)
  //   notes       Notes - my encounters (write,addonly optional)
  //   notes_a     Notes - any encounters (write,addonly optional)
  //   date_a      Fix encounter dates - any encounters
  //   relaxed     Less-private information (write,addonly optional)
  //               (e.g. the Sports Fitness encounter form)
  //
  // Section "squads" applies to sports team use only:
  //   acos in this section define the user-specified list of squads
  //
  // Section "sensitivities" (Sensitivities):
  //   normal     Normal
  //   high       High

  if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl.class.php");
    $gacl_object = new gacl();
  }

  // acl_check should return 0 if access is denied.  Otherwise it may
  // return anything that evaluates to true.  In addition if any of the
  // following types of access are applicable, then the corresponding value
  // must be returned if and only if such access is granted (ony one may
  // be specified):
  //
  // * write   - the user may add or modify the ACO
  // * wsome   - the user has limited add/modify access to the ACO
  // * addonly - the user may view and add but not modify entries
  //
  function acl_check($section, $value, $user = '') {
    global $gacl_object, $phpgacl_location;
    if (! $user) $user = $_SESSION['authUser'];

    if ($phpgacl_location) {
      return $gacl_object->acl_check($section, $value, 'users', $user);
    }

    // If no phpgacl, then apply the old static rules whereby "authorized"
    // users (providers) can do anything, and other users can do most things.
    // If you want custom access control but don't want to mess with phpGACL,
    // then you could customize the code below instead.

    if ($section == 'admin' && $value == 'super') return 0;

    if ($_SESSION['userauthorized']) return 'write';

    if ($section == 'patients') {
      if ($value == 'med') return 1;
      return 'write';
    }
    else if ($section == 'encounters') {
      if (strpos($value, 'coding' ) === 0) return 'write';
      if (strpos($value, 'notes'  ) === 0) return 'write';
      if ($value == 'relaxed') return 'write';
    }
    else if ($section != 'admin') {
      return 'write';
    }

    return 0;
  }

  // Get the ACO name/value pairs for a designated section.  Each value
  // is an array (section_value, value, order_value, name, hidden).
  //
  function acl_get_section_acos($section) {
    global $phpgacl_location;
    if ($phpgacl_location) {
      include_once("$phpgacl_location/gacl_api.class.php");
      $gacl = new gacl_api();
      $arr1 = $gacl->get_objects($section, 1, 'ACO');
      $arr = array();
      foreach ($arr1[$section] as $value) {
        $odata = $gacl->get_object_data($gacl->get_object_id($section, $value, 'ACO'), 'ACO');
        $arr[$value] = $odata[0];
      }
      return $arr;
    }
    return 0;
  }

  // Return an array keyed on squad ACO names.
  // This is only applicable for sports team use.
  //
  function acl_get_squads() {
    return acl_get_section_acos('squads');
  }

  // Return an array keyed on encounter sensitivity level ACO names.
  // Sensitivities are useful when some encounter notes are not
  // medically sensitive (e.g. a physical fitness test), and/or if
  // some will be "for doctor's eyes only" (e.g. STD treatment).
  //
  // When a non-blank sensitivity value exists in the new encounter
  // form, it names an additional ACO required for access to all forms
  // in the encounter.  If you want some encounters to be non-sensitive,
  // then you also need some default nonblank sensitivity for normal
  // encounters, as well as greater encounter notes permissions for
  // those allowed to view non-sensitive encounters.
  //
  function acl_get_sensitivities() {
    return acl_get_section_acos('sensitivities');
  }

?>
