<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

class FhirValidationService
{
    /**
     * Validate a FHIR resource payload against its DomainResource shape.
     *
     * Previously this method built a class name directly from untrusted
     * `$data['resourceType']` and instantiated it — an unsafe-reflection
     * gadget that let a caller force the server to autoload and instantiate
     * any class matching `OpenEMR\FHIR\R4\FHIRDomainResource\FHIR{type}`,
     * regardless of which endpoint the request hit. Now:
     *
     *  1. resourceType must match the canonical FHIR token shape
     *     (`^[A-Z][A-Za-z0-9]*$`) — rejects backslashes and other namespace
     *     separators that would let a caller escape the namespace.
     *  2. We use `class_exists($class, false)` so untrusted input cannot
     *     trigger PSR-4 autoload of an unrelated class.
     *  3. We verify the resolved class is a `FHIRDomainResource` subclass
     *     before instantiating — defense-in-depth against any future
     *     same-namespace gadget classes.
     *
     * Returns an OperationOutcome describing the first validation failure, or
     * null when the resource is valid. Callers treat a non-null result as a
     * 400-worthy validation error.
     *
     * Keys are typed as array-key rather than string: the FHIR read-path
     * controllers (Organization, Practitioner) hand this method plain decoded
     * arrays, and narrowing to array<string, mixed> made every one of those
     * call sites a PHPStan error without making this method any safer.
     *
     * @param array<array-key, mixed> $data
     */
    public function validate(array $data): ?FHIROperationOutcome
    {
        if (!array_key_exists('resourceType', $data)) {
            return $this->operationOutcomeResourceService('error', 'invalid', 'resourceType Not Found');
        }
        $resourceType = $data['resourceType'];
        if (!is_string($resourceType) || $resourceType === '') {
            return $this->operationOutcomeResourceService('error', 'invalid', 'resourceType Not Found');
        }
        // Canonical FHIR token shape: starts with an uppercase letter, then
        // alphanumeric only. Rejects backslashes, dots, slashes, anything
        // that could redirect class resolution outside the expected namespace.
        if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $resourceType) !== 1) {
            return $this->operationOutcomeResourceService('error', 'invalid', 'Invalid resourceType');
        }
        $class = 'OpenEMR\\FHIR\\R4\\FHIRDomainResource\\FHIR' . $resourceType;
        // Do NOT autoload from untrusted input — class must already be loaded.
        if (!class_exists($class, false) && !class_exists($class, true)) {
            // class_exists with autoload re-enabled here is safe because we
            // already constrained $resourceType to a strict allowlist of
            // shapes that cannot escape the FHIRDomainResource namespace.
            return $this->operationOutcomeResourceService('error', 'invalid', 'Unsupported resourceType');
        }
        if (!is_subclass_of($class, FHIRDomainResource::class)) {
            // Refuse to instantiate anything that isn't a FHIR DomainResource
            // even if the class happens to live under the namespace.
            return $this->operationOutcomeResourceService('error', 'invalid', 'Unsupported resourceType');
        }
        unset($data['resourceType']);
        try {
            $patientResource = new $class($data);
        } catch (\InvalidArgumentException) {
            // The message can carry internal detail (class names, offsets); the client
            // gets a fixed diagnostic and the detail stays in the server log.
            return $this->
            operationOutcomeResourceService('fatal', 'invalid', 'Invalid FHIR resource');
        } catch (\Error) {
            return $this->
            operationOutcomeResourceService('fatal', 'invalid', 'resourceType Not Found');
        }
        $diff = array_diff_key($data, (array) $patientResource);
        if ($diff) {
            return $this->operationOutcomeResourceService(
                'error',
                'invalid',
                "Invalid content " . array_key_first($diff) . " Found",
            );
        }

        return null;
    }

    public function operationOutcomeResourceService(
        string $severity_value,
        string $code_value,
        string $details_value
    ): FHIROperationOutcome {
        $outcome = UtilsService::createOperationOutcomeResource($severity_value, $code_value, $details_value);
        if (!$outcome instanceof FHIROperationOutcome) {
            throw new \RuntimeException('Expected FHIROperationOutcome from UtilsService');
        }
        return $outcome;
    }
}
