<?php

/**
 * PortalPatientController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
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
class PortalPatientController extends AppBasePortalController
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
        $rid = $pid = $user = $encounter = 0;
        if (isset($_GET['id'])) {
            $rid = (int) $_GET['id'];
        }

        if (isset($_GET['pid'])) {
            $pid = (int) $_GET['pid'];
        }

        if (isset($_GET['user'])) {
            $user = $_GET['user'];
        }

        if (isset($_GET['enc'])) {
            $encounter = $_GET['enc'];
        }

        $this->Assign('recid', $rid);
        $this->Assign('cpid', $pid);
        $this->Assign('cuser', $user);
        $this->Assign('encounter', $encounter);
        $this->Render();
    }

    /**
     * API Method queries for Patient records and render as JSON
     */
    public function Query()
    {
        try {
            $criteria = new PatientCriteria();
            $recnum = RequestUtil::Get('patientId');
            $criteria->Pid_Equals = $recnum;

            $output = new stdClass();

            // if a sort order was specified then specify in the criteria
            $output->orderBy = RequestUtil::Get('orderBy');
            $output->orderDesc = RequestUtil::Get('orderDesc') != '';
            if ($output->orderBy) {
                $criteria->SetOrder($output->orderBy, $output->orderDesc);
            }

            $page = RequestUtil::Get('page');

            // return all results
            $patientdata = $this->Phreezer->Query('PatientReporter', $criteria);
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
            // not required here but, represents patient rec id, not audit id.
            $pk = $this->GetRouter()->GetUrlParam('id');
            $ppid = RequestUtil::Get('patientId');
            $appsql = new ApplicationTable();
            $edata = $appsql->getPortalAudit($ppid, 'review');
            $changed = !empty($edata['table_args']) ? unserialize($edata['table_args'], ['allowed_classes' => false]) : [];
            $newv = array();
            foreach ($changed as $key => $val) {
                $newv[lcfirst(ucwords(preg_replace_callback("/(\_(.))/", function ($match) {
                    return strtoupper($match[2]);
                }, strtolower($key))))] = $val;
            }

            $this->RenderJSON($newv, $this->JSONPCallback(), false, $this->SimpleObjectParams());
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
            /*$patient->MonthlyIncome = $this->SafeGetVal($json, 'monthlyIncome', $patient->MonthlyIncome);
            $patient->BillingNote = $this->SafeGetVal($json, 'billingNote', $patient->BillingNote);
            $patient->Homeless = $this->SafeGetVal($json, 'homeless', $patient->Homeless);
            $patient->FinancialReview = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'financialReview', $patient->FinancialReview)));*/
            $patient->Pubpid = $this->SafeGetVal($json, 'pubpid', $patient->Pubpid);
            $patient->Pid = $this->SafeGetVal($json, 'pid', $patient->Pid);
            $patient->HipaaMail = $this->SafeGetVal($json, 'hipaaMail', $patient->HipaaMail);
            $patient->HipaaVoice = $this->SafeGetVal($json, 'hipaaVoice', $patient->HipaaVoice);
            $patient->HipaaNotice = $this->SafeGetVal($json, 'hipaaNotice', $patient->HipaaNotice);
            $patient->HipaaMessage = $this->SafeGetVal($json, 'hipaaMessage', $patient->HipaaMessage);
            $patient->HipaaAllowsms = $this->SafeGetVal($json, 'hipaaAllowsms', $patient->HipaaAllowsms);
            $patient->HipaaAllowemail = $this->SafeGetVal($json, 'hipaaAllowemail', $patient->HipaaAllowemail);
            /*$patient->Squad = $this->SafeGetVal($json, 'squad', $patient->Squad);
            $patient->Fitness = $this->SafeGetVal($json, 'fitness', $patient->Fitness);
            $patient->ReferralSource = $this->SafeGetVal($json, 'referralSource', $patient->ReferralSource);
            $patient->Pricelevel = $this->SafeGetVal($json, 'pricelevel', $patient->Pricelevel);*/
            if (!empty($patient->Regdate)) {
                $patient->Regdate = date('Y-m-d', strtotime($this->SafeGetVal($json, 'regdate', $patient->Regdate)));
            }
            /*$patient->Contrastart = date('Y-m-d', strtotime($this->SafeGetVal($json, 'contrastart', $patient->Contrastart)));
            $patient->CompletedAd = $this->SafeGetVal($json, 'completedAd', $patient->CompletedAd);
            $patient->AdReviewed = date('Y-m-d', strtotime($this->SafeGetVal($json, 'adReviewed', $patient->AdReviewed)));
            $patient->Vfc = $this->SafeGetVal($json, 'vfc', $patient->Vfc);*/
            $patient->Mothersname = $this->SafeGetVal($json, 'mothersname', $patient->Mothersname);
            $patient->Guardiansname = $this->SafeGetVal($json, 'guardiansname', $patient->Guardiansname);
            $patient->AllowImmRegUse = $this->SafeGetVal($json, 'allowImmRegUse', $patient->AllowImmRegUse);
            $patient->AllowImmInfoShare = $this->SafeGetVal($json, 'allowImmInfoShare', $patient->AllowImmInfoShare);
            $patient->AllowHealthInfoEx = $this->SafeGetVal($json, 'allowHealthInfoEx', $patient->AllowHealthInfoEx);
            $patient->AllowPatientPortal = $this->SafeGetVal($json, 'allowPatientPortal', $patient->AllowPatientPortal);
            $patient->CareTeam = $this->SafeGetVal($json, 'careTeam', $patient->CareTeam);
            $patient->County = $this->SafeGetVal($json, 'county', $patient->County);
            //$patient->Industry = $this->SafeGetVal($json, 'industry', $patient->Industry);
            $patient->Note = $this->SafeGetVal($json, 'note', $patient->Note);
            $patient->Validate();
            $errors = $patient->GetValidationErrors();

            if (count($errors) > 0) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
            } else {
                self::SaveAudit($patient);
                // $patient->Save(); //active records save
                $this->RenderJSON($patient, $this->JSONPCallback(), true, $this->SimpleObjectParams());
            }
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }
    public function SaveAudit($p)
    {
        $appsql = new ApplicationTable();
        $ja = $p->GetArray();
        $ja['note'] = $p->Note;
        try {
            $audit = array ();
            // date("Y-m-d H:i:s");
            $audit['patient_id'] = $ja['pid'];
            $audit['activity'] = "profile";
            $audit['require_audit'] = "1";
            $audit['pending_action'] = "review";
            $audit['action_taken'] = "";
            $audit['status'] = "waiting";
            $audit['narrative'] = "Patient request changes to demographics.";
            $audit['table_action'] = "";
            $audit['table_args'] = $ja; // edited record
            $audit['action_user'] = "0";
            $audit['action_taken_time'] = "";
            $audit['checksum'] = "0";

            // returns false for new audit
            $edata = $appsql->getPortalAudit($ja['pid'], 'review');
            if ($edata) {
                if (empty($edata['id'])) {
                    throw new Exception("Invalid ID on Save!");
                }
                $audit['date'] = $edata['date'] ?? null;
                $appsql->portalAudit('update', $edata['id'], $audit);
            } else {
                $appsql->portalAudit('insert', '', $audit);
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
            // TODO: if a soft delete is prefered, change this to update the deleted flag instead of hard-deleting

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
