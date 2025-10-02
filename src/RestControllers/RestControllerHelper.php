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

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use Http\Message\Encoding\GzipEncodeStream;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\RestApiExtend\RestApiResourceServiceEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\Services\FHIR\IResourceSearchableService;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRestfulCapabilityMode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRTypeRestfulInteraction;
use OpenEMR\FHIR\R4\FHIRResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementInteraction;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementOperation;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementRest;
use OpenEMR\Services\FHIR\IResourceUSCIGProfileService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RestControllerHelper
{
    /**
     * The resource endpoint names we want to skip over.
     */
    const IGNORE_ENDPOINT_RESOURCES = ['.well-known', 'metadata'];

    /**
     * The default FHIR services class namespace
     * TODO: should we build a fhir service locator class?  There are two places this is now used, in this class and
     * in the FhirProvenanceService...
     */
    const FHIR_SERVICES_NAMESPACE = "OpenEMR\\Services\\FHIR\\Fhir";

    const DEFAULT_STRUCTURE_DEFINITION = "http://hl7.org/fhir/StructureDefinition/";

    // @see https://www.hl7.org/fhir/search.html#table
    const FHIR_SEARCH_CONTROL_PARAM_REV_INCLUDE_PROVENANCE = "Provenance:target";

    public function __construct(private readonly string $restURL = "")
    {
    }

    const FHIR_PREFER_HEADER_RETURN_VALUES = ['minimal', 'representation', 'OperationOutcome'];

    public static function returnSingleObjectResponse($object, bool $gzipEncoded = false): ResponseInterface
    {
        $psrFactory = new Psr17Factory();
        // should we gzip this?

        $response = $psrFactory->createResponse(200);
        $stream = $psrFactory->createStream(json_encode($object));
        $stream->rewind(); // have to rewind the stream.
        // TODO: @adunsulag the private module this came from supported gzip encoding, but do we want that in the generic core code?
        if ($gzipEncoded) {
            $encodedStream = new GzipEncodeStream($stream);
            $response = $response->withAddedHeader('Content-Encoding', 'gzip')
                ->withHeader('Content-Type', 'application/json')
                ->withBody($encodedStream);
        } else {
            $response = $response->withHeader('Content-Type', 'application/json')
                ->withBody($stream);
        }
        return $response;
    }

    public static function getEmptyResponse(): ResponseInterface
    {
        $psrFactory = new Psr17Factory();
        return $psrFactory->createResponse(200)->withBody($psrFactory->createStream(json_encode([])));
    }

    public static function getResponseForProcessingResult(ProcessingResult $processingResult): ResponseInterface
    {
        $status = 200;
        $httpResponseBody = [];
        if (!$processingResult->isValid()) {
            $status = 400;
            $httpResponseBody["validationErrors"] = $processingResult->getValidationMessages();
        } elseif (count($processingResult->getData()) <= 0) {
            return self::getNotFoundResponse();
        } elseif ($processingResult->hasInternalErrors()) {
            $httpResponseBody["internalErrors"] = $processingResult->getInternalErrors();
        } else {
            return self::returnSingleObjectResponse($processingResult->getData()[0]);
        }
        $psrFactory = new Psr17Factory();
        return $psrFactory->createResponse($status)->withBody($psrFactory->createStream(json_encode($httpResponseBody)));
    }

    /**
     * @return ResponseInterface
     */
    public static function getNotFoundResponse(): ResponseInterface
    {
        $psrFactory = new Psr17Factory();
        return $psrFactory->createResponse(404)->withBody($psrFactory->createStream(json_encode(['error' => xlt('Not Found')])));
    }

    public static function addFhirLocationHeader(ResponseInterface $response, string $resourceType, int|string $id): ResponseInterface
    {
        $serverConfig = new ServerConfig();
        $url = $serverConfig->getFhirUrl() . "/" . $resourceType . "/" . $id;
        return $response->withHeader("Location", $url);
    }

    public static function getFhirOperationOutcomeSuccessResponse(string $resourceType, int|string $id): ResponseInterface
    {
        $operationOutcome = UtilsService::createOperationOutcomeSuccess($resourceType, $id);
        $psrFactory = new Psr17Factory();
        return $psrFactory->createResponse(200)->withBody($psrFactory->createStream(json_encode($operationOutcome)));
    }

    /**
     * @param  string $preferHeaderValue
     * @return 'minimal'|'representation'|'OperationOutcome'
     */
    public static function getReturnTypeFromPrefer(string $preferHeaderValue): string
    {
        $parts = explode("=", $preferHeaderValue);
        $prefer = end($parts);
        if (!in_array($prefer, self::FHIR_PREFER_HEADER_RETURN_VALUES)) {
            return 'minimal';
        }
        return $prefer;
    }

    /**
     * Configures the HTTP status code and payload returned within a response.
     *
     * @param $serviceResult
     * @param $customRespPayload
     * @param int $idealStatusCode
     * @return Response
     */
    public static function responseHandler($serviceResult, $customRespPayload = null, int $idealStatusCode = Response::HTTP_OK)
    {
        if ($serviceResult) {
            if ($customRespPayload) {
                $response = self::getResponseForPayload($customRespPayload);
            } else {
                $response = self::getResponseForPayload($serviceResult);
            }
            $response->setStatusCode($idealStatusCode);
        } else {
            $response = new Response('', Response::HTTP_NOT_FOUND);
        }
        return $response;
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
     * @param  $processingResult         - The service processing result.
     * @param  $successStatusCode        - The HTTP status code to return for a successful operation that completes without error.
     * @param  $isMultipleResultResponse - Indicates if the response contains multiple results.
     * @return array[]
     * @deprecated use createProcessingResultResponse() instead.
     */
    public static function handleProcessingResult(ProcessingResult $processingResult, $successStatusCode, $isMultipleResultResponse = false): array
    {
        $httpResponseBody = [
            "validationErrors" => [],
            "internalErrors" => [],
            "data" => [],
            "links" => []
        ];
        if (!$processingResult->isValid()) {
            http_response_code(400);
            $httpResponseBody["validationErrors"] = $processingResult->getValidationMessages();
            (new SystemLogger())->debug("RestControllerHelper::handleProcessingResult() 400 error", ['validationErrors' => $processingResult->getValidationMessages()]);
        } elseif ($processingResult->hasInternalErrors()) {
            http_response_code(500);
            $httpResponseBody["internalErrors"] = $processingResult->getInternalErrors();
            (new SystemLogger())->debug("RestControllerHelper::handleProcessingResult() 500 error", ['internalErrors' => $processingResult->getValidationMessages()]);
        } else {
            http_response_code($successStatusCode ?? 0);
            $dataResult = $processingResult->getData();
            $recordsCount = count($dataResult);
            (new SystemLogger())->debug("RestControllerHelper::handleFhirProcessingResult() Records found", ['count' => $recordsCount]);

            if (!$isMultipleResultResponse) {
                $dataResult = ($recordsCount === 0) ? [] : $dataResult[0];
            } else {
                $pagination = $processingResult->getPagination();
                // if site_addr_oauth is not set then we set it to be empty so we can handle relative urls
                $bundleUrl = ($GLOBALS['site_addr_oath'] ?? '') . ($_SERVER['REDIRECT_URL'] ?? '');
                $getParams = $_GET;
                // cleanup _limit and _offset
                unset($getParams['_limit']);
                unset($getParams['_offset']);
                // cleanup a mod_rewrite piece so the URL is nicer.
                if (isset($getParams['_REWRITE_COMMAND'])) {
                    unset($getParams['_REWRITE_COMMAND']);
                }
                $queryParams = http_build_query($getParams);

                $pagination->setSearchUri($bundleUrl . '?' . $queryParams);
                $httpResponseBody['links'] = $processingResult->getPagination()->getLinks();
            }

            $httpResponseBody["data"] = $dataResult;
        }

        return $httpResponseBody;
    }

    public static function createProcessingResultResponse(HttpRestRequest $request, ProcessingResult $processingResult, $successStatusCode = Response::HTTP_OK, $isMultipleResultResponse = false)
    {
        $httpResponseBody = [
            "validationErrors" => [],
            "internalErrors" => [],
            "data" => [],
            "links" => []
        ];
        $statusCode = $successStatusCode;
        if (!$processingResult->isValid()) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $httpResponseBody["validationErrors"] = $processingResult->getValidationMessages();
            (new SystemLogger())->debug("RestControllerHelper::handleProcessingResult() 400 error", ['validationErrors' => $processingResult->getValidationMessages()]);
        } elseif ($processingResult->hasInternalErrors()) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $httpResponseBody["internalErrors"] = $processingResult->getInternalErrors();
            (new SystemLogger())->debug("RestControllerHelper::handleProcessingResult() 500 error", ['internalErrors' => $processingResult->getValidationMessages()]);
        } else {
            $dataResult = $processingResult->getData();
            $recordsCount = count($dataResult);
            (new SystemLogger())->debug("RestControllerHelper::handleFhirProcessingResult() Records found", ['count' => $recordsCount]);

            if (!$isMultipleResultResponse) {
                $dataResult = ($recordsCount === 0) ? [] : $dataResult[0];
            } else {
                $pagination = $processingResult->getPagination();
                // if site_addr_oauth is not set then we set it to be empty so we can handle relative urls
                $bundleUrl = ($GLOBALS['site_addr_oath'] ?? '') . ($request->server->get('REDIRECT_URL', ''));
                $getParams = $request->query->all();
                // cleanup _limit and _offset
                unset($getParams['_limit']);
                unset($getParams['_offset']);
                // cleanup a mod_rewrite piece so the URL is nicer.
                if (isset($getParams['_REWRITE_COMMAND'])) {
                    unset($getParams['_REWRITE_COMMAND']);
                }
                $queryParams = http_build_query($getParams);

                $pagination->setSearchUri($bundleUrl . '?' . $queryParams);
                $httpResponseBody['links'] = $processingResult->getPagination()->getLinks();
            }

            $httpResponseBody["data"] = $dataResult;
        }
        // TODO: Do we want to use JsonResponse here? makes it hard to interoperate with other PSR-7 implementations if we go w/ symfony internally
        $psrFactory = new Psr17Factory();
        $response = $psrFactory->createResponse($statusCode)
            ->withHeader("Content-Type", "application/json")
            ->withBody($psrFactory->createStream(json_encode($httpResponseBody)));
        return $response;
    }

    /**
     * Parses a service processing result for FHIR endpoints to determine the appropriate HTTP status code and response format
     * for a request.
     *
     * The response body has a normal Fhir Resource json:
     *
     * @param        $processingResult  - The service processing result.
     * @param        $successStatusCode - The HTTP status code to return for a successful operation that completes without error.
     * @return Response
     */
    public static function handleFhirProcessingResult(ProcessingResult $processingResult, $successStatusCode): Response
    {
        $httpResponseBody = [];
        if (!$processingResult->isValid()) {
            $httpResponseBody["validationErrors"] = $processingResult->getValidationMessages();
            (new SystemLogger())->debug("RestControllerHelper::handleFhirProcessingResult() 400 error", ['validationErrors' => $processingResult->getValidationMessages()]);
            return new JsonResponse($httpResponseBody, Response::HTTP_BAD_REQUEST);
        } elseif (count($processingResult->getData()) <= 0) {
            (new SystemLogger())->debug("RestControllerHelper::handleFhirProcessingResult() 404 records not found");
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        } elseif ($processingResult->hasInternalErrors()) {
            (new SystemLogger())->debug("RestControllerHelper::handleFhirProcessingResult() 500 error", ['internalErrors' => $processingResult->getValidationMessages()]);
            $httpResponseBody["internalErrors"] = $processingResult->getInternalErrors();
            return new JsonResponse($httpResponseBody, Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            $dataResult = $processingResult->getData();
            (new SystemLogger())->debug("RestControllerHelper::handleFhirProcessingResult() Records found", ['count' => count($dataResult)]);
            return new JsonResponse($dataResult[0], $successStatusCode);
        }
    }

    public function setSearchParams($resource, FHIRCapabilityStatementResource $capResource, $service)
    {
        if (empty($service)) {
            return; // nothing to do here as the service isn't defined.
        }

        if (!$service instanceof IResourceSearchableService) {
            return; // nothing to do here as the source is not searchable.
        }

        if (empty($capResource->getSearchInclude())) {
            $capResource->addSearchInclude('*');
        }
        if ($service instanceof IResourceUSCIGProfileService && empty($capResource->getSearchRevInclude())) {
            $capResource->addSearchRevInclude(self::FHIR_SEARCH_CONTROL_PARAM_REV_INCLUDE_PROVENANCE);
        }
        $searchParams = $service->getSearchParams();
        $searchParams = is_array($searchParams) ? $searchParams : [];
        foreach ($searchParams as $fhirSearchField => $searchDefinition) {

            /**
             * @var FhirSearchParameterDefinition $searchDefinition
             */

            $paramExists = false;
            $type = $searchDefinition->getType();
            if ($type == SearchFieldType::DATETIME) {
                $type = 'date'; // fhir merges date and datetime into a single date for capability statement purposes.
            }

            foreach ($capResource->getSearchParam() as $searchParam) {
                if (strcmp($searchParam->getName(), (string) $fhirSearchField) == 0) {
                    $paramExists = true;
                }
            }
            if (!$paramExists) {
                $param = new FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSearchParam();
                $param->setName($fhirSearchField);
                $param->setType($type);
                $capResource->addSearchParam($param);
            }
        }
    }


    /**
     * Retrieves the fully qualified service class name for a given FHIR resource.  It will only return a class that
     * actually exists.
     *
     * @param  string $resource              The name of the FHIR resource that we attempt to find the service class for.
     * @param  string $serviceClassNameSpace The namespace to find the class in.  Defaults to self::FHIR_SERVICES_NAMESPACE
     * @return string|null  Returns the fully qualified name if the class is found, otherwise it returns null.
     */
    public function getFullyQualifiedServiceClassForResource(string $resource, string $serviceClassNameSpace = self::FHIR_SERVICES_NAMESPACE)
    {
        $serviceClass = $serviceClassNameSpace . $resource . "Service";
        if (class_exists($serviceClass)) {
            return $serviceClass;
        }
        return null;
    }

    public function addOperations($resource, $items, FHIRCapabilityStatementResource $capResource)
    {

        // TODO: @adunsulag we need to architect a more generic way of adding operations like we do with resources
        $operation = end($items);
        // we want to skip over anything that's not a resource $operation

        // first check to make sure the operation is not already defined
        // such as $bulkdata-status when we have both a POST and a DELETE rest route to the same operation
        if (!empty($capResource->getOperation())) {
            foreach ($capResource->getOperation() as $existingOperation) {
                // this doesn't handle the $export operations
                // TODO: is there a better way to handle all operations and not just things such as $bulkdata-status?
                if ($existingOperation->getName() == $operation) {
                    return; // already exists so let's skip adding this operation
                }
            }
        }

        if ($operation == '$export') {
            // operation definition must use the operation 'name'
            // rest.resource.operation.name must come from the OperationDefinition's code attribute which in this case is 'export'
            $definitionName = 'export';
            $operationName = 'export';
            if ($resource != '$export') {
                $definitionName = strtolower((string) $resource) . '-export';
            }
            // define export operation
            $fhirOperation = new FHIRCapabilityStatementOperation();
            $fhirOperation->setName($operationName);
            $fhirOperation->setDefinition(new FHIRCanonical('http://hl7.org/fhir/uv/bulkdata/OperationDefinition/' . $definitionName));
            $capResource->addOperation($fhirOperation);
        } elseif ($operation === '$bulkdata-status') {
            $fhirOperation = new FHIRCapabilityStatementOperation();
            $fhirOperation->setName($operation);
            $fhirOperation->setDefinition($this->restURL . '/OperationDefinition/$bulkdata-status');
            $capResource->addOperation($fhirOperation);
            // TODO: @adunsulag we should document in our capability statement how to use the bulkdata-status operation
        } elseif ($operation === '$docref') {
            $fhirOperation = new FHIRCapabilityStatementOperation();
            $fhirOperation->setName($operation);
            $fhirOperation->setDefinition(new FHIRCanonical('http://hl7.org/fhir/us/core/OperationDefinition/docref'));
            $capResource->addOperation($fhirOperation);
        } elseif (is_string($operation) && str_starts_with($operation, '$')) {
            (new SystemLogger())->debug("Found operation that is not supported in system", ['resource' => $resource, 'operation' => $operation, 'items' => $items]);
        }
    }

    public function addRequestMethods($items, FHIRCapabilityStatementResource $capResource)
    {
        $reqMethod = trim((string) $items[0], " ");
        $numberItems = count($items);
        $code = "";
        // we want to skip over $export operations.
        if (end($items) === '$export') {
            return;
        }

        // now setup our interaction types
        if (strcmp($reqMethod, "GET") == 0) {
            $code = !empty(preg_match('/:/', (string) $items[$numberItems - 1])) ? "read" : "search-type";
        } elseif (strcmp($reqMethod, "POST") == 0) {
            $code = "create";
        } elseif (strcmp($reqMethod, "PUT") == 0) {
            $code = "update";
        } elseif (strcmp($reqMethod, "DELETE") == 0) {
            $code = "delete";
        }

        if (!empty($code)) {
            $interaction = new FHIRCapabilityStatementInteraction();
            $restfulInteraction = new FHIRTypeRestfulInteraction();
            $restfulInteraction->setValue($code);
            $interaction->setCode($restfulInteraction);
            $capResource->addInteraction($interaction);
        }
    }


    public function getCapabilityRESTObject($routes, $serviceClassNameSpace = self::FHIR_SERVICES_NAMESPACE, $structureDefinition = self::DEFAULT_STRUCTURE_DEFINITION): FHIRCapabilityStatementRest
    {
        $restItem = new FHIRCapabilityStatementRest();
        $mode = new FHIRRestfulCapabilityMode();
        $mode->setValue('server');
        $restItem->setMode($mode);

        $resourcesHash = [];
        foreach ($routes as $key => $function) {
            $items = explode("/", (string) $key);
            if ($serviceClassNameSpace == self::FHIR_SERVICES_NAMESPACE) {
                // FHIR routes always have the resource at $items[2]
                $resource = $items[2];
            } else {
                // API routes do not always have the resource at $items[2]
                if (count($items) < 5) {
                    $resource = $items[2];
                } elseif (count($items) < 7) {
                    $resource = $items[4];
                    if (str_starts_with($resource, ':')) {
                        // special behavior needed for the API portal route
                        $resource = $items[3];
                    }
                } else { // count($items) < 9
                    $resource = $items[6];
                }
            }

            if (!in_array($resource, self::IGNORE_ENDPOINT_RESOURCES)) {
                $service = null;
                $serviceClass = $this->getFullyQualifiedServiceClassForResource($resource, $serviceClassNameSpace);
                $serviceClass = self::filterServiceClassForResource($resource, $serviceClass);
                if (!empty($serviceClass)) {
                    $service = new $serviceClass();
                }
                // typically the type is the same as the resource, but for operations it will be our OperationDefinition
                $type = self::getResourceTypeForResource($resource);
                $capResource = $resourcesHash[$type] ?? null;

                if (empty($capResource)) {
                    $capResource = new FHIRCapabilityStatementResource();
                    // make it explicit that we do not let the user use their own resource ids to create a new resource
                    // in the PUT/update operation.
                    $capResource->setUpdateCreate(false);
                    $capResource->setType(new FHIRCode($type));
                    $capResource->setProfile(new FHIRCanonical($structureDefinition . $type));

                    if ($service instanceof IResourceUSCIGProfileService) {
                        $profileUris = $service->getProfileURIs();
                        foreach ($profileUris as $uri) {
                            $capResource->addSupportedProfile(new FHIRCanonical($uri));
                        }
                    }
                    // per the specification type must be unique in the capability statement
                    $resourcesHash[$type] = $capResource;
                }
                $this->setSearchParams($resource, $capResource, $service);
                $this->addRequestMethods($items, $capResource);
                $this->addOperations($resource, $items, $capResource);
            }
        }

        foreach ($resourcesHash as $capResource) {
            $restItem->addResource($capResource);
        }
        return $restItem;
    }


    /**
     * Given a payload, returns a JsonResponse or Response object based on the type of the payload.  If the payload type is not supported,
     * a TypeError is thrown.
     * @param $payload The payload to convert into a response.
     * @throws \TypeError if the payload is not a supported type
     * @return JsonResponse|Response
     */
    private static function getResponseForPayload($payload)
    {
        if ($payload instanceof \JsonSerializable || is_array($payload) || is_numeric($payload)) {
            $response = new JsonResponse($payload);
        } else if ($payload instanceof \Stringable || is_string($payload)) {
            $response = new Response((string)$payload, Response::HTTP_OK, ['Content-Type' => 'text/html']);
        } else {
            throw new \TypeError(sprintf(
                'RestControllerHelper::getResponseForPayload() expects a string, array, numeric, or JsonSerializable object, %s given.',
                get_debug_type($payload)
            ));
        }
        return $response;
    }


    /**
     * Given a resource we've pulled from our rest route definitions figure out the type from our valueset
     * for the resource type: http://hl7.org/fhir/2021Mar/valueset-resource-types.html
     *
     * @param  string $resource
     * @return string
     */
    private static function getResourceTypeForResource(string $resource)
    {
        $firstChar = $resource[0] ?? '';
        if ($firstChar == '$') {
            return 'OperationDefinition';
        }
        return $resource;
    }

    /**
     * Fires off a system event for the given API resource to filter the serviceClass.  This gives module writers
     * the opportunity to extend the api, add / remove Implementation Guide profiles and declare different API conformance
     *
     * @param  $resource     The api resource that was parsed
     * @param  $serviceClass The service class that was found by default in the system or null if none was found
     * @return string|null The filtered service class property
     */
    private static function filterServiceClassForResource(string $resource, ?string $serviceClass)
    {
        if (!empty($GLOBALS['kernel'])) {
            $dispatcher = $GLOBALS['kernel']->getEventDispatcher();
            $event = $dispatcher->dispatch(new RestApiResourceServiceEvent($resource, $serviceClass), RestApiResourceServiceEvent::EVENT_HANDLE);
            return $event->getServiceClass();
        }

        return $serviceClass;
    }
}
