<?php

namespace OpenEMR\Validators;

use OpenEMR\Common\Database\QueryPagination;

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
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class ProcessingResult
{
    private $validationMessages;
    private $internalErrors;
    private $data;
    private QueryPagination $pagination;

    /**
     * Initializes internal data structures to an internal array.
     */
    public function __construct()
    {
        $this->validationMessages = [];
        $this->internalErrors = [];
        $this->data = [];
        $this->pagination = new QueryPagination();
    }

    /**
     * @param QueryPagination $pagination
     */
    public function setPagination(QueryPagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * @return QueryPagination
     */
    public function getPagination(): QueryPagination
    {
        return $this->pagination;
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

    public function getFirstDataResult()
    {
        if ($this->hasData()) {
            return $this->data[0];
        }
        return null;
    }

    public function setData($data)
    {
        // we trim the data to be within the confines of the pagination limit and set our has more data flag if we have it
        $this->data = $data;
        $limit = $this->getPagination()->getLimit();
        $isEmpty = empty($this->data);
        if ($limit > 0 && !$isEmpty) {
            $count = count($this->data);
            if ($count > $limit) {
                $this->getPagination()->setHasMoreData(true);
                $this->data = array_slice($this->data, 0, $limit);
            }
        }
        if ($isEmpty) {
            $this->getPagination()->setTotalCount(0);
        }
    }

    /**
     * Appends a new data item to the current instance.
     * @param $newData The new data item.
     */
    public function addData($newData)
    {
        $count = count($this->data);
        $limit = max(0, $this->getPagination()->getLimit());
        if ($limit === 0 || $count < $this->getPagination()->getLimit()) {
            array_push($this->data, $newData);
        } else {
            // we exceeded our limit so we are going to just say we have more data and skip the element
            // any time a query actually runs we always grab the limit + 1 data element so we can determine if we have more data
            // this facilitates our pagination logic in the service layer
            $this->getPagination()->setHasMoreData(true);
        }
    }

    /**
     * Removes all the data in the processing result
     */
    public function clearData()
    {
        $this->setData([]);
    }

    /**
     * Given another processing result, combine all of its properties into this processing result
     * @param ProcessingResult $other  The result to combine into the current object.
     */
    public function addProcessingResult(ProcessingResult $other)
    {
        $this->internalErrors = array_merge($this->internalErrors, $other->internalErrors);
        $this->validationMessages = array_merge($this->validationMessages, $other->validationMessages);
        if (!empty($other->getPagination())) {
            $this->pagination->copy($other->getPagination());
        }
        // make sure to handle our pagination properly by using the setData method
        $this->setData(array_merge($this->data, $other->data));
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
