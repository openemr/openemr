<?php
/** @package    OpenHealthEMR::Controller */

/** import supporting libraries */
require_once("AppBaseController.php");
require_once("Model/User.php");

/**
 * UserController is the controller class for the User object.  The
 * controller is responsible for processing input from the user, reading/updating
 * the model as necessary and displaying the appropriate view.
 *
 * @package OpenHealthEMR::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class UserController extends AppBaseController
{

	/**
	 * Override here for any controller-specific functionality
	 *
	 * @inheritdocs
	 */
	protected function Init()
	{
		parent::Init();

		// TODO: add controller-wide bootstrap code

		// TODO: if authentiation is required for this entire controller, for example:
		// $this->RequirePermission(SecureApp::$PERMISSION_USER,'SecureApp.LoginForm');
	}

	/**
	 * Displays a list view of User objects
	 */
	public function ListView()
	{
		$rid=0;
		if (isset($_GET['id']) )
			$rid = (int) $_GET['id'];
		$this->Assign ( 'recid', $rid );
		$this->Render();
	}

	/**
	 * API Method queries for User records and render as JSON
	 */
	public function Query()
	{
		try
		{
			$criteria = new UserCriteria();
			$recnum = RequestUtil::Get ( 'recId' );
			$criteria->Id_Equals = $recnum;

			$output = new stdClass();

			// if a sort order was specified then specify in the criteria
 			$output->orderBy = RequestUtil::Get('orderBy');
 			$output->orderDesc = RequestUtil::Get('orderDesc') != '';
 			if ($output->orderBy) $criteria->SetOrder($output->orderBy, $output->orderDesc);

			$page = RequestUtil::Get('page');

				// return all results
				$users = $this->Phreezer->Query('User',$criteria);
				$output->rows = $users->ToObjectArray(true, $this->SimpleObjectParams());
				$output->totalResults = count($output->rows);
				$output->totalPages = 1;
				$output->pageSize = $output->totalResults;
				$output->currentPage = 1;

			$this->RenderJSON($output, $this->JSONPCallback());
		}
		catch (Exception $ex)
		{
			$this->RenderExceptionJSON($ex);
		}
	}

	/**
	 * API Method retrieves a single User record and render as JSON
	 */
	public function Read()
	{
		try
		{
			$pk = $this->GetRouter()->GetUrlParam('id');
			$user = $this->Phreezer->Get('User',$pk);
			$this->RenderJSON($user, $this->JSONPCallback(), true, $this->SimpleObjectParams());
		}
		catch (Exception $ex)
		{
			$this->RenderExceptionJSON($ex);
		}
	}

	/**
	 * API Method inserts a new User record and render response as JSON
	 */
	public function Create()
	{
		try
		{

			$json = json_decode(RequestUtil::GetBody());

			if (!$json)
			{
				throw new Exception('The request body does not contain valid JSON');
			}

			$user = new User($this->Phreezer);

			// TODO: any fields that should not be inserted by the user should be commented out

			// this is an auto-increment.  uncomment if updating is allowed
			// $user->Id = $this->SafeGetVal($json, 'id');

			$user->Username = $this->SafeGetVal($json, 'username');
			$user->Password = $this->SafeGetVal($json, 'password');
			$user->Authorized = $this->SafeGetVal($json, 'authorized');
			$user->Info = $this->SafeGetVal($json, 'info');
			$user->Source = $this->SafeGetVal($json, 'source');
			$user->Fname = $this->SafeGetVal($json, 'fname');
			$user->Mname = $this->SafeGetVal($json, 'mname');
			$user->Lname = $this->SafeGetVal($json, 'lname');
			$user->Federaltaxid = $this->SafeGetVal($json, 'federaltaxid');
			$user->Federaldrugid = $this->SafeGetVal($json, 'federaldrugid');
			$user->Upin = $this->SafeGetVal($json, 'upin');
			$user->Facility = $this->SafeGetVal($json, 'facility');
			$user->FacilityId = $this->SafeGetVal($json, 'facilityId');
			$user->SeeAuth = $this->SafeGetVal($json, 'seeAuth');
			$user->Active = $this->SafeGetVal($json, 'active');
			$user->Npi = $this->SafeGetVal($json, 'npi');
			$user->Title = $this->SafeGetVal($json, 'title');
			$user->Specialty = $this->SafeGetVal($json, 'specialty');
			$user->Billname = $this->SafeGetVal($json, 'billname');
			$user->Email = $this->SafeGetVal($json, 'email');
			$user->EmailDirect = $this->SafeGetVal($json, 'emailDirect');
			$user->EserUrl = $this->SafeGetVal($json, 'eserUrl');
			$user->Assistant = $this->SafeGetVal($json, 'assistant');
			$user->Organization = $this->SafeGetVal($json, 'organization');
			$user->Valedictory = $this->SafeGetVal($json, 'valedictory');
			$user->Street = $this->SafeGetVal($json, 'street');
			$user->Streetb = $this->SafeGetVal($json, 'streetb');
			$user->City = $this->SafeGetVal($json, 'city');
			$user->State = $this->SafeGetVal($json, 'state');
			$user->Zip = $this->SafeGetVal($json, 'zip');
			$user->Street2 = $this->SafeGetVal($json, 'street2');
			$user->Streetb2 = $this->SafeGetVal($json, 'streetb2');
			$user->City2 = $this->SafeGetVal($json, 'city2');
			$user->State2 = $this->SafeGetVal($json, 'state2');
			$user->Zip2 = $this->SafeGetVal($json, 'zip2');
			$user->Phone = $this->SafeGetVal($json, 'phone');
			$user->Fax = $this->SafeGetVal($json, 'fax');
			$user->Phonew1 = $this->SafeGetVal($json, 'phonew1');
			$user->Phonew2 = $this->SafeGetVal($json, 'phonew2');
			$user->Phonecell = $this->SafeGetVal($json, 'phonecell');
			$user->Notes = $this->SafeGetVal($json, 'notes');
			$user->CalUi = $this->SafeGetVal($json, 'calUi');
			$user->Taxonomy = $this->SafeGetVal($json, 'taxonomy');
			$user->SsiRelayhealth = $this->SafeGetVal($json, 'ssiRelayhealth');
			$user->Calendar = $this->SafeGetVal($json, 'calendar');
			$user->AbookType = $this->SafeGetVal($json, 'abookType');
			$user->PwdExpirationDate = date('Y-m-d H:i:s',strtotime($this->SafeGetVal($json, 'pwdExpirationDate')));
			$user->PwdHistory1 = $this->SafeGetVal($json, 'pwdHistory1');
			$user->PwdHistory2 = $this->SafeGetVal($json, 'pwdHistory2');
			$user->DefaultWarehouse = $this->SafeGetVal($json, 'defaultWarehouse');
			$user->Irnpool = $this->SafeGetVal($json, 'irnpool');
			$user->StateLicenseNumber = $this->SafeGetVal($json, 'stateLicenseNumber');
			$user->NewcropUserRole = $this->SafeGetVal($json, 'newcropUserRole');
			$user->Cpoe = $this->SafeGetVal($json, 'cpoe');
			$user->PhysicianType = $this->SafeGetVal($json, 'physicianType');

			$user->Validate();
			$errors = $user->GetValidationErrors();

			if (count($errors) > 0)
			{
				$this->RenderErrorJSON('Please check the form for errors',$errors);
			}
			else
			{
				$user->Save();
				$this->RenderJSON($user, $this->JSONPCallback(), true, $this->SimpleObjectParams());
			}

		}
		catch (Exception $ex)
		{
			$this->RenderExceptionJSON($ex);
		}
	}

	/**
	 * API Method updates an existing User record and render response as JSON
	 */
	public function Update()
	{
		try
		{

			$json = json_decode(RequestUtil::GetBody());

			if (!$json)
			{
				throw new Exception('The request body does not contain valid JSON');
			}

			$pk = $this->GetRouter()->GetUrlParam('id');
			$user = $this->Phreezer->Get('User',$pk);

			// TODO: any fields that should not be updated by the user should be commented out

			// this is a primary key.  uncomment if updating is allowed
			// $user->Id = $this->SafeGetVal($json, 'id', $user->Id);

			$user->Username = $this->SafeGetVal($json, 'username', $user->Username);
			$user->Password = $this->SafeGetVal($json, 'password', $user->Password);
			$user->Authorized = $this->SafeGetVal($json, 'authorized', $user->Authorized);
			$user->Info = $this->SafeGetVal($json, 'info', $user->Info);
			$user->Source = $this->SafeGetVal($json, 'source', $user->Source);
			$user->Fname = $this->SafeGetVal($json, 'fname', $user->Fname);
			$user->Mname = $this->SafeGetVal($json, 'mname', $user->Mname);
			$user->Lname = $this->SafeGetVal($json, 'lname', $user->Lname);
			$user->Federaltaxid = $this->SafeGetVal($json, 'federaltaxid', $user->Federaltaxid);
			$user->Federaldrugid = $this->SafeGetVal($json, 'federaldrugid', $user->Federaldrugid);
			$user->Upin = $this->SafeGetVal($json, 'upin', $user->Upin);
			$user->Facility = $this->SafeGetVal($json, 'facility', $user->Facility);
			$user->FacilityId = $this->SafeGetVal($json, 'facilityId', $user->FacilityId);
			$user->SeeAuth = $this->SafeGetVal($json, 'seeAuth', $user->SeeAuth);
			$user->Active = $this->SafeGetVal($json, 'active', $user->Active);
			$user->Npi = $this->SafeGetVal($json, 'npi', $user->Npi);
			$user->Title = $this->SafeGetVal($json, 'title', $user->Title);
			$user->Specialty = $this->SafeGetVal($json, 'specialty', $user->Specialty);
			$user->Billname = $this->SafeGetVal($json, 'billname', $user->Billname);
			$user->Email = $this->SafeGetVal($json, 'email', $user->Email);
			$user->EmailDirect = $this->SafeGetVal($json, 'emailDirect', $user->EmailDirect);
			$user->EserUrl = $this->SafeGetVal($json, 'eserUrl', $user->EserUrl);
			$user->Assistant = $this->SafeGetVal($json, 'assistant', $user->Assistant);
			$user->Organization = $this->SafeGetVal($json, 'organization', $user->Organization);
			$user->Valedictory = $this->SafeGetVal($json, 'valedictory', $user->Valedictory);
			$user->Street = $this->SafeGetVal($json, 'street', $user->Street);
			$user->Streetb = $this->SafeGetVal($json, 'streetb', $user->Streetb);
			$user->City = $this->SafeGetVal($json, 'city', $user->City);
			$user->State = $this->SafeGetVal($json, 'state', $user->State);
			$user->Zip = $this->SafeGetVal($json, 'zip', $user->Zip);
			$user->Street2 = $this->SafeGetVal($json, 'street2', $user->Street2);
			$user->Streetb2 = $this->SafeGetVal($json, 'streetb2', $user->Streetb2);
			$user->City2 = $this->SafeGetVal($json, 'city2', $user->City2);
			$user->State2 = $this->SafeGetVal($json, 'state2', $user->State2);
			$user->Zip2 = $this->SafeGetVal($json, 'zip2', $user->Zip2);
			$user->Phone = $this->SafeGetVal($json, 'phone', $user->Phone);
			$user->Fax = $this->SafeGetVal($json, 'fax', $user->Fax);
			$user->Phonew1 = $this->SafeGetVal($json, 'phonew1', $user->Phonew1);
			$user->Phonew2 = $this->SafeGetVal($json, 'phonew2', $user->Phonew2);
			$user->Phonecell = $this->SafeGetVal($json, 'phonecell', $user->Phonecell);
			$user->Notes = $this->SafeGetVal($json, 'notes', $user->Notes);
			$user->CalUi = $this->SafeGetVal($json, 'calUi', $user->CalUi);
			$user->Taxonomy = $this->SafeGetVal($json, 'taxonomy', $user->Taxonomy);
			$user->SsiRelayhealth = $this->SafeGetVal($json, 'ssiRelayhealth', $user->SsiRelayhealth);
			$user->Calendar = $this->SafeGetVal($json, 'calendar', $user->Calendar);
			$user->AbookType = $this->SafeGetVal($json, 'abookType', $user->AbookType);
			$user->PwdExpirationDate = date('Y-m-d H:i:s',strtotime($this->SafeGetVal($json, 'pwdExpirationDate', $user->PwdExpirationDate)));
			$user->PwdHistory1 = $this->SafeGetVal($json, 'pwdHistory1', $user->PwdHistory1);
			$user->PwdHistory2 = $this->SafeGetVal($json, 'pwdHistory2', $user->PwdHistory2);
			$user->DefaultWarehouse = $this->SafeGetVal($json, 'defaultWarehouse', $user->DefaultWarehouse);
			$user->Irnpool = $this->SafeGetVal($json, 'irnpool', $user->Irnpool);
			$user->StateLicenseNumber = $this->SafeGetVal($json, 'stateLicenseNumber', $user->StateLicenseNumber);
			$user->NewcropUserRole = $this->SafeGetVal($json, 'newcropUserRole', $user->NewcropUserRole);
			$user->Cpoe = $this->SafeGetVal($json, 'cpoe', $user->Cpoe);
			$user->PhysicianType = $this->SafeGetVal($json, 'physicianType', $user->PhysicianType);

			$user->Validate();
			$errors = $user->GetValidationErrors();

			if (count($errors) > 0)
			{
				$this->RenderErrorJSON('Please check the form for errors',$errors);
			}
			else
			{
				$user->Save();
				$this->RenderJSON($user, $this->JSONPCallback(), true, $this->SimpleObjectParams());
			}


		}
		catch (Exception $ex)
		{


			$this->RenderExceptionJSON($ex);
		}
	}

	/**
	 * API Method deletes an existing User record and render response as JSON
	 */
	public function Delete()
	{
		try
		{

			// TODO: if a soft delete is prefered, change this to update the deleted flag instead of hard-deleting

			$pk = $this->GetRouter()->GetUrlParam('id');
			$user = $this->Phreezer->Get('User',$pk);

			$user->Delete();

			$output = new stdClass();

			$this->RenderJSON($output, $this->JSONPCallback());

		}
		catch (Exception $ex)
		{
			$this->RenderExceptionJSON($ex);
		}
	}
}

?>
