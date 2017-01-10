<?php
/** @package Cargo::Controller */

/** import supporting libraries */
require_once("AppBaseController.php");
require_once("App/SecureApp.php");

/**
 * SecureAppController is a sample controller to demonstrate
 * one approach to authentication in a Phreeze app
 *
 * @package Cargo::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class SecureAppController extends AppBaseController
{

	/**
	 * Override here for any controller-specific functionality
	 */
	protected function Init()
	{
		parent::Init();

		// TODO: add controller-wide bootstrap code
	}

	/**
	 * This page requires SecureApp::$PERMISSION_USER to view
	 */
	public function UserPage()
	{
		$this->RequirePermission(SecureApp::$PERMISSION_USER,
				'SecureApp.LoginForm',
				'Login is required to access the secure user page',
				'You do not have permission to access the secure user page');

		$this->Assign("currentUser", $this->GetCurrentUser());

		$this->Assign('page','userpage');
		$this->Render("SecureApp");
	}

	/**
	 * This page requires SecureApp::$PERMISSION_ADMIN to view
	 */
	public function AdminPage()
	{
		$this->RequirePermission(SecureApp::$PERMISSION_ADMIN,
				'SecureApp.LoginForm',
				'Login is required to access the admin page',
				'Admin permission is required to access the admin page');

		$this->Assign("currentUser", $this->GetCurrentUser());

		$this->Assign('page','adminpage');
		$this->Render("SecureApp");
	}

	/**
	 * Display the login form
	 */
	public function LoginForm()
	{
		$this->Assign("currentUser", $this->GetCurrentUser());

		$this->Assign('page','login');
		$this->Render("SecureApp");
	}

	/**
	 * Process the login, create the user session and then redirect to
	 * the appropriate page
	 */
	public function Login()
	{
		$user = new SecureApp();

		if ($user->Login(RequestUtil::Get('username'), RequestUtil::Get('password')))
		{
			// login success
			$this->SetCurrentUser($user);
			$this->Redirect('SecureApp.UserPage');
		}
		else
		{
			// login failed
			$this->Redirect('SecureApp.LoginForm','Unknown username/password combination');
		}
	}

	/**
	 * Clear the user session and redirect to the login page
	 */
	public function Logout()
	{
		$this->ClearCurrentUser();
		$this->Redirect("SecureApp.LoginForm","You are now logged out");
	}

}
?>