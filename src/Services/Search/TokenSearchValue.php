<?php

/**
 * TokenSearchValue represents a searchable token value containing the code and system
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Common\Uuid\UuidRegistry;

class TokenSearchValue implements \Stringable
{
    /**
     * @var string|int|float|boolean
     */
    private $code;

    /**
     * @param mixed $code
     * @param string $system
     * @param bool $isUuid
     */
    public function __construct(
        $code,
        private $system = null,
        private $isUuid = false
    ) {
        $this->setCode($code);
    }

    /**
     * Given a FHIR code system string, return the FHIR class value.
     * A backslash is a plain character here. Values from a FHIR request go through buildFromEscapedFHIRString().
     * @param bool $isUuid Whether the code system value represents a unique uuid in the system and should be converted to binary
     */
    public static function buildFromFHIRString(string $codeSystemValue, bool $isUuid = false): TokenSearchValue
    {
        $code = $codeSystemValue;
        $valueParts = explode("|", $codeSystemValue);
        if (count($valueParts) == 1) {
            $system = null;
        } else {
            $system = $valueParts[0];
            $code = end($valueParts);
        }
        return new TokenSearchValue($code, $system, $isUuid);
    }

    /**
     * Reads a system|code value as a FHIR request sends it: an escaped separator (\|) is part of the system or code.
     * @see https://hl7.org/fhir/R4/search.html#escaping
     * @param bool $isUuid Whether the code represents a unique uuid in the system and should be converted to binary
     */
    public static function buildFromEscapedFHIRString(string $codeSystemValue, bool $isUuid = false): TokenSearchValue
    {
        $valueParts = FhirSearchValueEscape::split($codeSystemValue, '|');
        if (count($valueParts) == 1) {
            $system = null;
            $code = FhirSearchValueEscape::unescape($valueParts[0]);
        } else {
            $system = FhirSearchValueEscape::unescape($valueParts[0]);
            $code = FhirSearchValueEscape::unescape(end($valueParts));
        }
        return new TokenSearchValue($code, $system, $isUuid);
    }

    /**
     * @return float|int|string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param float|int|string $code
     * @throws \InvalidArgumentException if the class search value is a UUID the code value must be a valid UUID string
     */
    public function setCode($code): void
    {
        if ($this->isUuid) {
            if (UuidRegistry::isValidStringUUID($code)) {
                $this->code = UuidRegistry::uuidToBytes($code);
            } else {
                throw new \InvalidArgumentException("UUID columns must be a valid UUID string");
            }
        } else {
            $this->code = $code;
        }
    }

    /**
     * @return string
     */
    public function getSystem(): ?string
    {
        return $this->system;
    }

    /**
     * @param string $system
     */
    public function setSystem(string $system): void
    {
        $this->system = $system;
    }

    public function getHumanReadableCode()
    {
        $code = $this->getCode();
        if ($this->isUuid && is_string($code) && $code !== '') {
            return UuidRegistry::uuidToString($code);
        } else {
            return $code;
        }
    }

    public function __toString(): string
    {
        return ($this->getCode() ? $this->getHumanReadableCode() : "") . "|" . ($this->getSystem() ?: "");
    }
}
