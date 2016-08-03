<?php
/**
 * User: Dror Golan
 * Date: 03/07/16
 * Time: 10:42
 */
namespace Patientvalidation;

use Patientvalidation\Model\PatientData;
use Patientvalidation\Model\PatientDataTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\ModuleManager;


class Module {

    /* base path for js file in public folder */
    const JS_BASE_PATH = '/js/Patientvalidation';
    const CSS_BASE_PATH = '/css/Patientvalidation';

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,

                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Patientvalidation\Model\PatientDataTable' =>  function($sm) {
                    $tableGateway = $sm->get('PatientDataTableGateway');
                    $table = new PatientDataTable($tableGateway);
                    return $table;
                },
                'PatientDataTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new PatientData());
                    return new TableGateway('patient_data', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }


    /**
     * load global variables foe every controllers
     * @param ModuleManager $manager
     */
    public function init(ModuleManager $manager)
    {
        $events = $manager->getEventManager();
        $sharedEvents = $events->getSharedManager();

        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
            $controller = $e->getTarget();
            //$controller->layout()->setVariable('status', null);
            $controller->layout('layout/layout.phtml');

            $controller->layout()->setVariable('jsBasePath',  self::JS_BASE_PATH);
            $controller->layout()->setVariable('cssBasePath',  self::CSS_BASE_PATH);
            //global variable of language direction
            $controller->layout()->setVariable('language_direction', $_SESSION['language_direction']);
            $controller->layout()->setVariable('status', null);
            //variable that get object with all js variables from php

        }, 100);
    }



}