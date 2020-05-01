<?php

/**
 * ROS form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormROS.class.php");

use OpenEMR\Common\Csrf\CsrfUtils;

class C_FormROS extends Controller
{

    var $template_dir;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $returnurl = 'encounter_top.php';
        $this->template_mod = $template_mod;
        $this->template_dir = dirname(__FILE__) . "/templates/ros/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);
        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());
    }

    function default_action()
    {
        $ros = new FormROS();
        $this->assign("form", $ros);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function view_action($form_id)
    {

        if (is_numeric($form_id)) {
            $ros = new FormROS($form_id);
        } else {
            $ros = new FormROS();
        }

        $this->assign("form", $ros);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $this->ros = new FormROS($_POST['id']);

        parent::populate_object($this->ros);
        $this->ros->persist();

        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }

        if (empty($_POST['id'])) {
            addForm($GLOBALS['encounter'], "Review Of Systems", $this->ros->id, "ros", $GLOBALS['pid'], $_SESSION['userauthorized']);
            $_POST['process'] = "";
        }

        return;
    }
}
