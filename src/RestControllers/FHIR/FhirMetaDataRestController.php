<?php

/**
 * FhirMetaDataRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Services\FHIR\FhirValidationService;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleEntry;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCapabilityStatement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementRest;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSoftware;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementImplementation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;

require_once(__DIR__ . '/../../../_rest_config.php');


/**
 * Supports REST interactions with the FHIR METADATA
 */
class FhirMetaDataRestController
{
    private $fhirPatientService;
    private $fhirService;
    private $fhirValidate;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidate = new FhirValidationService();
    }

    protected function setSearchParams($resource, $paramsList)
    {
        $serviceClass = "OpenEMR\\Services\\FHIR\\Fhir" . $resource . "Service";
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
                    array_push($paramsList, $param);
                }
            }
        }
        return $paramsList;
        // error_log(print_r($paramsList,TRUE));
    }

    protected function addRequestMethods($items, $methods)
    {
        $reqMethod = trim($items[0], " ");
        if (strcmp($reqMethod, "GET") == 0) {
            if (sizeof($items)  == 4) {
                if (strcmp($items[3], ":id") == 0 || strcmp($items[3], ":uuid") == 0) {
                    $method = array(
                        "code" => "read"
                    );
                    array_push($methods, $method);
                }
            } else {
                $method = array(
                    "code" => "search-type"
                );
                array_push($methods, $method);
            }
        } elseif (strcmp($reqMethod, "POST") == 0) {
            $method = array(
                "code" => "insert"
            );
            array_push($methods, $method);
        } elseif (strcmp($reqMethod, "PUT") == 0) {
            $method = array(
                "code" => "update"
            );
            array_push($methods, $method);
        }
        return $methods;
    }


    protected function getCapabilityRESTJSON($routes)
    {
        $ignore = ["metadata","auth"];
        $resourcesHash = array();
        foreach ($routes as $key => $function) {
            $items  = explode("/", $key);
            $resource = $items[2];
            if (!in_array($resource, $ignore)) {
                if (!array_key_exists($resource, $resourcesHash)) {
                    $resourcesHash[$resource] = array(
                        "methods" => [],
                        "params" => []
                    );
                }
                $resourcesHash[$resource]["params"] = $this->setSearchParams($resource, $resourcesHash[$resource]["params"]);
                $resourcesHash[$resource]["methods"] = $this->addRequestMethods($items, $resourcesHash[$resource]["methods"]);
            }
        }
        $resources = [];
        foreach ($resourcesHash as $resource => $data) {
            $resArray = array(
                "type" => $resource,
                "profile" => "http://hl7.org/fhir/StructureDefinition/" . $resource,
                "interaction" => $data["methods"],
                "searchParam" => $data["params"]
            );
            array_push($resources, $resArray);
        }
        $restItem = array(
            "resource" => $resources,
            "mode" => "server"
        );
        return $restItem;
    }

    protected function buildCapabilityStatement()
    {
        $gbl = \RestConfig::GetInstance();
        $routes = $gbl::$FHIR_ROUTE_MAP;
        $serverRoot = $gbl::$webserver_root;
        $capabilityStatement = new FHIRCapabilityStatement();
        $capabilityStatement->setStatus("active");
        $fhirVersion = new FHIRFHIRVersion();
        $fhirVersion->setValue("4.0.1");
        $capabilityStatement->setFhirVersion($fhirVersion);
        $capabilityStatement->setKind("instance");
        $capabilityStatement->setStatus("Not provided");
        $capabilityStatement->addFormat(new FHIRCode("application/json"));
        $resturl = new FHIRUrl();
        $resturl->setValue("http://" . $_SERVER['SERVER_NAME'] . $gbl::$SITE . $gbl::$ROOT_URL . "/fhir");
        $implementation = new FHIRCapabilityStatementImplementation();
        $implementation->setUrl($resturl);
        $implementation->setDescription("OpenEMR FHIR API");
        $capabilityStatement->setImplementation($implementation);
        $dateTime = new FHIRDateTime();
        $dateTime->setValue(date("Y-m-d", time()));
        $capabilityStatement->setDate($dateTime);
        $restJSON = $this->getCapabilityRESTJSON($routes);
        $restObj = new FHIRCapabilityStatementRest($restJSON);
        $capabilityStatement->addRest($restObj);
        $composerStr = file_get_contents($serverRoot . "/composer.json");
        $composerObj = json_decode($composerStr, true);
        $software = new FHIRCapabilityStatementSoftware();
        $software->setName("OpenEMR");
        $software->setVersion($composerObj["version"]);
        $capabilityStatement->setSoftware($software);
        return $capabilityStatement;
    }

    /**
     *
     *
     * Returns Metadata in CapabilityStatement FHIR resource format
     *
     */
    public function getMetaData()
    {
        return $this->buildCapabilityStatement();
    }
}
