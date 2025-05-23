<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Helper/SendToHieHelper.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Basil PT <basil@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Exception;
use Application\Controller\SendtoController;

class SendToHieHelper extends \Laminas\View\Helper\AbstractHelper
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
   * @return \Laminas\ServiceManager\ServiceLocatorInterface
   *
   */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
