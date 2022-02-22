<?php

/**
 * PatientRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jonathan Moore <Jdcmoore@aol.com>
 * @copyright Copyright (c) 2022 Jonathan Moore <Jdcmoore@aol.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

// require_once("../../globals.php");
// require_once("$srcdir/transactions.inc");

use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PatientTransactionService;
use OpenEMR\Services\TransactionService;

class TransactionRestController
{
    private $patientTransactionService;

    /**
     * White list of patient search fields
     */
    private const SUPPORTED_SEARCH_FIELDS = array(
        'pid'
    );

    public function __construct()
    {
        $this->patientTransactionService = new PatientTransactionService();
        //$this->TransactionService = new TransactionService();
    }

    /**
     * Process a HTTP POST request used to create a patient record.
     * @param $data - array of patient fields.
     * @return a 201/Created status code and the patient identifier if successful.
     */
    public function CreateTransaction($pid, $data)
    {
        $validationResult = $this->patientTransactionService->validate($data);
        $validationResult = RestControllerHelper::validationHandler($validationResult);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $processingResult = $this->patientTransactionService->insert($pid, $data);
        //return RestControllerHelper::handleProcessingResult($processingResult, 201);
        return RestControllerHelper::responseHandler($processingResult, $processingResult, 201);
    }

    public function UpdateTransaction($tid, $data)
    {
        // $validationResult = $this->patientTransactionService->validate($data);
        // $validationResult = RestControllerHelper::validationHandler($validationResult);
        // if (is_array($validationResult)) {
        //     return $validationResult;
        // }

        $processingResult = $this->patientTransactionService->update($tid, $data);
        //return RestControllerHelper::handleProcessingResult($processingResult, 201);
        return RestControllerHelper::responseHandler($processingResult, $processingResult, 201);
    }

    /**
     * Returns patient resources which match an optional search criteria.
     */
    public function getPatientTransactions($puuidString)
    {
        $processingResult = $this->patientTransactionService->getAll($puuidString);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) 
            return RestControllerHelper::handleProcessingResult($processingResult, 404);

        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }

    public function getTransactionTypes()
    {

    }
}