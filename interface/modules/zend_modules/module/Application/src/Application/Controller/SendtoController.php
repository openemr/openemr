<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  BASIL PT <basil@zhservices.com>
* +------------------------------------------------------------------------------+
*/

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Listener\Listener;

class SendtoController extends AbstractActionController
{
    protected $sendtoTable;
    protected $applicationTable;
    protected $listenerObject;
    
    public function __construct()
    {
        $this->listenerObject	= new Listener;
    }
    
    /*
    * Display the content of Send To button
    */
    public function sendAction()
    {
        $button_only            = $this->params()->fromQuery('embedded_button');
        $required_butons        = $this->params()->fromQuery('required_butons');
        $selected_cform         = $this->params()->fromQuery('selected_form');
        $default_send_via       = $this->params()->fromQuery('default_send_via');
        $default_send_via       = $default_send_via ? $default_send_via : 'printer';
        $encounter              = $GLOBALS['encounter'];        
        $faxRecievers           = $this->getSendtoTable()->getFaxRecievers();
        $ccda_sections          = $this->getSendtoTable()->getCCDAComponents(0);
        $ccda_components        = $this->getSendtoTable()->getCCDAComponents(1);
        $this->layout('layout/sendto');
        $view =  new ViewModel(array(                                
                                'send_via'            => $default_send_via,
                                'faxRecievers'        => $faxRecievers,
                                'ccda_sections'       => $ccda_sections,
                                'required_butons'     => $required_butons,
                                'selected_form'       => $selected_cform,
                                'listenerObject'      => $this->listenerObject,
                                'ccda_components'     => $ccda_components,
                            ));
        if($button_only == 1) {
            $this->layout('layout/embedded_button');
        }
        
        return $view;
    }
    
    /*
    * 
    */
    public function ajaxAction(){
        $ajax_mode  = $this->getRequest()->getPost('ajax_mode', null);
        $encounter  = $GLOBALS['encounter'];
        $pid        = $GLOBALS['pid'];
        switch ($ajax_mode) {
            case 'get_componets':
                $formId = $this->getRequest()->getPost('form_id', null);
                $components = $this->getSendtoTable()->getCombinationFormComponents($encounter,$formId);
                echo $components;
                break;
            case 'send_fax':
                $x=ob_get_level();
                for(;$x>0;$x--){
                    ob_end_clean();
                }
                ob_start();
                $attention                      = $_POST['attentionto'];
                $_REQUEST['formnames']          = $_POST['selectedforms'];
                $_REQUEST['formnames_title']    = $_POST['form_sel_title'];
                $_REQUEST['covering_letter']    = $_POST['covering_letter'] ? 1 : 0;
                include_once __DIR__ . '/../../../../../../../patient_file/encounter/report.php';
                $content = ob_get_clean();
                include_once __DIR__ . '/../../../../../../../../library/faxing.inc.php';
                break;
            case 'fax_details':
                $req_list   = $this->getRequest()->getPost('req_list', null);
                if($req_list == "facility") {
                    $facility = $this->getSendtoTable()->getFacility();
                    echo "<option value=''>-".$this->listenerObject->z_xlt("Select")."-</option>";
                    foreach($facility as $fac_query_result){
                        echo "<option value='".$this->escapeHtml($fac_query_result['fax'])."' >".$fac_query_result['name']."</option>";
                    }
                } else {
                    $users = $this->getSendtoTable()->getUsers($req_list);
                    echo "<option value=''>-".$this->listenerObject->z_xlt("Select")."-</option>";
                    foreach($users as $user){
                        if ($user['ab_option'] == 3) {
                            $displayName = $user['organization'];
                        }else {
                            $displayName = $user['fname'] . ' ' . $user['mname'] . ' ' . $user['lname'];
                        }
                        echo "<option value='".$this->escapeHtml($user['fax'])."' >".$displayName."</option>";    
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
        if (!$this->sendtoTable) {
            $sm = $this->getServiceLocator();
            $this->sendtoTable = $sm->get('Application\Model\SendtoTable');
        }
        return $this->sendtoTable;
    }
    
    /**
    * Table Gateway
    * 
    * @return type
    */
    public function getApplicationTable()
    {
        if (!$this->applicationTable) {
            $sm = $this->getServiceLocator();
            $this->applicationTable = $sm->get('Application\Model\ApplicationTable');
        }
        return $this->applicationTable;
    }
}
