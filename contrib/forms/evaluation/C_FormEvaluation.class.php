<?php

/**
 * class C_FormEvaluation
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Daniel Ehrlich <daniel.ehrlich1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormEvaluation.class.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Csrf\CsrfUtils;

class C_FormEvaluation extends Controller
{

    var $template_dir;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->template_mod = $template_mod;
        $this->template_dir = dirname(__FILE__) . "/templates/evaluation/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);
        $this->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());
    }

    function default_action()
    {
        $evaluation = new FormEvaluation();
        $this->assign("checks", $evaluation->_form_layout());
        $this->assign("evaluation", $evaluation);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function view_action($form_id)
    {
        if (is_numeric($form_id)) {
            $evaluation = new FormEvaluation($form_id);
        } else {
            $evaluation = new FormEvaluation();
        }

        $this->assign("VIEW", true);
        $this->assign("checks", $evaluation->_form_layout());
        $this->assign("evaluation", $evaluation);
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $this->evaluation = new FormEvaluation($_POST['id']);
        parent::populate_object($this->evaluation);

        $this->evaluation->persist();
        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }

        addForm($GLOBALS['encounter'], "Evaluation Form", $this->evaluation->id, "evaluation", $GLOBALS['pid'], $_SESSION['userauthorized']);

        if (!empty($_POST['cpt_code'])) {
            $sql = "select * from codes where code = ? ORDER BY id";

            $results = sqlQ($sql, array($_POST['cpt_code']));

            $row = sqlFetchArray($results);
            if (!empty($row)) {
                BillingUtilites::addBilling(date("Ymd"), 'CPT4', $row['code'], $row['code_text'], $_SESSION['pid'], $_SESSION['userauthorized'], $_SESSION['authUserID'], $row['modifier'], $row['units'], $row['fee']);
            }
        }

        $_POST['process'] = "";
        return;
    }
}
