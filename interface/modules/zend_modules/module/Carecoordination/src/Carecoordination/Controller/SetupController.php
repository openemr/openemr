<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/SetupController.php
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
use Application\Listener\Listener;

class SetupController extends AbstractActionController
{
    /**
     * @var \Carecoordination\Model\SetupTable
     */
    protected $setupTable;

    protected $listenerObject;

    public function __construct(\Carecoordination\Model\SetupTable $setupTable)
    {
        $this->setupTable = $setupTable;
        $this->listenerObject   = new Listener();
    }

    public function indexAction()
    {
        $layout     = $this->layout();
        $layout->setTemplate('carecoordination/layout/setup');

        $index      = $this->getSetupTable()->getSections();
        $forms      = $this->getSetupTable()->getFormsList();
        $lbfforms   = $this->getSetupTable()->getLbfList();
        $table_list = $this->getSetupTable()->getTableList();
        $folders    = $this->getSetupTable()->getDocuments();
        $ccda_saved = $this->getSetupTable()->getMappedFields(1);

        $index      = new ViewModel(array(
            'menu'                      => array('system_based_forms' => 'System Based Forms','layout_based_forms' => 'Layout Based Forms', 'database_tables' => 'Database Tables', 'folders' => 'Document Folders'),
            'sections'                  => $index,
            'system_based_forms'        => $forms,
            'layout_based_forms'        => $lbfforms,
            'database_tables'           => $table_list,
        'saved'         => $ccda_saved,
            'folders'                   => $folders,
            'listenerObject'          => $this->listenerObject,
        ));
        return $index;
    }

    public function savedataAction()
    {
        $existing_id = $this->getSetupTable()->getMaxIdCcda();
        $request    = $this->getRequest();
        $action     = $request->getPost('save');
        $tosave     = $request->getPost('tosave');

        $components = explode('|***|', $tosave);
        foreach ($components as $key => $value) {
            $sections       = explode('|**|', $value);
            $component_name     = array_shift($sections);

            foreach ($sections as $key_1 => $value_1) {
                $forms      = explode('|*|', $value_1);
                $section_name   = array_shift($forms);

                foreach ($forms as $key_2 => $value_2) {
                    $value_2    = trim($value_2);
                    $sub_id     = '';
                    $form_dir   = '';
                    $form_type  = '';
                    $form_table = '';

                    if (substr($value_2, 0, 1) == 1) {
                        $form_dir   = preg_replace('/^1\|/', '', $value_2);
                        $form_type  = 1;
                    } elseif (substr($value_2, 0, 1) == 2) {
                        $value_2    = preg_replace('/^2\|/', '', $value_2);
                        if (strpos($value_2, '|')) {
                            $temp_1     = explode('|', $value_2);
                            $form_table = $form_dir = $temp_1[0];
                            $sub_id     = $temp_1[1];
                        } else {
                            $form_dir = $value_2;
                        }

                        $form_type  = 2;
                    } elseif (substr($value_2, 0, 1) == 3) {
                        $value_2 = preg_replace('/^3\|/', '', $value_2);
                        if (strpos($value_2, '|')) {
                            $temp_1     = explode('|', $value_2);
                            $form_table = $form_dir = $temp_1[0];
                            $sub_id     = $temp_1[1];
                        } else {
                            $form_dir = $value_2;
                        }

                        $form_type = 1;
                    } elseif (substr($value_2, 0, 1) == 4) {
                        $value_2    = preg_replace('/^4\|/', '', $value_2);
                        $form_dir   = $value_2;
                        $form_type  = 3;
                    }

                    $insert_id = $this->getSetupTable()->insertMaster(array(trim($component_name), trim($section_name), trim($form_dir), trim($form_type), trim($form_table), '1'));
                    if ($sub_id) {
                        $this->getSetupTable()->insertChild(array($insert_id,trim($sub_id)));
                    }
                }
            }
        }

        $this->getSetupTable()->updateExistingMappedFields(array($existing_id,1));
        // Only reference I found for the framework for this is here
        // @see https://framework.zend.com/apidoc/2.3/classes/Laminas.Mvc.Controller.Plugin.Redirect.html
        return $this->redirect()->toRoute('setup', array('action' => 'index'));
    }

    /**
    * Table Gateway
    *
    * @return \Carecoordination\Model\SetupTable
    */
    public function getSetupTable()
    {
        return $this->setupTable;
    }

    /**
    * Funtion getTitle
    * Setup Title settings at Configuration View
    *
    * @return string
    */
    public function getTitle()
    {
        $title = "Mapper";
        return $title;
    }
}
