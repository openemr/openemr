<?php

/**
 * No-Op class for handling Automated Measure Calculation (AMC) individual item report tracking and collection. This
 * class mocks the tracking operations but skips any data reporting.  It is used for when report tracking is turned off.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\AMC\Trackers;

class AMCItemSkipTracker extends AMCItemTracker
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addItem($reportId, $itemId, $ruleId, $tempBeginMeasurement, $endMeasurement, $pass, $pid, $object_to_count, \AmcItemizedActionData $numeratorItemizedDetails, \AmcItemizedActionData $denominatorItemizedDetails)
    {
        // no-op
        return;
    }
}
