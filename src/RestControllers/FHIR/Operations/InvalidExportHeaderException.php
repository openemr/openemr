<?php

/**
 * InvalidExportHeaderException.php  Class InvalidExportHeaderException Represents an invalid export header per Bulk FHIR Spect
 * @see https://hl7.org/fhir/uv/bulkdata/export/index.html#headers
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\FHIR\Operations;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Http\StatusCode;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportMemoryStreamWriter;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGroup;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType;
use OpenEMR\FHIR\R4\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue;
use OpenEMR\Services\FHIR\FhirExportJobService;
use OpenEMR\Services\FHIR\FhirExportServiceLocator;
use OpenEMR\Services\FHIR\FhirGroupService;
use OpenEMR\Services\FHIR\IFhirExportableResourceService;
use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;
use OpenEMR\Services\FHIR\UtilsService;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

/**
 *
 * @package OpenEMR\RestControllers\FHIR
 */
class InvalidExportHeaderException extends \Exception
{
}
