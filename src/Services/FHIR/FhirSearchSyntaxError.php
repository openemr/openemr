<?php

/**
 * Carries a search parameter's syntax error from a FHIR service's search result to the controller that answers it.
 *
 * A FHIR search service records a SearchFieldException as a validation message ([field => message]) and returns
 * an empty result. FHIR R4 search, section 3.1.1.3, asks for an error when "the content of the parameter is
 * syntactically incorrect", and an empty search set when the parameter is unknown or can't be satisfied. So the
 * service marks only a SearchParameterSyntaxException here, under its own key, and the search controllers answer
 * that one with 400 and an OperationOutcome. The mark is a plain string, so it survives
 * ProcessingResult::addProcessingResult() when a mapped service combines its sub-services' results.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROperationOutcome;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueSeverity;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIssueType;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRResource\FHIROperationOutcome\FHIROperationOutcomeIssue;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchParameterSyntaxException;
use OpenEMR\Validators\ProcessingResult;

final class FhirSearchSyntaxError
{
    /** The validation-message key that names the parameter whose value didn't parse. */
    public const VALIDATION_KEY = '_search_parameter_syntax_error';

    /**
     * Records the exception as a validation message, as the services always have, and marks a syntax error.
     */
    public static function record(ProcessingResult $result, SearchFieldException $exception): void
    {
        $messages = [$exception->getField() => $exception->getMessage()];
        if ($exception instanceof SearchParameterSyntaxException) {
            $messages[self::VALIDATION_KEY] = $exception->getField();
        }
        $result->setValidationMessages($messages);
    }

    /**
     * @return string|null the parameter whose value didn't parse, or null
     */
    public static function find(ProcessingResult $result): ?string
    {
        $messages = $result->getValidationMessages();
        if (!is_array($messages)) {
            return null;
        }
        $parameter = $messages[self::VALIDATION_KEY] ?? null;
        return is_string($parameter) ? $parameter : null;
    }

    /**
     * The diagnostics are fixed text naming the parameter and the form it takes. The parser's own message stays
     * in the validation messages and the server log, out of the response. Only date parameters are marked today,
     * so the text names the date forms.
     */
    public static function operationOutcome(string $parameter): FHIROperationOutcome
    {
        $issue = new FHIROperationOutcomeIssue();
        $severity = new FHIRIssueSeverity();
        $severity->setValue('error');
        $issue->setSeverity($severity);
        $code = new FHIRIssueType();
        $code->setValue('invalid');
        $issue->setCode($code);
        $issue->setDiagnostics(new FHIRString(
            "Search parameter '" . $parameter . "' must be a FHIR date or dateTime, such as 2020-01-31 or 2020-01-31T13:45:00Z."
        ));
        $outcome = new FHIROperationOutcome();
        $outcome->addIssue($issue);
        return $outcome;
    }
}
