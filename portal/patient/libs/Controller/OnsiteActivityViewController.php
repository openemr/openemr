<?php

/**
 * OnsiteActivityViewController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionWrapperFactory;

/**
 * import supporting libraries
 */
require_once("AppBasePortalController.php");
require_once("Model/OnsiteActivityView.php");

/**
 * OnsiteActivityViewController is the controller class for the OnsiteActivityView object.
 * The
 * controller is responsible for processing input from the user, reading/updating
 * the model as necessary and displaying the appropriate view.
 *
 * @package Patient Portal::Controller
 * @author  ClassBuilder
 * @version 1.0
 */
class OnsiteActivityViewController extends AppBasePortalController
{
    /**
     * Override here for any controller-specific functionality
     *
     * @inheritdocs
     */
    protected function Init()
    {
        parent::Init();

        // $this->RequirePermission(User::$PERMISSION_USER,'SecureApp.LoginForm');
    }

    /**
     * Displays a list view of Onsite Activity View objects
     */
    public function ListView()
    {
        $session = SessionWrapperFactory::getInstance()->getWrapper();
        $user = 0;
        if ($session->has('authUser')) {
            $user = $session->get('authUser');
        } else {
            header("refresh:5;url= ./provider");
            echo 'Redirecting in about 5 secs. Session shared with Onsite Portal<br /> Shared session not allowed!.';
            exit();
        }

        $this->Assign('cuser', $user);
        $this->Render();
    }

    /**
     * API Method queries for OnsiteActivityView records and render as JSON
     */
    public function Query()
    {
        try {
            $criteria = new OnsiteActivityViewCriteria();
            $status = RequestUtil::Get('status');
            $criteria->Status_Equals = $status;

            $filter = RequestUtil::Get('filter');
            if ($filter) {
                $criteria->AddFilter(new CriteriaFilter('Id,Date,PatientId,Activity,RequireAudit,PendingAction,ActionTaken,Status,Narrative,TableAction,TableArgs,ActionUser,ActionTakenTime,Checksum,Title,Fname,Lname,Mname,Dob,Ss,Street,PostalCode,City,State,Referrerid,Providerid,RefProviderid,Pubpid,CareTeam,Username,Authorized,Ufname,Umname,Ulname,Facility,Active,Utitle,PhysicianType', '%' . $filter . '%'));
            }

            // TODO: this is generic query filtering based only on criteria properties
            foreach (array_keys($_REQUEST) as $prop) {
                $prop_normal = ucfirst((string) $prop);
                $prop_equals = $prop_normal . '_Equals';

                if (property_exists($criteria, $prop_normal)) {
                    $criteria->$prop_normal = RequestUtil::Get($prop);
                } elseif (property_exists($criteria, $prop_equals)) {
                    // this is a convenience so that the _Equals suffix is not needed
                    $criteria->$prop_equals = RequestUtil::Get($prop);
                }
            }

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

                $onsiteactivityviews = $this->Phreezer->Query('OnsiteActivityViewReporter', $criteria)->GetDataPage($page, $pagesize);
                $output->rows = $onsiteactivityviews->ToObjectArray(true, $this->SimpleObjectParams());
                $output->totalResults = $onsiteactivityviews->TotalResults;
                $output->totalPages = $onsiteactivityviews->TotalPages;
                $output->pageSize = $onsiteactivityviews->PageSize;
                $output->currentPage = $onsiteactivityviews->CurrentPage;
            } else {
                // return all results
                $onsiteactivityviews = $this->Phreezer->Query('OnsiteActivityViewReporter', $criteria);
                $output->rows = $onsiteactivityviews->ToObjectArray(true, $this->SimpleObjectParams());
                $output->totalResults = count($output->rows);
                $output->totalPages = 1;
                $output->pageSize = $output->totalResults;
                $output->currentPage = 1;
            }

            $this->RenderJSON($output, $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method retrieves a single OnsiteActivityView record and render as JSON
     */
    public function Read()
    {
        try {
            $pk = $this->GetRouter()->GetUrlParam('id');
            $onsiteactivityview = $this->Phreezer->Get('OnsiteActivityView', $pk);
            $this->RenderJSON($onsiteactivityview, $this->JSONPCallback(), true, $this->SimpleObjectParams());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

}
