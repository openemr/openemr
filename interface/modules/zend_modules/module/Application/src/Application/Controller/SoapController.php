<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Controller/SoapController.php
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
use Carecoordination\Controller\EncounterccdadispatchController;
use Laminas\Soap\Server;

class SoapController extends AbstractActionController
{
    protected $listenerObject;
    protected $encounterCCDADispatchController;

    // TODO: verify that a single object instance (ie singleton) that is injected here is ok
    // as the prior codebase instantiated a new $encounterController on each call to indexAction...
    // should only be one call per http request lifecycle, but needs to be double checked.
    public function __construct(EncounterccdadispatchController $encounterCCDADispatchController)
    {
        $this->listenerObject   = new Listener();
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
