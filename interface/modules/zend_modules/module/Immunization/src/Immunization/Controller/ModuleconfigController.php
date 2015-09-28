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
*    @author  Vinish K <vinish@zhservices.com>
* +------------------------------------------------------------------------------+
*/
namespace Immunization\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Immunization\Form\ImmunizationForm;
use Application\Listener\Listener;

class ModuleconfigController extends AbstractActionController
{
    protected $inputFilter;

    public function __construct()
    {    }

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
        $hooks	=  array();
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
        return $dependedModules;
    }
}