<?php

/**
 * Date is a mustache helper trait with various helper methods for dealing with dates, date ranges, and timestamps.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda\Helpers;

use Mustache_Context;
use OpenEMR\Services\Qrda\Util\DateHelper;
use ReflectionClass;
use ReflectionMethod;

trait Date
{
    protected $_performance_period_start;
    protected $_performance_period_end;

    public function value_or_null_flavor($time)
    {
        if (!empty($time) && strpos($time, "0000-00-00") === false) {
            $time = DateHelper::format_datetime($time);
            $v = "value='{$time}'";
        } else {
            $v = "nullFlavor='UNK'";
        }
        return $v;
    }

    public function performance_period_start(Mustache_Context $context)
    {
        return $this->to_formatted_s_number($this->_performance_period_start);
    }

    public function performance_period_end(Mustache_Context $context)
    {
        return $this->to_formatted_s_number($this->_performance_period_end);
    }

    public function current_time(Mustache_Context $context)
    {
        return DateHelper::format_datetime(date('Y-m-d H:i'));
    }

    public function sent_date_time(Mustache_Context $context)
    {
        return "<low " . $this->value_or_null_flavor($context->find('sendDateTime')) . "/>";
    }

    public function received_date_time(Mustache_Context $context)
    {
        return "<high " . $this->value_or_null_flavor($context->find('receivedDatetime')) . "/>";
    }

    public function active_date_time(Mustache_Context $context)
    {
        return "<effectiveTime " . $this->value_or_null_flavor($context->find('activeDatetime')) . "/>";
    }

    public function author_time(Mustache_Context $context)
    {
        return "<time " . $this->value_or_null_flavor($context->find('authorDatetime')) . "/>";
    }

    public function author_effective_time(Mustache_Context $context)
    {
        return "<effectiveTime " . $this->value_or_null_flavor($context->find('authorDatetime')) . "/>";
    }

    public function birth_date_time(Mustache_Context $context)
    {
        return "<birthTime " . $this->value_or_null_flavor($context->find('birthDatetime')) . "/>";
    }

    public function has_result_date_time(Mustache_Context $context): bool
    {
        return !empty($context->find('resultDatetime'));
    }

    public function result_date_time(Mustache_Context $context): string
    {
        return "<effectiveTime " . $this->value_or_null_flavor($context->find('resultDatetime')) . "/>";
    }

    public function expired_date_time(Mustache_Context $context)
    {
        return "<effectiveTime>"
            . "<low " . $this->value_or_null_flavor($context->find('expiredDatetime')) . "/>"
            . "</effectiveTime>";
    }

    public function medication_supply_request_period(Mustache_Context $context)
    {
        $relevantPeriod = $context->find('relevantPeriod') ?? ['low' => null, 'high' => null];
        return "<effectiveTime xsi:type='IVL_TS'>"
            . "<low " . $this->value_or_null_flavor($relevantPeriod['low']) . "/>"
            . "<high " . $this->value_or_null_flavor($relevantPeriod['high']) . "/>"
            . "</effectiveTime>";
    }

    public function medication_duration_author_effective_time(Mustache_Context $context)
    {
        return "<effectiveTime xsi:type='IVL_TS'>"
            . "<low " . $this->value_or_null_flavor($context->find('authorDatetime')) . "/>"
            . "<high nullFlavor='UNK'/>"
            . "</effectiveTime>";
    }

    public function prevalence_period(Mustache_Context $context)
    {
        $prevalencePeriod = json_decode(
            json_encode($context->find('prevalencePeriod')),
            true
        ) ?? ['low' => null, 'high' => null];
        return "<effectiveTime>"
            . "<low " . $this->value_or_null_flavor($prevalencePeriod['low'] ?? '') . "/>"
            . "<high " . $this->value_or_null_flavor($prevalencePeriod['high'] ?? '') . "/>"
            . "</effectiveTime>";
    }

    public function relevant_period(Mustache_Context $context)
    {
        $relevantPeriod = $context->find('relevantPeriod') ?? ['low' => null, 'high' => null];
        return "<effectiveTime>"
            . "<low " . $this->value_or_null_flavor($relevantPeriod['low']) . "/>"
            . "<high " . $this->value_or_null_flavor($relevantPeriod['high']) . "/>"
            . "</effectiveTime>";
    }

    public function participation_period(Mustache_Context $context)
    {
        $participationPeriod = $context->find('participationPeriod') ?? ['low' => null, 'high' => null];
        return "<effectiveTime>"
            . "<low " . $this->value_or_null_flavor($participationPeriod['low']) . "/>"
            . "<high " . $this->value_or_null_flavor($participationPeriod['high']) . "/>"
            . "</effectiveTime>";
    }

    public function relevant_date_time_value(Mustache_Context $context)
    {
        return "<effectiveTime " . $this->value_or_null_flavor($context->find('relevantDatetime')) . "/>";
    }

    /**
     * Returns the helper function to call for the relevant date period or returns null flavor
     * if there is no date period.
     * If the current context has a period we return the period helpfunction,
     * otherwise if we have a dateTime we return the date time helper function
     *
     * @param Mustache_Context $context The current stack context
     * @return string Helper function name or null flavor xml
     */
    public function relevant_date_period_or_null_flavor(Mustache_Context $context)
    {
        $relevantPeriod = $context->find('relevantPeriod');
        if (
            !empty($relevantPeriod) &&
            (
            isset($relevantPeriod['high'])
            )
        ) {
            return $this->relevant_period($context);
        } elseif (!empty($context->find('relevantDatetime'))) {
            return $this->relevant_date_time_value($context);
        } else {
            "<effectiveTime nullFlavor='UNK'/>";
        }
    }

    public function medication_duration_effective_time(Mustache_Context $context)
    {
        $relevantPeriod = $context->find('relevantPeriod') ?? ['low' => null, 'high' => null];
        return "<effectiveTime xsi:type=\"IVL_TS\">"
            . "<low " . $this->value_or_null_flavor($relevantPeriod['low']) . "/>"
            . "<high " . $this->value_or_null_flavor($relevantPeriod['high']) . "/>"
            . "</effectiveTime>";
    }

    public function facility_period(Mustache_Context $context)
    {
        $locationPeriod = $context->find('locationPeriod') ?? ['low' => null, 'high' => null];
        return "<low " . $this->value_or_null_flavor($locationPeriod['low']) . "/>"
            . "<high " . $this->value_or_null_flavor($locationPeriod['high']) . "/>";
    }

    public function incision_datetime(Mustache_Context $context)
    {
        return "<effectiveTime " . $this->value_or_null_flavor($context->find('incisionDatetime')) . "/>";
    }

    public function completed_prevalence_period(Mustache_Context $context): bool
    {
        $period = json_decode(json_encode($context->find('prevalencePeriod')), true) ?? ['low' => null, 'high' => null];
        return !empty($period['high']);
    }

    private function to_formatted_s_number($dateTime)
    {
        if (empty($dateTime) || !($dateTime instanceof \DateTime)) {
            if (!empty($dateTime) && is_string($dateTime)) {
                return (new \DateTime($dateTime))->format('YmdHis');
            }
            return 0;
        } else {
            return $dateTime->format("YmdHis");
        }
    }
}
