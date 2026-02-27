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

use Application\Listener\Listener;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use OpenEMR\Common\Database\QueryUtils;

class IndexController extends AbstractActionController
{
    protected Listener $listenerObject;

    public function __construct()
    {
        $this->listenerObject = new Listener();
    }

    public function indexAction()
    {
        // you can uncomment this to test the index action.
        // $request  = $this->getRequest();
        // $message  = $request->getPost()->msg;
        // $array    = array('msg' => "test message");
         return new JsonModel([]);
    }

     /**
     * Function ajaxZXL
     * All JS Messages to xl Translation
     *
     * @return \Laminas\View\Model\JsonModel
     */
    public function ajaxZxlAction()
    {
        $request  = $this->getRequest();
        $message  = $request->getPost()->msg;
        $array    = ['msg' => $this->listenerObject->z_xlt($message)];
        $return   = new JsonModel($array);
        return $return;
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
        $result       = $this->forward()->dispatch(IndexController::class, [
                                                      'action' => 'auto-suggest'
                                                 ]);
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
        $result       = $this->listAutoSuggest($post, $limit);
      /** disable layout **/
        $index        = new ViewModel();
        $index->setTerminal(true);
        $index->setVariables([
                                        'result'        => $result,
                                        'keyword'       => $keyword,
                                        'page'          => $page,
                                        'searchType'    => $searchType,
                                        'searchEleNo'   => $searchEleNo,
                                        'searchMode'    => $searchMode,
                                        'limit'         => $limit,
                                        'CommonPlugin'  => $this->CommonPlugin(),
                                        'listenerObject' => $this->listenerObject,
                                    ]);
        return $index;
    }

    /**
     * @return array<string, mixed>
     */
    private function listAutoSuggest($post, $limit)
    {
        $limitEnd = \Application\Plugin\CommonPlugin::escapeLimit($limit);

        if (isset($GLOBALS['set_autosuggest_options'])) {
            $leading = $GLOBALS['set_autosuggest_options'] == 1 ? '%' : $post->leading;

            $trailing = $GLOBALS['set_autosuggest_options'] == 2 ? '%' : $post->trailing;

            if ($GLOBALS['set_autosuggest_options'] == 3) {
                $leading = '%';
                $trailing = '%';
            }
        } else {
            $leading = $post->leading;
            $trailing = $post->trailing;
        }

        $queryString = $post->queryString;
        $page = $post->page;
        $searchType = $post->searchType;

        $limitStart = $page == '' ? 0 : \Application\Plugin\CommonPlugin::escapeLimit($page);

        $keyword = $leading . $queryString . $trailing;
        $rowCount = 0;
        $result = [];

        if (strtolower((string) $searchType) == 'patient') {
            $sql = "SELECT fname, mname, lname, pid, DOB FROM patient_data
                WHERE pid LIKE ?
                OR  CONCAT(fname, ' ', lname) LIKE ?
                OR  CONCAT(lname, ' ', fname) LIKE ?
                OR DATE_FORMAT(DOB,'%m-%d-%Y') LIKE ?
                OR DATE_FORMAT(DOB,'%d-%m-%Y') LIKE ?
                OR DATE_FORMAT(DOB,'%Y-%m-%d') LIKE ?
                ORDER BY fname ";
            $params = [$keyword, $keyword, $keyword, $keyword, $keyword, $keyword];
            $countResult = QueryUtils::fetchRecords($sql, $params);
            $rowCount = count($countResult);
            $sql .= "LIMIT $limitStart, $limitEnd";
            $result = QueryUtils::fetchRecords($sql, $params);
        } elseif (strtolower((string) $searchType) == 'emrdirect') {
            $sql = "SELECT fname, mname, lname,email_direct AS 'email',id FROM users
                WHERE (CONCAT(fname, ' ', lname) LIKE ?
                OR  CONCAT(lname, ' ', fname) LIKE ?
                OR email_direct LIKE ?)
                AND abook_type = 'emr_direct'
                AND active = 1
                ORDER BY fname ";
            $params = [$keyword, $keyword, $keyword];
            $countResult = QueryUtils::fetchRecords($sql, $params);
            $rowCount = count($countResult);
            $sql .= "LIMIT $limitStart, $limitEnd";
            $result = QueryUtils::fetchRecords($sql, $params);
        }

        $arr = [];
        foreach ($result as $row) {
            $arr[] = $row;
        }
        $arr['rowCount'] = $rowCount;

        return $arr;
    }
}
