<?php

/**
 * Encounter controller implementation
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 OEMR
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/

namespace ESign;

require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Abstract/Controller.php';
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Encounter/Configuration.php';
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Encounter/Signable.php';
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Encounter/Log.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

class Encounter_Controller extends Abstract_Controller
{
    public function esign_is_encounter_locked(): never
    {
        $encounterId = $this->getRequest()->getParam('encounterId', '');
        $signable = new Encounter_Signable($encounterId);
        echo json_encode($signable->isLocked());
        exit;
    }

    public function esign_form_view()
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $form = new \stdClass();
        $form->table = 'form_encounter';
        $form->encounterId = $this->getRequest()->getParam('encounterid', 0);
        $form->userId = $session->get('authUserID');
        $form->action = '#';
        $signable = new Encounter_Signable($form->encounterId);
        $form->showLock = false;
        $form->displayGoogleSignin = OEGlobalsBag::getInstance()->getBoolean('google_signin_enabled') && !empty(OEGlobalsBag::getInstance()->getString('google_signin_client_id'));
        $form->googleSigninClientID = OEGlobalsBag::getInstance()->getString('google_signin_client_id');

        if (
            $signable->isLocked() === false &&
            OEGlobalsBag::getInstance()->getBoolean('lock_esign_all') &&
            OEGlobalsBag::getInstance()->getBoolean('esign_lock_toggle')
        ) {
            $form->showLock = true;
        }

        $this->_view->form = $form;
        $this->setViewScript('encounter/esign_form.php');
        $this->render();
    }

    public function esign_log_view(): never
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
     * @return string[]
     */
    public function esign_form_submit()
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $message = '';
        $status = self::STATUS_FAILURE;
        $password = $this->getRequest()->getParam('password', '');
        $encounterId = $this->getRequest()->getParam('encounterId', '');

        // Signing is an encounter authorization: "Authorize - any" (auth_a), or "Authorize - my encounters" (auth)
        // for an encounter the user created or is the provider of.
        if (!$this->canSignEncounter($encounterId)) {
            $response = new Response($status, xlt("You are not authorized to sign this encounter"));
            $response->encounterId = $encounterId;
            echo json_encode($response);
            exit;
        }

        // If google sign-in enable
        $usedGoogleSignin = $this->getRequest()->getParam('used_google_signin', '');
        $googleSigninToken = $this->getRequest()->getParam('google_signin_token', '');
        $force_google = (
            OEGlobalsBag::getInstance()->getBoolean('google_signin_enabled') &&
            !empty(OEGlobalsBag::getInstance()->getString('google_signin_client_id')) &&
            !empty($usedGoogleSignin) &&
            !empty($googleSigninToken)
        ) ? 1 : 0;

        // Lock if 'Lock e-signed encounters and their forms' option is set,
        // unless esign_lock_toggle option is enable in globals, then check the request param
        $lock = false;
        if (OEGlobalsBag::getInstance()->getBoolean('lock_esign_all')) {
            $lock = true;
            if (OEGlobalsBag::getInstance()->getBoolean('esign_lock_toggle')) {
                $lock = $this->getRequest()->getParam('lock', '') == 'on';
            }
        }

        $amendment = $this->getRequest()->getParam('amendment', '');

        // If google sign-in enable then valid google sign-in
        if ($force_google ===  1) {
            $valid = false;
            $uPayload = AuthUtils::verifyGoogleSignIn($googleSigninToken);
            if (!empty($uPayload) && isset($uPayload['id']) && $uPayload['id'] == $session->get('authUserID')) {
                $valid = true;
            }
            $gMessage = xlt("Invalid google log in");
        } else {
            $valid = (new AuthUtils())->confirmPassword($session->get('authUser'), $password);
        }

        if ($valid) {
            $signable = new Encounter_Signable($encounterId);
            if ($signable->sign($session->get('authUserID'), $lock, $amendment)) {
                $message = xlt("Form signed successfully");
                $status = self::STATUS_SUCCESS;
            } else {
                $message = xlt("An error occurred signing the form");
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

    /**
     * Whether the signed-in user may sign the encounter: auth_a for any encounter, auth for their own.
     */
    private function canSignEncounter(mixed $encounterId): bool
    {
        if (AclMain::aclCheckCore('encounters', 'auth_a')) {
            return true;
        }
        if (!AclMain::aclCheckCore('encounters', 'auth')) {
            return false;
        }
        $id = filter_var($encounterId, FILTER_VALIDATE_INT);
        if ($id === false) {
            return false;
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $creator = QueryUtils::fetchSingleValue(
            "SELECT `user` FROM `forms` WHERE `encounter` = ? AND `formdir` = 'newpatient' LIMIT 1",
            'user',
            [$id]
        );
        $provider = QueryUtils::fetchSingleValue(
            'SELECT `provider_id` FROM `form_encounter` WHERE `encounter` = ? LIMIT 1',
            'provider_id',
            [$id]
        );
        if (is_string($creator) && $creator !== '' && $creator === $session->get('authUser')) {
            return true;
        }
        $providerId = filter_var($provider, FILTER_VALIDATE_INT);
        return $providerId !== false && $providerId > 0 && $providerId === filter_var($session->get('authUserID'), FILTER_VALIDATE_INT);
    }
}
