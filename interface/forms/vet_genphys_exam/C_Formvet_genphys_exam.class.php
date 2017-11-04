<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
// Copyright (C) 2017 Roland Wick <ronhen_at_yandex_com>
// version 0.9
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2

require_once ($GLOBALS['fileroot'] . "/library/classes/Controller.class.php");
require_once($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("Formvet_genphys_exam.class.php");

class C_Formvet_genphys_exam extends Controller {

    var $template_dir;

    function __construct($template_mod = "general") {
        parent::__construct();
        $this->template_mod = $template_mod;
        $this->template_dir = dirname(__FILE__) . "/templates/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);
    }

    function default_action() {
        $form = new Formvet_genphys_exam();
        $this->assign("data", $form);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function view_action($form_id) {
        if (is_numeric($form_id)) {
            $form = new Formvet_genphys_exam($form_id);
        }
         else {
            $form = new Formvet_genphys_exam();
        }
        $dbconn = $GLOBALS['adodb']['db'];
        $this->assign("data", $form);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function default_action_process() {
        if ($_POST['process'] != "true") 
            return;
        $this->form = new Formvet_genphys_exam($_POST['id']);
        parent::populate_object($this->form);
        $this->form->persist();
        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }
        if (empty($_POST['id'])) {
            addForm($GLOBALS['encounter'], "Vet General Physical Exam", $this->form->id,   "vet_genphys_exam",
                $GLOBALS['pid'], $_SESSION['userauthorized']);
            $_POST['process'] = "";
        }
        return;
    }

}
?>
