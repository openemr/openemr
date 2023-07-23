<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    BASIL PT <basil@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Listener\Listener;
use OpenEMR\Cqm\QrdaControllers\QrdaReportController;

class SendtoController extends AbstractActionController
{
    protected $sendtoTable;
    protected $applicationTable;
    protected $listenerObject;

    public function __construct(\Application\Model\ApplicationTable $applicationTable, \Application\Model\SendtoTable $sendToTable)
    {
        $this->listenerObject = new Listener();
        $this->applicationTable = $applicationTable;
        $this->sendtoTable = $sendToTable;
    }

    /*
    * Display the content of Send To button
    */
    public function sendAction()
    {
        $button_only = $this->params()->fromQuery('embedded_button');
        $required_butons = $this->params()->fromQuery('required_butons');
        $selected_cform = $this->params()->fromQuery('selected_form');
        $default_send_via = $this->params()->fromQuery('default_send_via');
        $default_send_via = $default_send_via ? $default_send_via : 'printer';
        $encounter = $GLOBALS['encounter'];
        $faxRecievers = $this->getSendtoTable()->getFaxRecievers();
        $ccda_sections = $this->getSendtoTable()->getCCDAComponents(0);
        $ccda_components = $this->getSendtoTable()->getCCDAComponents(1);
        $reportController = new QrdaReportController();
        $measures = $reportController->reportMeasures;

        $this->layout('layout/sendto');
        $view = new ViewModel(array(
            'send_via' => $default_send_via,
            'faxRecievers' => $faxRecievers,
            'ccda_sections' => $ccda_sections,
            'required_butons' => $required_butons,
            'selected_form' => $selected_cform,
            'listenerObject' => $this->listenerObject,
            'ccda_components' => $ccda_components,
            'current_measures' => $measures,
            'download_format' => [] // empty array, can be populated by SendToHieHelper...
        ));
        if ($button_only == 1) {
            $this->layout('layout/embedded_button');
        }

        return $view;
    }

    /*
    *
    */
    public function ajaxAction()
    {
        $ajax_mode = $this->getRequest()->getPost('ajax_mode', null);
        $encounter = $GLOBALS['encounter'];
        $pid = $GLOBALS['pid'];
        switch ($ajax_mode) {
            case 'get_componets':
                $formId = $this->getRequest()->getPost('form_id', null);
                $components = $this->getSendtoTable()->getCombinationFormComponents($encounter, $formId);
                echo $components;
                break;
            case 'send_fax':
                $x = ob_get_level();
                for (; $x > 0; $x--) {
                    ob_end_clean();
                }

                ob_start();
                $attention = $_POST['attentionto'];
                $_REQUEST['formnames'] = $_POST['selectedforms'];
                $_REQUEST['formnames_title'] = $_POST['form_sel_title'];
                $_REQUEST['covering_letter'] = $_POST['covering_letter'] ? 1 : 0;
                include_once __DIR__ . '/../../../../../../../patient_file/encounter/report.php';
                $content = ob_get_clean();
                include_once __DIR__ . '/../../../../../../../../library/faxing.inc.php';
                break;
            case 'fax_details':
                $req_list = $this->getRequest()->getPost('req_list', null);
                if ($req_list == "facility") {
                    $facility = $this->getSendtoTable()->getFacility();
                    echo "<option value=''>-" . $this->listenerObject->z_xlt("Select") . "-</option>";
                    foreach ($facility as $fac_query_result) {
                        echo "<option value='" . $this->escapeHtml($fac_query_result['fax']) . "' >" . $fac_query_result['name'] . "</option>";
                    }
                } else {
                    $users = $this->getSendtoTable()->getUsers($req_list);
                    echo "<option value=''>-" . $this->listenerObject->z_xlt("Select") . "-</option>";
                    foreach ($users as $user) {
                        if ($user['ab_option'] == 3) {
                            $displayName = $user['organization'];
                        } else {
                            $displayName = $user['fname'] . ' ' . $user['mname'] . ' ' . $user['lname'];
                        }

                        echo "<option value='" . $this->escapeHtml($user['fax']) . "' >" . $displayName . "</option>";
                    }
                }
                break;
        }

        return $this->response;
    }

    /**
     * Table Gateway
     *
     * @return type
     */
    public function getSendtoTable()
    {
        return $this->sendtoTable;
    }

    /**
     * Table Gateway
     *
     * @return type
     */
    public function getApplicationTable()
    {
        return $this->applicationTable;
    }
}
