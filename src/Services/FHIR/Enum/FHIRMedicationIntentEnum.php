<?php

/*
 * FHIRMedicationStatusEnum.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Enum;

enum FHIRMedicationIntentEnum: string
{
    /**
     * The request is a suggestion made by someone/something that doesn't have an intention to ensure it occurs and without providing an authorization to act
     */
    case PROPOSAL = "proposal";

    /**
     * The request represents an intention to ensure something occurs without providing an authorization for others to act.
     */
    case PLAN = "plan";

    /**
     * The request represents a request/demand and authorization for action
     */
    case ORDER = "order";

    /**
     * The request represents the original authorization for the medication request.
     */
    case ORIGINAL_ORDER = "original-order";

    /**
     * The request represents an automatically generated supplemental authorization for action based on a parent authorization together with initial results of the action taken against that parent authorization..
     */
    case REFLEX_ORDER = "reflex-order";

    /**
     * The request represents the view of an authorization instantiated by a fulfilling system representing the details of the fulfiller's intention to act upon a submitted order.
     */
    case FILLER_ORDER = "filler-order";
    /**
     * The request represents an instance for the particular order, for example a medication administration record.
     */
    case INSTANCE_ORDER = "instance-order";

    /**
     * The request represents a component or option for a RequestGroup that establishes timing, conditionality and/or other constraints among a set of requests.
     */
    case OPTION = "option";
}
