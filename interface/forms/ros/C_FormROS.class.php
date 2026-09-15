<?php

/**
 * ROS form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (!defined('OPENEMR_GLOBALS_LOADED')) {
    http_response_code(404);
    exit();
}

require_once("FormROS.class.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\EncounterFormAccess;
use OpenEMR\Common\Forms\FormActionBarSettings;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

class C_FormROS extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $returnurl = 'encounter_top.php';
        $this->template_dir = __DIR__ . "/templates/ros/";
        $this->assign("FORM_ACTION", OEGlobalsBag::getInstance()->getWebRoot());
        $this->assign("DONT_SAVE_LINK", FormActionBarSettings::EXIT_URL);
        $this->assign("STYLE", OEGlobalsBag::getInstance()->get('style'));
        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken(session: $session));
    }

    public function default_action(): string
    {
        $ros = new FormROS();
        $this->assign("form", $ros);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    public function view_action(int|false|null $form_id): string
    {
        $formId = is_int($form_id) && $form_id >= 0 ? $form_id : 0;
        EncounterFormAccess::assertFormBelongsToSessionPatient($formId, 'ros');

        $ros = $formId > 0 ? new FormROS($formId) : new FormROS();

        $this->assign("form", $ros);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    public function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        // Empty-string POST id is the new-form case; missing/invalid → 0.
        $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $formId = is_int($postId) ? $postId : 0;
        EncounterFormAccess::assertFormBelongsToSessionPatient($formId, 'ros');

        $this->form = $formId > 0 ? new FormROS($formId) : new FormROS();

        parent::populate_object($this->form);
        EncounterFormAccess::applySessionPidToForm($this->form);
        $this->form->persist();

        if (OEGlobalsBag::getInstance()->get('encounter') == "") {
            OEGlobalsBag::getInstance()->set('encounter', date("Ymd"));
        }

        if ($formId === 0) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            addForm(OEGlobalsBag::getInstance()->get('encounter'), "Review Of Systems", $this->form->id, "ros", OEGlobalsBag::getInstance()->get('pid'), $session->get('userauthorized'));
            $_POST['process'] = "";
        }

        return;
    }
}
