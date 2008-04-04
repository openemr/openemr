<?php
/*
	PHP LDAP CLASS FOR MANIPULATING ACTIVE DIRECTORY
	Version 2.1
	
	Written by Scott Barnett
	email: scott@wiggumworld.com
	http://adldap.sourceforge.net/
	
	Copyright (C) 2006-2007 Scott Barnett
	
	I'd appreciate any improvements or additions to be submitted back
	to benefit the entire community :)
	
	Works with PHP 5, should be fine with PHP 4, let me know if/where it doesn't :)
	
	Please visit the project website for a full list of the functions and
	documentation on using them.
	http://adldap.sourceforge.net/documentation.php
	
	This library is free software; you can redistribute it and/or
	modify it under the terms of the GNU Lesser General Public
	License as published by the Free Software Foundation; either
	version 2.1 of the License, or (at your option) any later version.
	
	This library is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	Lesser General Public License for more details.

	********************************************************************
	Something to keep in mind is that Active Directory is a permissions
	based directory. If you bind as a domain user, you can't fetch as
	much information on other users as you could as a domain admin.
	********************************************************************
*/

// Different type of accounts in AD
define ('ADLDAP_NORMAL_ACCOUNT', 805306368);
define ('ADLDAP_WORKSTATION_TRUST', 805306369);
define ('ADLDAP_INTERDOMAIN_TRUST', 805306370);
define ('ADLDAP_SECURITY_GLOBAL_GROUP', 268435456);
define ('ADLDAP_DISTRIBUTION_GROUP', 268435457);
define ('ADLDAP_SECURITY_LOCAL_GROUP', 536870912);
define ('ADLDAP_DISTRIBUTION_LOCAL_GROUP', 536870913);

class adLDAP {
	// BEFORE YOU ASK A QUESTION, PLEASE READ THE DOCUMENTATION AND THE FAQ
	// http://adldap.sourceforge.net/documentation.php
	// http://adldap.sourceforge.net/faq.php

	// You can set your default variables here, or when you invoke the class
	var $_account_suffix="";
	var $_base_dn = "";
	
	// An array of domain controllers. Specify multiple controllers if you 
	// would like the class to balance the LDAP queries amongst multiple servers
	var $_domain_controllers = array ("");
	
	// optional account with higher privileges for searching
	// not really that optional because you can't query much as a user
	var $_ad_username="";
	var $_ad_password="";
	
	// AD does not return the primary group. http://support.microsoft.com/?kbid=321360
	// This tweak will resolve the real primary group, but may be resource intensive. 
	// Setting to false will fudge "Domain Users" and is much faster. Keep in mind though that if
	// someone's primary group is NOT domain users, this is obviously going to bollocks the results
	var $_real_primarygroup=true;
	
	// Use SSL, your server needs to be setup, please see - http://adldap.sourceforge.net/ldap_ssl.php
	var $_use_ssl=false;
	
	// When querying group memberships, do it recursively
	// eg. User Fred is a member of Group A, which is a member of Group B, which is a member of Group C
	// user_ingroup("Fred","C") will returns true with this option turned on, false if turned off
	var $_recursive_groups=true;

	// You should not need to edit anything below this line
	//******************************************************************************************
	
	//other variables
	var $_conn;
	var $_bind;

	// default constructor
	function adLDAP($options=array()){
		//you can specifically overide any of the default configuration options setup above
		if (count($options)>0){
			if (array_key_exists("account_suffix",$options)){ $this->_account_suffix=$options["account_suffix"]; }
			if (array_key_exists("base_dn",$options)){ $this->_base_dn=$options["base_dn"]; }
			if (array_key_exists("domain_controllers",$options)){ $this->_domain_controllers=$options["domain_controllers"]; }
			if (array_key_exists("ad_username",$options)){ $this->_ad_username=$options["ad_username"]; }
			if (array_key_exists("ad_password",$options)){ $this->_ad_password=$options["ad_password"]; }
			if (array_key_exists("real_primarygroup",$options)){ $this->_real_primarygroup=$options["real_primarygroup"]; }
			if (array_key_exists("use_ssl",$options)){ $this->_use_ssl=$options["use_ssl"]; }
			if (array_key_exists("recursive_groups",$options)){ $this->_recursive_groups=$options["recursive_groups"]; }
		}
	
		//connect to the LDAP server as the username/password
		$dc=$this->random_controller();
		if ($this->_use_ssl){
			$this->_conn = ldap_connect("ldaps://".$dc);
		} else {
			$this->_conn = ldap_connect($dc);
		}
		
		//set some ldap options for talking to AD
		ldap_set_option($this->_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->_conn, LDAP_OPT_REFERRALS, 0);
		
		//bind as a domain admin if they've set it up
		if ($this->_ad_username!=NULL && $this->_ad_password!=NULL){
			$this->_bind = @ldap_bind($this->_conn,$this->_ad_username.$this->_account_suffix,$this->_ad_password);
			if (!$this->_bind){
				if ($this->_use_ssl){
					//if you have problems troubleshooting, remove the @ character from the ldap_bind command above to get the actual error message
					echo ("FATAL: AD bind failed. Either the LDAPS connection failed or the login credentials are incorrect."); exit();
				} else {
					echo ("FATAL: AD bind failed. Check the login credentials."); exit();
				}
			}
		}
		
		return (true);
	}

	// default destructor
	function __destruct(){ ldap_close ($this->_conn); }

	//validate a users login credentials
	function authenticate($username,$password,$prevent_rebind=false){
		if ($username==NULL || $password==NULL){ return (false); } //prevent null binding
		
		//bind as the user		
		$this->_bind = @ldap_bind($this->_conn,$username.$this->_account_suffix,$password);
		if (!$this->_bind){ return (false); }
		
		//once we've checked their details, kick back into admin mode if we have it
		if ($this->_ad_username!=NULL && !$prevent_rebind){
			$this->_bind = @ldap_bind($this->_conn,$this->_ad_username.$this->_account_suffix,$this->_ad_password);
			if (!$this->_bind){ echo ("FATAL: AD rebind failed."); exit(); } //this should never happen in theory
		}
		
		return (true);
	}

	//*****************************************************************************************************************
	// GROUP FUNCTIONS

	// Add a group to a group
	function group_add_group($parent,$child){

		//find the parent group's dn
		$parent_group=$this->group_info($parent,array("cn"));
		if ($parent_group[0]["dn"]==NULL){ return (false); }
		$parent_dn=$parent_group[0]["dn"];
		
		//find the child group's dn
		$child_group=$this->group_info($child,array("cn"));
		if ($child_group[0]["dn"]==NULL){ return (false); }
		$child_dn=$child_group[0]["dn"];
				
		$add=array();
		$add["member"] = $child_dn;
		
		$result=@ldap_mod_add($this->_conn,$parent_dn,$add);
		if ($result==false){ return (false); }
		return (true);
	}
	
	// Add a user to a group
	function group_add_user($group,$user){
		//adding a user is a bit fiddly, we need to get the full DN of the user
		//and add it using the full DN of the group
		
		//find the user's dn
		$user_info=$this->user_info($user,array("cn"));
		if ($user_info[0]["dn"]==NULL){ return (false); }
		$user_dn=$user_info[0]["dn"];
		
		//find the group's dn
		$group_info=$this->group_info($group,array("cn"));
		if ($group_info[0]["dn"]==NULL){ return (false); }
		$group_dn=$group_info[0]["dn"];
		
		$add=array();
		$add["member"] = $user_dn;
		
		$result=@ldap_mod_add($this->_conn,$group_dn,$add);
		if ($result==false){ return (false); }
		return (true);
	}

	// Create a group
	function group_create($attributes){
		if (!is_array($attributes)){ return ("Attributes must be an array"); }
		if (!array_key_exists("group_name",$attributes)){ return ("Missing compulsory field [group_name]"); }
		if (!array_key_exists("container",$attributes)){ return ("Missing compulsory field [container]"); }
		if (!array_key_exists("description",$attributes)){ return ("Missing compulsory field [description]"); }
		if (!is_array($attributes["container"])){ return ("Container attribute must be an array."); }
		$attributes["container"]=array_reverse($attributes["container"]);

		//$member_array = array();
		//$member_array[0] = "cn=user1,cn=Users,dc=yourdomain,dc=com";
		//$member_array[1] = "cn=administrator,cn=Users,dc=yourdomain,dc=com";
		
		$add=array();
		$add["cn"] = $attributes["group_name"];
		$add["samaccountname"] = $attributes["group_name"];
		$add["objectClass"] = "Group";
		$add["description"] = $attributes["description"];
		//$add["member"] = $member_array; UNTESTED

		$container="OU=".implode(",OU=",$attributes["container"]);
		$result=ldap_add($this->_conn,"CN=".$add["cn"].", ".$container.",".$this->_base_dn,$add);
		if ($result!=true){ return (false); }
		
		return (true);
	}

	// Remove a group from a group
	function group_del_group($parent,$child){
	
		//find the parent dn
		$parent_group=$this->group_info($parent,array("cn"));
		if ($parent_group[0]["dn"]==NULL){ return (false); }
		$parent_dn=$parent_group[0]["dn"];
		
		//find the child dn
		$child_group=$this->group_info($child,array("cn"));
		if ($child_group[0]["dn"]==NULL){ return (false); }
		$child_dn=$child_group[0]["dn"];
		
		$del=array();
		$del["member"] = $child_dn;
		
		$result=@ldap_mod_del($this->_conn,$parent_dn,$del);
		if ($result==false){ return (false); }
		return (true);
	}
	
	// Remove a user from a group
	function group_del_user($group,$user){
	
		//find the parent dn
		$group_info=$this->group_info($group,array("cn"));
		if ($group_info[0]["dn"]==NULL){ return (false); }
		$group_dn=$group_info[0]["dn"];
		
		//find the child dn
		$user_info=$this->user_info($user,array("cn"));
		if ($user_info[0]["dn"]==NULL){ return (false); }
		$user_dn=$user_info[0]["dn"];

		$del=array();
		$del["member"] = $user_dn;
		
		$result=@ldap_mod_del($this->_conn,$group_dn,$del);
		if ($result==false){ return (false); }
		return (true);
	}
	
	// Returns an array of information for a specified group
	function group_info($group_name,$fields=NULL){
		if ($group_name==NULL){ return (false); }
		if (!$this->_bind){ return (false); }
		
		$filter="(&(objectCategory=group)(name=".$this->ldap_slashes($group_name)."))";
		//echo ($filter."!!!<br>");
		if ($fields==NULL){ $fields=array("member","memberof","cn","description","distinguishedname","objectcategory","samaccountname"); }
		$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
		$entries = ldap_get_entries($this->_conn, $sr);
		//print_r($entries);
		return ($entries);
	}
	
	// Retun a complete list of "groups in groups"	
	function recursive_groups($group){
		if ($group==NULL){ return (false); }

		$ret_groups=array();
		
		$groups=$this->group_info($group,array("memberof"));
		$groups=$groups[0]["memberof"];

		if ($groups){
			$group_names=$this->nice_names($groups);
			$ret_groups=array_merge($ret_groups,$group_names); //final groups to return
			
			foreach ($group_names as $id => $group_name){
				$child_groups=$this->recursive_groups($group_name);
				$ret_groups=array_merge($ret_groups,$child_groups);
			}
		}

		return ($ret_groups);
	}
	
	//*****************************************************************************************************************
	// USER FUNCTIONS

	//create a user
	function user_create($attributes){
		//check for compulsory fields
		if (!array_key_exists("username",$attributes)){ return ("Missing compulsory field [username]"); }
		if (!array_key_exists("firstname",$attributes)){ return ("Missing compulsory field [firstname]"); }
		if (!array_key_exists("surname",$attributes)){ return ("Missing compulsory field [surname]"); }
		if (!array_key_exists("email",$attributes)){ return ("Missing compulsory field [email]"); }
		if (!array_key_exists("container",$attributes)){ return ("Missing compulsory field [container]"); }
		if (!is_array($attributes["container"])){ return ("Container attribute must be an array."); }

		if (array_key_exists("password",$attributes) && !$this->_use_ssl){ echo ("FATAL: SSL must be configured on your webserver and enabled in the class to set passwords."); exit(); }

		if (!array_key_exists("display_name",$attributes)){ $attributes["display_name"]=$attributes["firstname"]." ".$attributes["surname"]; }

		//translate the schema
		$add=$this->adldap_schema($attributes);
		
		//additional stuff only used for adding accounts
		$add["cn"][0]=$attributes["display_name"];
		$add["samaccountname"][0]=$attributes["username"];
		$add["objectclass"][0]="top";
		$add["objectclass"][1]="person";
		$add["objectclass"][2]="organizationalPerson";
		$add["objectclass"][3]="user"; //person?
		//$add["name"][0]=$attributes["firstname"]." ".$attributes["surname"];

		//set the account control attribute
		$control_options=array("NORMAL_ACCOUNT");
		if (!$attributes["enabled"]){ $control_options[]="ACCOUNTDISABLE"; }
		$add["userAccountControl"][0]=$this->account_control($control_options);
		//echo ("<pre>"); print_r($add);

		//determine the container
		$attributes["container"]=array_reverse($attributes["container"]);
		$container="OU=".implode(",OU=",$attributes["container"]);

		//add the entry
		$result=@ldap_add($this->_conn, "CN=".$add["cn"][0].", ".$container.",".$this->_base_dn, $add);
		if ($result!=true){ return (false); }
		
		return (true);
	}

	// user_groups($user)
	//	Returns an array of groups that a user is a member off
	function user_groups($username,$recursive=NULL){
		if ($username==NULL){ return (false); }
		if ($recursive==NULL){ $recursive=$this->_recursive_groups; } //use the default option if they haven't set it
		if (!$this->_bind){ return (false); }
		
		//search the directory for their information
		$info=@$this->user_info($username,array("memberof","primarygroupid"));
		$groups=$this->nice_names($info[0]["memberof"]); //presuming the entry returned is our guy (unique usernames)

		if ($recursive){
			foreach ($groups as $id => $group_name){
				$extra_groups=$this->recursive_groups($group_name);
				$groups=array_merge($groups,$extra_groups);
			}
		}
		
		return ($groups);
	}

	// Returns an array of information for a specific user
	function user_info($username,$fields=NULL){
		if ($username==NULL){ return (false); }
		if (!$this->_bind){ return (false); }

		$filter="samaccountname=".$username;
		if ($fields==NULL){ $fields=array("samaccountname","mail","memberof","department","displayname","telephonenumber","primarygroupid"); }
		$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
		$entries = ldap_get_entries($this->_conn, $sr);
		
		// AD does not return the primary group in the ldap query, we may need to fudge it
		if ($this->_real_primarygroup){
			$entries[0]["memberof"][]=$this->group_cn($entries[0]["primarygroupid"][0]);
		} else {
			$entries[0]["memberof"][]="CN=Domain Users,CN=Users,".$this->_base_dn;
		}
		
		$entries[0]["memberof"]["count"]++;
		return ($entries);
	}
	
	// Returns true if the user is a member of the group
	function user_ingroup($username,$group,$recursive=NULL){
		if ($username==NULL){ return (false); }
		if ($group==NULL){ return (false); }
		if (!$this->_bind){ return (false); }
		if ($recursive==NULL){ $recursive=$this->_recursive_groups; } //use the default option if they haven't set it
		
		//get a list of the groups
		$groups=$this->user_groups($username,array("memberof"),$recursive);
		
		//return true if the specified group is in the group list
		if (in_array($group,$groups)){ return (true); }

		return (false);
	}
	
	//modify a user
	function user_modify($username,$attributes){
		if ($username==NULL){ return ("Missing compulsory field [username]"); }
		if (array_key_exists("password",$attributes) && !$this->_use_ssl){ echo ("FATAL: SSL must be configured on your webserver and enabled in the class to set passwords."); exit(); }
		//if (array_key_exists("container",$attributes)){
			//if (!is_array($attributes["container"])){ return ("Container attribute must be an array."); }
			//$attributes["container"]=array_reverse($attributes["container"]);
		//}

		//find the dn of the user
		$user=$this->user_info($username,array("cn"));
		if ($user[0]["dn"]==NULL){ return (false); }
		$user_dn=$user[0]["dn"];

		//translate the update to the LDAP schema				
		$mod=$this->adldap_schema($attributes);
		if (!$mod){ return (false); }

		//set the account control attribute (only if specified)
		if (array_key_exists("enabled",$attributes)){
			if ($attributes["enabled"]){ $control_options=array("NORMAL_ACCOUNT"); }
			else { $control_options=array("NORMAL_ACCOUNT","ACCOUNTDISABLE"); }
			$mod["userAccountControl"][0]=$this->account_control($control_options);
		}

		//do the update
		$result=ldap_modify($this->_conn,$user_dn,$mod);
		if ($result==false){ return (false); }
		
		return (true);
	}
		
	// Set the password of a user
	function user_password($username,$password){
		if ($username==NULL){ return (false); }
		if ($password==NULL){ return (false); }
		if (!$this->_bind){ return (false); }
		if (!$this->_use_ssl){ echo ("FATAL: SSL must be configured on your webserver and enabled in the class to set passwords."); exit(); }
		
		$user=$this->user_info($username,array("cn"));
		if ($user[0]["dn"]==NULL){ return (false); }
		$user_dn=$user[0]["dn"];
				
		$add=array();
		$add["unicodePwd"][0]=$this->encode_password($password);
		
		$result=ldap_mod_replace($this->_conn,$user_dn,$add);
		if ($result==false){ return (false); }
		
		return (true);
	}

	//*****************************************************************************************************************
	// COMPUTER FUNCTIONS
	
	// Returns an array of information for a specific computer
	function computer_info($computer_name,$fields=NULL){
		if ($computer_name==NULL){ return (false); }
		if (!$this->_bind){ return (false); }

		$filter="(&(objectClass=computer)(cn=".$computer_name."))";
		if ($fields==NULL){ $fields=array("memberof","cn","displayname","dnshostname","distinguishedname","objectcategory","operatingsystem","operatingsystemservicepack","operatingsystemversion"); }
		$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
		$entries = ldap_get_entries($this->_conn, $sr);
		
		return ($entries);
	}

	// Returns all AD users
	function all_users($include_desc = false, $search = "*", $sorted = true){
		if (!$this->_bind){ return (false); }
		
		//perform the search and grab all their details
		$filter = "(&(objectClass=user)(samaccounttype=". ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=".$search."))";
		$fields=array("samaccountname","displayname");
		$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
		$entries = ldap_get_entries($this->_conn, $sr);

		$users_array = array();
		for ($i=0; $i<$entries["count"]; $i++){
			if ($include_desc && strlen($entries[$i]["displayname"][0])>0){
				$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["displayname"][0];
			} elseif ($include_desc){
				$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
			} else {
				array_push($users_array, $entries[$i]["samaccountname"][0]);
			}
		}
		if ($sorted){ asort($users_array); }
		return ($users_array);
	}

        /*
         * custom LDAP search returning AD users
         */
	function find_users($include_desc = false, $filter = "*", $sorted = true){
		if (!$this->_bind){ return (false); }
		
		//perform the search and grab all their details
		//$filter = "(&(objectClass=user)(samaccounttype=". ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=".$search."))";
		$fields=array("samaccountname","displayname");
		$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
		$entries = ldap_get_entries($this->_conn, $sr);

		$users_array = array();
		for ($i=0; $i<$entries["count"]; $i++){
			if ($include_desc && strlen($entries[$i]["displayname"][0])>0){
				$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["displayname"][0];
			} elseif ($include_desc){
				$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
			} else {
				array_push($users_array, $entries[$i]["samaccountname"][0]);
			}
		}
		if ($sorted){ asort($users_array); }
		return ($users_array);
	}
	
	// Returns a complete list of the groups in AD
	function all_groups($include_desc = false, $search = "*", $sorted = true){
		if (!$this->_bind){ return (false); }
		
		//perform the search and grab all their details
		$filter = "(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP .")(cn=".$search."))";
		$fields=array("samaccountname","description");
		$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
		$entries = ldap_get_entries($this->_conn, $sr);

		$groups_array = array();		
		for ($i=0; $i<$entries["count"]; $i++){
			if ($include_desc && strlen($entries[$i]["description"][0]) > 0 ){
				$groups_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["description"][0];
			} elseif ($include_desc){
				$groups_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
			} else {
				array_push($groups_array, $entries[$i]["samaccountname"][0]);
			}
		}
		if( $sorted ){ asort($groups_array); }
		return ($groups_array);
	}

	//************************************************************************************************************
	// UTILITY FUNCTIONS (not intended to be called directly but I spose you could?)

	function adldap_schema($attributes){
	
		//ldap doesn't like NULL attributes, only set them if they have values
		// I'd like to know how to set an LDAP attribute to NULL though, at the moment I set it to a space
		$mod=array();
		if ($attributes["address_city"]){ $mod["l"][0]=$attributes["address_city"]; }
		if ($attributes["address_code"]){ $mod["postalCode"][0]=$attributes["address_code"]; }
		//if ($attributes["address_country"]){ $mod["countryCode"][0]=$attributes["address_country"]; } // use country codes?
		if ($attributes["address_pobox"]){ $mod["postOfficeBox"][0]=$attributes["address_pobox"]; }
		if ($attributes["address_state"]){ $mod["st"][0]=$attributes["address_state"]; }
		if ($attributes["address_street"]){ $mod["streetAddress"][0]=$attributes["address_street"]; }
		if ($attributes["company"]){ $mod["company"][0]=$attributes["company"]; }
		if ($attributes["change_password"]){ $mod["pwdLastSet"][0]=0; }
		if ($attributes["company"]){ $mod["company"][0]=$attributes["company"]; }
		if ($attributes["department"]){ $mod["department"][0]=$attributes["department"]; }
		if ($attributes["description"]){ $mod["description"][0]=$attributes["description"]; }
		if ($attributes["display_name"]){ $mod["displayName"][0]=$attributes["display_name"]; }
		if ($attributes["email"]){ $mod["mail"][0]=$attributes["email"]; }
		if ($attributes["expires"]){ $mod["accountExpires"][0]=$attributes["expires"]; } //unix epoch format?
		if ($attributes["firstname"]){ $mod["givenName"][0]=$attributes["firstname"]; }
		if ($attributes["home_directory"]){ $mod["homeDirectory"][0]=$attributes["home_directory"]; }
		if ($attributes["home_drive"]){ $mod["homeDrive"][0]=$attributes["home_drive"]; }
		if ($attributes["initials"]){ $mod["initials"][0]=$attributes["initials"]; }
		if ($attributes["logon_name"]){ $mod["userPrincipalName"][0]=$attributes["logon_name"]; }
		if ($attributes["manager"]){ $mod["manager"][0]=$attributes["manager"]; }  //UNTESTED ***Use DistinguishedName***
		if ($attributes["office"]){ $mod["physicalDeliveryOfficeName"][0]=$attributes["office"]; }
		if ($attributes["password"]){ $mod["unicodePwd"][0]=$this->encode_password($attributes["password"]); }
		if ($attributes["profile_path"]){ $mod["profilepath"][0]=$attributes["profile_path"]; }
		if ($attributes["script_path"]){ $mod["scriptPath"][0]=$attributes["script_path"]; }
		if ($attributes["surname"]){ $mod["sn"][0]=$attributes["surname"]; }
		if ($attributes["title"]){ $mod["title"][0]=$attributes["title"]; }
		if ($attributes["telephone"]){ $mod["telephoneNumber"][0]=$attributes["telephone"]; }
		if ($attributes["web_page"]){ $mod["wWWHomePage"][0]=$attributes["web_page"]; }
		
                // what if we actually use the REAL LDAP attribute names?! -- JRM

                if ($attributes["givenname"]){ $mod["givenname"][0]=$attributes["givenname"]; } //JRM
                if ($attributes["sn"]){ $mod["sn"][0]=$attributes["sn"]; } //JRM
                if ($attributes["displayname"]){ $mod["displayname"][0]=$attributes["displayname"]; } //JRM
                if ($attributes["physicaldeliveryofficename"]){ $mod["physicaldeliveryofficename"][0]=$attributes["physicaldeliveryofficename"]; } //JRM
                if ($attributes["homephone"]){ $mod["homephone"][0]=$attributes["homephone"]; } //JRM
                if ($attributes["telephonenumber"]){ $mod["telephonenumber"][0]=$attributes["telephonenumber"]; } //JRM
                if ($attributes["mobile"]){ $mod["mobile"][0]=$attributes["mobile"]; } //JRM
                if ($attributes["pager"]){ $mod["pager"][0]=$attributes["pager"]; } //JRM
                if ($attributes["facsimiletelephonenumber"]){ $mod["facsimiletelephonenumber"][0]=$attributes["facsimiletelephonenumber"]; } //JRM
                if ($attributes["mail"]){ $mod["mail"][0]=$attributes["mail"]; } //JRM
                if ($attributes["title"]){ $mod["title"][0]=$attributes["title"]; } //JRM
                if ($attributes["department"]){ $mod["department"][0]=$attributes["department"]; } //JRM
                if ($attributes["streetaddress"]){ $mod["streetaddress"][0]=$attributes["streetaddress"]; } //JRM
                if ($attributes["postofficebox"]){ $mod["postofficebox"][0]=$attributes["postofficebox"]; } //JRM
                if ($attributes["l"]){ $mod["l"][0]=$attributes["l"]; } //JRM
                if ($attributes["st"]){ $mod["st"][0]=$attributes["st"]; } //JRM
                if ($attributes["postalcode"]){ $mod["postalcode"][0]=$attributes["postalcode"]; } //JRM
		
                //echo ("<pre>"); print_r($mod);
/*
		// modifying a name is a bit fiddly
		if ($attributes["firstname"] && $attributes["surname"]){
			$mod["cn"][0]=$attributes["firstname"]." ".$attributes["surname"];
			$mod["displayname"][0]=$attributes["firstname"]." ".$attributes["surname"];
			$mod["name"][0]=$attributes["firstname"]." ".$attributes["surname"];
		}
*/
		if (count($mod)==0){ return (false); }
		return ($mod);
	}


	function group_cn($gid){
		// coping with AD not returning the primary group
		// http://support.microsoft.com/?kbid=321360
		// for some reason it's not possible to search on primarygrouptoken=XXX
		// if someone can show otherwise, I'd like to know about it :)
		// this way is resource intensive and generally a pain in the @#%^
		
		if ($gid==NULL){ return (false); }
		$r=false;
		
		$filter="(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP ."))";
		$fields=array("primarygrouptoken","samaccountname","distinguishedname");
		$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
		$entries = ldap_get_entries($this->_conn, $sr);
		
		for ($i=0; $i<$entries["count"]; $i++){
			if ($entries[$i]["primarygrouptoken"][0]==$gid){
				$r=$entries[$i]["distinguishedname"][0];
				$i=$entries["count"];
			}
		}

		return ($r);
	}

	// Encode a password for transmission over LDAP
	function encode_password($password){
		$password="\"".$password."\"";
		$encoded="";
		for ($i=0; $i <strlen($password); $i++){ $encoded.="{$password{$i}}\000"; }
		return ($encoded);
	}
	
	// Escape bad characters
	// DEVELOPERS SHOULD BE DOING PROPER FILTERING IF THEY'RE ACCEPTING USER INPUT
	// this is just a list of characters with known problems and I'm trying not to strip out other languages
	function ldap_slashes($str){
		$illegal=array("(",")","#"); // the + character has problems too, but it's an illegal character
		
		$legal=array();
		foreach ($illegal as $id => $char){ $legal[$id]="\\".$char; } //make up the array of legal chars
		
		$str=str_replace($illegal,$legal,$str); //replace them
		return ($str);
	}
	
	// Return a random controller
	function random_controller(){
		//select a random domain controller
		mt_srand(doubleval(microtime()) * 100000000); // for older php versions
		return ($this->_domain_controllers[array_rand($this->_domain_controllers)]);
	}
	
	function account_control($options){
		$val=0;

		if (is_array($options)){
			if (in_array("SCRIPT",$options)){ $val=$val+1; }
			if (in_array("ACCOUNTDISABLE",$options)){ $val=$val+2; }
			if (in_array("HOMEDIR_REQUIRED",$options)){ $val=$val+8; }
			if (in_array("LOCKOUT",$options)){ $val=$val+16; }
			if (in_array("PASSWD_NOTREQD",$options)){ $val=$val+32; }
			//PASSWD_CANT_CHANGE Note You cannot assign this permission by directly modifying the UserAccountControl attribute.
			//For information about how to set the permission programmatically, see the "Property flag descriptions" section.
			if (in_array("ENCRYPTED_TEXT_PWD_ALLOWED",$options)){ $val=$val+128; }
			if (in_array("TEMP_DUPLICATE_ACCOUNT",$options)){ $val=$val+256; }
			if (in_array("NORMAL_ACCOUNT",$options)){ $val=$val+512; }
			if (in_array("INTERDOMAIN_TRUST_ACCOUNT",$options)){ $val=$val+2048; }
			if (in_array("WORKSTATION_TRUST_ACCOUNT",$options)){ $val=$val+4096; }
			if (in_array("SERVER_TRUST_ACCOUNT",$options)){ $val=$val+8192; }
			if (in_array("DONT_EXPIRE_PASSWORD",$options)){ $val=$val+65536; }
			if (in_array("MNS_LOGON_ACCOUNT",$options)){ $val=$val+131072; }
			if (in_array("SMARTCARD_REQUIRED",$options)){ $val=$val+262144; }
			if (in_array("TRUSTED_FOR_DELEGATION",$options)){ $val=$val+524288; }
			if (in_array("NOT_DELEGATED",$options)){ $val=$val+1048576; }
			if (in_array("USE_DES_KEY_ONLY",$options)){ $val=$val+2097152; }
			if (in_array("DONT_REQ_PREAUTH",$options)){ $val=$val+4194304; } 
			if (in_array("PASSWORD_EXPIRED",$options)){ $val=$val+8388608; }
			if (in_array("TRUSTED_TO_AUTH_FOR_DELEGATION",$options)){ $val=$val+16777216; }
		}
		return ($val);
	}
	
	// Take an ldap query and return the nice names, without all the LDAP prefixes (eg. CN, DN)
	function nice_names($groups){

		$group_array=array();
		for ($i=0; $i<$groups["count"]; $i++){ //for each group
			$line=$groups[$i];
			
			if (strlen($line)>0){ 
				//more presumptions, they're all prefixed with CN=
				//so we ditch the first three characters and the group
				//name goes up to the first comma
				$bits=explode(",",$line);
				$group_array[]=substr($bits[0],3,(strlen($bits[0])-3));
			}
		}
		return ($group_array);	
	}

        // return the most recent LDAP error
        function LastError() {
            return ldap_error($this->_conn);
        }
	
} // End class

?>
