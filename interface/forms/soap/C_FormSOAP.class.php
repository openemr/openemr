<?php

/*
 * soap form
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(\OpenEMR\Core\OEGlobalsBag::getInstance()->getProjectDir() . "/library/forms.inc.php");
require_once("FormSOAP.class.php");

use OpenEMR\Common\Forms\EncounterFormAccess;
use OpenEMR\Common\Forms\FormActionBarSettings;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;

class C_FormSOAP extends Controller
{
    public function __construct()
    {
        parent::__construct(
            (new TwigContainer($this->getTemplatePath(), OEGlobalsBag::getInstance()->getKernel()))->getTwig(),
        );
    }

    /**
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     */
    function default_action(): string
    {
        $form = new FormSOAP();
        return $this->twig->render(
            'soap_form.twig',
            [
                "FORM_ACTION" => OEGlobalsBag::getInstance()->getWebRoot(),
                "DONT_SAVE_LINK" => FormActionBarSettings::EXIT_URL,
                "data" => $form
            ]
        );
    }

    public function view_action(int|false|null $form_id): string
    {
        $formId = is_int($form_id) && $form_id >= 0 ? $form_id : 0;
        EncounterFormAccess::assertFormBelongsToSessionPatient($formId, 'soap');

        $form = $formId > 0 ? new FormSOAP($formId) : new FormSOAP();

        return $this->twig->render(
            'soap_form.twig',
            [
                "FORM_ACTION" => OEGlobalsBag::getInstance()->getWebRoot(),
                "DONT_SAVE_LINK" => FormActionBarSettings::EXIT_URL,
                "data" => $form
            ]
        );
    }

    function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        // Empty-string POST id is the new-form case; missing/invalid → 0.
        $postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $formId = is_int($postId) ? $postId : 0;
        EncounterFormAccess::assertFormBelongsToSessionPatient($formId, 'soap');

        $this->form = $formId > 0 ? new FormSOAP($formId) : new FormSOAP();
        parent::populate_object($this->form);
        EncounterFormAccess::applySessionPidToForm($this->form);

        $this->form->persist();
        if (OEGlobalsBag::getInstance()->get('encounter') == "") {
            OEGlobalsBag::getInstance()->set('encounter', date("Ymd"));
        }

        if ($formId === 0) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            addForm(
                OEGlobalsBag::getInstance()->get('encounter'),
                "SOAP",
                $this->form->id,
                "soap",
                OEGlobalsBag::getInstance()->get('pid'),
                $session->get('userauthorized')
            );
            $_POST['process'] = "";
        }
    }
    /**
     * @return string
     */
    private function getTemplatePath(): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "soap/templates" . DIRECTORY_SEPARATOR;
    }
}
