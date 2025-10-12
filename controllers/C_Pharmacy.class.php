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
    public $pharmacies;
    public $totalpages;
    private $pageno;
    private $Pharmacy;

    function __construct(public $template_mod = "general")
    {
        parent::__construct();
        $this->pharmacies = [];
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

    function edit_action($id = "", $patient_id = "")
    {
        if (!(($this->pharmacies[0] ?? null) instanceof Pharmacy)) {
            $this->pharmacies[0] = new Pharmacy($id);
        }

        if (!empty($patient_id)) {
            $this->pharmacies[0]->set_patient_id($patient_id);
            $this->pharmacies[0]->set_provider($this->pharmacies[0]->patient->get_provider());
        }

        $this->assign("pharmacy", $this->pharmacies[0]);
        return $this->fetch($GLOBALS['template_dir'] . "pharmacies/" . $this->template_mod . "_edit.html");
    }

    function list_action()
    {
        $this->assign("pharmacies", $this->Pharmacy->pharmacies_factory());

        //print_r(Prescription::prescriptions_factory($id));
        return $this->fetch($GLOBALS['template_dir'] . "pharmacies/" . $this->template_mod . "_list.html");
    }


    function edit_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        //print_r($_POST);
        $this->pharmacies[0] = is_numeric($_POST['id']) ? new Pharmacy($_POST['id']) : new Pharmacy();

        parent::populate_object($this->pharmacies[0]);
        //print_r($this->pharmacies[0]);
        //echo $this->pharmacies[0]->toString(true);
        $this->pharmacies[0]->persist();
        //echo "action processeed";
        $_POST['process'] = "";
        header('Location:' . $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&pharmacy&action=list");//Z&H
    }
}
