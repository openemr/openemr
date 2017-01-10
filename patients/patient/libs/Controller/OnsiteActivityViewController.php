<?php
/** @package    Patient Portal::Controller */

/**
 * import supporting libraries
 */
require_once ( "AppBaseController.php" );
require_once ( "Model/OnsiteActivityView.php" );

/**
 * OnsiteActivityViewController is the controller class for the OnsiteActivityView object.
 * The
 * controller is responsible for processing input from the user, reading/updating
 * the model as necessary and displaying the appropriate view.
 *
 * @package Patient Portal::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class OnsiteActivityViewController extends AppBaseController{

	/**
	 * Override here for any controller-specific functionality
	 *
	 * @inheritdocs
	 */
	protected function Init(){
		parent::Init();

		// $this->RequirePermission(User::$PERMISSION_USER,'SecureApp.LoginForm');
	}

	/**
	 * Displays a list view of Onsite Activity View objects
	 */
	public function ListView(){
		$user = 0;
		if( isset( $_SESSION['authUser'] ) ) $user = $_SESSION['authUser'];
		else{
			header( "refresh:6;url= ./provider" );
			echo 'Redirecting in about 5 secs. Session shared with Onsite Portal<br> Shared session not allowed!.';
			exit();
		}
		$this->Assign( 'cuser', $user );
		$this->Render();
	}

	/**
	 * API Method queries for OnsiteActivityView records and render as JSON
	 */
	public function Query(){
		self::CreateView( '' );
		try{
			$criteria = new OnsiteActivityViewCriteria();
			$status = RequestUtil::Get( 'status' );
			$criteria->Status_Equals = $status;

			$filter = RequestUtil::Get( 'filter' );
			if( $filter ) $criteria->AddFilter( new CriteriaFilter( 'Id,Date,PatientId,Activity,RequireAudit,PendingAction,ActionTaken,Status,Narrative,TableAction,TableArgs,ActionUser,ActionTakenTime,Checksum,Title,Fname,Lname,Mname,Dob,Ss,Street,PostalCode,City,State,Referrerid,Providerid,RefProviderid,Pubpid,CareTeam,Username,Authorized,Ufname,Umname,Ulname,Facility,Active,Utitle,PhysicianType', '%' . $filter . '%' ) );

			// TODO: this is generic query filtering based only on criteria properties
			foreach( array_keys( $_REQUEST ) as $prop ){
				$prop_normal = ucfirst( $prop );
				$prop_equals = $prop_normal . '_Equals';

				if( property_exists( $criteria, $prop_normal ) ){
					$criteria->$prop_normal = RequestUtil::Get( $prop );
				} elseif( property_exists( $criteria, $prop_equals ) ){
					// this is a convenience so that the _Equals suffix is not needed
					$criteria->$prop_equals = RequestUtil::Get( $prop );
				}
			}

			$output = new stdClass();

			// if a sort order was specified then specify in the criteria
			$output->orderBy = RequestUtil::Get( 'orderBy' );
			$output->orderDesc = RequestUtil::Get( 'orderDesc' ) != '';
			if( $output->orderBy ) $criteria->SetOrder( $output->orderBy, $output->orderDesc );

			$page = RequestUtil::Get( 'page' );

			if( $page != '' ){
				// if page is specified, use this instead (at the expense of one extra count query)
				$pagesize = $this->GetDefaultPageSize();

				$onsiteactivityviews = $this->Phreezer->Query( 'OnsiteActivityViewReporter', $criteria )->GetDataPage( $page, $pagesize );
				$output->rows = $onsiteactivityviews->ToObjectArray( true, $this->SimpleObjectParams() );
				$output->totalResults = $onsiteactivityviews->TotalResults;
				$output->totalPages = $onsiteactivityviews->TotalPages;
				$output->pageSize = $onsiteactivityviews->PageSize;
				$output->currentPage = $onsiteactivityviews->CurrentPage;
			} else{
				// return all results
				$onsiteactivityviews = $this->Phreezer->Query( 'OnsiteActivityViewReporter', $criteria );
				$output->rows = $onsiteactivityviews->ToObjectArray( true, $this->SimpleObjectParams() );
				$output->totalResults = count( $output->rows );
				$output->totalPages = 1;
				$output->pageSize = $output->totalResults;
				$output->currentPage = 1;
			}

			$this->RenderJSON( $output, $this->JSONPCallback() );
		} catch( Exception $ex ){
			$this->RenderExceptionJSON( $ex );
		}
	}

	/**
	 * API Method retrieves a single OnsiteActivityView record and render as JSON
	 */
	public function Read(){
		try{
			$pk = $this->GetRouter()->GetUrlParam( 'id' );
			$onsiteactivityview = $this->Phreezer->Get( 'OnsiteActivityView', $pk );
			$this->RenderJSON( $onsiteactivityview, $this->JSONPCallback(), true, $this->SimpleObjectParams() );
		} catch( Exception $ex ){
			$this->RenderExceptionJSON( $ex );
		}
	}
	public function CreateView( $viewcriteria ){
		$sql = "CREATE OR REPLACE VIEW onsite_activity_view As Select
  onsite_portal_activity.status,
  onsite_portal_activity.narrative,
  onsite_portal_activity.table_action,
  onsite_portal_activity.table_args,
  onsite_portal_activity.action_user,
  onsite_portal_activity.action_taken_time,
  onsite_portal_activity.checksum,
  patient_data.title,
  patient_data.fname,
  patient_data.lname,
  patient_data.mname,
  patient_data.DOB,
  patient_data.ss,
  patient_data.street,
  patient_data.postal_code,
  patient_data.city,
  patient_data.state,
  patient_data.referrerID,
  patient_data.providerID,
  patient_data.ref_providerID,
  patient_data.pubpid,
  patient_data.care_team,
  users.username,
  users.authorized,
  users.fname As ufname,
  users.mname As umname,
  users.lname As ulname,
  users.facility,
  users.active,
  users.title As utitle,
  users.physician_type,
  onsite_portal_activity.date,
  onsite_portal_activity.require_audit,
  onsite_portal_activity.pending_action,
  onsite_portal_activity.action_taken,
  onsite_portal_activity.id,
  onsite_portal_activity.activity,
  onsite_portal_activity.patient_id ";
		$sql .= "From onsite_portal_activity Left Join
  patient_data On onsite_portal_activity.patient_id = patient_data.pid Left Join
  users On patient_data.providerID = users.id ";
		// $sql .= "Where onsite_portal_activity.status = 'waiting'";
		try{
			$this->Phreezer->DataAdapter->Execute( $sql );
		} catch( Exception $ex ){
			$this->RenderExceptionJSON( $ex );
		}
	}
	/**
	 * API Method inserts a new OnsiteActivityView record and render response as JSON
	 */
	public function Create(){
		;
	}

	/**
	 * API Method updates an existing OnsiteActivityView record and render response as JSON
	 */
	public function Update(){
		try{
			// TODO: views are read-only by default. uncomment at your own discretion
			throw new Exception( 'Database views are read-only and cannot be updated' );

			$json = json_decode( RequestUtil::GetBody() );

			if( ! $json ){throw new Exception( 'The request body does not contain valid JSON' );}

			$pk = $this->GetRouter()->GetUrlParam( 'id' );
			$onsiteactivityview = $this->Phreezer->Get( 'OnsiteActivityView', $pk );

			// TODO: any fields that should not be updated by the user should be commented out

			// this is a primary key. uncomment if updating is allowed
			// $onsiteactivityview->Id = $this->SafeGetVal($json, 'id', $onsiteactivityview->Id);

			$onsiteactivityview->Date = date( 'Y-m-d H:i:s', strtotime( $this->SafeGetVal( $json, 'date', $onsiteactivityview->Date ) ) );
			$onsiteactivityview->PatientId = $this->SafeGetVal( $json, 'patientId', $onsiteactivityview->PatientId );
			$onsiteactivityview->Activity = $this->SafeGetVal( $json, 'activity', $onsiteactivityview->Activity );
			$onsiteactivityview->RequireAudit = $this->SafeGetVal( $json, 'requireAudit', $onsiteactivityview->RequireAudit );
			$onsiteactivityview->PendingAction = $this->SafeGetVal( $json, 'pendingAction', $onsiteactivityview->PendingAction );
			$onsiteactivityview->ActionTaken = $this->SafeGetVal( $json, 'actionTaken', $onsiteactivityview->ActionTaken );
			$onsiteactivityview->Status = $this->SafeGetVal( $json, 'status', $onsiteactivityview->Status );
			$onsiteactivityview->Narrative = $this->SafeGetVal( $json, 'narrative', $onsiteactivityview->Narrative );
			$onsiteactivityview->TableAction = $this->SafeGetVal( $json, 'tableAction', $onsiteactivityview->TableAction );
			$onsiteactivityview->TableArgs = $this->SafeGetVal( $json, 'tableArgs', $onsiteactivityview->TableArgs );
			$onsiteactivityview->ActionUser = $this->SafeGetVal( $json, 'actionUser', $onsiteactivityview->ActionUser );
			$onsiteactivityview->ActionTakenTime = date( 'Y-m-d H:i:s', strtotime( $this->SafeGetVal( $json, 'actionTakenTime', $onsiteactivityview->ActionTakenTime ) ) );
			$onsiteactivityview->Checksum = $this->SafeGetVal( $json, 'checksum', $onsiteactivityview->Checksum );
			$onsiteactivityview->Title = $this->SafeGetVal( $json, 'title', $onsiteactivityview->Title );
			$onsiteactivityview->Fname = $this->SafeGetVal( $json, 'fname', $onsiteactivityview->Fname );
			$onsiteactivityview->Lname = $this->SafeGetVal( $json, 'lname', $onsiteactivityview->Lname );
			$onsiteactivityview->Mname = $this->SafeGetVal( $json, 'mname', $onsiteactivityview->Mname );
			$onsiteactivityview->Dob = date( 'Y-m-d H:i:s', strtotime( $this->SafeGetVal( $json, 'dob', $onsiteactivityview->Dob ) ) );
			$onsiteactivityview->Ss = $this->SafeGetVal( $json, 'ss', $onsiteactivityview->Ss );
			$onsiteactivityview->Street = $this->SafeGetVal( $json, 'street', $onsiteactivityview->Street );
			$onsiteactivityview->PostalCode = $this->SafeGetVal( $json, 'postalCode', $onsiteactivityview->PostalCode );
			$onsiteactivityview->City = $this->SafeGetVal( $json, 'city', $onsiteactivityview->City );
			$onsiteactivityview->State = $this->SafeGetVal( $json, 'state', $onsiteactivityview->State );
			$onsiteactivityview->Referrerid = $this->SafeGetVal( $json, 'referrerid', $onsiteactivityview->Referrerid );
			$onsiteactivityview->Providerid = $this->SafeGetVal( $json, 'providerid', $onsiteactivityview->Providerid );
			$onsiteactivityview->RefProviderid = $this->SafeGetVal( $json, 'refProviderid', $onsiteactivityview->RefProviderid );
			$onsiteactivityview->Pubpid = $this->SafeGetVal( $json, 'pubpid', $onsiteactivityview->Pubpid );
			$onsiteactivityview->CareTeam = $this->SafeGetVal( $json, 'careTeam', $onsiteactivityview->CareTeam );
			$onsiteactivityview->Username = $this->SafeGetVal( $json, 'username', $onsiteactivityview->Username );
			$onsiteactivityview->Authorized = $this->SafeGetVal( $json, 'authorized', $onsiteactivityview->Authorized );
			$onsiteactivityview->Ufname = $this->SafeGetVal( $json, 'ufname', $onsiteactivityview->Ufname );
			$onsiteactivityview->Umname = $this->SafeGetVal( $json, 'umname', $onsiteactivityview->Umname );
			$onsiteactivityview->Ulname = $this->SafeGetVal( $json, 'ulname', $onsiteactivityview->Ulname );
			$onsiteactivityview->Facility = $this->SafeGetVal( $json, 'facility', $onsiteactivityview->Facility );
			$onsiteactivityview->Active = $this->SafeGetVal( $json, 'active', $onsiteactivityview->Active );
			$onsiteactivityview->Utitle = $this->SafeGetVal( $json, 'utitle', $onsiteactivityview->Utitle );
			$onsiteactivityview->PhysicianType = $this->SafeGetVal( $json, 'physicianType', $onsiteactivityview->PhysicianType );

			$onsiteactivityview->Validate();
			$errors = $onsiteactivityview->GetValidationErrors();

			if( count( $errors ) > 0 ){
				$this->RenderErrorJSON( 'Please check the form for errors', $errors );
			} else{
				$onsiteactivityview->Save();
				$this->RenderJSON( $onsiteactivityview, $this->JSONPCallback(), true, $this->SimpleObjectParams() );
			}
		} catch( Exception $ex ){

			// this table does not have an auto-increment primary key, so it is semantically correct to
			// issue a REST PUT request, however we have no way to know whether to insert or update.
			// if the record is not found, this exception will indicate that this is an insert request
			if( is_a( $ex, 'NotFoundException' ) ){return $this->Create();}

			$this->RenderExceptionJSON( $ex );
		}
	}

	/**
	 * API Method deletes an existing OnsiteActivityView record and render response as JSON
	 */
	public function Delete(){
		try{
			// TODO: views are read-only by default. uncomment at your own discretion
			throw new Exception( 'Database views are read-only and cannot be updated' );

			// TODO: if a soft delete is prefered, change this to update the deleted flag instead of hard-deleting

			$pk = $this->GetRouter()->GetUrlParam( 'id' );
			$onsiteactivityview = $this->Phreezer->Get( 'OnsiteActivityView', $pk );

			$onsiteactivityview->Delete();

			$output = new stdClass();

			$this->RenderJSON( $output, $this->JSONPCallback() );
		} catch( Exception $ex ){
			$this->RenderExceptionJSON( $ex );
		}
	}
}

?>
