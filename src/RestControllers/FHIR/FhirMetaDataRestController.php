<?php

/**
 * FhirMetaDataRestController
 * TODO: @adunsulag we should probably rename this to be FhirCapabilityStatementRestController
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR;

use OpenApi\Attributes as OA;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCapabilityStatement;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCapabilityStatementKind;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRFHIRVersion;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPublicationStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementImplementation;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementRest;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSecurity;
use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementSoftware;
use OpenEMR\FHIR\SMART\Capability;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritableType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWritePolicy;

/**
 * Supports REST interactions with the FHIR METADATA
 */
class FhirMetaDataRestController
{
    private readonly RestControllerHelper $restHelper;
    private readonly ServerConfig $config;
    private readonly PatientWritePolicy $patientWritePolicy;

    /**
     * Documentation on the create and update of a type a site opened to patient apps.
     */
    public const PATIENT_WRITE_DOCUMENTATION = 'A patient app\'s create or update is held for staff review and is not written to the chart until staff approve it. Until then only the app that sent it can read it back, for the same patient, tagged held-for-review; once staff deny it or the app withdraws it (entered-in-error), it is gone (410). Other writes by a patient app are refused with 403.';

    public function __construct(?PatientWritePolicy $patientWritePolicy = null)
    {
        $this->config = new ServerConfig();
        $this->restHelper = new RestControllerHelper($this->config->getFhirUrl());
        $this->patientWritePolicy = $patientWritePolicy ?? PatientWritePolicy::fromGlobals(OEGlobalsBag::getInstance());
    }

    protected function buildCapabilityStatement(): FHIRCapabilityStatement
    {
        // TODO: @adunsulag we need to centralize the route inclusion and figure out how to handle the profiles...
        $includedRoutes = include $this->config->getWebServerRoot() . "/apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php";
        if (!is_array($includedRoutes)) {
            throw new \RuntimeException('FHIR route map could not be loaded');
        }
        // The route map is keyed by "<METHOD> /path"; re-key so the declared string
        // keys survive the include, which PHPStan can only see as mixed.
        $routes = [];
        foreach ($includedRoutes as $key => $handler) {
            $routes[(string) $key] = $handler;
        }
        $capabilityStatement = new FHIRCapabilityStatement();
        // TODO: @adunsulag we need to figure out capability statements for different FHIR versions/profiles
        $capabilityStatement->addInstantiates('http://hl7.org/fhir/us/core/CapabilityStatement/us-core-server');
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
        $this->documentPatientWrites($restObj);

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
     * Says on the create and update of each type a site opened to patient apps that a patient
     * app's write is held for review. Nothing changes while the mode is Off.
     */
    private function documentPatientWrites(FHIRCapabilityStatementRest $restObj): void
    {
        $opened = array_map(
            static fn (PatientWritableType $type): string => $type->fhirResourceType()->value,
            $this->patientWritePolicy->advertisedTypes(),
        );
        if ($opened === []) {
            return;
        }
        foreach ($restObj->getResource() as $resource) {
            if (!in_array($resource->getType()->getValue(), $opened, true)) {
                continue;
            }
            foreach ($resource->getInteraction() as $interaction) {
                if (in_array($interaction->getCode()->getValue(), ['create', 'update'], true)) {
                    $interaction->setDocumentation(new FHIRMarkdown(self::PATIENT_WRITE_DOCUMENTATION));
                }
            }
        }
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
     */
    #[OA\Get(
        path: '/fhir/metadata',
        description: 'Returns metadata (ie. CapabilityStatement resource) of the fhir server.',
        tags: ['fhir'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Return CapabilityStatement resource of the fhir server'
            ),
        ]
    )]
    public function getMetaData(): FHIRCapabilityStatement
    {
        return $this->buildCapabilityStatement();
    }
}
