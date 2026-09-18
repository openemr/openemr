<?php

/**
 * INonPatientCompartmentResourceService is a marker interface declaring that a
 * FHIR resource service represents a resource which is NOT part of any patient
 * compartment per the FHIR specification -- so patient-scoped access rules do
 * not apply to it. Two groups of resources qualify:
 *
 *   - FHIR "conformance" / "definitional" resources that describe the system
 *     rather than a specific record: CapabilityStatement, OperationDefinition,
 *     StructureDefinition, CodeSystem, ValueSet, ImplementationGuide,
 *     SearchParameter.
 *   - Non-patient-compartment clinical resources that describe entities
 *     (practitioners, organizations, locations, medications, questionnaire
 *     templates) rather than a specific patient's data:
 *     Practitioner / PractitionerRole, Location, Organization, Medication,
 *     Questionnaire (template -- distinct from QuestionnaireResponse).
 *
 * This marker exists to make the patient-compartment enforcement
 * in {@see FhirServiceBase::getOne()} and
 * {@see \OpenEMR\Services\FHIR\Traits\ResourceServiceSearchTrait::createOpenEMRSearchParameters()}
 * explicit rather than implicit. Any service that neither implements
 * {@see IPatientCompartmentResourceService} nor this interface is treated as
 * an unrecognized shape when a patient-scoped request reaches it -- the
 * request is denied so a missing compartment implementation cannot return
 * another patient's data.
 *
 * DO NOT add this interface to services that legitimately hold patient data
 * (i.e. anything that has a `patient` search field or a `puuid` column) -- for
 * those services, implement {@see IPatientCompartmentResourceService} instead.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

interface INonPatientCompartmentResourceService
{
}
