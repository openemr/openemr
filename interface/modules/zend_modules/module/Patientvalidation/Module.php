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
namespace Patientvalidation;

use Patientvalidation\Model\PatientData;
use Patientvalidation\Model\PatientDataTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\ModuleManager;


class Module {


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


            //global variable of language direction
            $controller->layout()->setVariable('language_direction', $_SESSION['language_direction']);
            $controller->layout()->setVariable('status', null);
            //variable that get object with all js variables from php

        }, 100);
    }



}