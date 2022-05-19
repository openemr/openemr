<?php

/**
 * Handles Automated Measure Calculation (AMC) individual item report tracking and collection.
 * Right now we insert db records one at a time, but these can be done via a batch operation eventually to streamline
 * the database operations.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\AMC\Trackers;

class AMCItemTracker
{
    protected $items;
    protected $rules;

    public function __construct()
    {
        $this->items = [];
        $this->rules = [];
    }

    public function addItem($reportId, $itemId, $ruleId, $tempBeginMeasurement, $endMeasurement, $pass, $pid, $object_to_count, \AmcItemizedActionData $numeratorItemizedDetails, \AmcItemizedActionData $denominatorItemizedDetails)
    {
        $this->items[] = ['reportId' => $reportId, 'itemId' => $itemId, 'ruleId' => $ruleId,
            'begin' => $tempBeginMeasurement, 'end' => $endMeasurement, 'pass' => $pass, 'pid' => $pid
            , 'object_to_count' => $object_to_count];

        $combinedAmc = new \AmcItemizedActionData();
        $combinedAmc->addActionObject($numeratorItemizedDetails);
        $combinedAmc->addActionObject($denominatorItemizedDetails);
        $detailsJson = json_encode($combinedAmc);

        insertItemReportTracker($reportId, $itemId, $pass, $pid, '', $ruleId, $detailsJson);
    }

    public function addRule($rule)
    {
        $this->rules[$rule['id']] = $rule;
    }

    public function getRuleById($id)
    {
        return $this->rules[$id] ?? null;
    }

    public function getResults(): array
    {
        return $this->items;
    }

    /**
     * Our summary result is empty as we don't do summarizing here.
     * @return array
     */
    public function getSummaryResult()
    {
        return [];
    }

    public function addAggregator(AMCItemTracker $aggregator)
    {
        $this->items = array_merge($this->items, $aggregator->items);
    }
}
