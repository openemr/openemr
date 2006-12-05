<?php
//----------THINGS WE ALWAYS DO

require_once("{$GLOBALS['srcdir']}/log.inc");
require_once("{$GLOBALS['srcdir']}/sql.inc");

if ($_GET['auth'] == "login" && isset($_POST['authUser']) && isset($_POST['authPass']) && isset($_POST['authProvider']))
{

	if (!authNewSession($_POST['authUser'], $_POST['authPass'], $_POST['authProvider']))
        {

		newEvent("login",$_POST['authUser'], $_POST['authProvider'], "failure");
		authLoginScreen();

	}
	newEvent("login", $_POST['authUser'], $_POST['authProvider'], "success");
	$_SESSION["last_update"] = time(); //store the very first initial timestamp for timeout errors
}elseif ($_GET['auth'] == "logout")
{
	newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], "success");
	authCloseSession();
	authLoginScreen();
}else
{

    if (authCheckSession()) {
        	
		if (isset($_SESSION['pid']))
		{
			require_once("{$GLOBALS['srcdir']}/patient.inc");
			$logpatient = getPatientData($_SESSION['pid'], "lname, fname, mname");
			newEvent("view", $_SESSION['authUser'], $_SESSION['authProvider'], "{$logpatient['lname']}, {$logpatient['fname']} {$logpatient['mname']} :: encounter ".$_SESSION['encounter']);
		}
		//LOG EVERYTHING
		//newEvent("view", $_SESSION['authUser'], $_SESSION['authProvider'], $_SERVER['REQUEST_URI']);
	}
	else {
		newEvent("login",$_POST['authUser'], $_POST['authProvider'], "insufficient data sent");
		authLoginScreen();
	}
}

if (!isset($_SESSION["last_update"])) {
	authLoginScreen();
} else {
	 //if page has not been updated in a given period of time, we call login screen
	if ((time()-$_SESSION["last_update"])>$timeout) {
		newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], "timeout");
		authCloseSession();
		authLoginScreen();
	} else {
		$_SESSION["last_update"] = time();
	}
}



//----------THINGS WE DO IF WE STILL LIKE YOU

function authNewSession ($user, $pass, $provider)
{
	//session_name("OpenEMR");
	//session_id("81279258720".str_replace(".", "", $_SERVER['REMOTE_ADDR']));
	if(!session_id()) {
	  session_start();
	}
	//echo "user is: $user pass is: $pass provider is: $provider<br />";
	$authDB = sqlQuery("select id, password, authorized from users where username = '$user'");
	//echo "<br>auth pass: ".$authDB['password'];
	if ($authDB['password'] == $pass)
	{
	//here, we check to see if the user is in fact a member of the correct group:
		if ($authGroup = sqlQuery("select * from groups where user='$user' and name='$provider'")) {
			$_SESSION['authUser'] = $user;
			$_SESSION['authGroup'] = $authGroup['name'];
			$_SESSION['authUserID'] = $authDB['id'];
			$_SESSION['authPass'] = $pass;
			$_SESSION['authProvider'] = $provider;
			$_SESSION['authId'] = $authDB{'id'};
			$_SESSION['userauthorized'] = $authDB['authorized'];
			return true;
		} else {
			return false;
		}
	}
	else
		return false;
}

function authCheckSession ()
{
	if (isset($_SESSION['authId'])) {
		
		$authDB = sqlQuery("select username, password from users where id = '".$_SESSION['authId']."'");
		if ($_SESSION['authUser'] == $authDB['username'] && $_SESSION['authPass'] == $authDB['password']) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

function authCloseSession ()
{
	ob_start();
	session_unset();
//	$_SESSION = array();
	session_destroy();
	//setcookie(session_name(),"","","/");
	//the following does the same as the above line:
	//if(isset($_COOKIE[session_name()])) {
	//	session_start();
	//	session_destroy();
		unset($_COOKIE[session_name()]);
	//}
}

function authLoginScreen()
{
	//header("Location: https://{$_SERVER['HTTP_HOST']}{$GLOBALS['login_screen']}");
	header("Location: {$GLOBALS['login_screen']}");
	exit;
}

function addUser ($username, $password_md5, $info, $authorized = 'yes')
{
	return sqlInsert("insert into users (username, password, info, authorized) values ('$username', '$password_md5', '$info', '$authorized')");
}

function delUser ($id)
{
	return sqlQuery("delete from users where id = '$id' limit 0,1");
}

function changePasword ($id, $new_md5)
{
	return sqlQuery("update users set password = '$new_md5' where id = '$id'");
}

function getUserList ($cols = '*', $limit = 'all', $start = '0')
{
	if ($limit = "all")
		$rez = sqlStatement("select $cols from users where username != '' order by date DESC");
	else
		$rez = sqlStatement("select $cols from users where username != '' order by date DESC limit $limit, $start");
	for ($iter = 0; $row = sqlFetchArray($rez); $iter++)
		$tbl[$iter] = $row;
	return $tbl;
}

function getProviderList ($cols = '*', $limit= 'all', $start = '0')
{
	if ($limit = "all")
		$rez = sqlStatement("select $cols from groups order by date DESC");
	else
		$rez = sqlStatement("select $cols from groups order by date DESC limit $limit, $start");
	for ($iter = 0; $row = sqlFetchArray($rez); $iter++)
		$tbl[$iter] = $row;
	return $tbl;
}

function addGroup ($groupname)
{
	return sqlInsert("insert into groups (name) values ('$groupname')");
}

function delGroup ($group_id)
{
	return sqlQuery("delete from groups where id = '$group_id' limit 0,1");
}

/***************************************************************
//pennfirm
//Function currently user by new post calendar code to determine
//if a given user is in a group with another user
//and if so to allow editing of that users events
//
//*************************************************************/

function validateGroupStatus ($user_to_be_checked, $group_user) {
	if (isset($user_to_be_checked) && isset($group_user)) {
		if ($user_to_be_checked == $group_user) {

			return true;
		}
		elseif ($_SESSION['authorizeduser'] == 1)
			return true;

		$query = "SELECT groups.name FROM users,groups WHERE users.username =  \"" . mysql_real_escape_string($user_to_be_checked) . "\" " .
				 "AND users.username = groups.user group by groups.name";
		$result = sqlStatement($query);

		$usertbcGroups = array();

		while ($row = mysql_fetch_array($result)) {
			$usertbcGroups[] = $row[0];
		}

		$query = "SELECT groups.name FROM users,groups WHERE users.username =  \"" . mysql_real_escape_string($group_user) . "\" " .
				 "AND users.username = groups.user group by groups.name";
		$result = sqlStatement($query);

		$usergGroups = array();

		while ($row = mysql_fetch_array($result)) {
			$usergGroups[] = $row[0];
		}
		foreach ($usertbcGroups as $group) {
	  		if(in_array($group,$usergGroups)) {
			  return true;
			}
		}

	}

	return false;
}


?>
