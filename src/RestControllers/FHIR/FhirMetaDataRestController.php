<?php

/**
 * FhirMetaDataRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity;
use OpenEMR\FHIR\SMART\Capability;
use OpenEMR\RestControllers\AuthorizationController;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\FHIR\FhirResourcesService;
use OpenEMR\Services\FHIR\FhirValidationService;
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
    private $fhirService;
    private $fhirValidate;
    private $restHelper;

    public function __construct()
    {
        $this->fhirService = new FhirResourcesService();
        $this->fhirValidate = new FhirValidationService();
        $this->restHelper = new RestControllerHelper();
    }

    protected function buildCapabilityStatement(): FHIRCapabilityStatement
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
        $resturl->setValue($gbl::$apisBaseFullUrl . "/fhir");
        $implementation = new FHIRCapabilityStatementImplementation();
        $implementation->setUrl($resturl);
        $implementation->setDescription("OpenEMR FHIR API");
        $capabilityStatement->setImplementation($implementation);
        $dateTime = new FHIRDateTime();
        $dateTime->setValue(date("Y-m-d", time()));
        $capabilityStatement->setDate($dateTime);

        // we build our rest object with our helpers here
        $restObj = $this->restHelper->getCapabilityRESTObject($routes);
        $restObj->setSecurity($this->getRestSecurity());

        $capabilityStatement->addRest($restObj);
        $composerStr = file_get_contents($serverRoot . "/composer.json");
        $composerObj = json_decode($composerStr, true);
        $software = new FHIRCapabilityStatementSoftware();
        $software->setName("OpenEMR");
        $software->setVersion($composerObj["version"]);
        $capabilityStatement->setSoftware($software);

        // add the fact that we support Bulk FHIR operations.
        $capabilityStatement->addInstantiates(new FHIRCanonical("http://hl7.org/fhir/uv/bulkdata/CapabilityStatement/bulk-data"));

        return $capabilityStatement;
    }


    /**
     * Creates the Security Capability Statement and returns it.
     * @return FHIRCapabilityStatementSecurity
     */
    private function getRestSecurity(): FHIRCapabilityStatementSecurity
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
        $this->addOauthSecurityExtensions($security);

        return $security;
    }

    /**
     * Adds all of the FHIR REST Extensions needed for things such as SMART on FHIR
     * @param FHIRCapabilityStatementSecurity $statement
     */
    private function addOauthSecurityExtensions(FHIRCapabilityStatementSecurity $statement): void
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
            ,'register' => $authServer->getRegistrationUrl()
            ,'introspect' => $authServer->getIntrospectionUrl()
            // TODO: if we have these URIs we can provide them
//            ,'manage' => $authServer->getManageUrl()
//            ,'revoke' => ''
        ];
        foreach ($oauthUrls as $url => $valueUri) {
            $oauthEndpointExtension = new FHIRExtension();
            $oauthEndpointExtension->setUrl($url);
            $oauthEndpointExtension->setValueUri($valueUri);
            $oauthExtension->addExtension($oauthEndpointExtension);
        }
        $statement->addExtension($oauthExtension);

        // now add our SMART capabilities
        foreach (Capability::SUPPORTED_CAPABILITIES as $smartCapability) {
            $extension = new FHIRExtension();
            $fhirCode = new FHIRCode($smartCapability);
            $extension->setUrl("http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities");
            $extension->setValueCode($fhirCode);
            $statement->addExtension($extension);
        }
    }

    /**
     * Returns Metadata in CapabilityStatement FHIR resource format
     *
     */
    public function getMetaData(): FHIRCapabilityStatement
    {
        return $this->buildCapabilityStatement();
    }
}
