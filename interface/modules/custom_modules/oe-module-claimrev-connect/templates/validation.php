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

use OpenEMR\Modules\ClaimRevConnector\PrintProperty;

/** @var \stdClass $data */
/** @var \stdClass $eligibilityData */

$asValidations = static fn(mixed $v): ?iterable => is_iterable($v) ? $v : null;

if (property_exists($data, 'requestValidations')) {
    PrintProperty::printValidation("Primary Validations", $asValidations($data->requestValidations));
}
if (property_exists($data, 'informationSourceName')) {
    $informationSourceName = $data->informationSourceName;
    if (is_object($informationSourceName) && property_exists($informationSourceName, 'requestValidations')) {
        PrintProperty::printValidation("information Source Validations", $asValidations($informationSourceName->requestValidations));
    }
}

if (property_exists($data, 'receiver')) {
    $receiver = $data->receiver;
    if (is_object($receiver) && property_exists($receiver, 'requestValidations')) {
        PrintProperty::printValidation("Receiver Validations", $asValidations($receiver->requestValidations));
    }
}

if (property_exists($data, 'subscriber')) {
    $subscriber = $data->subscriber;
    if (is_object($subscriber) && property_exists($subscriber, 'requestValidations')) {
        PrintProperty::printValidation("Receiver Validations", $asValidations($subscriber->requestValidations));
    }
}

if (property_exists($eligibilityData, 'validations')) {
    PrintProperty::printValidation("Main Validations", $asValidations($eligibilityData->validations));
}
