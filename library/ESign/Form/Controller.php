<?php

/**
 * Form controller implementation
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 OEMR 501c3 www.oemr.org
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/Abstract/Controller.php';
require_once $GLOBALS['srcdir'] . '/ESign/Form/Configuration.php';
require_once $GLOBALS['srcdir'] . '/ESign/Form/Factory.php';
require_once $GLOBALS['srcdir'] . '/ESign/Form/Log.php';

use OpenEMR\Common\Auth\AuthUtils;

class Form_Controller extends Abstract_Controller
{
    /**
     *
     */
    public function esign_form_view()
    {
        $form = new \stdClass();
        $form->table = 'forms';
        $form->formDir = $this->getRequest()->getParam('formdir', '');
        $form->formId = $this->getRequest()->getParam('formid', 0);
        $form->encounterId = $this->getRequest()->getParam('encounterid', 0);
        $form->userId = $GLOBALS['authUserID'] ?? null;
        $form->action = '#';
        $signable = new Form_Signable($form->formId, $form->formDir, $form->encounterId);
        $form->showLock = false;
        if (
            $signable->isLocked() === false &&
            $GLOBALS['lock_esign_individual'] &&
            $GLOBALS['esign_lock_toggle']
        ) {
            $form->showLock = true;
        }

        $this->_view->form = $form;
        $this->setViewScript('form/esign_form.php');
        $this->render();
    }

    public function esign_log_view()
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
     * @return multitype:string
     */
    public function esign_form_submit()
    {
        $message = '';
        $status = self::STATUS_FAILURE;
        $password = $this->getRequest()->getParam('password', '');
        $formId = $this->getRequest()->getParam('formId', '');
        $formDir = $this->getRequest()->getParam('formDir', '');
        $encounterId = $this->getRequest()->getParam('encounterId', '');
        // Always lock, unless esign_lock_toggle option is enable in globals
        $lock = true;
        if ($GLOBALS['esign_lock_toggle']) {
            $lock = ( $this->getRequest()->getParam('lock', '') == 'on' ) ? true : false;
        }

        $amendment = $this->getRequest()->getParam('amendment', '');

        $valid = (new AuthUtils())->confirmPassword($_SESSION['authUser'], $password);

        if ($valid) {
            $factory = new Form_Factory($formId, $formDir, $encounterId);
            $signable = $factory->createSignable();
            if ($signable->sign($_SESSION['authUserID'], $lock, $amendment)) {
                $message = xlt("Form signed successfully");
                $status = self::STATUS_SUCCESS;
            } else {
                $message = xlt("An error occured signing the form");
            }
        } else {
            $message = xlt("The password you entered is invalid");
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
}
