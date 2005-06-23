<?
  // If you have installed phpGACL (http://phpgacl.sourceforge.net/)
  // and have configured it for your site, then uncomment the following
  // statement and change it to point to the location where
  // gacl.class.php is intalled.
  //
  // $phpgacl_location = "/var/www/phpgacl";

  // Tentatively, the following Access Control Objects will be supported.
  // These are the "things to be protected":
  //
  // Section "admin" (Administration):
  //   acl         Access Control Administration
  //   calendar    Calendar Settings
  //   database    Database Reporting
  //   forms       Forms Administration
  //   practice    Practice Settings
  //   superbill   Superbill Codes Administration
  //   users       Users/Groups/Logs Administration
  //
  // Section "acct" (Accounting):
  //   bill        Billing (write optional)
  //   eob         EOB data entry
  //   rep         Financial Reporting - my encounters
  //   rep_a       Financial Reporting - anything
  //
  // Section "patients" (Patient Information):
  //   appt        Appointments (write optional)
  //   demo        Demographics (write optional)
  //   med         Medical Records and History (write optional)
  //   trans       Transactions, e.g. referrals (write optional)
  //   docs        Documents (write optional)
  //
  // Section "encounters" (Encounter Information):
  //   auth        Authorize - my encounters
  //   auth_a      Authorize - any encounters
  //   coding      Coding - my encounters (write,wsome optional)
  //   coding_a    Coding - any encounters (write,wsome optional)
  //   notes       Notes - my encounters (write optional)
  //   notes_a     Notes - any encounters (write optional)
  //   date_a      Fix encounter dates - any encounters

  if ($phpgacl_location) {
    include_once("$phpgacl_location/gacl.class.php");
    $gacl_object = new gacl();
  }

  // acl_check should return 0 if access is denied.  Otherwise it may
  // return anything that evaluates to true.  In addition if any of the
  // following types of access are applicable, then the corresponding value
  // must be returned if and only if such access is granted:
  //
  // * write - the user may add or modify the ACO
  // * wsome - the user has limited add/modify access to the ACO
  //
  function acl_check($section, $value, $user = '') {
    global $gacl_object, $phpgacl_location;
    if (! $user) $user = $_SESSION['authUser'];

    if ($phpgacl_location) {
      return $gacl_object->acl_check($section, $value, 'users', $user);
    }

    // If no phpgacl, then apply the old static rules whereby "authorized"
    // users (providers) can do anything, and other users can do most things.

    if ($_SESSION['userauthorized']) return 'write';

    if ($section == 'patients') {
      if ($value != 'med') return 'write';
    }
    else if ($section == 'encounters') {
      if (strpos($value, 'coding') === 0) return 'write';
      if (strpos($value, 'notes' ) === 0) return 'write';
    }
    else if ($section == 'acct') {
      return 'write';
    }

    return 0;
  }
?>
