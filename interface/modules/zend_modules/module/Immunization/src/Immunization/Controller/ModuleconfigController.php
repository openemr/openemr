<?php

/**
 * interface/modules/zend_modules/module/Installer/view/layout/layout.phtml
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Immunization\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Immunization\Form\ImmunizationForm;
use Application\Listener\Listener;

class ModuleconfigController extends AbstractActionController
{
    protected $inputFilter;

    public function __construct()
    {
    }

    public function exchangeArray($data)
    {
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getHookConfig()
    {
        $hooks  =  array();
        return $hooks;
    }

    public function getAclConfig()
    {
        $acl = array();
        return $acl;
    }

    public function configSettings()
    {
        $settings = array();
        return $settings;
    }

    public function getDependedModulesConfig()
    {
        $dependedModules = array();
        return $dependedModules;
    }
}
