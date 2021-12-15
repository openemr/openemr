<?php

/**
 * C_Pharmacy class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


class C_Pharmacy extends Controller
{
    var $template_mod;
    var $pharmacies;
    public $totalpages;
    private $pageno;
    private $Pharmacy;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->pharmacies = array();
        $this->template_mod = $template_mod;
        $this->assign("FORM_ACTION", $GLOBALS['webroot'] . "/controller.php?" . attr($_SERVER['QUERY_STRING']));
        $this->assign("CURRENT_ACTION", $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&pharmacy&");
        $this->assign("STYLE", $GLOBALS['style']);
        $this->Pharmacy = new Pharmacy();
        $this->totalpages = $this->Pharmacy->totalPages();
        $this->pageno = $this->Pharmacy->getPageno();
    }

    function default_action()
    {
        return $this->list_action();
    }

    function edit_action($id = "", $patient_id = "", $p_obj = null)
    {
        if ($p_obj != null && get_class($p_obj) == "pharmacy") {
            $this->pharmacies[0] = $p_obj;
        } elseif (empty($this->pharmacies[0]) || !is_object($this->pharmacies[0]) || get_class($this->pharmacies[0]) != "pharmacy") {
            $this->pharmacies[0] = new Pharmacy($id);
        }

        if (!empty($patient_id)) {
            $this->pharmacies[0]->set_patient_id($patient_id);
            $this->pharmacies[0]->set_provider($this->pharmacies[0]->patient->get_provider());
        }

        $this->assign("pharmacy", $this->pharmacies[0]);
        return $this->fetch($GLOBALS['template_dir'] . "pharmacies/" . $this->template_mod . "_edit.html");
    }

    function list_action($sort = "")
    {

        if (!empty($sort)) {
            $this->assign("pharmacies", $this->Pharmacy->pharmacies_factory("", $sort));
        } else {
            $this->assign("pharmacies", $this->Pharmacy->pharmacies_factory());
        }

        //print_r(Prescription::prescriptions_factory($id));
        return $this->fetch($GLOBALS['template_dir'] . "pharmacies/" . $this->template_mod . "_list.html");
    }


    function edit_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        //print_r($_POST);
        if (is_numeric($_POST['id'])) {
            $this->pharmacies[0] = new Pharmacy($_POST['id']);
        } else {
            $this->pharmacies[0] = new Pharmacy();
        }

        parent::populate_object($this->pharmacies[0]);
        //print_r($this->pharmacies[0]);
        //echo $this->pharmacies[0]->toString(true);
        $this->pharmacies[0]->persist();
        //echo "action processeed";
        $_POST['process'] = "";
        header('Location:' . $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&pharmacy&action=list");//Z&H
    }
}
