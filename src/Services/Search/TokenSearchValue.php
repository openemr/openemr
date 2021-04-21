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

class TokenSearchValue
{
    /**
     * @var string|int|float
     */
    private $code;

    /**
     * @var string
     */
    private $system;

    public function __construct($code, $system = null)
    {
        $this->code = $code;
        $this->system = $system;
    }

    /**
     * Given a FHIR code system string, return the FHIR class value.
     * @param $codeSystemValue
     * @return TokenSearchValue
     */
    public static function buildFromFHIRString($codeSystemValue)
    {
        $code = $codeSystemValue;
        $valueParts = explode("|", $codeSystemValue);
        if (count($valueParts) == 1) {
            $system = null;
        } else {
            $system = $valueParts[0];
            $code = end($valueParts);
        }
        return new TokenSearchValue($code, $system);
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
     */
    public function setCode($code): void
    {
        $this->code = $code;
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
}
