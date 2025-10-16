<?php

/**
 * FhirMetaDataRestController
 * TODO: @adunsulag we should probably rename this to be FhirCapabilityStatementRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCapabilityStatementKind;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity;
use OpenEMR\FHIR\SMART\Capability;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCapabilityStatement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSoftware;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementImplementation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;

/**
 * Supports REST interactions with the FHIR METADATA
 */
class FhirMetaDataRestController
{
    private readonly RestControllerHelper $restHelper;
    private readonly ServerConfig $config;

    public function __construct()
    {
        $this->config = new ServerConfig();
        $this->restHelper = new RestControllerHelper($this->config->getFhirUrl());
    }

    protected function buildCapabilityStatement(): FHIRCapabilityStatement
    {
        // TODO: @adunsulag we need to centralize the route inclusion and figure out how to handle the profiles...
        $routes = include $this->config->getWebServerRoot() . "/apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php";
        $capabilityStatement = new FHIRCapabilityStatement();
        $pubStatus = new FHIRPublicationStatus();
        $pubStatus->setValue("active");
        $capabilityStatement->setStatus($pubStatus);
        $fhirVersion = new FHIRFHIRVersion();
        $fhirVersion->setValue("4.0.1");
        $capabilityStatement->setFhirVersion($fhirVersion);
        $kind = new FHIRCapabilityStatementKind();
        $kind->setValue("instance");
        $capabilityStatement->setKind($kind);
        $capabilityStatement->addFormat(new FHIRCode("application/json"));
        $resturl = new FHIRUrl();
        $resturl->setValue($this->config->getFhirUrl());
        $implementation = new FHIRCapabilityStatementImplementation();
        $implementation->setUrl($resturl);
        $implementation->setDescription(new FHIRString("OpenEMR FHIR API"));
        $capabilityStatement->setImplementation($implementation);
        $dateTime = new FHIRDateTime();
        $dateTime->setValue(date("Y-m-d", time()));
        $capabilityStatement->setDate($dateTime);

        // we build our rest object with our helpers here
        $restObj = $this->restHelper->getCapabilityRESTObject($routes);
        $restObj->setSecurity($this->getRestSecurity());

        $capabilityStatement->addRest($restObj);
        $composerStr = file_get_contents($this->config->getWebServerRoot() . "/composer.json");
        $composerObj = json_decode($composerStr, true);
        $software = new FHIRCapabilityStatementSoftware();
        $software->setName(new FHIRString("OpenEMR"));
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
        $service->text = new FHIRString(xlt("OAuth2 using SMART-on-FHIR profile (see http://docs.smarthealthit.org)"));

        $coding = new FHIRCoding();
        $coding->setSystem(new FHIRUri("http://terminology.hl7.org/CodeSystem/restful-security-service"));
        $coding->setCode(new FHIRCode("SMART-on-FHIR"));
        $coding->setDisplay(new FHIRString("SMART-on-FHIR"));

        $service->addCoding($coding)
                ->setText(new FHIRString(xlt("OAuth2 using SMART-on-FHIR profile (see http://docs.smarthealthit.org)")));

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
        // now add our SMART capabilities
        foreach (Capability::FHIR_SUPPORTED_CAPABILITIES as $smartCapability) {
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
