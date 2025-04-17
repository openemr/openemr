<?php

/**
 * prior auth form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/forms.inc.php");
require_once("FormPriorAuth.class.php");

use OpenEMR\Common\Csrf\CsrfUtils;

class C_FormPriorAuth extends Controller
{
    var $template_dir;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $returnurl = 'encounter_top.php';
        $this->template_mod = $template_mod;
        $this->template_dir = dirname(__FILE__) . "/templates/prior_auth/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);
        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());
    }

    function default_action()
    {
        $prior_auth = new FormPriorAuth();
        $this->assign("prior_auth", $prior_auth);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function view_action($form_id)
    {
        if (is_numeric($form_id)) {
            $prior_auth = new FormPriorAuth($form_id);
        } else {
            $prior_auth = new FormPriorAuth();
        }

        $this->assign("VIEW", true);
        $this->assign("prior_auth", $prior_auth);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $this->form = new FormPriorAuth($_POST['id']);
        parent::populate_object($this->form);


        $this->form->persist();
        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }

        if (empty($_POST['id'])) {
            addForm($GLOBALS['encounter'], "Prior Authorization", $this->form->id, "prior_auth", $GLOBALS['pid'], $_SESSION['userauthorized']);
            $_POST['process'] = "";
        }
        return;
    }
}
