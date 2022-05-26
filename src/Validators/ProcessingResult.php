<?php

namespace OpenEMR\Validators;

/**
 * OpenEMR Service Processing Result
 *
 * Data contained within a processing result includes:
 * - isValid: indicates if the data provided to the service was valid
 * - validatiomMessages: validation errors, if any, which occurred during processing
 * - internalErrors: system related errors, if any, which occured during processing
 * - data: the return value of the operation/process (array)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class ProcessingResult
{
    private $validationMessages;
    private $internalErrors;
    private $data;

    /**
     * Initializes internal data structures to an internal array.
     */
    public function __construct()
    {
        $this->validationMessages = [];
        $this->internalErrors = [];
        $this->data = [];
    }

    /**
     * @return true if the instance does not contain validation messages/errors
     */
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

    public function getInternalErrors()
    {
        return $this->internalErrors;
    }

    public function setInternalErrors($internalErrors)
    {
        $this->internalErrors = $internalErrors;
    }

    /**
     * Appends an internal  error to the current instance.
     * @param $internalError - The internal error to append.
     */
    public function addInternalError($internalError)
    {
        array_push($this->internalErrors, $internalError);
    }

    public function hasData()
    {
        return !empty($this->data);
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
     * Appends a new data item to the current instance.
     * @param $newData The new data item.
     */
    public function addData($newData)
    {
        array_push($this->data, $newData);
    }

    /**
     * Removes all the data in the processing result
     */
    public function clearData()
    {
        $this->data = [];
    }

    /**
     * Given another processing result, combine all of its properties into this processing result
     * @param ProcessingResult $other  The result to combine into the current object.
     */
    public function addProcessingResult(ProcessingResult $other)
    {
        $this->internalErrors = array_merge($this->internalErrors, $other->internalErrors);
        $this->validationMessages = array_merge($this->validationMessages, $other->validationMessages);
        $this->data = array_merge($this->data, $other->data);
    }

    /**
     * @return true if the instance has 1 or more internal errors.
     */
    public function hasInternalErrors()
    {
        return count($this->internalErrors) > 0;
    }

    /**
     * @return true if the instance contains either validation or internal errors.
     */
    public function hasErrors()
    {
        return !$this->isValid() || $this->hasInternalErrors();
    }

    public static function extractDataArray(ProcessingResult $result): ?array
    {
        if ($result->hasData()) {
            return $result->getData();
        }
        return null;
    }
}
