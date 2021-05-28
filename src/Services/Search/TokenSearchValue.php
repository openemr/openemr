<?php

/**
 * TokenSearchValue represents a searchable token value containing the code and system
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Common\Uuid\UuidRegistry;

class TokenSearchValue
{
    /**
     * @var string|int|float|boolean
     */
    private $code;

    /**
     * @var string
     */
    private $system;

    /**
     * @var
     */
    private $isUuid;

    public function __construct($code, $system = null, $isUuid = false)
    {
        $this->isUuid = $isUuid;
        $this->setCode($code);
        $this->system = $system;
    }

    /**
     * Given a FHIR code system string, return the FHIR class value.
     * @param $codeSystemValue
     * @param @isUuid Whether the code system value represents a unique uuid in the system and should be converted to binary
     * @return TokenSearchValue
     */
    public static function buildFromFHIRString($codeSystemValue, $isUuid = false)
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
        if ($this->isUuid && !empty($code)) {
            return UuidRegistry::uuidToString($code);
        } else {
            return $code;
        }
    }

    public function __toString()
    {
        return ($this->getCode() ? $this->getHumanReadableCode() : "") . "|" . ($this->getSystem() ? $this->getSystem() : "");
    }
}
