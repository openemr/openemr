<?php

/**
 * FhirMetaDataRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
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


require_once(__DIR__ . '/../../../_rest_config.php');

/**
 * Supports REST interactions with the FHIR patient resource
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

    

    /**
     * Queries for a single FHIR patient resource by FHIR id
     * @param $fhirId The FHIR patient resource id (uuid)
     * @returns 200 if the operation completes successfully
     */
    public function getOne($fhirId)
    {
        $processingResult = $this->fhirPatientService->getOne($fhirId, true);
        return RestControllerHelper::handleProcessingResult($processingResult, 200);
    }

    /**
     * 
     * 
     * Returns CapabilityStatement FHIR resource 
     * 
     */
    public function getAll($searchParams)
    {
        $gbl = \RestConfig::GetInstance();
        $ignore = ["metadata","auth"];
        $resourcesHash = array();
        foreach($gbl::$FHIR_ROUTE_MAP as $key=>$function){
            $items  = explode("/",$key);
            $resource = $items[2];
            if(!in_array($resource, $ignore)){
                $reqMethod = trim($items[0]," ");
                if(!array_key_exists($resource,$resourcesHash)){
                    $resourcesHash[$resource] = array(
                        "methods" => [],
                        "params" => []
                    );
                }
                $serviceClass = "OpenEMR\\Services\\FHIR\\Fhir".$resource."Service";
                if(class_exists($serviceClass)){
                    $service = new $serviceClass;
                    foreach($service->getSearchParams() as $searchParam=>$searchFields){
                        $paramExists = false;
                        foreach($resourcesHash[$resource]["params"] as $param){
                            if(strcmp($param["name"],$searchParam) == 0){
                                $paramExists = true;
                            }
                        }
                        if(!$paramExists){
                            $param = array(
                                "name"=>$searchParam
                            );
                            array_push($resourcesHash[$resource]["params"],$param);
                        }
                        
                    }
                }
                if(strcmp($reqMethod,"GET") == 0){
                    if( sizeof($items)  == 4){
                        if(strcmp($items[3],":id") == 0 || strcmp($items[3],":uuid") == 0){
                            $method = array(
                                "code"=>"read"
                            );
                            array_push($resourcesHash[$resource]["methods"],$method);
                        }
                    }
                    else{
                        $method = array(
                            "code"=>"search-type"
                        );
                        array_push($resourcesHash[$resource]["methods"],$method);
                    }
                }
                elseif (strcmp($reqMethod,"POST") == 0) {
                    $method = array(
                        "code"=>"insert"
                    );
                    array_push($resourcesHash[$resource]["methods"],$method);
                }
                elseif (strcmp($reqMethod,"PUT") == 0) {
                    $method = array(
                        "code"=>"update"
                    );
                    array_push($resourcesHash[$resource]["methods"],$method);
                }
                
                
            }
           
        }
        $resources = [];
        foreach($resourcesHash as $resource=>$data){
            $resArray = array(
                "type" => $resource,
                "profile"=> "http://hl7.org/fhir/StructureDefinition/".$resource,
                "interaction" => $data["methods"],
                "searchParam"=>$data["params"]
            );
            
            array_push($resources ,$resArray );
        }
        $responseBody = '{
            "resourceType": "CapabilityStatement",
            "status": "active",
            "publisher": "Not provided",
            "kind": "instance",
            "software": {
                "name": "OpenEMR",
                "version": "5.0.2"
            },
            "implementation": {
                "description": "HAPI FHIR",
                "url": "http://localhost/openemr/apis/fhir"
            },
            "fhirVersion": "4.0.1",
            "format": [
                "application/json"
            ]
        }';
        $resObj = json_decode($responseBody,true);
        $capabilityStatement = new FHIRCapabilityStatement($resObj);
        $dateTime = new FHIRDateTime();
        $dateTime->setValue(date("Y-m-d",time()));
        $capabilityStatement->setDate($dateTime);
        $restItem = array(
            "resource" => $resources,
            "mode"=>"server"
        );
        $restObj = new FHIRCapabilityStatementRest($restItem);
        $capabilityStatement->addRest($restObj);
        $composerStr = file_get_contents($gbl::$webserver_root."/composer.json");
        $composerObj = json_decode($composerStr, true);
        if(in_array("version", $composerObj)){
            $software = new FHIRCapabilityStatementSoftware();
            $software->setName("Open EMR");
            $software->setVersion($composerObj["version"]);
            $capabilityStatement->setSoftware($software);
        }

        // $resObj["rest"][0]["resource"]=$resources;
        // $resObj["date"] = date("Y-m-d",time());
        return $capabilityStatement;
    
    }
}