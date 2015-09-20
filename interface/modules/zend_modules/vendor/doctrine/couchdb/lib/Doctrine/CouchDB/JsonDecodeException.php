<?php

namespace Doctrine\CouchDB;

class JsonDecodeException extends \Exception
{
    static public function fromLastJsonError()
    {
        $lastError = \json_last_error();
        switch ($lastError) {
            case \JSON_ERROR_DEPTH:
                return new self("The maximum stack depth has been exceeded");
            case \JSON_ERROR_STATE_MISMATCH:
                return new self("Invalid or malformed JSON");
            case \JSON_ERROR_CTRL_CHAR:
                return new self("Control character error, possibly incorrectly encoded");
            case \JSON_ERROR_SYNTAX:
                return new self("Syntax error");
            default:
                return new self("An unknownerror occured with code: " . $lastError);
        }
    }
}