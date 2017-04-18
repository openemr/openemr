<?php

/* +-----------------------------------------------------------------------------+
* Copyright 2016 matrix israel
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see
* http://www.gnu.org/licenses/licenses.html#GPL
*    @author  Dror Golan <drorgo@matrix.co.il>
* +------------------------------------------------------------------------------+
 *
 */
namespace Patientvalidation\Controller;

use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Listener\Listener;
use Zend\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;

class BaseController extends AbstractActionController
{

    /**
     * path to file after base pass from ModuleconfigController
     * @var array
     */
    protected $jsFiles = array(
        //jquery
        '/jquery-min-1-9-1/index.js',
        //bootstrap
        '/bootstrap-3-3-4/dist/js/bootstrap.min.js',

    );

    /**
     * path to file after base pass from ModuleconfigController
     * @var array
     */
    protected $cssFiles = array(
        //bootstrap
        '/bootstrap-3-3-4/dist/css/bootstrap.min.css',
    );

    public function __construct()
    {
        //load translation class
        $this->translate = new Listener();
    }

    /**
     * Add js files per method.
     * @param $method __METHOD__ magic constant
     * @return array
     */
    protected function getJsFiles()
    {

                $this->jsFiles[] = '/datatables.net-1-10-13/js/jquery.dataTables.min.js';
                $this->jsFiles[] = '/datatables.net-bs-1-10-13/js/dataTables.bootstrap.min.js';

        return $this->jsFiles;
    }

    /**
     * Add css files per method.
     * @param $method __METHOD__ magic constant
     * @return array
     */
    protected function getCssFiles()
    {

        //adding bootstrap rtl for rtl languages
        if ($_SESSION['language_direction'] == 'rtl') {
            $this->cssFiles[] = '/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css';
        }


                $this->cssFiles[] = '/datatables.net-bs-1-10-13/css/dataTables.bootstrap.min.css';


        return $this->cssFiles;
    }



    /**
     * @return mixed params object
     */
    protected function getRequestedParams(){

        return $this->getRequest()->getQuery() ;
    }

    protected function getRequestedParamsArray(){

        return (array)$this->getRequest()->getQuery() ;
    }
    /**
     * @return post params as array
     */
    protected function getPostParamsArray()
    {
        $putParams = array();
        parse_str($this->getRequest()->getContent(), $putParams);
        return $putParams;
    }
    /**
     * return current user id
     * @return int
     */
    protected function getUserId(){

        return $_SESSION['authUserID'];
    }

    /**
     * @param $data
     * @param bool $convertToJson
     * @param int $responsecode
     * @return \Zend\Stdlib\ResponseInterface
     * @comment to use this function return this $response in your controller
     */
    public function responseWithNoLayout($data, $convertToJson=true, $responsecode=200){

        $response = $this->getResponse();
        $response->setStatusCode($responsecode);
        if($convertToJson) {
            $response->setContent(json_encode($data));
        }
        else{
            $response->setContent($data);
        }
        return $response;
    }



}