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

use Zend\View\Helper\AbstractHelper,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface,
    Zend\View\Exception;
 
class Getvariables extends \Zend\View\Helper\AbstractHelper implements ServiceLocatorAwareInterface
{

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
  
   public function __invoke($controllerName, $actionName, $params = array())
   {
      $controllerLoader = $this->serviceLocator->getServiceLocator()->get('ControllerLoader');
      $controllerLoader->setInvokableClass($controllerName, $controllerName);
      $controller = $controllerLoader->get($controllerName);
      $viewModel = $controller->$actionName($params);
      return $viewModel->getVariables();
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
?>