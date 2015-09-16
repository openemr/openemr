<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
*    @author  Remesh Babu S <remesh@zhservices.com>
* +------------------------------------------------------------------------------+
*/

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Listener\Listener;

class IndexController extends AbstractActionController
{
    protected $applicationTable;
    protected $listenerObject;
    
    public function __construct()
    {
      $this->listenerObject	= new Listener;
    }
    
    public function indexAction()
    {
      
    }
    
     /**
     * Function ajaxZXL
     * All JS Mesages to xl Translation
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function ajaxZxlAction()
    {
      $request  = $this->getRequest();
      $message  = $request->getPost()->msg;
      $array    = array('msg' => $this->listenerObject->z_xl($message));
      $return   = new JsonModel($array);
      return $return;
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
    
    /**
     * Search Mechanism
     * Auto Suggest
     * 
     * @return string
     */
    public function searchAction()
    {
      $request      = $this->getRequest();
      $result       = $this->forward()->dispatch('Application\Controller\Index', array(
                                                      'action' => 'auto-suggest'
                                                 ));
      return $result;
    }
    
    public function autoSuggestAction()
    {
      $request      = $this->getRequest();
      $post         = $request->getPost();
      $keyword      = $request->getPost()->queryString;
      $page         = $request->getPost()->page;
      $searchType   = $request->getPost()->searchType;
      $searchEleNo  = $request->getPost()->searchEleNo;
      $searchMode   = $request->getPost()->searchMode;
      $limit        = 20;
      $result       = $this->getApplicationTable()->listAutoSuggest($post, $limit);
      /** disable layout **/
      $index        = new ViewModel(); 
      $index->setTerminal(true);
      $index->setVariables(array(
                                        'result'        => $result,
                                        'keyword'       => $keyword,
                                        'page'          => $page,
                                        'searchType'    => $searchType,
                                        'searchEleNo'   => $searchEleNo,
                                        'searchMode'    => $searchMode,
                                        'limit'         => $limit,
                                        'CommonPlugin'  => $this->CommonPlugin(),
                                        'listenerObject'=>$this->listenerObject,
                                    ));
      return $index;
    }
}
