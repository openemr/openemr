<?php

/**
 * FhirMetaDataRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity;
use OpenEMR\RestControllers\AuthorizationController;
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
    /**
     * The SMART extension capabilites that our system supports
     * @see http://hl7.org/fhir/smart-app-launch/conformance/index.html
     *
     * All of these capabilities for MU3 are required to be implemented before HIT certification
     * can be complete.
     * @see ONC final rule commentary https://www.federalregister.gov/d/2020-07419/p-1184 Accessed on December 9th 2020
     */
    const SMART_CAPABILITIES = array (
        "launch-ehr"    // support for SMART’s EHR Launch mode
        , "launch-standalone" // support for SMART’s Standalone Launch mode
        , "client-public" // support for SMART’s public client profile (no client authentication)
        , "client-confidential-symmetric" // support for SMART’s confidential client profile (symmetric client secret authentication)
        , "sso-openid-connect" // support for SMART’s OpenID Connect profile
        , "context-banner" // support for “need patient banner” launch context (conveyed via need_patient_banner token parameter)
        , "context-style" // support for “SMART style URL” launch context (conveyed via smart_style_url token parameter)

        // These two capabilities apply just to Launching an app inside the EHR
        , "context-ehr-patient" // support for patient-level launch context (requested by launch/patient scope, conveyed via patient token parameter)
        , "context-ehr-encounter" // support for encounter-level launch context (requested by launch/encounter scope, conveyed via encounter token parameter)

        // These two capabilities apply for launching a standalone app and providing additional context information
        , "context-standalone-patient" // support for patient-level launch context (requested by launch/patient scope, conveyed via patient token parameter)
        , "context-standalone-encounter" // support for encounter-level launch context (requested by launch/encounter scope, conveyed via encounter token


        , "permission-offline"  // support for refresh tokens (requested by offline_access scope)
        , "permission-patient"  // support for patient-level scopes (e.g. patient/Observation.read)
        , "permission-user"     // support for user-level scopes (e.g. user/Appointment.read)
    );

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
            "mode" => "server",
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
        $resturl->setValue($GLOBALS['site_addr_oath'] . $gbl::$ROOT_URL . "/" . $gbl::$SITE . "/fhir");
        $implementation = new FHIRCapabilityStatementImplementation();
        $implementation->setUrl($resturl);
        $implementation->setDescription("OpenEMR FHIR API");
        $capabilityStatement->setImplementation($implementation);
        $dateTime = new FHIRDateTime();
        $dateTime->setValue(date("Y-m-d", time()));
        $capabilityStatement->setDate($dateTime);
        $restJSON = $this->getCapabilityRESTJSON($routes);
        $restObj = new FHIRCapabilityStatementRest($restJSON);
        $restObj->setSecurity($this->getRestSecurity());
        $this->addRestExtensions($restObj);
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
     * Creates the Security Capability Statement and returns it.
     * @return FHIRCapabilityStatementSecurity
     */
    private function getRestSecurity()
    {
        $service = new FHIRCodeableConcept();
        $service->text = xlt("OAuth2 using SMART-on-FHIR profile (see http://docs.smarthealthit.org)");

        $coding = new FHIRCoding();
        $coding->setSystem(new FHIRUrl("http://hl7.org/fhir/restful-security-service"));
        $coding->setCode("SMART-on-FHIR");

        $service->addCoding($coding)
                ->setText(xlt("OAuth2 using SMART-on-FHIR profile (see http://docs.smarthealthit.org)"));

        $security = new FHIRCapabilityStatementSecurity();
        $security->addService($service);
        return $security;
    }

    /**
     * Adds all of the FHIR REST Extensions needed for things such as SMART on FHIR
     * @param FHIRCapabilityStatementRest $restStatement
     */
    private function addRestExtensions(FHIRCapabilityStatementRest $restStatement)
    {
        $authServer = new AuthorizationController();
        $oauthExtension = new FHIRExtension();
        $oauthExtension->setUrl(new FHIRUrl("http://fhir-registry.smarthealthit.org/StructureDefinition/oauth-uris"));
        $oauthUrls = [
            // @see http://www.hl7.org/fhir/smart-app-launch/StructureDefinition-oauth-uris.html
            // and @see http://www.hl7.org/fhir/smart-app-launch/conformance/index.html#declaring-support-for-oauth2-endpoints
            // token and authorize are required because we don't use implicit grant flow.
            'token' => $authServer->getTokenUrl()
            ,'authorize' => $authServer->getAuthorizeUrl()
            // TODO: if we have these URIs we can provide them
//            ,'manage' => $authServer->getManageUrl()
//            ,'introspect' => ''
//            ,'revoke' => ''
        ];
        foreach ($oauthUrls as $url => $valueUri)
        {
            $oauthEndpointExtension = new FHIRExtension();
            $oauthEndpointExtension->setUrl($url);
            $oauthEndpointExtension->setValueUri($valueUri);
            $oauthExtension->addExtension($oauthEndpointExtension);
        }
        $restStatement->addExtension($oauthExtension);

        // now add our SMART capabilities
        foreach (self::SMART_CAPABILITIES as $smartCapability)
        {
            $extension = new FHIRExtension();
            $extension->setUrl("http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities");
            $extension->setValueCode($smartCapability);
            $restStatement->addExtension($extension);
        }
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
