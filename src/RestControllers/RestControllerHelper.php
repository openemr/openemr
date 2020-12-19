<?php

/**
 * RestControllerHelper
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

class RestControllerHelper
{
    /**
     * Configures the HTTP status code and payload returned within a response.
     *
     * @param $serviceResult
     * @param $customRespPayload
     * @param $idealStatusCode
     * @return null
     */
    public static function responseHandler($serviceResult, $customRespPayload, $idealStatusCode)
    {
        if ($serviceResult) {
            http_response_code($idealStatusCode);

            if ($customRespPayload) {
                return $customRespPayload;
            }
            return $serviceResult;
        }

        // if no result is present return a 404 with a null response
        http_response_code(404);
        return null;
    }

    public static function validationHandler($validationResult)
    {
        if (property_exists($validationResult, 'isValid') && !$validationResult->isValid()) {
            http_response_code(400);
            $validationMessages = null;
            if (property_exists($validationResult, 'getValidationMessages')) {
                $validationMessages = $validationResult->getValidationMessages();
            } else {
                $validationMessages = $validationResult->getMessages();
            }
            return $validationMessages;
        }
        return null;
    }

    /**
     * Parses a service processing result for standard Apis to determine the appropriate HTTP status code and response format
     * for a request.
     *
     * The response body has a uniform structure with the following top level keys:
     * - validationErrors
     * - internalErrors
     * - data
     *
     * The response data key conveys the data payload for a response. The payload is either a "top level" array for a
     * single result, or an array for multiple results.
     *
     * @param        $processingResult         - The service processing result.
     * @param        $successStatusCode        - The HTTP status code to return for a successful operation that completes without error.
     * @param        $isMultipleResultResponse - Indicates if the response contains multiple results.
     * @return array[]
     */
    public static function handleProcessingResult($processingResult, $successStatusCode, $isMultipleResultResponse = false): array
    {
        $httpResponseBody = [
            "validationErrors" => [],
            "internalErrors" => [],
            "data" => []
        ];
        if (!$processingResult->isValid()) {
            http_response_code(400);
            $httpResponseBody["validationErrors"] = $processingResult->getValidationMessages();
        } elseif ($processingResult->hasInternalErrors()) {
            http_response_code(500);
            $httpResponseBody["internalErrors"] = $processingResult->getInternalErrors();
        } else {
            http_response_code($successStatusCode);
            $dataResult = $processingResult->getData();

            if (!$isMultipleResultResponse) {
                $dataResult = (count($dataResult) === 0) ? [] : $dataResult[0];
            }

            // TODO: adunsulag this is a temporary fix to get the FHIR api working, remove this once sjpadgett/others
            // have provided the correct approach.
            $httpResponseBody = $dataResult;
//            $httpResponseBody["data"] = $dataResult;
        }

        return $httpResponseBody;
    }

    /**
     * Parses a service processing result for FHIR endpoints to determine the appropriate HTTP status code and response format
     * for a request.
     *
     * The response body has a normal Fhir Resource json:
     *
     * @param        $processingResult         - The service processing result.
     * @param        $successStatusCode        - The HTTP status code to return for a successful operation that completes without error.
     * @return array|mixed
     */
    public static function handleFhirProcessingResult($processingResult, $successStatusCode)
    {
        $httpResponseBody = [];
        if (!$processingResult->isValid()) {
            http_response_code(400);
            $httpResponseBody["validationErrors"] = $processingResult->getValidationMessages();
        } elseif ($processingResult->hasInternalErrors()) {
            http_response_code(500);
            $httpResponseBody["internalErrors"] = $processingResult->getInternalErrors();
        } else {
            http_response_code($successStatusCode);
            $dataResult = $processingResult->getData();

            $httpResponseBody = $dataResult[0];
        }

        return $httpResponseBody;
    }
}
