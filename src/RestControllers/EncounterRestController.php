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

    /**
     * White list of patient search fields
     */
    private const SUPPORTED_SEARCH_FIELDS = array(
        "pid",
        "provider_id"
    );

    public function __construct()
    {
        $this->encounterService = new EncounterService();
    }

    /**
     * Process a HTTP POST request used to create a encounter record.
     * @param $puuid - The patient identifier used to lookup the existing record.
     * @param $data - array of encounter fields.
     * @return a 201/Created status code and the encounter identifier if successful.
     */
    public function post($puuid, $data)
    {
        $data['user'] = $_SESSION['authUser'];
        $data['group'] = $_SESSION['authProvider'];
        $processingResult = $this->encounterService->insertEncounter($puuid, $data);
        return RestControllerHelper::handleProcessingResult($processingResult, 201);
    }

    /**
     * Processes a HTTP PUT request used to update an existing encounter record.
     * @param $puuid - The patient identifier used to lookup the existing record.
     * @param $euuid - The encounter identifier used to lookup the existing record.
     * @param $data - array of encounter fields (full resource).
     * @return a 200/Ok status code and the encounter resource.
     */
    public function put($puuid, $euuid, $data)
    {
        $processingResult = $this->encounterService->updateEncounter($puuid, $euuid, $data);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Fetches a single encounter resource by pid and eid.
     * @param $puuid The patient identifier used to lookup the existing record.
     * @param $euuid The encounter identifier to fetch.
     * @return a 200/Ok status code and the encounter resource.
     */
    public function getOne($puuid, $euuid)
    {
        $processingResult = $this->encounterService->getEncounter($euuid, $puuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * Returns all encounter resources which match (pid) patient identifier.
     * @param $puuid The patient identifier used to lookup the existing record.
     * @return a 200/Ok status code and the encounter resource.
     */
    public function getAll($puuid)
    {
        $processingResult = $this->encounterService->search([], true, $puuid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function postVital($pid, $eid, $data)
    {
        $validationResult = $this->encounterService->validateVital($data);

        $validationHandlerResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationHandlerResult)) {
            return $validationHandlerResult;
        }

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
            return $validationHandlerResult;
        }

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
            return $validationHandlerResult;
        }

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
            return $validationHandlerResult;
        }

        $serviceResult = $this->encounterService->updateSoapNote($pid, $eid, $sid, $data);
        return RestControllerHelper::responseHandler($serviceResult, array('sid' => $sid), 200);
    }
}
