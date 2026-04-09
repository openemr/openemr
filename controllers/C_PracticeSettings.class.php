<?php

use OpenEMR\Core\OEGlobalsBag;

class C_PracticeSettings extends Controller
{
    public $direction;

    function __construct(public $template_mod = "general")
    {
        parent::__construct();
        $this->assign("FORM_ACTION", OEGlobalsBag::getInstance()->get('webroot') . "/controller.php?" . attr($_SERVER['QUERY_STRING']));
        $this->assign("TOP_ACTION", OEGlobalsBag::getInstance()->get('webroot') . "/controller.php?" . "practice_settings" . "&");
        $this->assign("STYLE", OEGlobalsBag::getInstance()->get('style'));
        $this->direction = (OEGlobalsBag::getInstance()->get('_SESSION')['language_direction'] == 'rtl') ? 'right' : 'left';
    }

    function default_action($display = "")
    {
        $this->assign("display", $display);
        $this->assign("direction", $this->direction);
        $this->display(OEGlobalsBag::getInstance()->get('template_dir') . "practice_settings/" . $this->template_mod . "_list.html");
    }

    function pharmacy_action($arg)
    {
        $c = new Controller();
        $fga = func_get_args();
        $fga = array_slice($fga, 1);
        $params = array_merge(['controller' => 'pharmacy', 'action' => $arg], $fga);
        $this->assign("direction", $this->direction);
        $display = $c->dispatch($params);
        $this->assign("ACTION_NAME", xl("Pharmacies"));
        $this->default_action($display);
    }

    function insurance_company_action($arg)
    {
        $c = new Controller();
        $fga = func_get_args();
        $fga = array_slice($fga, 1);
        $params = array_merge(['controller' => 'insurance_company', 'action' => $arg], $fga);
        $display = $c->dispatch($params);
        $this->assign("direction", $this->direction);
        $this->assign("ACTION_NAME", xl("Insurance Companies"));
        $this->default_action($display);
    }

    function insurance_numbers_action($arg)
    {
        $c = new Controller();
        $fga = func_get_args();
        $fga = array_slice($fga, 1);
        $params = array_merge(['controller' => 'insurance_numbers', 'action' => $arg], $fga);
        $display = $c->dispatch($params);
        $this->assign("ACTION_NAME", xl("Insurance Numbers"));
        $this->assign("direction", $this->direction);
        $this->default_action($display);
    }

    function document_action($arg)
    {
        $c = new Controller();
        $fga = func_get_args();
        $fga = array_slice($fga, 1);
        $params = array_merge(['controller' => 'document', 'action' => $arg], $fga);
        $display = $c->dispatch($params);
        $this->assign("ACTION_NAME", xl("Documents"));
        $this->assign("direction", $this->direction);
        $this->default_action($display);
    }

    function document_category_action($arg)
    {
        $c = new Controller();
        $fga = func_get_args();
        $fga = array_slice($fga, 1);
        $params = array_merge(['controller' => 'document_category', 'action' => $arg], $fga);
        $display = $c->dispatch($params);
        $this->assign("ACTION_NAME", xl("Documents"));
        $this->assign("direction", $this->direction);
        $this->default_action($display);
    }

    function x12_partner_action($arg)
    {
        $c = new Controller();
        $fga = func_get_args();
        $fga = array_slice($fga, 1);
        $params = array_merge(['controller' => 'x12_partner', 'action' => $arg], $fga);
        $display = $c->dispatch($params);
        $this->assign("ACTION_NAME", xl("X12 Partners"));
        $this->assign("direction", $this->direction);
        $this->default_action($display);
    }


    function hl7_action($arg)
    {
        $c = new Controller();
        $fga = func_get_args();
        $fga = array_slice($fga, 1);
        $params = array_merge(['controller' => 'hl7', 'action' => $arg], $fga);
        $display = $c->dispatch($params);
        $this->assign("ACTION_NAME", xl("HL7 Viewer"));
        $this->assign("direction", $this->direction);
        $this->default_action($display);
    }
}
