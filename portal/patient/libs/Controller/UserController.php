<?php

/**
 * UserController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
        $rid = 0;
        if (isset($_GET['id'])) {
            $rid = (int) $_GET['id'];
        }

        $this->Assign('recid', $rid);
        $this->Render();
    }

    /**
     * API Method queries for User records and render as JSON
     */
    public function Query()
    {
        try {
            $criteria = new UserCriteria();
            $recnum = RequestUtil::Get('recId');
            $criteria->Id_Equals = $recnum;

            $output = new stdClass();

            // if a sort order was specified then specify in the criteria
            $output->orderBy = RequestUtil::Get('orderBy');
            $output->orderDesc = RequestUtil::Get('orderDesc') != '';
            if ($output->orderBy) {
                $criteria->SetOrder($output->orderBy, $output->orderDesc);
            }

            $page = RequestUtil::Get('page');

            // return all results
            $users = $this->Phreezer->Query('User', $criteria);

            $output->rows = $users->ToObjectArray(true, $this->SimpleObjectParams());
            $output->totalResults = count($output->rows);
            $output->totalPages = 1;
            $output->pageSize = $output->totalResults;
            $output->currentPage = 1;

            if (!empty($GLOBALS['bootstrap_register']) || !empty($GLOBALS['bootstrap_pid'])) {
                // in this case, only provide id, fname, lname, speciality, active, authorized
                $outputToJson = json_encode($output);
                $jsonToArr = json_decode($outputToJson, true);
                $newArr = [];
                foreach ($jsonToArr as $akey => $avalue) {
                    if ($akey == "rows") {
                        foreach ($avalue as $bkey => $bvalue) {
                            foreach ($bvalue as $ckey => $cvalue) {
                                if (($ckey == 'id') || ($ckey == 'fname') || ($ckey == 'lname') || ($ckey == 'specialty') || ($ckey == 'active') || ($ckey == 'authorized')) {
                                    $newArr[$akey][$bkey][$ckey] = $cvalue;
                                }
                            }
                        }
                    } else {
                        $newArr[$akey] = $avalue;
                    }
                }
                $arrToJson = json_encode($newArr);
                $jsonToObject = json_decode($arrToJson);
                $output = $jsonToObject;
            }
            $this->RenderJSON($output, $this->JSONPCallback());
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method retrieves a single User record and render as JSON
     */
    public function Read()
    {
        try {
            $pk = $this->GetRouter()->GetUrlParam('id');
            $user = $this->Phreezer->Get('User', $pk);
            $this->RenderJSON($user, $this->JSONPCallback(), true, $this->SimpleObjectParams());
        } catch (Exception $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }
}
