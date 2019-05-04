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
    protected $listenerObject;
    protected $encounterCCDADispatchController;
    
    // TODO: verify that a single object instance (ie singleton) that is injected here is ok
    // as the prior codebase instantiated a new $encounterController on each call to indexAction...
    // should only be one call per http request lifecycle, but needs to be double checked.
    public function __construct(EncounterccdadispatchController $encounterCCDADispatchController)
    {
        $this->listenerObject   = new Listener;
        $this->encounterCCDADispatchController = $encounterCCDADispatchController;
    }
    
    
    public function indexAction()
    {

        // What we are doing is taking all of the public methods of EncounterccdadispatchController and exposing it as
        // part of our soap service.
        // @see https://framework.zend.com/blog/2017-01-24-zend-soap-server.html for more details
        $server = new Server(
            null,
            array('uri' => 'http://localhost/index/soap')
        );
        // set SOAP service class
        // Bind already initialized object to Soap Server
        // TODO: This is bad practice to couple our Application Module to the Carecoordination module as Application is loaded
        // much before CareCoordination module (if its even enabled)...
        // we should check to see if this is even used anywhere?  If not we should remove it or move it into the Carecoordination module...
        $server->setObject($this->encounterCCDADispatchController);
        // handle request
        $server->handle();
        exit;
    }
}
