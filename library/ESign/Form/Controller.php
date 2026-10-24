<?php

/**
 * Form controller implementation
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 OEMR
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/

namespace ESign;

require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Abstract/Controller.php';
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Form/Configuration.php';
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Form/Factory.php';
require_once \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . '/ESign/Form/Log.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

class Form_Controller extends Abstract_Controller
{
    /**
     *
     */
    public function esign_form_view()
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $form = new \stdClass();
        $form->table = 'forms';
        $form->formDir = $this->getRequest()->getParam('formdir', '');
        $form->formId = $this->getRequest()->getParam('formid', 0);
        $form->encounterId = $this->getRequest()->getParam('encounterid', 0);
        $form->userId = $session->get('authUserID');
        $form->action = '#';
        $signable = new Form_Signable($form->formId, $form->formDir, $form->encounterId);
        $form->showLock = false;
        $form->displayGoogleSignin = OEGlobalsBag::getInstance()->getBoolean('google_signin_enabled') && !empty(OEGlobalsBag::getInstance()->getString('google_signin_client_id'));
        $form->googleSigninClientID = OEGlobalsBag::getInstance()->getString('google_signin_client_id');
        if (
            $signable->isLocked() === false &&
            OEGlobalsBag::getInstance()->getBoolean('lock_esign_individual') &&
            OEGlobalsBag::getInstance()->getBoolean('esign_lock_toggle')
        ) {
            $form->showLock = true;
        }

        $this->_view->form = $form;
        $this->setViewScript('form/esign_form.php');
        $this->render();
    }

    public function esign_log_view(): never
    {
        $formId = $this->getRequest()->getParam('formId', '');
        $formDir = $this->getRequest()->getParam('formDir', '');
        $encounterId = $this->getRequest()->getParam('encounterId', '');
        $factory = new Form_Factory($formId, $formDir, $encounterId);
        $signable = $factory->createSignable(); // Contains features that make object signable
        $log = $factory->createLog(); // Make the log behavior
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
        $formId = $this->getRequest()->getParam('formId', '');
        $formDir = $this->getRequest()->getParam('formDir', '');
        $encounterId = $this->getRequest()->getParam('encounterId', '');

        // If google sign-in enable
        $usedGoogleSignin = $this->getRequest()->getParam('used_google_signin', '');
        $googleSigninToken = $this->getRequest()->getParam('google_signin_token', '');
        $force_google = (
            OEGlobalsBag::getInstance()->getBoolean('google_signin_enabled') &&
            !empty(OEGlobalsBag::getInstance()->getString('google_signin_client_id')) &&
            !empty($usedGoogleSignin) &&
            !empty($googleSigninToken)
        ) ? 1 : 0;

        // Always lock, unless esign_lock_toggle option is enable in globals
        $lock = true;
        if (OEGlobalsBag::getInstance()->getBoolean('esign_lock_toggle')) {
            $lock = $this->getRequest()->getParam('lock', '') == 'on';
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
            // Mirror the per-form write-ACL + session-bound (pid, encounter)
            // gating the encounter forms list uses before exposing the sign
            // button. Rejects direct submissions that target forms whose
            // registry declares an aco_spec the caller does not hold, or that
            // reference a form row outside the caller's active chart context.
            $formIdInt = is_numeric($formId) ? (int) $formId : 0;
            $encounterIdInt = is_numeric($encounterId) ? (int) $encounterId : 0;
            $formDirStr = is_string($formDir) ? $formDir : '';
            if (!$this->callerMaySignForm($formDirStr, $formIdInt, $encounterIdInt)) {
                $message = xlt("You are not authorized to sign this form");
            } else {
                $factory = new Form_Factory($formId, $formDir, $encounterId);
                $signable = $factory->createSignable();
                if ($signable->sign($session->get('authUserID'), $lock, $amendment)) {
                    $message = xlt("Form signed successfully");
                    $status = self::STATUS_SUCCESS;
                } else {
                    $message = xlt("An error occurred signing the form");
                }
            }
        } else {
            $message = (isset($gMessage) && !empty($gMessage)) ? $gMessage : xlt("The password you entered is invalid");
        }

        $response = new Response($status, $message);
        $response->formId = $formId;
        $response->formDir = $formDir;
        $response->encounterId = $encounterId;
        $response->locked = $lock;
        $response->editButtonHtml = "";
        if ($lock) {
            // If we're locking the form, replace the edit button with a "disabled" lock button
            $response->editButtonHtml = "<a href=# class='btn btn-secondary btn-sm form-edit-button-locked' id='form-edit-button-'" . attr($formDir) . "-" . attr($formId) . ">" . xlt('Locked') . "</a>";
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Verify the caller may sign the referenced encounter form.
     *
     * Mirrors the pre-render checks performed by
     * interface/patient_file/encounter/forms.php: the form row must belong to
     * the caller's active session (pid, encounter), and if the form's registry
     * entry declares an aco_spec the caller must hold the matching write ACL.
     */
    private function callerMaySignForm(string $formDir, int $formId, int $encounterId): bool
    {
        if ($formId <= 0 || $encounterId <= 0 || $formDir === '') {
            return false;
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $pidRaw = $session->get('pid');
        $encRaw = $session->get('encounter');
        $therapyGroupRaw = $session->get('therapy_group');
        $sessionPid = is_numeric($pidRaw) ? (int) $pidRaw : 0;
        $sessionEncounter = is_numeric($encRaw) ? (int) $encRaw : 0;
        $sessionTherapyGroup = is_numeric($therapyGroupRaw) ? (int) $therapyGroupRaw : 0;
        if (
            $sessionEncounter <= 0
            || $sessionEncounter !== $encounterId
            || ($sessionPid <= 0 && $sessionTherapyGroup <= 0)
        ) {
            return false;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT pid, encounter, formdir, therapy_group_id FROM forms WHERE id = ? LIMIT 1',
            [$formId]
        );
        if (!is_array($row)) {
            return false;
        }
        $rowPid = is_numeric($row['pid'] ?? null) ? (int) $row['pid'] : 0;
        $rowEnc = is_numeric($row['encounter'] ?? null) ? (int) $row['encounter'] : 0;
        $rowDir = is_string($row['formdir'] ?? null) ? $row['formdir'] : '';
        $rowTherapyGroup = is_numeric($row['therapy_group_id'] ?? null) ? (int) $row['therapy_group_id'] : 0;
        if ($rowEnc !== $sessionEncounter || $rowDir !== $formDir) {
            return false;
        }
        if ($sessionPid > 0) {
            // Patient session: form must match the patient and not be a group row.
            if ($rowPid !== $sessionPid || $rowTherapyGroup !== 0) {
                return false;
            }
        } else {
            // Group therapy session: form must have no patient and match the group.
            if ($rowPid !== 0 || $rowTherapyGroup !== $sessionTherapyGroup) {
                return false;
            }
            // Group signing additionally requires write on groups/glog, matching
            // the gate forms.php applies before exposing the group e-sign button.
            if (!AclMain::aclCheckCore('groups', 'glog', '', 'write')) {
                return false;
            }
        }

        // Encounter calendar category write ACL — applies to both patient and
        // group forms, mirrors $authPostCalendarCategoryWrite in forms.php.
        $pcCatAcoRaw = AclMain::fetchPostCalendarCategoryACO(fetchCategoryIdByEncounter($encounterId));
        if (is_string($pcCatAcoRaw) && $pcCatAcoRaw !== '') {
            $pcCatAco = explode('|', $pcCatAcoRaw);
            if (count($pcCatAco) >= 2 && !AclMain::aclCheckCore($pcCatAco[0], $pcCatAco[1], '', 'write')) {
                return false;
            }
        }

        // Mirror interface/patient_file/encounter/forms.php: LBF forms derive
        // their ACL from layout_group_properties (user-configurable in the
        // Layouts admin); non-LBF forms derive theirs from the forms registry.
        if (str_starts_with($formDir, 'LBF')) {
            $layoutGroup = QueryUtils::querySingleRow(
                "SELECT grp_aco_spec FROM layout_group_properties WHERE " .
                "grp_form_id = ? AND grp_group_id = '' AND grp_activity = 1",
                [$formDir]
            );
            $layoutAcoSpecRaw = is_array($layoutGroup)
                && isset($layoutGroup['grp_aco_spec'])
                && is_string($layoutGroup['grp_aco_spec'])
                ? $layoutGroup['grp_aco_spec']
                : '';
            if ($layoutAcoSpecRaw !== '') {
                $layoutAcoSpec = explode('|', $layoutAcoSpecRaw);
                if (count($layoutAcoSpec) >= 2 && !AclMain::aclCheckCore($layoutAcoSpec[0], $layoutAcoSpec[1], '', 'write')) {
                    return false;
                }
            }
        } else {
            $registry = getRegistryEntryByDirectory($formDir, 'aco_spec');
            $acoSpecRaw = is_array($registry) && isset($registry['aco_spec']) && is_string($registry['aco_spec'])
                ? $registry['aco_spec']
                : '';
            if ($acoSpecRaw !== '') {
                $acoSpec = explode('|', $acoSpecRaw);
                if (count($acoSpec) >= 2 && !AclMain::aclCheckCore($acoSpec[0], $acoSpec[1], '', 'write')) {
                    return false;
                }
            }
        }

        return true;
    }
}
