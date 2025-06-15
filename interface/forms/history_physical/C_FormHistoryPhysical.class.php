<?php
/*
 * History and Physical Note form
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/forms.inc.php");
require_once("FormHistoryPhysical.class.php");

use OpenEMR\Common\Twig\TwigContainer;

class C_FormHistoryPhysical extends Controller
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
        $form = new FormHistoryPhysical();
        return $this->twig->getTwig()->render(
            'history_physical_form.twig',
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
            $form = new FormHistoryPhysical($form_id);
        } else {
            $form = new FormHistoryPhysical();
        }

        return $this->twig->getTwig()->render(
            'history_physical_form.twig',
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

        $this->form = new FormHistoryPhysical($_POST['id']);
        parent::populate_object($this->form);

        $this->form->persist();
        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }

        if (empty($_POST['id'])) {
            addForm(
                $GLOBALS['encounter'],
                "History and Physical Note",
                $this->form->id,
                "history_physical",
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
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "history_physical/templates" . DIRECTORY_SEPARATOR;
    }
}
