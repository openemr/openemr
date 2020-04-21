<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/MapperController.php
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
use Carecoordination\Model\MapperTable;

// TODO: this class appears to be deprecated as nothing else refers to it.  It looks like it does the same thing as the SetupController does...
// Recomend removing this if it's not used.
class MapperController extends AbstractActionController
{
    protected $mapperTable;

    public function __construct(MapperTable $mapperTable)
    {
        $this->mapperTable = $mapperTable;
    }

    public function indexAction()
    {
        $layout     = $this->layout();
        $layout->setTemplate('carecoordination/layout/mapper');

        $index      = $this->getMapperTable()->getSections();
        $forms      = $this->getMapperTable()->getFormsList();
        $lbfforms   = $this->getMapperTable()->getLbfList();
        $table_list = $this->getMapperTable()->getTableList();
        $folders    = $this->getMapperTable()->getDocuments();
        $ccda_saved = $this->getMapperTable()->getMappedFields(1);

        $index      = new ViewModel(array(
            'menu'                      => array('system_based_forms' => 'System Based Forms','layout_based_forms' => 'Layout Based Forms', 'database_tables' => 'Database Tables', 'folders' => 'Document Folders'),
            'sections'                  => $index,
            'system_based_forms'        => $forms,
            'layout_based_forms'        => $lbfforms,
            'database_tables'           => $table_list,
        'saved'         => $ccda_saved,
            'folders'                   => $folders,
        ));
        return $index;
    }

    public function savedataAction()
    {
        $existing_id = $this->getMapperTable()->getMaxIdCcda();
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

                    $insert_id = $this->getMapperTable()->insertMaster(array(trim($component_name), trim($section_name), trim($form_dir), trim($form_type), trim($form_table), '1'));
                    if ($sub_id) {
                        $this->getMapperTable()->insertChild(array($insert_id,trim($sub_id)));
                    }
                }
            }
        }

        $this->getMapperTable()->updateExistingMappedFields(array($existing_id,1));
        return $this->redirect()->toRoute('mapper', array('action' => 'index'));
    }

    /**
    * Table Gateway
    *
    * @return type
    */
    public function getMapperTable()
    {
        return $this->mapperTable;
    }
}
