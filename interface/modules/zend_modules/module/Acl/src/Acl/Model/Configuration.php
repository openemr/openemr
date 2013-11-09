<?php
// +-----------------------------------------------------------------------------+
//OpenEMR - Open Source Electronic Medical Record
//    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
// Author:  Jacob T.Paul <jacob@zhservices.com>
//          Basil PT <basil@zhservices.com>  
//
// +------------------------------------------------------------------------------+

namespace Acl\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Form\Form;

class Configuration extends Form implements InputFilterAwareInterface
{
  protected $inputFilter;

  public function __construct()
  {
    parent::__construct('configuration');
    $this->setAttribute('method', 'post');
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
    //SHOULD SPECIFY THE CONTROLLER AND ITS ACTION IN THE PATH, INCLUDING INDEX ACTION
    /*
    //SAMPLE CONFIGURATION
    $hooks	=  array(
      '0' => array(
        'name' 	=> "HookName1",
        'title' 	=> "HookTitle1",
        'path' 	=> "path/to/Hook1",
      ),
      '1' => array(
        'name' 	=> "HookName2",
        'title' 	=> "HookTitle2",
        'path' 	=> "path/to/Hook2",
      ),									
    );*/
    $hooks	=  array();
    return $hooks;
  }
  public function getAclConfig()
  {
    /*
    //SAMPLE CONFIGURATION
    $acl = array(
      array(
        'section_id' 				=> 'SectionID1',
        'section_name' 			=> 'SectionDisplayName1',
        'parent_section' 		=> 'ParentSectionID1',
      ),
      array(
        'section_id' 				=> 'SectionID2',
        'section_name' 			=> 'SectionDisplayName2',
        'parent_section' 		=> 'ParentSectionID2',
      ),
    );
    */
    $acl = array();
    return $acl;
  }
  
  public function configSettings()
  {
    /*
    //SAMPLE CONFIGURATION
    $settings = array(
      array(
        'display'   => 'Display1',
        'field'     => 'Filed1',
        'type'      => 'FieldType1',
      ),
      array(
        'display'   => 'Display2',
        'field'     => 'Filed2',
        'type'      => 'FieldType2',
      ),
    );*/
    $settings = array();
    return $settings;
  }
  
  public function getDependedModulesConfig()
  {
    //SPECIFY LIST OF MODULES NEEDED FOR THE WORKING OF THE CURRENT MODULE
    //$dependedModules	=  array('Encounter','Calendar',);
    return $dependedModules;
  }
  
}