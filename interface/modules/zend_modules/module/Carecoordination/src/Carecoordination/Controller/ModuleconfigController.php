<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/ModuleconfigController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Filter\Compress\Zip;
use Carecoordination\Form\ModuleconfigForm;

class ModuleconfigController extends AbstractActionController
{
    protected $inputFilter;

    public function __construct()
    {
    }

    public function indexAction()
    {
        // TODO: how does this even work?? It's constructor expects an adapter class and it's not used...
        $form = new ModuleconfigForm();
        $form->get('hie_author_id')->setAttribute('options', array('user 1','user 2'));

        $view =  new ViewModel(array(
            'form' => $form,
        ));
        return $view;
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
    //SOECIFY HOOKS DETAILS OF A MODULE IN AN ARRAY, WITH MODULE NAME AS KEY
    //SHOULD SPECIFY THE CONTROLLER AND ITS ACTION IN THE PATH, INCLUDING INDEX ACTION
        $hooks  =  array(
                '0' => array(
                        'name'  => "send_to_hie",
                        'title' => "Send To HIE",
                        'path'  => "encountermanager",
                    ),
               );

        return $hooks;
    }

    public function getDependedModulesConfig()
    {
        // these modules need to be activated before this module can be installed
        $dependedModules = array(
            'Ccr'
            ,'Immunization'
            ,'Syndromicsurveillance'
            , 'Documents'       // Handles the saving and retrieving of embedded documents in this module.
        );
        return $dependedModules;
    }

    public function getAclConfig()
    {
        $acl = array(
        array(
        'section_id' => 'send_to_hie',
        'section_name' => 'Send To HIE',
        'parent_section' => 'carecoordination',
        ),
        );
        return $acl;
    }
}
