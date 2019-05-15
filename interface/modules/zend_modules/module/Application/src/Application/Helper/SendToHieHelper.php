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
*    @author  Basil PT <basil@zhservices.com>
* +------------------------------------------------------------------------------+
*/

namespace Application\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Exception;
use Application\Controller\SendtoController;
 
class SendToHieHelper extends \Zend\View\Helper\AbstractHelper
{
  /**
   * @var \Application\Controller\SendtoController
   */
    private $sendController;

    public function __construct(SendtoController $sendController)
    {
        $this->sendController = $sendController;
    }

  /**
   * @var ServiceLocatorInterface
   */
    protected $serviceLocator;
   
  /**
   * Get variables from actions view model object
   * @param String $controllerName Controller
   * @param String $actionName Action
   * @param Array $params Parameters to action
   * @return Array
   * @author  Basil PT <basil@zhservices.com>
   **/
  
    public function __invoke($layoutName, array $required_buttons, $send_via, $download_format = null)
    {
        $viewModel = $this->sendController->sendAction();
        $arr = $viewModel->getVariables();
        $arr['required_butons'] = $required_buttons;
        $arr['send_via']        = $send_via;
        if (!empty($download_format)) {
            $arr['download_format'] = $download_format;
        }
      
        $this->getView()->layout('carecoordination/layout/encountermanager');
        echo $this->getView()->partial("application/sendto/send", $arr);
        return '';
    }

  /**
   * Set the service locator.
   *
   * @param ServiceLocatorInterface $serviceLocator
   * @return AbstractHelper
   *
   */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

  /**
   * Get the service locator.
   *
   * @return \Zend\ServiceManager\ServiceLocatorInterface
   *
   */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
