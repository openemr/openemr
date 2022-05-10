<?php

/**
 * CodeTypeEventsSubscriber  Handles the mapping of code systems to our list options.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ZendModules\CodeTypes\Listener;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\Codes\CodeTypeInstalledEvent;
use OpenEMR\Events\Core\SQLUpgradeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CodeTypeEventsSubscriber implements EventSubscriberInterface
{
    private const SNOMED_ENCOUNTER_TYPE_MAPPINGS = [
        'visit-after-hours' => '185463005',
        'visit-after-hours-not-night' => '185464004',
        'weekend-visit' => '185465003',
        'office-visit' => '30346009',
        'established-patient' => '3391000175108',
        'new-patient' => '37894004',
        'postoperative-follow-up' => '439740005'
    ];

    private const SNOMED_IMMUNIZATION_REFUSAL_REASON_MAPPINGS = [
        // note only thing not mapped here is 'other' as we don't know what that goes here
        // note valueset OID is: 2.16.840.1.113883.3.526.3.1008
        'religious_exemption' => '183945002',
        'patient_decision' => '105480006',
        'parental_decision' => '105480006', // patient and parental refuse are considered the same
        'financial_problem' => '160932005',
        'financial_circumstances_change' => '160934006',
        'alternative_treatment_requested' => '182890002',
        'patient_declined_procedure' => '105480006',
        'patient_declined_drug' => '182895007',
        'patient_declined_drug_effects' => '182897004',
        'patient_declined_drug_beliefs' => '182900006',
        'patient_declined_drug_cannot_pay' => '182902003',
        'patient_moved' => '184081006',
        'patient_dissatisfied_result' => '185479006',
        'patient_dissatisfied_doctor' => '185481008',
        'patient_variable_income' => '224187001',
        'patient_self_discharge' => '225928004',
        'drugs_not_completed' => '266710000',
        'family_illness' => '266966009',
        'follow_defaulted' => '275694009',
        'patient_noncompliance' => '275936005',
        'patient_noshow' => '281399006',
        'patient_further_opinion' => '310343007',
        'patient_treatment_delay' => '373787003',
        'patient_medication_declined' => '406149000',
        'patient_medication_forgot' => '408367005',
        'patient_non_compliant' => '413311005',
        'procedure_not_wanted' => '416432009',
        'income_insufficient' => '423656007',
        'income_necessities_only' => '424739004',
        'refused' => '443390004',
        'patient_procedure_discontinued' => '713247000'
    ];

    private const CODE_TYPE_SNOMED = "SNOMED";
    private const CODE_TYPE_SNOMED_CT = "SNOMED-CT";
    private const CODE_TYPE_SNOMED_PR = "SNOMED-PR";
    private const CODE_TYPE_CPT4 = "CPT4";

    private const CPT4_ENCOUNTER_TYPE_MAPPINGS = [
        'new-patient-10' => 'New Patient (Brief)'
        ,'new-patient-15-29' => 'New Patient (Limited)'
        ,'new-patient-30-44' => 'Level 3, New Patient, Office Visit'
        , 'new-patient-45-59' => 'Extended Physical Exam'
        , 'new-patient-60-74' => 'New Exam (Comprehensive)'
        , 'established-patient-10-19' => 'Established Patient (Limited)'
        , 'established-patient-20-29' => 'Established Patient (Detailed)'
        , 'established-patient-30-39' => 'Established Patient (Extended)'
        , 'established-patient-40-54' => 'Established Patient (Comprehensive)'
    ];

    private const LIST_ID_ENCOUNTER_TYPES = 'encounter-types';
    private const LIST_ID_IMMUNIZATION_REFUSAL = 'immunization_refusal_reason';

    public static function getSubscribedEvents()
    {
        return [
            SQLUpgradeEvent::EVENT_UPGRADE_POST => 'onSqlUpgradeEvent'
            ,CodeTypeInstalledEvent::EVENT_INSTALLED_POST => 'onCodeTypeInstalledEvent'
        ];
    }

    public function onSqlUpgradeEvent(SQLUpgradeEvent $event)
    {
        // grab our currently installed code types, check for SNOMED-CT AND CPT4 and then update accordingly.
        // SELECT * FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key
        $activatedCodeTypes = QueryUtils::fetchRecords("SELECT ct_key FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key");
        if (empty($activatedCodeTypes)) {
            return;
        }
        // we want to push out to the system that we are making changes...
        $logger = function ($message) use ($event) {
            // make sure we escape this here.
            $event->getSqlUpgradeService()->flush_echo(text($message) . "<br />");
        };

        foreach ($activatedCodeTypes as $record) {
            if ($record['ct_key'] == self::CODE_TYPE_CPT4) {
                if ($this->shouldUpdateCPT4Mappings()) {
                    $logger("Updating " . $record['ct_key'] . " Mappings");
                    $this->updateCPT4Mappings($logger);
                } else {
                    $logger("Skipping Section #updateCPT4Mappings");
                }
            } else if ($this->isSnomedCodeType($record['ct_key'])) {
                if ($this->shouldUpdateSNOMEDMappings()) {
                    $logger("Updating " . $record['ct_key'] . " Mappings");
                    $this->updateSNOMEDCTMappings($logger);
                } else {
                    $logger("Skipping Section #updateSNOMEDMappings type=" . $record['ct_key']);
                }
            }
        }
    }

    public function onCodeTypeInstalledEvent(CodeTypeInstalledEvent $event)
    {
        if ($event->getCodeType() == "SNOMED") {
            // check if we have SNOMED codes installed and update our list options
            $this->updateSNOMEDCTMappings();
        } else if ($event->getCodeType() == "CPT4" && $this->shouldUpdateCPT4Mappings()) {
            // check if we have CPT4 codes installed and update our list options
            $this->updateCPT4Mappings();
        }
    }

    private function isSnomedCodeType($codeType)
    {
        return in_array($codeType, [self::CODE_TYPE_SNOMED, self::CODE_TYPE_SNOMED_CT, self::CODE_TYPE_SNOMED_PR]);
    }

    private function is_code_type_active($codeType)
    {
        // make sure our table is installed
        $table_records = QueryUtils::fetchRecords("select * from code_types WHERE `ct_active`=1 AND ct_key = ? ", [$codeType]);
        if (empty($table_records)) {
            (new SystemLogger())->debug("code_type is not active in system", ['codeType' => $codeType]);
        }
        return !empty($table_records);
    }

    private function shouldUpdateCPT4Mappings()
    {
        if (!$this->is_code_type_active('CPT4')) {
            // no codes installed so we aren't updating anything.
            return false;
        }

        foreach (self::CPT4_ENCOUNTER_TYPE_MAPPINGS as $option_id => $code_text) {
            $code_id = QueryUtils::fetchSingleValue("SELECT `code` FROM codes WHERE code_text =? "
                . " AND code_type IN (SELECT ct_id FROM code_types WHERE ct_key = 'CPT4')", 'code', [$code_text]);
            if (empty($code_id)) {
                (new SystemLogger())->debug(
                    "Failed to find cpt4 code in codes with code_text. Skipping option_id",
                    ['code_text' => $code_text, 'option_id' => $option_id]
                );
            }
            $sql = "SELECT codes FROM list_options WHERE list_id=? AND option_id=?";
            $codes = QueryUtils::fetchSingleValue($sql, 'codes', [self::LIST_ID_ENCOUNTER_TYPES, $option_id]);
            if ($codes != "CPT4:" . $code_id) {
                return true;
            }
        }
        // no upgrade needed
        return false;
    }

    private function shouldUpdateSNOMEDMappings()
    {
        if ($this->shouldUpdateListWithSnomedCodes(self::SNOMED_ENCOUNTER_TYPE_MAPPINGS, self::LIST_ID_ENCOUNTER_TYPES)) {
            return true;
        }

        if (
            $this->shouldUpdateListWithSnomedCodes(
                self::SNOMED_IMMUNIZATION_REFUSAL_REASON_MAPPINGS,
                self::LIST_ID_IMMUNIZATION_REFUSAL
            )
        ) {
            return true;
        }
        // no upgrade needed
        return false;
    }

    private function shouldUpdateListWithSnomedCodes($mappings, $list_id)
    {
        foreach ($mappings as $option_id => $code_id) {
            $sql = "SELECT codes FROM list_options WHERE list_id=? AND option_id=?";
            $codes = QueryUtils::fetchSingleValue($sql, 'codes', [$list_id, $option_id]);
            if ($codes != self::CODE_TYPE_SNOMED_CT . ":" . $code_id) {
                return true;
            }
        }
        return false;
    }

    private function updateSNOMEDCTMappings($logger = null)
    {
        $this->updateSNOMEDCTMappingsForList(
            self::SNOMED_ENCOUNTER_TYPE_MAPPINGS,
            self::LIST_ID_ENCOUNTER_TYPES,
            $logger
        );
        $this->updateSNOMEDCTMappingsForList(
            self::SNOMED_IMMUNIZATION_REFUSAL_REASON_MAPPINGS,
            self::LIST_ID_IMMUNIZATION_REFUSAL,
            $logger
        );
    }

    private function updateSNOMEDCTMappingsForList($mappings, $list_id, $logger = null)
    {
        // update our list options
        try {
            \sqlBeginTrans();
            foreach ($mappings as $option_id => $code_id) {
                $sql = "UPDATE list_options SET codes=CONCAT('SNOMED-CT:', ?) WHERE list_id=? AND option_id=?";
                $values = [$code_id, $list_id, $option_id];
                QueryUtils::sqlStatementThrowException($sql, $values);
                if (!empty($logger) && is_callable($logger)) {
                    $logger(xl('Success') . ' - (sql=`"' . $sql . '`, values=`' . var_export($values, true) . "`)");
                }
            }
            \sqlCommitTrans();
        } catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            if (!empty($logger) && is_callable($logger)) {
                $logger(xl('Failed') . ' - (sql=`"' . $sql . '`, values=`' . var_export($values, true) . "`)");
            }
            \sqlRollbackTrans();
        }
    }

    private function updateCPT4Mappings($logger = null)
    {
        // update our list options
        try {
            \sqlBeginTrans();
            foreach (self::CPT4_ENCOUNTER_TYPE_MAPPINGS as $option_id => $code_text) {
                $code_id = QueryUtils::fetchSingleValue("SELECT `code` FROM codes WHERE code_text =? "
                    . " AND code_type IN (SELECT ct_id FROM code_types WHERE ct_key = 'CPT4')", 'code', [$code_text]);
                if (empty($code_id)) {
                    (new SystemLogger())->debug(
                        "Failed to find cpt4 code in codes with code_text. Skipping option_id",
                        ['code_text' => $code_text, 'option_id' => $option_id]
                    );
                }
                $sql = "UPDATE list_options SET codes=CONCAT('CPT4:', ?) WHERE list_id=? AND option_id=?";
                $values = [$code_id, self::LIST_ID_ENCOUNTER_TYPES, $option_id];
                if (!empty($logger) && is_callable($logger)) {
                    $logger('(sql=`"' . $sql . '`, values=`' . var_export($values, true) . "`)");
                }
                QueryUtils::sqlStatementThrowException($sql, $values);
            }
            \sqlCommitTrans();
        } catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            \sqlRollbackTrans();
        }
    }
}
