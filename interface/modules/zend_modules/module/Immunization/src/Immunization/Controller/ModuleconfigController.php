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

use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Mvc\Controller\AbstractActionController;

class ModuleconfigController extends AbstractActionController
{
    protected InputFilterInterface $inputFilter;

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

    public function setInputFilter(InputFilterInterface $inputFilter): never
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!isset($this->inputFilter)) {
            $inputFilter = new InputFilter();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function getHookConfig()
    {
        $hooks  =  [];
        return $hooks;
    }

    public function getAclConfig()
    {
        $acl = [];
        return $acl;
    }

    public function configSettings()
    {
        $settings = [];
        return $settings;
    }

    public function getDependedModulesConfig()
    {
        $dependedModules = [];
        return $dependedModules;
    }
}
