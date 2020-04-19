<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Listener\Listener;

class IndexController extends AbstractActionController
{
    protected $applicationTable;
    protected $listenerObject;

    public function __construct(\Application\Model\ApplicationTable $applicationTable)
    {
        $this->listenerObject = new Listener();
        $this->applicationTable = $applicationTable;
    }

    public function indexAction()
    {
        // you can uncomment this to test the index action.
        // $request  = $this->getRequest();
        // $message  = $request->getPost()->msg;
        // $array    = array('msg' => "test message");
        // $return   = new JsonModel($array);
        // return $return;
    }

     /**
     * Function ajaxZXL
     * All JS Mesages to xl Translation
     *
     * @return \Laminas\View\Model\JsonModel
     */
    public function ajaxZxlAction()
    {
        $request  = $this->getRequest();
        $message  = $request->getPost()->msg;
        $array    = array('msg' => $this->listenerObject->z_xl($message));
        $return   = new JsonModel($array);
        return $return;
    }

    /**
     * Table Gateway
     *
     * @return type
     */
    public function getApplicationTable()
    {
        return $this->applicationTable;
    }

    /**
     * Search Mechanism
     * Auto Suggest
     *
     * @return string
     */
    public function searchAction()
    {
        $request      = $this->getRequest();
        $result       = $this->forward()->dispatch(IndexController::class, array(
                                                      'action' => 'auto-suggest'
                                                 ));
        return $result;
    }

    public function autoSuggestAction()
    {
        $request      = $this->getRequest();
        $post         = $request->getPost();
        $keyword      = $request->getPost()->queryString;
        $page         = $request->getPost()->page;
        $searchType   = $request->getPost()->searchType;
        $searchEleNo  = $request->getPost()->searchEleNo;
        $searchMode   = $request->getPost()->searchMode;
        $limit        = 20;
        $result       = $this->getApplicationTable()->listAutoSuggest($post, $limit);
      /** disable layout **/
        $index        = new ViewModel();
        $index->setTerminal(true);
        $index->setVariables(array(
                                        'result'        => $result,
                                        'keyword'       => $keyword,
                                        'page'          => $page,
                                        'searchType'    => $searchType,
                                        'searchEleNo'   => $searchEleNo,
                                        'searchMode'    => $searchMode,
                                        'limit'         => $limit,
                                        'CommonPlugin'  => $this->CommonPlugin(),
                                        'listenerObject' => $this->listenerObject,
                                    ));
        return $index;
    }
}
