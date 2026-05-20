<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

    namespace OpenEMR\Modules\ClaimRevConnector;

class EligibilityInquiryRequest
{
    public ?string $originatingSystemId = null;
    public ?string $relationship = null;
    public ?string $payerNumber = null;
    public ?string $payerName = null;
    public ?string $payerResponsibility = null;
    public ?InformationReceiver $provider = null;
    public ?string $industryCode = null;
    public ?string $serviceTypeCodes = null;

    public function __construct(
        public SubscriberPatientEligibilityRequest $subscriber,
        public SubscriberPatientEligibilityRequest $patient,
        string $relationship,
        string $payerResponsibility,
    ) {
        $pr = strtolower($payerResponsibility);
        $this->payerResponsibility = match ($pr) {
            'primary' => 'p',
            'secondary' => 's',
            'tertiary' => 't',
            default => null,
        };

        $rel = strtolower($relationship);
        $this->relationship = match ($rel) {
            'spouse' => '01',
            'child' => '19',
            default => '34',
        };
    }
}
