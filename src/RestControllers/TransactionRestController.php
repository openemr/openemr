<?php

/**
 * PatientRestController
 * This controller creates, updates, and retrieves transactions
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jonathan Moore <Jdcmoore@aol.com>
 * @copyright Copyright (c) 2022 Jonathan Moore <Jdcmoore@aol.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PatientTransactionService;
use OpenEMR\Services\TransactionService;
use OpenEMR\Validators\ProcessingResult;

class TransactionRestController
{
    /**
     * @var PatientTransactionService
     */
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
    }

    /**
     * Process a HTTP POST request used to create a patient record.
     *
     * @param  $data - array of patient fields.
     * @return a 201/Created status code and the patient identifier if successful.
     */
    public function CreateTransaction($pid, $data)
    {
        $processingResult = new ProcessingResult();

        $serviceValidation = $this->patientTransactionService->validate($data);
        $controllerValidationResult = RestControllerHelper::validationHandler($serviceValidation);
        if (is_array($controllerValidationResult)) {
            $processingResult->setValidationMessages($controllerValidationResult);
        }


        $serviceResult = $this->patientTransactionService->insert($pid, $data);
        $processingResult->addData($serviceResult);

        return RestControllerHelper::handleProcessingResult($processingResult, 201, true);
    }

    public function UpdateTransaction($tid, $data)
    {
        $processingResult = new ProcessingResult();

        $data = $this->patientTransactionService->update($tid, $data);
        $processingResult->addData($data);
        return RestControllerHelper::handleProcessingResult($processingResult, 200, false);
    }

    /**
     * Returns patient resources which match an optional search criteria.
     */
    public function GetPatientTransactions($pid)
    {
        $processingResult = $this->patientTransactionService->getAll($pid);

        if (!$processingResult->hasErrors() && count($processingResult->getData()) == 0) {
            return RestControllerHelper::handleProcessingResult($processingResult, 404);
        }

        return RestControllerHelper::handleProcessingResult($processingResult, 200, true);
    }
}
