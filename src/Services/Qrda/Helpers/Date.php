<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Services\Qrda\Helpers;

trait Date
{
    public function DateHelper() {
        return [
            'value_or_null_flavor' => function ($time) {
                if (!empty($time)) {
                    $v = "value='{$time}'";
                } else {
                    $v = "nullFlavor='UNK'";
                }
                return $v;
            },
            'performance_period_start' => function () {
                return "Hello Period Start";
            },
            'performance_period_end' => function () {
                return "Hello Period End";
            },
            'current_time' => function () {
                return date('Y-m-d H:i');
            },
            'birth_date_time' => function () {
                $birth_date_time = date('Ymd', strtotime($this->patient->birthDatetime));
                return "<birthTime {{#value_or_null_flavor}}" . $birth_date_time . "{{/value_or_null_flavor}}/>";
            }
        ];
    }
/*
def value_or_null_flavor(time)
    # this is a bit of a hack for a defineded undefined date
if time && DateTime.parse(time).year < 3000
"value='#{DateTime.parse(time).utc.to_formatted_s(:number)}'"
else
"nullFlavor='UNK'"
end
end

def performance_period_start
@performance_period_start.to_formatted_s(:number)
end

def performance_period_end
@performance_period_end.to_formatted_s(:number)
end

def current_time
Time.now.utc.to_formatted_s(:number)
end

def sent_date_time
"<low #{value_or_null_flavor(self['sentDatetime'])}/>"
end

def received_date_time
"<high #{value_or_null_flavor(self['receivedDatetime'])}/>"
end

def active_date_time
"<effectiveTime #{value_or_null_flavor(self['activeDatetime'])}/>"
end

def author_time
"<time #{value_or_null_flavor(self['authorDatetime'])}/>"
end

def author_effective_time
"<effectiveTime #{value_or_null_flavor(self['authorDatetime'])}/>"
end

def birth_date_time
"<birthTime #{value_or_null_flavor(self['birthDatetime'])}/>"
end

def result_date_time?
!self['resultDatetime'].nil?
end

def result_date_time
"<effectiveTime #{value_or_null_flavor(self['resultDatetime'])}/>"
end

def expired_date_time
"<effectiveTime>"\
"<low #{value_or_null_flavor(self['expiredDatetime'])}/>"\
"</effectiveTime>"
end

def medication_supply_request_period
"<effectiveTime xsi:type='IVL_TS'>"\
"<low #{value_or_null_flavor(self['relevantPeriod']['low'])}/>"\
"<high #{value_or_null_flavor(self['relevantPeriod']['high'])}/>"\
"</effectiveTime>"
end

def medication_duration_author_effective_time
"<effectiveTime xsi:type='IVL_TS'>"\
"<low #{value_or_null_flavor(self['authorDatetime'])}/>"\
"<high nullFlavor='UNK'/>"\
"</effectiveTime>"
end

def prevalence_period
"<effectiveTime>"\
"<low #{value_or_null_flavor(self['prevalencePeriod']['low'])}/>"\
"<high #{value_or_null_flavor(self['prevalencePeriod']['high'])}/>"\
"</effectiveTime>"
end

def relevant_period
"<effectiveTime>"\
"<low #{value_or_null_flavor(self['relevantPeriod']['low'])}/>"\
"<high #{value_or_null_flavor(self['relevantPeriod']['high'])}/>"\
"</effectiveTime>"
end

def participation_period
"<effectiveTime>"\
"<low #{value_or_null_flavor(self['participationPeriod']['low'])}/>"\
"<high #{value_or_null_flavor(self['participationPeriod']['high'])}/>"\
"</effectiveTime>"
end

def relevant_date_time_value
"<effectiveTime #{value_or_null_flavor(self['relevantDatetime'])}/>"
end

def relevant_date_period_or_null_flavor
return relevant_period if self['relevantPeriod'] && (self['relevantPeriod']['low'] || self['relevantPeriod']['high'])
return relevant_date_time_value if self['relevantDatetime']
"<effectiveTime nullFlavor='UNK'/>"
end

def medication_duration_effective_time
"<effectiveTime xsi:type=\"IVL_TS\">"\
"<low #{value_or_null_flavor(self['relevantPeriod']['low'])}/>"\
"<high #{value_or_null_flavor(self['relevantPeriod']['high'])}/>"\
"</effectiveTime>"
end

def facility_period
"<low #{value_or_null_flavor(self['locationPeriod']['low'])}/>"\
"<high #{value_or_null_flavor(self['locationPeriod']['high'])}/>"
end

def incision_datetime
"<effectiveTime #{value_or_null_flavor(self['incisionDatetime'])}/>"
end

def completed_prevalence_period
self['prevalencePeriod']['high'] ? true : false
end
*/
}
