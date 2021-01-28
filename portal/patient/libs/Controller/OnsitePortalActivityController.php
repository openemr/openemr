<?php

/**
 * OnsitePortalActivityController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** import supporting libraries */
require_once("AppBasePortalController.php");
require_once("Model/OnsitePortalActivity.php");

/**
 * OnsitePortalActivityController is the controller class for the OnsitePortalActivity object.  The
 * controller is responsible for processing input from the user, reading/updating
 * the model as necessary and displaying the appropriate view.
 *
 * @package Patient Portal::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class OnsitePortalActivityController extends AppBasePortalController
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
     * Displays a list view of OnsitePortalActivity objects
     */
    public function ListView()
    {
        $this->Render();
    }

    /**
     * API Method queries for OnsitePortalActivity records and render as JSON
     */
    public function Query()
    {
        try {
            $criteria = new OnsitePortalActivityCriteria();

            // only allow patient to see their own activity
            if (!empty($GLOBALS['bootstrap_pid'])) {
                $pid = $GLOBALS['bootstrap_pid'];
            } else {
                $pid = RequestUtil::Get('patientId');
            }

            $activity = RequestUtil::Get('activity');
            $doc = RequestUtil::Get('doc');
            $doc = $doc ? $doc : 0;
            $criteria->PatientId_Equals = $pid;
            $criteria->Activity_Equals = $activity;
            $criteria->TableArgs_Equals = $doc;

            $output = new stdClass();

            // if a sort order was specified then specify in the criteria
            $output->orderBy = RequestUtil::Get('orderBy');
            $output->orderDesc = RequestUtil::Get('orderDesc') != '';
            if ($output->orderBy) {
                $criteria->SetOrder($output->orderBy, $output->orderDesc);
            }

            $page = RequestUtil::Get('page');

            if ($page != '') {
                // if page is specified, use this instead (at the expense of one extra count query)
                $pagesize = $this->GetDefaultPageSize();

                $onsiteportalactivities = $this->Phreezer->Query('OnsitePortalActivity', $criteria)->GetDataPage($page, $pagesize);
                $output->rows = $onsiteportalactivities->ToObjectArray(true, $this->SimpleObjectParams());
                $output->totalResults = $onsiteportalactivities->TotalResults;
                $output->totalPages = $onsiteportalactivities->TotalPages;
                $output->pageSize = $onsiteportalactivities->PageSize;
                $output->currentPage = $onsiteportalactivities->CurrentPage;
            } else {
                // return all results
                $onsiteportalactivities = $this->Phreezer->Query('OnsitePortalActivity', $criteria);
                $output->rows = $onsiteportalactivities->ToObjectArray(true, $this->SimpleObjectParams());
                $output->totalResults = count($output->rows);
                $output->totalPages = 1;
                $output->pageSize = $output->totalResults;
                $output->currentPage = 1;
            }


            $this->RenderJSON($output, $this->JSONPCallback());
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method retrieves a single OnsitePortalActivity record and render as JSON
     */
    public function Read()
    {
        try {
            $pk = $this->GetRouter()->GetUrlParam('id');
            $onsiteportalactivity = $this->Phreezer->Get('OnsitePortalActivity', $pk);
            // only allow patient to update onsiteportalactivity about themself
            if (!empty($GLOBALS['bootstrap_pid'])) {
                if ($GLOBALS['bootstrap_pid'] !== $onsiteportalactivity->PatientId) {
                    $error = 'Unauthorized';
                    throw new Exception($error);
                }
            }
            $this->RenderJSON($onsiteportalactivity, $this->JSONPCallback(), true, $this->SimpleObjectParams());
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method inserts a new OnsitePortalActivity record and render response as JSON
     */
    public function Create()
    {
        try {
            $json = json_decode(RequestUtil::GetBody());

            if (!$json) {
                throw new Exception('The request body does not contain valid JSON');
            }

            $onsiteportalactivity = new OnsitePortalActivity($this->Phreezer);

            // TODO: any fields that should not be inserted by the user should be commented out

            // this is an auto-increment.  uncomment if updating is allowed
            // $onsiteportalactivity->Id = $this->SafeGetVal($json, 'id');

            $onsiteportalactivity->Date = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'date')));

            // only allow patient to create onsiteportalactivity about themself
            if (!empty($GLOBALS['bootstrap_pid'])) {
                $onsiteportalactivity->PatientId = $GLOBALS['bootstrap_pid'];
            } else {
                $onsiteportalactivity->PatientId = $this->SafeGetVal($json, 'patientId');
            }

            $onsiteportalactivity->Activity = $this->SafeGetVal($json, 'activity');
            $onsiteportalactivity->RequireAudit = $this->SafeGetVal($json, 'requireAudit');
            $onsiteportalactivity->PendingAction = $this->SafeGetVal($json, 'pendingAction');
            $onsiteportalactivity->ActionTaken = $this->SafeGetVal($json, 'actionTaken');
            $onsiteportalactivity->Status = $this->SafeGetVal($json, 'status');
            $onsiteportalactivity->Narrative = $this->SafeGetVal($json, 'narrative');
            $onsiteportalactivity->TableAction = $this->SafeGetVal($json, 'tableAction');
            $onsiteportalactivity->TableArgs = $this->SafeGetVal($json, 'tableArgs');
            $onsiteportalactivity->ActionUser = $this->SafeGetVal($json, 'actionUser');
            $onsiteportalactivity->ActionTakenTime = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'actionTakenTime')));
            $onsiteportalactivity->Checksum = $this->SafeGetVal($json, 'checksum');

            $onsiteportalactivity->Validate();
            $errors = $onsiteportalactivity->GetValidationErrors();

            if (count($errors) > 0) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
            } else {
                $onsiteportalactivity->Save();
                $this->RenderJSON($onsiteportalactivity, $this->JSONPCallback(), true, $this->SimpleObjectParams());
            }
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method updates an existing OnsitePortalActivity record and render response as JSON
     */
    public function Update()
    {
        try {
            $json = json_decode(RequestUtil::GetBody());

            if (!$json) {
                throw new Exception('The request body does not contain valid JSON');
            }

            $pk = $this->GetRouter()->GetUrlParam('id');
            $onsiteportalactivity = $this->Phreezer->Get('OnsitePortalActivity', $pk);

            // only allow patient to update onsiteportalactivity about themself
            if (!empty($GLOBALS['bootstrap_pid'])) {
                if ($GLOBALS['bootstrap_pid'] != $this->SafeGetVal($json, 'patientId', $onsiteportalactivity->PatientId)) {
                    throw new Exception('Bad PID');
                }
            }

            // TODO: any fields that should not be updated by the user should be commented out

            // this is a primary key.  uncomment if updating is allowed
            // $onsiteportalactivity->Id = $this->SafeGetVal($json, 'id', $onsiteportalactivity->Id);

            $onsiteportalactivity->Date = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'date', $onsiteportalactivity->Date)));
            $onsiteportalactivity->PatientId = $this->SafeGetVal($json, 'patientId', $onsiteportalactivity->PatientId);
            $onsiteportalactivity->Activity = $this->SafeGetVal($json, 'activity', $onsiteportalactivity->Activity);
            $onsiteportalactivity->RequireAudit = $this->SafeGetVal($json, 'requireAudit', $onsiteportalactivity->RequireAudit);
            $onsiteportalactivity->PendingAction = $this->SafeGetVal($json, 'pendingAction', $onsiteportalactivity->PendingAction);
            $onsiteportalactivity->ActionTaken = $this->SafeGetVal($json, 'actionTaken', $onsiteportalactivity->ActionTaken);
            $onsiteportalactivity->Status = $this->SafeGetVal($json, 'status', $onsiteportalactivity->Status);
            $onsiteportalactivity->Narrative = $this->SafeGetVal($json, 'narrative', $onsiteportalactivity->Narrative);
            $onsiteportalactivity->TableAction = $this->SafeGetVal($json, 'tableAction', $onsiteportalactivity->TableAction);
            $onsiteportalactivity->TableArgs = $this->SafeGetVal($json, 'tableArgs', $onsiteportalactivity->TableArgs);
            $onsiteportalactivity->ActionUser = $this->SafeGetVal($json, 'actionUser', $onsiteportalactivity->ActionUser);
            $onsiteportalactivity->ActionTakenTime = date('Y-m-d H:i:s', strtotime($this->SafeGetVal($json, 'actionTakenTime', $onsiteportalactivity->ActionTakenTime)));
            $onsiteportalactivity->Checksum = $this->SafeGetVal($json, 'checksum', $onsiteportalactivity->Checksum);

            $onsiteportalactivity->Validate();
            $errors = $onsiteportalactivity->GetValidationErrors();

            if (count($errors) > 0) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
            } else {
                $onsiteportalactivity->Save();
                $this->RenderJSON($onsiteportalactivity, $this->JSONPCallback(), true, $this->SimpleObjectParams());
            }
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method deletes an existing OnsitePortalActivity record and render response as JSON
     */
    public function Delete()
    {
        try {
            // TODO: if a soft delete is prefered, change this to update the deleted flag instead of hard-deleting

            $pk = $this->GetRouter()->GetUrlParam('id');
            $onsiteportalactivity = $this->Phreezer->Get('OnsitePortalActivity', $pk);

            $onsiteportalactivity->Delete();

            $output = new stdClass();

            $this->RenderJSON($output, $this->JSONPCallback());
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }
}
