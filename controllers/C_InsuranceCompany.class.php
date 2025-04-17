<?php

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\InsuranceCompanyService;

class C_InsuranceCompany extends Controller
{
    var $template_mod;
    var $icompanies;
    var $InsuranceCompany;

    public function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->icompanies = array();
        $this->template_mod = $template_mod;
        $this->template_dir = __DIR__ . "/templates/insurance_companies/";
        $this->assign("FORM_ACTION", $GLOBALS['webroot'] . "/controller.php?" . attr($_SERVER['QUERY_STRING']));
        $this->assign("CURRENT_ACTION", $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&insurance_company&");
        $this->assign("STYLE", $GLOBALS['style']);
        $this->assign("SUPPORT_ENCOUNTER_CLAIMS", $GLOBALS['support_encounter_claims']);
        $this->assign("SUPPORT_ELIGIBILITY_REQUESTS", $GLOBALS['enable_eligibility_requests']);
        $this->InsuranceCompany = new InsuranceCompany();
    }

    public function default_action()
    {
        return $this->list_action();
    }

    public function edit_action($id = "", $patient_id = "", $p_obj = null)
    {
        if ($p_obj != null && get_class($p_obj) == "insurancecompany") {
            $this->icompanies[0] = $p_obj;
        } elseif (empty($this->icompanies[0]) || $this->icompanies[0] == null || get_class($this->icompanies[0]) != "insurancecompany") {
            $this->icompanies[0] = new InsuranceCompany($id);
        }

        $x = new X12Partner();
        $this->assign("x12_partners", $x->_utility_array($x->x12_partner_factory()));

        $this->assign("insurancecompany", $this->icompanies[0]);
        return $this->fetch($GLOBALS['template_dir'] . "insurance_companies/" . $this->template_mod . "_edit.html");
    }

    public function list_action()
    {
        $twig = new TwigContainer(null, $GLOBALS['kernel']);

        $insuranceCompanyService = new InsuranceCompanyService();
        $results = $insuranceCompanyService->search([]);
        $iCompanies = [];
        if ($results->hasData()) {
            foreach ($results->getData() as $record) {
                $company = [
                    'id' => $record['id'],
                    'name' => $record['name'],
                    'line1' => $record['line1'],
                    'line2' => $record['line2'],
                    'city' => $record['city'],
                    'state' => $record['state'],
                    'zip' => $record['zip'],
                    'phone' => $record['work_number'],
                    'fax' => $record['fax_number'],
                    'cms_id' => $record['cms_id'],
                    'x12_default_partner_name' => $record['x12_default_partner_name'],
                    'inactive' => $record['inactive']
                ];
                $iCompanies[] = $company;
            }
            usort($iCompanies, function ($a, $b) {
                return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
            });
        }
        $templateVars = [
            'CURRENT_ACTION' => $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&insurance_company&"
            ,'icompanies' => $iCompanies
        ];

        return $twig->getTwig()->render('insurance_companies/general_list.html.twig', $templateVars);
    }


    public function edit_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        if (is_numeric($_POST['id'])) {
            $this->icompanies[0] = new InsuranceCompany($_POST['id']);
        } else {
            $this->icompanies[0] = new InsuranceCompany();
        }

        self::populate_object($this->icompanies[0]);

        $this->icompanies[0]->persist();
        $this->icompanies[0]->populate();

        $_POST['process'] = "";
        header('Location:' . $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&insurance_company&action=list");//Z&H
    }
}
