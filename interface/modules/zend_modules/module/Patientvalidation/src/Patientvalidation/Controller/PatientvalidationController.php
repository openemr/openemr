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

use Patientvalidation\Model\PatientData;
use Laminas\Json\Server\Exception\ErrorException;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Listener\Listener;
use Patientvalidation\Model\PatientDataTable;
use Error;

class PatientvalidationController extends BaseController
{

    /**
     * @var PatientDataTable
     */
    private $PatientDataTable;

    /**
     * PatientvalidationController constructor.
     */
    public function __construct(PatientDataTable $dataTable)
    {
        parent::__construct();
        $this->listenerObject = new Listener();
        $this->PatientDataTable = $dataTable;
        //todo add permission of admin
    }

    private function getAllRealatedPatients()
    {
        //Collect all of the data received from the new patient form
        $patientParams = $this->getRequestedParamsArray();
        if (isset($patientParams["closeBeforeOpening"])) {
            $closeBeforeOpening = $patientParams["closeBeforeOpening"];
        } else {
            $closeBeforeOpening = '';
        }

        //clean the mf_
        foreach ($patientParams as $key => $item) {
                $keyArr = explode("mf_", $key);
                $patientParams[$keyArr[1]] = $item;
                unset($patientParams[$key]);
        }


        $patientData = $this->getPatientDataTable()->getPatients($patientParams);


        if (isset($patientData)) {
            foreach ($patientData as $data) {
                if ($data['pubpid'] == $patientParams['pubpid']) {
                    return array("status" => "failed","list" => $patientData,"closeBeforeOpening" => $closeBeforeOpening);
                }
            }

            return array("status" => "ok","list" => $patientData,"closeBeforeOpening" => $closeBeforeOpening);
        }
    }
    /**
     * @return \Laminas\Stdlib\ResponseInterface the index action
     */

    public function indexAction()
    {

        $this->getJsFiles();
        $this->getCssFiles();
        $this->layout()->setVariable('jsFiles', $this->jsFiles);
        $this->layout()->setVariable('cssFiles', $this->cssFiles);
        $this->layout()->setVariable("title", $this->listenerObject->z_xl("Patient validation"));
        $this->layout()->setVariable("translate", $this->translate);

         $relatedPatients =  $this->getAllRealatedPatients();



        return array("related_patients" => $relatedPatients['list'],"translate" => $this->translate,"closeBeforeOpening" => $relatedPatients['closeBeforeOpening'],"status" => $relatedPatients['status']);
    }
    /**
     * get instance of Patientvalidation
     * @return array|object
     */
    private function getPatientDataTable()
    {

        return $this->PatientDataTable;
    }
}
