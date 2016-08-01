<?php

namespace Patientvalidation\Controller;

use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Listener\Listener;
use Zend\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;

class BaseController extends AbstractActionController
{
    private $configParams = null;



    public function __construct()
    {
        //load translation class
        $this->translate = new Listener();
    }


    protected function getLanguage(){

        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $sql = new CustomSql($dbAdapter);

        $lang = $sql->getCurrentLang();

        return $lang;
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