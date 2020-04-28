<?php

namespace OpenEMR\Validators;

/**
 * OpenEMR Service Processing Result
 *
 * Data contained within a processing result includes:
 * - isValid: indicates if the data provided to the service was valid
 * - validatiomMessages: validation errors, if any, which occurred during processing
 * - processingErrors: system related errors, if any, which occured during processing
 * - data: the return value of the operation/process
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixon.whitmire@ibm.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixon.whitmire@ibm.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class ProcessingResult
{
    private $isValid;
    private $validationMessages;
    private $processingErrors;
    private $data;

    public function __construct()
    {
        $this->isValid = false;
        $this->validationMessages = array();
        $this->processingErrors = array();
        $this->data = null;
    }

    public function isValid()
    {
        return count($this->validationMessages) == 0;
    }

    public function getValidationMessages()
    {
        return $this->validationMessages;
    }

    public function setValidationMessages($validationMessages)
    {
        $this->validationMessages = $validationMessages;
    }

    public function getProcessingErrors()
    {
        return $this->processingErrors;
    }

    public function setProcessingErrors($processingErrors)
    {
        $this->processingErrors = $processingErrors;
    }

    /**
     * Appends a processing error to the current instance.
     */
    public function addProcessingError($processingError)
    {
        array_push($this->processingErrors, $processingError);
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return true if the processing result contains a validation or processing error
     */
    public function hasErrors()
    {
        return !$this->isValid() || count($this->processingErrors) > 0;
    }
}
