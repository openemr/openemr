<?php
/** @package    Secure::App */

/** import supporting libraries */
require_once("verysimple/Authentication/IAuthenticatable.php");
require_once("util/password.php");

/**
 * The SecureApp is a simple account object that demonstrates a simplistic way
 * to handle authentication.  Note that this uses a hard-coded username/password
 * combination (see inside the __construct method).
 *
 * A better approach is to use one of your existing model classes and implement
 * IAuthenticatable inside that class.
 *
 * @package Example::App
 * @author ClassBuilder
 * @version 1.0
 */
class SecureApp implements IAuthenticatable
{
	/**
	 * @var Array hard-coded list user/passwords.  initialized on contruction
	 */
	static $USERS;

	static $PERMISSION_ADMIN = 1;
	static $PERMISSION_USER = 2;

	public $Username = '';

	/**
	 * Initialize the array of users.  Note, this is only done this way because the
	 * users are hard-coded for this example.  In your own code you would most likely
	 * do a single lookup inside the Login method
	 */
	public function __construct()
	{
		if (!self::$USERS)
		{
			self::$USERS = Array(
				"demo"=>password_hash("pass",PASSWORD_BCRYPT),
				"admin"=>password_hash("pass",PASSWORD_BCRYPT)
			);
		}
	}

	/**
	 * Returns true if the user is anonymous (not logged in)
	 * @see IAuthenticatable
	 */
	public function IsAnonymous()
	{
		return $this->Username == '';
	}

	/**
	 * This is a hard-coded way of checking permission.  A better approach would be to look up
	 * this information in the database or base it on the account type
	 *
	 * @see IAuthenticatable
	 * @param int $permission
	 */
	public function IsAuthorized($permission)
	{
		if ($this->Username == 'admin') return true;

		if ($this->Username == 'demo' && $permission == self::$PERMISSION_USER) return true;

		return false;
	}

	/**
	 * This login method uses hard-coded username/passwords.  This is ok for simple apps
	 * but for a more robust application this would do a database lookup instead.
	 * The Username is used as a mechanism to determine whether the user is logged in or
	 * not
	 *
	 * @see IAuthenticatable
	 * @param string $username
	 * @param string $password
	 */
	public function Login($username,$password)
	{
		foreach (self::$USERS as $un=>$pw)
		{
			if ($username == $un && password_verify($password,$pw))
			{
				$this->Username = $username;
				break;
			}
		}

		return $this->Username != '';
	}

}

?>