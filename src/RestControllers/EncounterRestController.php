<?php
/**
 * EncounterRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\Services\EncounterService;
use OpenEMR\RestControllers\RestControllerHelper;

class EncounterRestController
{
    private $encounterService;

    public function __construct()
    {
        $this->encounterService = new EncounterService();
    }

    public function getOne($pid, $eid)
    {
        $serviceResult = $this->encounterService->getEncounterForPatient($pid, $eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll($pid)
    {
        $serviceResult = $this->encounterService->getEncountersForPatient($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function postVital($pid, $eid, $data)
    {
        $validationResult = $this->encounterService->validateVital($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->encounterService->insertVital($pid, $eid, $data);
        return RestControllerHelper::responseHandler(
            $serviceResult,
            array(
                'vid' => $serviceResult[0],
                'fid' => $serviceResult[1]
            ),
            201
        );
    }

    public function putVital($pid, $eid, $vid, $data)
    {
        $validationResult = $this->encounterService->validateVital($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->encounterService->updateVital($pid, $eid, $vid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array('vid' => $vid), 200);
    }

    public function getVitals($pid, $eid)
    {
        $serviceResult = $this->encounterService->getVitals($pid, $eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getVital($pid, $eid, $vid)
    {
        $serviceResult = $this->encounterService->getVital($pid, $eid, $vid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getSoapNotes($pid, $eid)
    {
        $serviceResult = $this->encounterService->getSoapNotes($pid, $eid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getSoapNote($pid, $eid, $sid)
    {
        $serviceResult = $this->encounterService->getSoapNote($pid, $eid, $sid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function postSoapNote($pid, $eid, $data)
    {
        $validationResult = $this->encounterService->validateSoapNote($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->encounterService->insertSoapNote($pid, $eid, $data);
        return RestControllerHelper::responseHandler(
            $serviceResult,
            array(
                'sid' => $serviceResult[0],
                'fid' => $serviceResult[1]
            ),
            201
        );
    }

    public function putSoapNote($pid, $eid, $sid, $data)
    {
        $validationResult = $this->encounterService->validateSoapNote($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult; }

        $serviceResult = $this->encounterService->updateSoapNote($pid, $eid, $sid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array('sid' => $sid), 200);
    }
}
