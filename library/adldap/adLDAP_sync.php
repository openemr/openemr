<?php
/*
 * Syncronize users with the Active Directory
 * - read user names and info from Active Directory
 * - update the Users table in OpenEMR
 * - handles deleted usernames
 * - handles new usernames
 *
 * 12 Dec 2007 - Jason Morrill
 */

require_once("adLDAP.php");
require_once("adLDAP_conf.inc");
require_once("../sql.inc");

/*====================================================
  Usernames to ignore when querying Active Directory
  ** CHANGE THIS ** to accommodate your AD userbase
 *====================================================*/

$excludedUsers = array ("Administrator", "SQLServer", "SQLDebugger", 
                        "TsInternetUser", "someotheruser"
                        );


/*====================================================
 * No changes below here should be necessary 
 *===================================================*/


// the attributes we pull from Active Directory
$ldapAttributes = array("givenname", "sn", "displayname", 
                        "physicaldeliveryofficename", "homephone",
                        "telephonenumber", "mobile", "pager",
                        "facsimiletelephonenumber", "mail", "title",
                        "department", "streetaddress", "postofficebox",
                        "l", "st", "postalcode"
                        );

// mapping of Active Directory attributes to OpenEMR Users table columns
$attributeMapping = array (
                        "givenname" => "fname"
                        ,"sn" => "lname"
                        //,"displayname" => ""
                        //,"physicaldeliveryofficename" => ""
                        //,"homephone"  => ""
                        ,"telephonenumber" => "phonew1"
                        ,"mobile" => "phonecell"
                        //,"pager" => ""
                        ,"facsimiletelephonenumber" => "fax"
                        ,"mail" => "email"
                        ,"title" => "specialty"
                        //,"department" => ""
                        ,"streetaddress" => "street"
                        ,"postofficebox" => "streetb"
                        ,"l" => "city"
                        ,"st" => "state"
                        ,"postalcode" => "zip"
                    );

// create new instance and connect to AD with user & pass
// defined in adLDAP_conf.inc 
$adldap = new adLDAP($adldap_options);

// gather all our known usernames from OpenEMR
// they will be used to compare what is found in Active Directory
$oemrUsers = array();
$sqlH = sqlStatement("select id, username from users");
while ($onerow = sqlFetchArray($sqlH)) { array_push($oemrUsers, $onerow); }

$adUsers = $adldap->all_users();
foreach ($adUsers as $adUser) {
    // loop over all the Active Directory users

    // skip the excluded usernames
    $skip = 0;
    foreach ($excludedUsers as $ex) {
        if ($ex == $adUser) { $skip = 1; break; }
    }
    if ($skip == 1) { continue; }

    // query LDAP for the full user info
    $userInfo = $adldap->user_info($adUser, $ldapAttributes);

    if (NewUser($adUser, $oemrUsers)) {
        // add new user
        echo "Adding user $adUser";
        if (AddUser($adUser, $userInfo)) { echo ", OK\n"; }
        else { echo ", FAILED\n"; }
    }
    else {
        // update existing users with Active Directory info
        echo "existing user $adUser";
        if (UpdateUser($adUser, $userInfo)) { echo ", OK\n"; }
        else { echo ", FAILED\n"; }
    }
}

// re-query in case we have updated a username in the previous loop
$oemrUsers = array();
$sqlH = sqlStatement("select id, username from users");
while ($onerow = sqlFetchArray($sqlH)) { array_push($oemrUsers, $onerow); }

// for all the usernames in OpenEMR and NOT IN Active Directory
// de-activate them in OpenEMR
foreach ($oemrUsers as $user) {
    $found = false;
    foreach ($adUsers as $adUser) {
        if ($user['username'] == $adUser) { $found = true; break; }
    }
    if ($found == false) {
        $sqlstmt = "update users set active=0 where ".
                    "id=".$user['id'];
        if (sqlStatement($sqlstmt)) { echo "Deactivated ".$user['username']." from OpenEMR\n"; }
        else { echo "Failed to deactivate ".$user['username']." from OpenEMR\n"; }
    }
}

exit;


/*=====================================
  Add a user to the OpenEMR database
  =====================================*/
function AddUser($adUsername, $adLDAPinfo) {
    global $attributeMapping;

    ksort($attributeMapping);
    $sqlstmt = "insert into users (id, username";
    foreach ($attributeMapping as $key=>$value) {
        $sqlstmt .= ", ".$value;
    }
    $sqlstmt .= ") values (null, '".$adUsername."'";
    foreach ($attributeMapping as $key=>$value) {
        $sqlstmt .= ", '".addslashes($adLDAPinfo[0][$key][0])."'";
    }
    $sqlstmt .= ")";
    if (sqlStatement($sqlstmt) == false) { return false; }

    // add the user to the default group
    $sqlstmt = "insert into groups (".
                "name, user ".
                ") values (".
                "'Default'".
                ", '".$adUsername."'".
                ")";
    if (sqlStatement($sqlstmt) == false) { return false; }

    return true;
}


/*=====================================
  Update and existing user in the OpenEMR database
  =====================================*/
function UpdateUser($adUsername, $adLDAPinfo) {
    global $attributeMapping;
    ksort($attributeMapping);

    $sqlstmt = "update users set ";
    $comma = "";
    foreach ($attributeMapping as $key=>$value) {
        $sqlstmt .= $comma . $value . "='". addslashes($adLDAPinfo[0][$key][0])."'";
        $comma = ", ";
    }
    $sqlstmt .= " where username = '".$adUsername."'";

    return sqlStatement($sqlstmt);
}


/*=====================================
  Determine if the supplied username
  exists in the OpenEMR Users table
  =====================================*/
function NewUser($username, $oemrUsers) {
    foreach ($oemrUsers as $user) {
        if ($user['username'] == $username) { return false; }
    }
    return true;
}
?>
