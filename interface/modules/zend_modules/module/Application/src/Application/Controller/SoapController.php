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

use Carecoordination\Controller\EncounterccdadispatchController;
use Zend\Soap\Server;

class SoapController extends AbstractActionController
{
    protected $sendtoTable;
    protected $applicationTable;
    protected $listenerObject;
    protected $encounterccdadispatchTable;
    
    public function __construct()
    {
        $this->listenerObject	= new Listener;
    }
    
    
    public function indexAction()
    {

        $server = new Server(null, 
        array('uri' => 'http://localhost/index/soap'));
        // set SOAP service class        
        // Bind already initialized object to Soap Server
        $server->setObject(new EncounterccdadispatchController($this->getServiceLocator()));
        // handle request
        $server->handle();				
        exit;
    }
    
        /**
    * Table Gateway
    * 
    * @return type
    */
    public function getEncounterccdadispatchTable()
    {
        if (!$this->encounterccdadispatchTable) {
            $sm = $this->getServiceLocator();
            $this->encounterccdadispatchTable = $sm->get('Zend\Db\Adapter\Adapter');
        }
        return $this->encounterccdadispatchTable;
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
