<?php

require_once($GLOBALS['fileroot'] . "/library/forms.inc");
require_once("FormReviewOfSystems.class.php");

use OpenEMR\Billing\BillingUtilities;

class C_FormReviewOfSystems extends Controller
{
    var $template_dir;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->template_mod = $template_mod;
        $this->template_dir = dirname(__FILE__) . "/templates/review_of_systems/";
        $this->assign("FORM_ACTION", $GLOBALS['web_root']);
        $this->assign("DONT_SAVE_LINK", $GLOBALS['form_exit_url']);
        $this->assign("STYLE", $GLOBALS['style']);
    }

    function default_action()
    {
        $review_of_systems = new FormReviewOfSystems();
        $this->assign("review_of_systems", $review_of_systems);
        $this->assign("checks", $review_of_systems->_form_layout());
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function view_action($form_id)
    {
        if (is_numeric($form_id)) {
            $review_of_systems = new FormReviewOfSystems($form_id);
        } else {
            $review_of_systems = new FormReviewOfSystems();
        }

        $this->assign("VIEW", true);
        $this->assign("review_of_systems", $review_of_systems);
        $this->assign("checks", $review_of_systems->_form_layout());
        return $this->fetch($this->template_dir . $this->template_mod . "_new.html");
    }

    function default_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $this->review_of_systems = new FormReviewOfSystems($_POST['id']);
        parent::populate_object($this->review_of_systems);
        $this->review_of_systems->persist();
        if ($GLOBALS['encounter'] == "") {
            $GLOBALS['encounter'] = date("Ymd");
        }

        addForm($GLOBALS['encounter'], "Review Of Systems", $this->review_of_systems->id, "review_of_systems", $GLOBALS['pid'], $_SESSION['userauthorized']);

        if (!empty($_POST['cpt_code'])) {
            $sql = "select * from codes where code = ? order by id";

            $results = sqlQ($sql, array($_POST['cpt_code']));

            $row = sqlFetchArray($results);
            if (!empty($row)) {
                BillingUtilities::addBilling(date("Ymd"), 'CPT4', $row['code'], $row['code_text'], $_SESSION['pid'], $_SESSION['userauthorized'], $_SESSION['authUserID'], $row['modifier'], $row['units'], $row['fee']);
            }
        }

        $_POST['process'] = "";
        return;
    }
}
