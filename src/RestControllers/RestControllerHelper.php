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

            $httpResponseBody["data"] = $dataResult;
        }

        return $httpResponseBody;
    }

    /**
     * Parses a service processing result for FHIR endpoints to determine the appropriate HTTP status code and response format
     * for a request.
     *
     * The response body has a normal Fhir Resource json:
     *
     * @param        $processingResult  - The service processing result.
     * @param        $successStatusCode - The HTTP status code to return for a successful operation that completes without error.
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

    public function setSearchParams($resource, $paramsList, $serviceClassNameSpace = "OpenEMR\\Services\\FHIR\\Fhir")
    {
        $serviceClass = $serviceClassNameSpace . $resource . "Service";
        if (class_exists($serviceClass)) {
            $service = new $serviceClass();
            foreach ($service->getSearchParams() as $searchParam => $searchFields) {
                $paramExists = false;
                foreach ($paramsList as $param) {
                    if (strcmp($param["name"], $searchParam) == 0) {
                        $paramExists = true;
                    }
                }
                if (!$paramExists) {
                    $param = array(
                        "name" => $searchParam,
                        "type" => "string"
                    );
                    $paramsList[] = $param;
                }
            }
        }
        return $paramsList;
        // error_log(print_r($paramsList,TRUE));
    }

    public function addRequestMethods($items, $methods)
    {
        $reqMethod = trim($items[0], " ");
        if (strcmp($reqMethod, "GET") == 0) {
            $numberItems = count($items);
            if (!empty(preg_match('/:/', $items[$numberItems - 1]))) {
                $method = array(
                    "code" => "read"
                );
            } else {
                $method = array(
                    "code" => "search-type"
                );
            }
            $methods[] = $method;
        } elseif (strcmp($reqMethod, "POST") == 0) {
            $method = array(
                "code" => "insert"
            );
            $methods[] = $method;
        } elseif (strcmp($reqMethod, "PUT") == 0) {
            $method = array(
                "code" => "update"
            );
            $methods[] = $method;
        }

        return $methods;
    }


    public function getCapabilityRESTJSON($routes, $serviceClassNameSpace = "OpenEMR\\Services\\FHIR\\Fhir", $structureDefinition = "http://hl7.org/fhir/StructureDefinition/"): array
    {
        $ignore = ["metadata", "auth"];
        $resourcesHash = array();
        foreach ($routes as $key => $function) {
            $items = explode("/", $key);
            if ($serviceClassNameSpace == "OpenEMR\\Services\\FHIR\\Fhir") {
                // FHIR routes always have the resource at $items[2]
                $resource = $items[2];
            } else {
                // API routes do not always have the resource at $items[2]
                if (count($items) < 5) {
                    $resource = $items[2];
                } elseif (count($items) < 7) {
                    $resource = $items[4];
                    if (substr($resource, 0, 1) === ':') {
                        // special behavior needed for the API portal route
                        $resource = $items[3];
                    }
                } else { // count($items) < 9
                    $resource = $items[6];
                }
            }

            if (!in_array($resource, $ignore)) {
                if (!array_key_exists($resource, $resourcesHash)) {
                    $resourcesHash[$resource] = array(
                        "methods" => [],
                        "params" => []
                    );
                }
                $resourcesHash[$resource]["params"] = $this->setSearchParams($resource, $resourcesHash[$resource]["params"], $serviceClassNameSpace);
                $resourcesHash[$resource]["methods"] = $this->addRequestMethods($items, $resourcesHash[$resource]["methods"]);
            }
        }
        $resources = [];
        foreach ($resourcesHash as $resource => $data) {
            $resArray = array(
                "type" => $resource,
                "profile" => $structureDefinition . $resource,
                "interaction" => $data["methods"],
                "searchParam" => $data["params"]
            );
            $resources[] = $resArray;
        }
        $restItem = array(
            "resource" => $resources,
            "mode" => "server",
        );
        return $restItem;
    }
}
