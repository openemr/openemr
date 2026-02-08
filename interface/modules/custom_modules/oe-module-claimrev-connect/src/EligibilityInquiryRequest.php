<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    namespace OpenEMR\Modules\ClaimRevConnector;

class EligibilityInquiryRequest
{
    public $originatingSystemId;
    public $relationship;
    public $payerNumber;
    public $payerName;
    public $payerResponsibility;
    public $provider;
    public $industryCode;
    public $serviceTypeCodes;

    public function __construct(public $subscriber, public $patient, $relationship, $payerResponsibility)
    {
        if (strtolower((string) $payerResponsibility) == "primary") {
            $this->payerResponsibility = "p";
        } elseif (strtolower((string) $payerResponsibility) == "secondary") {
            $this->payerResponsibility = "s";
        } elseif (strtolower((string) $payerResponsibility) == "tertiary") {
            $this->payerResponsibility = "t";
        }


        if (strtolower((string) $relationship) == "spouse") {
            $this->relationship = "01";
        } elseif (strtolower((string) $relationship) == "child") {
            $this->relationship = "19";
        } else {
            $this->relationship = "34";
        }
    }
}
