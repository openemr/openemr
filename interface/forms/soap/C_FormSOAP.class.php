<?php

/*
 * soap form
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/forms.inc.php");
require_once("FormSOAP.class.php");

use OpenEMR\Common\Twig\TwigContainer;

class C_FormSOAP extends Controller
{
    private TwigContainer $twig;
    public function __construct()
    {
        $path = $this->getTemplatePath();
        $this->twig = new TwigContainer($path);
    }

    /**
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     */
    function default_action()
    {
        $form = new FormSOAP();
        return $this->twig->getTwig()->render(
            'soap_form.twig',
            [
                "FORM_ACTION" => $GLOBALS['web_root'],
                "DONT_SAVE_LINK" => $GLOBALS['form_exit_url'],
                "data" => $form
            ]
        );
    }

    function view_action($form_id)
    {
        if (is_numeric($form_id)) {
            $form = new FormSOAP($form_id);
        } else {
            $form = new FormSOAP();
        }

        return $this->twig->getTwig()->render(
            'soap_form.twig',
            [
                "FORM_ACTION" => $GLOBALS['web_root'],
                "DONT_SAVE_LINK" => $GLOBALS['form_exit_url'],
                "data" => $form
            ]
        );
    }

    function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $this->form = new FormSOAP($_POST['id']);
        parent::populate_object($this->form);

        $this->form->persist();
        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }

        if (empty($_POST['id'])) {
            addForm(
                $GLOBALS['encounter'],
                "SOAP",
                $this->form->id,
                "soap",
                $GLOBALS['pid'],
                $_SESSION['userauthorized']
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
