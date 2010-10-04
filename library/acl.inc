<?php
// php-GACL access controls are included in OpenEMR. The below
// function will automatically create the path where gacl.class.php
// can be found. Note that this path can be manually set below
// for users who are using an external version of php-GACL.
// Also note that php-GACL access controls can be turned off
// below.

  $phpgacl_location = dirname(__FILE__).'/../gacl';

//
// If using an external version of phpGACL, then uncomment the following
// line and manually place the path below.  IN THIS CASE YOU MUST ALSO
// COMMENT OUT ABOVB $phpgacl_location ASSIGNMENT ABOVE, OR BACKUPS WILL
// NOT RESTORE PROPERLY!
//
//$phpgacl_location = "/var/www/gacl";
//
// If you want to turn off php-GACL, then uncomment the following line.
// IN THIS CASE YOU MUST ALSO COMMENT OUT ABOVE $phpgacl_location ASSIGNMENT(S)
// ABOVE, OR BACKUPS WILL NOT RESTORE PROPERLY!
//
//unset($phpgacl_location);
//

  // The following Access Control Objects (ACO) are currently supported.
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
  //   acl         ACL Administration
  //
  // Section "acct" (Accounting):
  //   bill        Billing (write optional)
  //   disc        Allowed to discount prices (in Fee Sheet or Checkout form)
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
  //   sign        Sign Lab Results (write,addonly optional)
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
  //
  // Section "lists" (Lists):
  //   default    Default List (write,addonly optional)
  //   state      State List (write,addonly optional)
  //   country    Country List (write,addonly optional)
  //   language   Language List (write,addonly optional)
  //   ethrace    Ethnicity-Race List (write,addonly optional)
  //
  // Section "placeholder" (Placeholder):
  //   filler     Placeholder (Maintains empty ACLs)
  

  if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl.class.php");
    $gacl_object = new gacl();
    //DO NOT CHANGE BELOW VARIABLE
    $section_aro_value = 'users';
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
    global $gacl_object, $phpgacl_location, $section_aro_value;
    if (! $user) $user = $_SESSION['authUser'];

    if ($phpgacl_location) {
      return $gacl_object->acl_check($section, $value, $section_aro_value, $user);
    }

    // If no phpgacl, then apply the old static rules whereby "authorized"
    // users (providers) can do anything, and other users can do most things.
    // If you want custom access control but don't want to mess with phpGACL,
    // then you could customize the code below instead.

    if ($user == 'admin') return 'write';
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
      if (!empty($arr1[$section])) {
        foreach ($arr1[$section] as $value) {
          $odata = $gacl->get_object_data($gacl->get_object_id($section, $value, 'ACO'), 'ACO');
          $arr[$value] = $odata[0];
        }
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

  //
  // Returns true if aco exist
  // Returns false if aco doesn't exist
  //    $section_name = name of section (string)
  //    $aco_name = name of aco (string)
  //
  function aco_exist($section_name, $aco_name) {
   global $phpgacl_location;
   if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl_api.class.php");
    $gacl = new gacl_api();
    $aco_id = $gacl->get_object_id($section_name,  $aco_name, 'ACO');
    if ($aco_id) {
     return true;
    }
   }
   return false;
  }

  //
  // Returns a sorted array of all available Group Titles.
  //
  function acl_get_group_title_list() {
    global $phpgacl_location;
    if (isset ($phpgacl_location)) {
      include_once("$phpgacl_location/gacl_api.class.php");
      $gacl = new gacl_api();
      $parent_id = $gacl->get_root_group_id();
      $arr_group_ids = $gacl->get_group_children($parent_id, 'ARO', 'RECURSE');
      $arr_group_titles = array();
      foreach ($arr_group_ids as $value) {
        $arr_group_data = $gacl->get_group_data($value, 'ARO');
        $arr_group_titles[$value] = $arr_group_data[3];
      }
      sort($arr_group_titles);
      return $arr_group_titles;
    }
    return 0;
  }

  //
  // Returns a sorted array of group Titles that a user belongs to.
  // Returns 0 if does not belong to any group yet.
  //   $user_name = Username, which is login name.
  //
  function acl_get_group_titles($user_name) {
    global $phpgacl_location, $section_aro_value;
    if (isset ($phpgacl_location)) {
      include_once("$phpgacl_location/gacl_api.class.php");
      $gacl = new gacl_api();
      $user_aro_id = $gacl->get_object_id($section_aro_value, $user_name, 'ARO');
      if ($user_aro_id) {
        $arr_group_id = $gacl->get_object_groups($user_aro_id, 'ARO', 'NO_RECURSE');
        if ($arr_group_id) {
          foreach ($arr_group_id as $key => $value) {
            $arr_group_data = $gacl->get_group_data($value, 'ARO');
            $arr_group_titles[$key] =  $arr_group_data[3];
          }
	sort($arr_group_titles);
        return $arr_group_titles;
        }
      }
    }
    return 0;
  }

  //
  // This will place the user aro object into selected group(s)
  // It uses the set_user_aro() function
  //   $username = username (string)
  //   $group = title of group(s) (string or array)
  //
  function add_user_aros($username, $group) {
   $current_user_groups = acl_get_group_titles($username);
   if (!$current_user_groups) {
    $current_user_groups = array();
   }
   if (is_array($group)){
    foreach ($group as $value) {
       if (!in_array($value, $current_user_groups)) { 
        array_push($current_user_groups, $value);
       }
    }
   }
   else {
    if (!in_array($group, $current_user_groups)) {
     array_push($current_user_groups, $group);
    }
   }
   $user_data = mysql_fetch_array(sqlStatement("select * from users where username='" .
    $username . "'"));
   set_user_aro($current_user_groups, $username, $user_data["fname"],
    $user_data["mname"], $user_data["lname"]);
   return;
  }

  //
  // This will remove the user aro object from the selected group(s)
  // It uses the set_user_aro() function
  //   $username = username (string)
  //   $group = title of group(s) (string or array)
  //
  function remove_user_aros($username, $group) {
   $current_user_groups = acl_get_group_titles($username);
   $new_user_groups = array();
   if (is_array($group)){
    foreach ($current_user_groups as $value) {
     if (!in_array($value, $group)) {
      array_push($new_user_groups, $value);
     }
    }
   }
   else {
    foreach ($current_user_groups as $value) {
     if ($value != $group) {
      array_push($new_user_groups, $value);
     }
    }
   }
   $user_data = mysql_fetch_array(sqlStatement("select * from users where username='" .
    $username . "'"));
   set_user_aro($new_user_groups, $username, $user_data["fname"],
    $user_data["mname"], $user_data["lname"]);
   return;
  }

  //
  // This will either create or edit a user aro object, and then place it
  // in the requested groups. It will not allow removal of the 'admin'
  // user from the 'admin' group.
  //   $arr_group_titles = titles of the groups that user will be added to.
  //   $user_name = username, which is login name.
  //   $first_name = first name
  //   $middle_name = middle name
  //   $last_name = last name
  //
  function set_user_aro($arr_group_titles, $user_name, $first_name, $middle_name, $last_name) {
    global $phpgacl_location, $section_aro_value;

    if (isset ($phpgacl_location)) {
      include_once("$phpgacl_location/gacl_api.class.php");
      $gacl = new gacl_api();

      //get array of all available group ID numbers
      $parent_id = $gacl->get_root_group_id();
      $arr_all_group_ids = $gacl->get_group_children($parent_id, 'ARO', 'RECURSE');

      //Cycle through ID array to find and process each selected group
      //Create a counter since processing of first hit is unique
      $counter = 0;
      foreach ($arr_all_group_ids as $value) {
        $arr_group_data = $gacl->get_group_data($value, 'ARO');
        if ((empty($arr_group_titles)) ||
	 (in_array($arr_group_data[3], $arr_group_titles))) {
          //We have a hit, so need to add group and increment counter
          // because processing of first hit is unique
	  //This will also deal with an empty $arr_group_titles array
	  // removing user from all groups unless 'admin'
          $counter = $counter + 1;
          //create user full name field
          if ($middle_name) {
            $full_name = $first_name . " " . $middle_name . " " .  $last_name;
          }
          else {
	    if ($last_name) {
              $full_name = $first_name . " " . $last_name;
	    }
	    else {
	      $full_name = $first_name;
	    }
          }

          //If this is not the first group to be added, then will skip below
          // and will be added. If this is the first group, then need to
          // go thru several steps before adding the group.
          if ($counter == 1) {
            //get ID of user ARO object, if it exist
            $user_aro_id = $gacl->get_object_id($section_aro_value, $user_name, 'ARO');
            if ($user_aro_id) {
              //user ARO object already exist, so will edit it
              $gacl->edit_object($user_aro_id, $section_aro_value, $full_name, $user_name, 10, 0, 'ARO');

              //remove all current user ARO object group associations
              $arr_remove_group_ids = $gacl->get_object_groups($user_aro_id, 'ARO', 'NO_RECURSE');
              foreach ($arr_remove_group_ids as $value2) {
                $gacl->del_group_object($value2, $section_aro_value, $user_name, 'ARO');
              }
            }
            else {
              //user ARO object does not exist, so will create it
              $gacl->add_object($section_aro_value, $full_name, $user_name, 10, 0, 'ARO');
            }
          }

          //place the user ARO object in the selected group (if group(s) is selected)
	  if (!empty($arr_group_titles)) {
            $gacl->add_group_object($value, $section_aro_value, $user_name, 'ARO');
	  }

          //
          //Below will not allow 'admin' user to be removed from 'admin' group
          //
          if ($user_name == 'admin') {
            $boolean_admin=0;
            $admin_id = $gacl->get_object_id($section_aro_value, 'admin', 'ARO');
            $arr_admin = $gacl->get_object_groups($admin_id, 'ARO', 'NO_RECURSE');
            foreach ($arr_admin as $value3) {
              $arr_admin_data = $gacl->get_group_data($value3, 'ARO');
              if (strcmp($arr_admin_data[2], 'admin') == 0) {
                $boolean_admin=1;
              }
            }
            if (!$boolean_admin) {
              foreach ($arr_all_group_ids as $value4) {
                $arr_temp = $gacl->get_group_data($value4, 'ARO');
                if ($arr_temp[2] == 'admin') {
                  $gacl->add_group_object($value4, $section_aro_value, 'admin', 'ARO');
                }
              }
            }
          }
        }
	//if array of groups was empty, then we are done, and can break from loop
	if (empty($arr_group_titles)) break;
      }
    }
   return true;
  }

  //
  // Returns true if acl exist
  // Returns false if acl doesn't exist
  //  EITHER $title or $name is required(send FALSE in variable
  //  not being used). If both are sent, then only $title will be
  //  used.
  //  $return_value is required
  //    $title = title of acl (string)
  //    $name = name of acl (string)
  //    $return_value = return value of acl (string)
  //
  function acl_exist($title, $name, $return_value) {
   global $phpgacl_location;
   if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl_api.class.php");
    $gacl = new gacl_api();
    if (!$name) {
     $acl = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $title, FALSE, FALSE, FALSE, $return_value);
    }
    else if (!$title) {
     $group_id = $gacl->get_group_id($name, NULL, 'ARO');
     if ($group_id) {
      $group_data = $gacl->get_group_data($group_id, 'ARO');
      $acl = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $group_data[3], FALSE, FALSE, FALSE, $return_value);
     }
     else {
     return false;
     }
    }
    else {
     $acl = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $title, FALSE, FALSE, FALSE, $return_value);
    }
    if (!empty($acl)) {
     return true;
    }
    else {
     return false;
    }
   }
  }

  //
  // This will add a new acl and group(if group doesn't yet exist)
  // with one aco in it.
  //   $acl_title = title of acl (string)
  //   $acl_name = name of acl (string)
  //   $return_value = return value of acl (string)
  //   $note = description of acl (array)
  //
  function acl_add($acl_title, $acl_name, $return_value, $note) {
   global $phpgacl_location;
   if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl_api.class.php");
    $gacl = new gacl_api();
    $group_id = $gacl->get_group_id($acl_name, $acl_title, 'ARO');
    if ($group_id) {
     //group already exist, so just create acl
     $gacl->add_acl(array("placeholder"=>array("filler")),
      NULL, array($group_id), NULL, NULL, 1, 1, $return_value, $note);
    }
    else {
     //create group, then create acl
     $parent_id = $gacl->get_root_group_id();
     $aro_id = $gacl->add_group($acl_name, $acl_title, $parent_id, 'ARO');
     $gacl->add_acl(array("placeholder"=>array("filler")),
      NULL, array($aro_id), NULL, NULL, 1, 1, $return_value, $note);
    }
    return;
   }
   return 0;
  }

  //
  // This will remove acl. It will also remove group(if the group
  // is no longer associated with any acl's).
  //   $acl_title = title of acl (string)
  //   $acl_name = name of acl (string)
  //   $return_value = return value of acl (string)
  //   $note = description of acl (array)
  //
  function acl_remove($acl_title, $return_value) {
   global $phpgacl_location;
   if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl_api.class.php");
    $gacl = new gacl_api();
    //First, delete the acl
    $acl_id=$gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $acl_title, FALSE, FALSE, FALSE, $return_value);
    $gacl->del_acl($acl_id[0]);
    //Then, remove the group(if no more acl's are remaining)
    $acl_search=$gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $acl_title, FALSE, FALSE, FALSE, FALSE);
    if (empty($acl_search)){
     $group_id=$gacl-> get_group_id(NULL, $acl_title, 'ARO');
     $gacl->del_group($group_id, TRUE, 'ARO');
    }
    return;
   }
   return 0;
  }

  //
  // This will place the aco(s) into the selected acl
  //   $acl_title = title of acl (string)
  //   $return_value = return value of acl (string)
  //   $aco_id = id of aco (array)
  //
  function acl_add_acos($acl_title, $return_value, $aco_id) {
   global $phpgacl_location;
   if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl_api.class.php");
    $gacl = new gacl_api();
    $acl_id = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $acl_title, FALSE, FALSE, FALSE, $return_value);
    foreach ($aco_id as $value) { 
     $aco_data = $gacl->get_object_data($value, 'ACO');
     $aco_section = $aco_data[0][0];
     $aco_name = $aco_data[0][1];   
     $gacl->append_acl($acl_id[0], NULL, NULL, NULL, NULL, array($aco_section=>array($aco_name)));
    }
    return;
   }
   return 0;
  }

  //
  // This will remove the aco(s) from the selected acl
  //  Note if all aco's are removed, then will place the filler-placeholder
  //  into the acl to avoid complete removal of the acl.
  //   $acl_title = title of acl (string)
  //   $return_value = return value of acl (string)
  //   $aco_id = id of aco (array)
  //
  function acl_remove_acos($acl_title, $return_value, $aco_id) {
   global $phpgacl_location;
   if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl_api.class.php");
    $gacl = new gacl_api();
    $acl_id = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $acl_title, FALSE, FALSE, FALSE, $return_value);

    // Check to see if removing all acos. If removing all acos then will
    //  ensure the filler-placeholder aco in acl to avoid complete
    //  removal of the acl.
    if (count($aco_id) == acl_count_acos($acl_title, $return_value)) {
     //1-get the filler-placeholder aco id
     $filler_aco_id = $gacl->get_object_id('placeholder','filler','ACO');     
     //2-add filler-placeholder aco
     acl_add_acos($acl_title, $return_value, array($filler_aco_id));
     //3-ensure filler-placeholder aco is not to be deleted
     $safeListaco = remove_element($_POST["selection"],$filler_aco_id);
     //4-prepare to safely delete the acos
     $aco_id = $safeListaco;
    }

    foreach ($aco_id as $value) {
     $aco_data = $gacl->get_object_data($value, 'ACO');
     $aco_section = $aco_data[0][0];
     $aco_name = $aco_data[0][1];
     $gacl->shift_acl($acl_id[0], NULL, NULL, NULL, NULL, array($aco_section=>array($aco_name)));
     }
    return;
   }
   return 0;
  }

  //
  // This will return the number of aco objects
  //  in a specified acl.
  //   $acl_title = title of acl (string)
  //   $return_value = return value of acl (string)
  //
  function acl_count_acos($acl_title, $return_value) {
   global $phpgacl_location;
   if (isset ($phpgacl_location)) {
    include_once("$phpgacl_location/gacl_api.class.php");
    $gacl = new gacl_api();
    $acl_id = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $acl_title, FALSE, FALSE, FALSE, $return_value);
    $acl_data = $gacl->get_acl($acl_id[0]);
    $aco_count = 0;
    foreach ($acl_data['aco'] as $key => $value) {
     $aco_count = $aco_count + count($acl_data['aco'][$key]);
    }
    return $aco_count;
   }
   return 0;
  }

  //
  // Function to remove an element from an array
  //
  function remove_element($arr, $val){
   $arr2 = array();
   foreach ($arr as $value){
    if ($value != $val) {
     array_push($arr2,$value);
    }
   }
   return $arr2;
  }
?>
