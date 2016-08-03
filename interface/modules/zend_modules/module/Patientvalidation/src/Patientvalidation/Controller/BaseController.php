<?php

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
        '/lib/jquery/jquery.min.js',
        //bootstrap
        '/lib/bootstrap/bootstrap.min.js',

    );

    /**
     * path to file after base pass from ModuleconfigController
     * @var array
     */
    protected $cssFiles = array(
        //bootstrap
        '/lib/bootstrap/bootstrap.min.css',
        //style.css - custom css
        '/style.css'
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

                $this->jsFiles[] = '/lib/datatables/datatables.min.js';
                $this->jsFiles[] = '/lib/datatables/dataTables.bootstrap.min.js';
                $this->jsFiles[] = '/lib/datatables/dataTables.buttons.min.js';

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
            $this->cssFiles[] = '/lib/bootstrap/bootstrap-rtl.min.css';
        }


                $this->cssFiles[] = '/lib/datatables/datatables.css';
                $this->cssFiles[] = '/lib/datatables/buttons.dataTables.min.css';

        return $this->cssFiles;
    }


 


    protected function getLanguage(){

        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $sql = new CustomSql($dbAdapter);

        $lang = $sql->getCurrentLang();

        return $lang;
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