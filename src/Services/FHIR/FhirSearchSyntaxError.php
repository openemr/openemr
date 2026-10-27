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
     * @return array{parameter: string, message: string}|null the marked parameter and its message, or null
     */
    public static function find(ProcessingResult $result): ?array
    {
        $messages = $result->getValidationMessages();
        if (!is_array($messages)) {
            return null;
        }
        $parameter = $messages[self::VALIDATION_KEY] ?? null;
        if (!is_string($parameter)) {
            return null;
        }
        $message = $messages[$parameter] ?? '';
        return ['parameter' => $parameter, 'message' => is_string($message) ? $message : ''];
    }

    public static function operationOutcome(string $parameter, string $message): FHIROperationOutcome
    {
        $issue = new FHIROperationOutcomeIssue();
        $severity = new FHIRIssueSeverity();
        $severity->setValue('error');
        $issue->setSeverity($severity);
        $code = new FHIRIssueType();
        $code->setValue('invalid');
        $issue->setCode($code);
        $issue->setDiagnostics(new FHIRString("Search parameter '" . $parameter . "': " . $message));
        $outcome = new FHIROperationOutcome();
        $outcome->addIssue($issue);
        return $outcome;
    }
}
