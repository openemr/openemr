<?php

/**
 * Thrown when a search parameter's value can't be parsed. Today that is a date or date-time the date
 * parser rejects. It carries, besides the parser's message, what to tell the client about the value:
 * that goes into the 400 response's OperationOutcome, while the parser's message stays in the server's
 * validation messages and log.
 *
 * FHIR R4 search, section 3.1.1.3: "Where the content of the parameter is syntactically incorrect,
 * servers SHOULD return an error. However, where the issue is a logical condition (e.g. unknown
 * subject or code), the server SHOULD process the search ... with the result of returning an empty
 * search set." So only parse failures use this class; an unknown parameter, or a value that parses
 * but can't be satisfied, stays a plain SearchFieldException.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Search;

final class SearchParameterSyntaxException extends SearchFieldException
{
    /** Said of a value when the thrower gives no message for the client. */
    public const DEFAULT_CLIENT_MESSAGE = 'has a value that is not valid.';

    /**
     * @param string $clientMessage what is wrong with the value, written for the client to read after
     *                              "Search parameter 'name' ", such as "must be a FHIR date or dateTime."
     *                              Never the parser's own message.
     */
    public function __construct(
        string $field,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly string $clientMessage = self::DEFAULT_CLIENT_MESSAGE,
    ) {
        parent::__construct($field, $message, $code, $previous);
    }

    public function getClientMessage(): string
    {
        return $this->clientMessage;
    }
}
