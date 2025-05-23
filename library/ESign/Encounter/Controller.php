<?php

/**
 * Encounter controller implementation
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 OEMR 501c3 www.oemr.org
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/Abstract/Controller.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Configuration.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Signable.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Log.php';

use OpenEMR\Common\Auth\AuthUtils;

class Encounter_Controller extends Abstract_Controller
{
    public function esign_is_encounter_locked()
    {
        $encounterId = $this->getRequest()->getParam('encounterId', '');
        $signable = new Encounter_Signable($encounterId);
        echo json_encode($signable->isLocked());
        exit;
    }

    public function esign_form_view()
    {
        $form = new \stdClass();
        $form->table = 'form_encounter';
        $form->encounterId = $this->getRequest()->getParam('encounterid', 0);
        $form->userId = $_SESSION['authUserID'];
        $form->action = '#';
        $signable = new Encounter_Signable($form->encounterId);
        $form->showLock = false;
        $form->displayGoogleSignin = (!empty($GLOBALS['google_signin_enabled']) && !empty($GLOBALS['google_signin_client_id'])) ? true : false;
        $form->googleSigninClientID = $GLOBALS['google_signin_client_id'];

        if (
            $signable->isLocked() === false &&
            $GLOBALS['lock_esign_all'] &&
            $GLOBALS['esign_lock_toggle']
        ) {
            $form->showLock = true;
        }

        $this->_view->form = $form;
        $this->setViewScript('encounter/esign_form.php');
        $this->render();
    }

    public function esign_log_view()
    {
        $encounterId = $this->getRequest()->getParam('encounterId', '');
        $signable = new Encounter_Signable($encounterId); // Contains features that make object signable
        $log = new Encounter_Log($encounterId); // Make the log behavior
        $html = $log->getHtml($signable);
        echo $html;
        exit;
    }

    /**
     *
     * @return multitype:string
     */
    public function esign_form_submit()
    {
        $message = '';
        $status = self::STATUS_FAILURE;
        $password = $this->getRequest()->getParam('password', '');
        $encounterId = $this->getRequest()->getParam('encounterId', '');

        // If google sign-in enable
        $usedGoogleSignin = $this->getRequest()->getParam('used_google_signin', '');
        $googleSigninToken = $this->getRequest()->getParam('google_signin_token', '');
        $force_google = (
            !empty($GLOBALS['google_signin_enabled']) &&
            !empty($GLOBALS['google_signin_client_id']) &&
            !empty($usedGoogleSignin) &&
            !empty($googleSigninToken)
        ) ? 1 : 0;

        // Lock if 'Lock e-signed encounters and their forms' option is set,
        // unless esign_lock_toggle option is enable in globals, then check the request param
        $lock = false;
        if ($GLOBALS['lock_esign_all']) {
            $lock = true;
            if ($GLOBALS['esign_lock_toggle']) {
                $lock = ( $this->getRequest()->getParam('lock', '') == 'on' ) ? true : false;
            }
        }

        $amendment = $this->getRequest()->getParam('amendment', '');

        // If google sign-in enable then valid google sign-in
        if ($force_google ===  1) {
            $valid = false;
            $uPayload = AuthUtils::verifyGoogleSignIn($googleSigninToken, false);
            if (!empty($uPayload) && isset($uPayload['id']) && $uPayload['id'] == $_SESSION['authUserID']) {
                $valid = true;
            }
            $gMessage = xlt("Invalid google log in");
        } else {
            $valid = (new AuthUtils())->confirmPassword($_SESSION['authUser'], $password);
        }

        if ($valid) {
            $signable = new Encounter_Signable($encounterId);
            if ($signable->sign($_SESSION['authUserID'], $lock, $amendment)) {
                $message = xlt("Form signed successfully");
                $status = self::STATUS_SUCCESS;
            } else {
                $message = xlt("An error occured signing the form");
            }
        } else {
            $message = (isset($gMessage) && !empty($gMessage)) ? $gMessage : xlt("The password you entered is invalid");
        }

        $response = new Response($status, $message);
        $response->encounterId = $encounterId;
        $response->locked = $lock;
        if ($lock) {
            $response->editButtonHtml = "<a href=# class='btn btn-secondary btn-sm form-edit-button-locked'>" . xlt('Locked') . "</a>";
        }

        echo json_encode($response);
        exit;
    }
}
