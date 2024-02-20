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

use OpenEMR\Modules\ClaimRevConnector\PrintProperty;

$validations = null;
if (property_exists($data, 'requestValidations')) {
    $validations = $data->requestValidations;
    PrintProperty::printValidation("Primary Validations", $validations);
}
if (property_exists($data, 'informationSourceName')) {
    $informationSourceName = $data->informationSourceName;

    if ($informationSourceName != null && property_exists($informationSourceName, 'requestValidations')) {
        $validations = $informationSourceName->requestValidations;
        PrintProperty::printValidation("information Source Validations", $validations);
    }
}

if (property_exists($data, 'receiver')) {
    $receiver = $data->receiver;
    if ($receiver != null && property_exists($receiver, 'requestValidations')) {
        $validations = $receiver->requestValidations;
        PrintProperty::printValidation("Receiver Validations", $validations);
    }
}

if (property_exists($data, 'subscriber')) {
    $subscriber = $data->subscriber;
    if ($subscriber != null && property_exists($subscriber, 'requestValidations')) {
        $validations = $subscriber->requestValidations;
        PrintProperty::printValidation("Receiver Validations", $validations);
    }
}

if (property_exists($eligibilityData, 'validations')) {
    $mainValidations = $eligibilityData->validations;
    PrintProperty::printValidation("Main Validations", $mainValidations);
}
