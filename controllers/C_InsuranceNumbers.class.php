<?php

/**
 * C_InsuranceNumbers class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


class C_InsuranceNumbers extends Controller
{

        var $template_mod;
        var $providers;
        var $insurance_numbers;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->providers = array();
        $this->insurance_numbers = array();
        $this->template_mod = $template_mod;
        $this->assign("FORM_ACTION", $GLOBALS['webroot'] . "/controller.php?" . attr($_SERVER['QUERY_STRING']));
        $this->assign("CURRENT_ACTION", $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&insurance_numbers&");
        $this->assign("STYLE", $GLOBALS['style']);
    }

    function default_action()
    {
        return $this->list_action();
    }

    function edit_action($id = "", $provider_id = "", $p_obj = null)
    {

        //case where a direct id is provided, doesn't matter if a provider id is available get it from the insurance_numbers record
        if ((empty($this->insurance_numbers[0]) || !is_object($this->insurance_numbers[0]) || get_class($this->insurance_numbers[0]) != "insurancenumbers") && is_numeric($id)) {
            $this->insurance_numbers[0] = new InsuranceNumbers($id);
            $this->providers[0] = new Provider($this->insurance_numbers[0]->get_provider_id());
        } elseif (is_numeric($provider_id)) {
            $this->providers[0] = new Provider($provider_id);
            if (empty($this->insurance_numbers[0]) || !is_object($this->insurance_numbers[0]) || get_class($this->insurance_numbers[0]) != "insurancenumbers") {
                if ($id == "default") {
                    $this->insurance_numbers[0] = $this->providers[0]->get_insurance_numbers_default();
                    if (!is_object($this->insurance_numbers[0])) {
                        $this->insurance_numbers[0] = new InsuranceNumbers();
                        $this->insurance_numbers[0]->set_provider_id($provider_id);
                    }
                } else {
                    $this->insurance_numbers[0] = new InsuranceNumbers();
                    $this->insurance_numbers[0]->set_provider_id($provider_id);
                }
            }
        } elseif (get_class($this->insurance_numbers[0]) == "insurancenumbers") {
            //this is the case that occurs after an update
            $this->providers[0] = new Provider($this->insurance_numbers[0]->get_provider_id());
        } else {
            $this->insurance_numbers[0] = new InsuranceNumbers();
            $this->providers[0] = new Provider();
            $this->assign("ERROR", "A provider must be specified. Check the link you you came from or the URL and try again.");
        }

        $ic = new InsuranceCompany();
        $icompanies =  $ic->insurance_companies_factory();

        //It is possible to set a group and provider number to be used in the event that there is not direct hit on the insurance-provider lookup
        //Those numbers are entered uder default
        $ic_array = array("Default");

        foreach ($icompanies as $ic_tmp) {
            $ic_array[$ic_tmp->get_id()] = $ic_tmp->get_name();
        }

        $ic_type_options_array = array();

        foreach ($this->insurance_numbers[0]->provider_number_type_array as $type => $type_title) {
            $ic_type_options_array[$type] = "$type  $type_title";
        }

        $ic_rendering_type_options_array = array();

        foreach ($this->insurance_numbers[0]->rendering_provider_number_type_array as $type => $type_title) {
            $ic_rendering_type_options_array[$type] = "$type  $type_title";
        }

        $this->assign("ic_array", $ic_array);
        $this->assign("ic_type_options_array", $ic_type_options_array);
        $this->assign("ic_rendering_type_options_array", $ic_rendering_type_options_array);

        $this->assign("provider", $this->providers[0]);
        $this->assign("ins", $this->insurance_numbers[0]);

        if (!empty($_GET['showform']) && ($_GET['showform'] == "true")) {
            $this->assign("show_edit_gui", true);
        } else {
            $this->assign("show_edit_gui", false);
        }

        return $this->fetch($GLOBALS['template_dir'] . "insurance_numbers/" . $this->template_mod . "_edit.html");
    }

    function list_action()
    {

        $p = new Provider();
        $this->assign("providers", $p->providers_factory());
        return $this->fetch($GLOBALS['template_dir'] . "insurance_numbers/" . $this->template_mod . "_list.html");
    }


    function edit_action_process()
    {
        if ($_POST['process'] != "true") {
                return;
        }

        //print_r($_POST);
        if (is_numeric($_POST['id'])) {
            $this->insurance_numbers[0] = new InsuranceNumbers($_POST['id']);
        } else {
            $this->insurance_numbers[0] = new InsuranceNumbers();
        }

        parent::populate_object($this->insurance_numbers[0]);

        $this->insurance_numbers[0]->persist();
        //insurance numbers need to be repopulated so that insurance_company_name recieves a value
        $this->insurance_numbers[0]->populate();

        //echo "action processeed";
        $_POST['process'] = "";

        if (!is_numeric($_POST['id'])) {//Z&H
            header('Location:' . $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&insurance_numbers&action=list");//Z&H
        }//Z&H
    }
}
