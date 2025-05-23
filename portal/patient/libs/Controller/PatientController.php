<?php

/**
 * PatientController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * import supporting libraries
 */
require_once("AppBasePortalController.php");
require_once("Model/Patient.php");
/**
 * PatientController is the controller class for the Patient object.
 * The
 * controller is responsible for processing input from the user, reading/updating
 * the model as necessary and displaying the appropriate view.
 *
 * @package Patient Portal::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class PatientController extends AppBasePortalController
{
    /**
     * Override here for any controller-specific functionality
     *
     * @inheritdocs
     */
    protected function Init()
    {
        parent::Init();
    }

    /**
     * Displays a list view of Patient objects
     */
    public function ListView()
    {

        $rid = $pid = $user = $encounter = $register = 0;
        if (isset($_GET['id'])) {
            $rid = (int) $_GET['id'];
        }

        if (isset($_GET['pid'])) {
            $pid = (int) $_GET['pid'];
        }

        // only allow patient to see themself
        if (!empty($GLOBALS['bootstrap_pid'])) {
            $pid = $GLOBALS['bootstrap_pid'];
        }

        if (isset($_GET['user'])) {
            $user = $_GET['user'];
        }

        if (isset($_GET['enc'])) {
            $encounter = $_GET['enc'];
        }

        if (isset($_GET['register'])) {
            $register = $_GET['register'];
        }

        // force register to pid of 0 and register of true
        if (!empty($GLOBALS['bootstrap_register'])) {
            $pid = 0;
            $register = true;
        }
        $this->Assign('recid', $rid);
        $this->Assign('cpid', $pid);
        $this->Assign('cuser', $user);
        $this->Assign('encounter', $encounter);
        $this->Assign('register', $register);

        $trow = array();
        $ptdata = $this->startupQuery($pid);
        foreach ($ptdata[0] as $key => $v) {
            $trow[lcfirst($key)] = $v;
        }
        $this->Assign('trow', $trow);

        // seek and qualify excluded edits
        $exclude = [];
        $q = sqlStatement("SELECT `field_id`, `uor`, `edit_options` FROM `layout_options` " .
            "WHERE `form_id` = 'DEM' AND (`uor` = 0 || `edit_options` > '') ORDER BY `group_id`, `seq`");
        while ($key = sqlFetchArray($q)) {
            if ((int)$key['uor'] === 0 || strpos($key['edit_options'], "EP") !== false) {
                $key['field_id'] = strtolower($key['field_id']);
                $key['field_id'] = preg_replace_callback('/_([^_])/', function (array $m) {

                        return ucfirst($m[1]);
                }, $key['field_id']);
                $exclude[] = lcfirst($key['field_id']) . "InputContainer";
            }
        }
        $this->Assign('exclude', $exclude);

        // Get providers list.
        $user_list = [];
        $user_list_rst = sqlStatement("SELECT `id`, `username`, `fname`, `lname` FROM `users` " .
            "WHERE `authorized` = 1 AND `active` = 1 AND `portal_user` = 1 ORDER BY `lname`, `fname`");
        while ($row = sqlFetchArray($user_list_rst)) {
            $user_list[] = $row;
        }

        $this->Assign('users_list', $user_list);

        // finally render the template.
        $this->Render();
    }
    /**
     * API Method queries for startup Patient records and return as php
     */
    public function startupQuery($pid)
    {
        try {
            $criteria = new PatientCriteria();
            $recnum = (int)$pid;
            $criteria->Pid_Equals = $recnum;
            $output = new stdClass();
            // return row
            $patientdata = $this->Phreezer->Query('PatientReporter', $criteria);
            $output->rows = $patientdata->ToObjectArray(false, $this->SimpleObjectParams());
            $output->totalResults = count($output->rows);
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
        return $output->rows;
    }
    /**
     * API Method queries for Patient records and render as JSON
     */
    public function Query()
    {
        try {
            $criteria = new PatientCriteria();
            $pid = RequestUtil::Get('patientId');
            // only allow patient to see themself
            if (!empty($GLOBALS['bootstrap_pid'])) {
                $pid = $GLOBALS['bootstrap_pid'];
            }
            // force register to pid of 0
            if (!empty($GLOBALS['bootstrap_register'])) {
                $pid = 0;
            }

            $criteria->Pid_Equals = $pid;
            $output = new stdClass();
            // if a sort order was specified then specify in the criteria
            $output->orderBy = RequestUtil::Get('orderBy');
            $output->orderDesc = RequestUtil::Get('orderDesc') != '';
            if ($output->orderBy) {
                $criteria->SetOrder($output->orderBy, $output->orderDesc);
            }

            $page = RequestUtil::Get('page');
            // return all results
            $patientdata = $this->Phreezer->Query('Patient', $criteria);
            $output->rows = $patientdata->ToObjectArray(true, $this->SimpleObjectParams());
            $output->totalResults = count($output->rows);
            $output->totalPages = 1;
            $output->pageSize = $output->totalResults;
            $output->currentPage = 1;
            $this->RenderJSON($output, $this->JSONPCallback());
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method retrieves a single Patient record and render as JSON
     */
    public function Read()
    {
        try {
            $pk = $this->GetRouter()->GetUrlParam('id');
            $patient = $this->Phreezer->Get('Patient', $pk);
            $this->RenderJSON($patient, $this->JSONPCallback(), true, $this->SimpleObjectParams());
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method inserts a new Patient record and render response as JSON
     */
    public function Create()
    {
        try {
            $json = json_decode(RequestUtil::GetBody());
            if (empty($json)) {
                throw new Exception('The request body does not contain valid JSON');
            }
            if ($_SESSION['pid'] !== true && $_SESSION['register'] !== true) {
                throw new Exception('Unauthorized');
            }

            if (empty($_SESSION['fnameRegistration']) || empty($_SESSION['lnameRegistration']) || empty($_SESSION['dobRegistration']) || empty($_SESSION['emailRegistration']) || empty($_SESSION['token_id_holder'])) {
                throw new Exception('Something went wrong');
            }

            // get new pid
            $result = sqlQueryNoLog("select max(`pid`)+1 as `pid` from `patient_data`");
            if (empty($result['pid'])) {
                $pidRegistration = 1;
            } else {
                $pidRegistration = $result['pid'];
            }
            // store the pid so can use for other registration elements inserted later (such as insurance)
            sqlStatementNoLog("UPDATE `verify_email` SET `pid_holder` = ? WHERE `id` = ?", [$pidRegistration , $_SESSION['token_id_holder']]);

            $patient = new Patient($this->Phreezer);
            $patient->Title = $this->SafeGetVal($json, 'title', $patient->Title);
            $patient->Language = $this->SafeGetVal($json, 'language', $patient->Language);
            $patient->Financial = $this->SafeGetVal($json, 'financial', $patient->Financial);
            //$patient->Fname = $this->SafeGetVal($json, 'fname', $patient->Fname);
            $patient->Fname = $_SESSION['fnameRegistration'];
            //$patient->Lname = $this->SafeGetVal($json, 'lname', $patient->Lname);
            $patient->Lname = $_SESSION['lnameRegistration'];
            //$patient->Mname = $this->SafeGetVal($json, 'mname', $patient->Mname);
            $patient->Mname = $_SESSION['mnameRegistration'];
            //$patient->Dob = date('Y-m-d', strtotime($this->SafeGetVal($json, 'dob', $patient->Dob)));
            $patient->Dob = $_SESSION['dobRegistration'];
            $patient->Street = $this->SafeGetVal($json, 'street', $patient->Street);
            $patient->PostalCode = $this->SafeGetVal($json, 'postalCode', $patient->PostalCode);
            $patient->City = $this->SafeGetVal($json, 'city', $patient->City);
            $patient->State = $this->SafeGetVal($json, 'state', $patient->State);
            $patient->CountryCode = $this->SafeGetVal($json, 'countryCode', $patient->CountryCode);
            $patient->DriversLicense = $this->SafeGetVal($json, 'driversLicense', $patient->DriversLicense);
            $patient->Ss = $this->SafeGetVal($json, 'ss', $patient->Ss);
            $patient->Occupation = $this->SafeGetVal($json, 'occupation', $patient->Occupation);
            $patient->PhoneHome = $this->SafeGetVal($json, 'phoneHome', $patient->PhoneHome);
            $patient->PhoneBiz = $this->SafeGetVal($json, 'phoneBiz', $patient->PhoneBiz);
            $patient->PhoneContact = $this->SafeGetVal($json, 'phoneContact', $patient->PhoneContact);
            $patient->PhoneCell = $this->SafeGetVal($json, 'phoneCell', $patient->PhoneCell);
            $patient->PharmacyId = $this->SafeGetVal($json, 'pharmacyId', $patient->PharmacyId);
            $patient->Status = $this->SafeGetVal($json, 'status', $patient->Status);
            $patient->ContactRelationship = $this->SafeGetVal($json, 'contactRelationship', $patient->ContactRelationship);
            $patient->Date = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'date', $patient->Date)));
            $patient->Sex = $this->SafeGetVal($json, 'sex', $patient->Sex);
            $patient->Referrer = $this->SafeGetVal($json, 'referrer', $patient->Referrer);
            $patient->Referrerid = $this->SafeGetVal($json, 'referrerid', $patient->Referrerid);
            $patient->Providerid = $this->SafeGetVal($json, 'providerid', $patient->Providerid);
            $patient->RefProviderid = $this->SafeGetVal($json, 'refProviderid', $patient->RefProviderid);
            //$patient->Email = $this->SafeGetVal($json, 'email', $patient->Email);
            $patient->Email = $_SESSION['emailRegistration'];
            //$patient->EmailDirect = $this->SafeGetVal($json, 'emailDirect', $patient->EmailDirect);
            $patient->Ethnoracial = $this->SafeGetVal($json, 'ethnoracial', $patient->Ethnoracial);
            $patient->Race = $this->SafeGetVal($json, 'race', $patient->Race);
            $patient->Ethnicity = $this->SafeGetVal($json, 'ethnicity', $patient->Ethnicity);
            $patient->Religion = $this->SafeGetVal($json, 'religion', $patient->Religion);
            //$patient->Interpretter = $this->SafeGetVal($json, 'interpretter', $patient->Interpretter);
            //$patient->Migrantseasonal = $this->SafeGetVal($json, 'migrantseasonal', $patient->Migrantseasonal);
            $patient->FamilySize = $this->SafeGetVal($json, 'familySize', $patient->FamilySize);
            //$patient->MonthlyIncome = $this->SafeGetVal($json, 'monthlyIncome', $patient->MonthlyIncome);
            //$patient->BillingNote = $this->SafeGetVal($json, 'billingNote', $patient->BillingNote);
            //$patient->Homeless = $this->SafeGetVal($json, 'homeless', $patient->Homeless);
            //$patient->FinancialReview = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'financialReview', $patient->FinancialReview)));
            //$patient->Pubpid = $this->SafeGetVal($json, 'pubpid', $patient->Pubpid);
            $patient->Pubpid = $pidRegistration;
            //$patient->Pid = $this->SafeGetVal($json, 'pid', $patient->Pid);
            $patient->Pid = $pidRegistration;
            //$patient->Genericname1 = $this->SafeGetVal($json, 'genericname1', $patient->Genericname1);
            //$patient->Genericval1 = $this->SafeGetVal($json, 'genericval1', $patient->Genericval1);
            //$patient->Genericname2 = $this->SafeGetVal($json, 'genericname2', $patient->Genericname2);
            //$patient->Genericval2 = $this->SafeGetVal($json, 'genericval2', $patient->Genericval2);
            $patient->HipaaMail = $this->SafeGetVal($json, 'hipaaMail', $patient->HipaaMail);
            $patient->HipaaVoice = $this->SafeGetVal($json, 'hipaaVoice', $patient->HipaaVoice);
            $patient->HipaaNotice = $this->SafeGetVal($json, 'hipaaNotice', $patient->HipaaNotice);
            $patient->HipaaMessage = $this->SafeGetVal($json, 'hipaaMessage', $patient->HipaaMessage);
            $patient->HipaaAllowsms = $this->SafeGetVal($json, 'hipaaAllowsms', $patient->HipaaAllowsms);
            $patient->HipaaAllowemail = $this->SafeGetVal($json, 'hipaaAllowemail', $patient->HipaaAllowemail);
            //$patient->Squad = $this->SafeGetVal($json, 'squad', $patient->Squad);
            //$patient->Fitness = $this->SafeGetVal($json, 'fitness', $patient->Fitness);
            //$patient->ReferralSource = $this->SafeGetVal($json, 'referralSource', $patient->ReferralSource);
            //$patient->Pricelevel = $this->SafeGetVal($json, 'pricelevel', $patient->Pricelevel);
            $patient->Regdate = date('Y-m-d', strtotime($this->SafeGetVal($json, 'regdate', $patient->Regdate)));
            //$patient->Contrastart = date('Y-m-d', strtotime($this->SafeGetVal($json, 'contrastart', $patient->Contrastart)));
            //$patient->CompletedAd = $this->SafeGetVal($json, 'completedAd', $patient->CompletedAd);
            //$patient->AdReviewed = date('Y-m-d', strtotime($this->SafeGetVal($json, 'adReviewed', $patient->AdReviewed)));
            //$patient->Vfc = $this->SafeGetVal($json, 'vfc', $patient->Vfc);
            $patient->Mothersname = $this->SafeGetVal($json, 'mothersname', $patient->Mothersname);
            $patient->Guardiansname = $this->SafeGetVal($json, 'guardiansname', $patient->Guardiansname);
            $patient->AllowImmRegUse = $this->SafeGetVal($json, 'allowImmRegUse', $patient->AllowImmRegUse);
            $patient->AllowImmInfoShare = $this->SafeGetVal($json, 'allowImmInfoShare', $patient->AllowImmInfoShare);
            $patient->AllowHealthInfoEx = $this->SafeGetVal($json, 'allowHealthInfoEx', $patient->AllowHealthInfoEx);
            $patient->AllowPatientPortal = $this->SafeGetVal($json, 'allowPatientPortal', $patient->AllowPatientPortal);
            //$patient->DeceasedDate = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'deceasedDate', $patient->DeceasedDate)));
            //$patient->DeceasedReason = $this->SafeGetVal($json, 'deceasedReason', $patient->DeceasedReason);
            //$patient->SoapImportStatus = $this->SafeGetVal($json, 'soapImportStatus', $patient->SoapImportStatus);
            //$patient->CmsportalLogin = $this->SafeGetVal($json, 'cmsportalLogin', $patient->CmsportalLogin);
            $patient->CareTeam = $this->SafeGetVal($json, 'careTeam', $patient->CareTeam);
            $patient->County = $this->SafeGetVal($json, 'county', $patient->County);
            //$patient->Industry = $this->SafeGetVal($json, 'industry', $patient->Industry);
            $patient->Validate();
            $errors = $patient->GetValidationErrors();
            if (count($errors) > 0) {
                $this->RenderErrorJSON('Please check the form for errors' . $errors, $errors);
            } else {
                $patient->Save(true);
                $this->RenderJSON($patient, $this->JSONPCallback(), true, $this->SimpleObjectParams());
            }
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method updates an existing Patient record and render response as JSON
     */
    public function Update()
    {
        try {
            $json = json_decode(RequestUtil::GetBody());
            if (! $json) {
                throw new Exception('The request body does not contain valid JSON');
            }

            $pk = $this->GetRouter()->GetUrlParam('id');
            $patient = $this->Phreezer->Get('Patient', $pk);
            // this is a primary key. uncomment if updating is allowed
            // $patient->Id = $this->SafeGetVal($json, 'id', $patient->Id);
            $patient->Title = $this->SafeGetVal($json, 'title', $patient->Title);
            $patient->Language = $this->SafeGetVal($json, 'language', $patient->Language);
            //$patient->Financial = $this->SafeGetVal($json, 'financial', $patient->Financial);
            $patient->Fname = $this->SafeGetVal($json, 'fname', $patient->Fname);
            $patient->Lname = $this->SafeGetVal($json, 'lname', $patient->Lname);
            $patient->Mname = $this->SafeGetVal($json, 'mname', $patient->Mname);
            $patient->Dob = date('Y-m-d', strtotime($this->SafeGetVal($json, 'dob', $patient->Dob)));
            $patient->Street = $this->SafeGetVal($json, 'street', $patient->Street);
            $patient->PostalCode = $this->SafeGetVal($json, 'postalCode', $patient->PostalCode);
            $patient->City = $this->SafeGetVal($json, 'city', $patient->City);
            $patient->State = $this->SafeGetVal($json, 'state', $patient->State);
            $patient->CountryCode = $this->SafeGetVal($json, 'countryCode', $patient->CountryCode);
            $patient->DriversLicense = $this->SafeGetVal($json, 'driversLicense', $patient->DriversLicense);
            $patient->Ss = $this->SafeGetVal($json, 'ss', $patient->Ss);
            $patient->Occupation = $this->SafeGetVal($json, 'occupation', $patient->Occupation);
            $patient->PhoneHome = $this->SafeGetVal($json, 'phoneHome', $patient->PhoneHome);
            $patient->PhoneBiz = $this->SafeGetVal($json, 'phoneBiz', $patient->PhoneBiz);
            $patient->PhoneContact = $this->SafeGetVal($json, 'phoneContact', $patient->PhoneContact);
            $patient->PhoneCell = $this->SafeGetVal($json, 'phoneCell', $patient->PhoneCell);
            $patient->PharmacyId = $this->SafeGetVal($json, 'pharmacyId', $patient->PharmacyId);
            $patient->Status = $this->SafeGetVal($json, 'status', $patient->Status);
            $patient->ContactRelationship = $this->SafeGetVal($json, 'contactRelationship', $patient->ContactRelationship);
            $patient->Date = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'date', $patient->Date)));
            $patient->Sex = $this->SafeGetVal($json, 'sex', $patient->Sex);
            $patient->Referrer = $this->SafeGetVal($json, 'referrer', $patient->Referrer);
            $patient->Referrerid = $this->SafeGetVal($json, 'referrerid', $patient->Referrerid);
            $patient->Providerid = $this->SafeGetVal($json, 'providerid', $patient->Providerid);
            $patient->RefProviderid = $this->SafeGetVal($json, 'refProviderid', $patient->RefProviderid);
            $patient->Email = $this->SafeGetVal($json, 'email', $patient->Email);
            $patient->EmailDirect = $this->SafeGetVal($json, 'emailDirect', $patient->EmailDirect);
            $patient->Ethnoracial = $this->SafeGetVal($json, 'ethnoracial', $patient->Ethnoracial);
            $patient->Race = $this->SafeGetVal($json, 'race', $patient->Race);
            $patient->Ethnicity = $this->SafeGetVal($json, 'ethnicity', $patient->Ethnicity);
            $patient->Religion = $this->SafeGetVal($json, 'religion', $patient->Religion);
            //$patient->Interpretter = $this->SafeGetVal($json, 'interpretter', $patient->Interpretter);
            //$patient->Migrantseasonal = $this->SafeGetVal($json, 'migrantseasonal', $patient->Migrantseasonal);
            $patient->FamilySize = $this->SafeGetVal($json, 'familySize', $patient->FamilySize);
            //$patient->MonthlyIncome = $this->SafeGetVal($json, 'monthlyIncome', $patient->MonthlyIncome);
            //$patient->BillingNote = $this->SafeGetVal($json, 'billingNote', $patient->BillingNote);
            //$patient->Homeless = $this->SafeGetVal($json, 'homeless', $patient->Homeless);
            //$patient->FinancialReview = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'financialReview', $patient->FinancialReview)));
            $patient->Pubpid = $this->SafeGetVal($json, 'pubpid', $patient->Pubpid);
            $patient->Pid = $this->SafeGetVal($json, 'pid', $patient->Pid);
            $patient->HipaaMail = $this->SafeGetVal($json, 'hipaaMail', $patient->HipaaMail);
            $patient->HipaaVoice = $this->SafeGetVal($json, 'hipaaVoice', $patient->HipaaVoice);
            $patient->HipaaNotice = $this->SafeGetVal($json, 'hipaaNotice', $patient->HipaaNotice);
            $patient->HipaaMessage = $this->SafeGetVal($json, 'hipaaMessage', $patient->HipaaMessage);
            $patient->HipaaAllowsms = $this->SafeGetVal($json, 'hipaaAllowsms', $patient->HipaaAllowsms);
            $patient->HipaaAllowemail = $this->SafeGetVal($json, 'hipaaAllowemail', $patient->HipaaAllowemail);
            //$patient->ReferralSource = $this->SafeGetVal($json, 'referralSource', $patient->ReferralSource);
            //$patient->Pricelevel = $this->SafeGetVal($json, 'pricelevel', $patient->Pricelevel);
            $patient->Regdate = date('Y-m-d', strtotime($this->SafeGetVal($json, 'regdate', $patient->Regdate)));
            //$patient->Contrastart = date('Y-m-d', strtotime($this->SafeGetVal($json, 'contrastart', $patient->Contrastart)));
            //$patient->CompletedAd = $this->SafeGetVal($json, 'completedAd', $patient->CompletedAd);
            //$patient->AdReviewed = date('Y-m-d', strtotime($this->SafeGetVal($json, 'adReviewed', $patient->AdReviewed)));
            //$patient->Vfc = $this->SafeGetVal($json, 'vfc', $patient->Vfc);
            $patient->Mothersname = $this->SafeGetVal($json, 'mothersname', $patient->Mothersname);
            $patient->Guardiansname = $this->SafeGetVal($json, 'guardiansname', $patient->Guardiansname);
            $patient->AllowImmRegUse = $this->SafeGetVal($json, 'allowImmRegUse', $patient->AllowImmRegUse);
            $patient->AllowImmInfoShare = $this->SafeGetVal($json, 'allowImmInfoShare', $patient->AllowImmInfoShare);
            $patient->AllowHealthInfoEx = $this->SafeGetVal($json, 'allowHealthInfoEx', $patient->AllowHealthInfoEx);
            $patient->AllowPatientPortal = $this->SafeGetVal($json, 'allowPatientPortal', $patient->AllowPatientPortal);
            $patient->CareTeam = $this->SafeGetVal($json, 'careTeam', $patient->CareTeam);
            $patient->County = $this->SafeGetVal($json, 'county', $patient->County);
            //$patient->Industry = $this->SafeGetVal($json, 'industry', $patient->Industry);
            $patient->Validate();
            $errors = $patient->GetValidationErrors();
            if (count($errors) > 0) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
            } else {
                $patient->Save();
                $this->CloseAudit($patient);
                $this->RenderJSON($patient, $this->JSONPCallback(), true, $this->SimpleObjectParams());
            }
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }
    public function CloseAudit($p)
    {
        $appsql = new ApplicationTable();
        $ja = $p->GetArray();
        try {
            $audit = array ();
            $audit['patient_id'] = $ja['pid'];
            $audit['activity'] = "profile";
            $audit['require_audit'] = "1";
            $audit['pending_action'] = "completed";
            $audit['action_taken'] = "accept";
            $audit['status'] = "closed";
            $audit['narrative'] = "Changes reviewed and commited to demographics.";
            $audit['table_action'] = "update";
            $audit['table_args'] = $ja;
            $audit['action_user'] = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "0";
            $audit['action_taken_time'] = date("Y-m-d H:i:s");
            $audit['checksum'] = "0";
            // returns false for new audit
            $edata = $appsql->getPortalAudit($ja['pid'], 'review');
            if ($edata) {
                if (empty($edata['id'])) {
                    throw new Exception("Invalid ID on Save!");
                }
                $audit['date'] = $edata['date'] ?? null;
                $appsql->portalAudit('update', $edata['id'], $audit);
            }
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }
    /**
     * API Method deletes an existing Patient record and render response as JSON
     */
    public function Delete()
    {
        try {
            $pk = $this->GetRouter()->GetUrlParam('id');
            $patient = $this->Phreezer->Get('Patient', $pk);
            $patient->Delete();
            $output = new stdClass();
            $this->RenderJSON($output, $this->JSONPCallback());
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }
}
