<?php

require_once($GLOBALS['fileroot'] . "/library/forms.inc.php");
require_once("FormSOAP.class.php");

class C_FormSOAP extends Controller
{
    var $template_dir;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->template_mod = $template_mod;
        $this->template_dir = dirname(__FILE__) . "/templates/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);
    }

    function default_action()
    {
        $form = new FormSOAP();
        $this->assign("data", $form);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function view_action($form_id)
    {
        if (is_numeric($form_id)) {
            $form = new FormSOAP($form_id);
        } else {
            $form = new FormSOAP();
        }

        $dbconn = $GLOBALS['adodb']['db'];
        $this->assign("data", $form);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
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
                "soap2",
                $GLOBALS['pid'],
                $_SESSION['userauthorized']
            );
            $_POST['process'] = "";
        }

        return;
    }
}
