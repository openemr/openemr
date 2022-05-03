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

    public function onCodeTypeInstalledPreEvent(CodeTypeInstalledEvent $event)
    {
        error_log("Got here in pre installed event");
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

    private function exists_code_table($tableName)
    {
        // make sure our table is installed
        $table_records = QueryUtils::fetchRecords("SHOW TABLES LIKE ?", [$tableName]);
        if (empty($table_records)) {
            (new SystemLogger())->debug("table for code_type does not exist", ['tableName' => $tableName]);
        }
        return !empty($table_records);
    }

    private function shouldUpdateCPT4Mappings()
    {
        if (!$this->exists_code_table('codes_cpt')) {
            // no codes installed so we aren't updating anything.
            return false;
        }

        foreach (self::CPT4_ENCOUNTER_TYPE_MAPPINGS as $option_id => $code_text) {
            $code_id = QueryUtils::fetchSingleValue("SELECT `code` FROM codes_cpt WHERE code_text =?", 'code', [$code_text]);
            if (empty($code_id)) {
                (new SystemLogger())->debug(
                    "Failed to find code in codes_cpt with code_text. Skipping option_id",
                    ['code_text' => $code_text, 'option_id' => $option_id]
                );
            }
            $sql = "SELECT codes FROM list_options WHERE list_id='encounter-types' AND option_id=?";
            $codes = QueryUtils::fetchSingleValue($sql, 'codes', [$option_id]);
            if ($codes != "CPT4:" . $code_id) {
                return true;
            }
        }
        // no upgrade needed
        return false;
    }

    private function shouldUpdateSNOMEDMappings()
    {
        foreach (self::SNOMED_ENCOUNTER_TYPE_MAPPINGS as $option_id => $code_id) {
            $sql = "SELECT codes FROM list_options WHERE list_id='encounter-types' AND option_id=?";
            $codes = QueryUtils::fetchSingleValue($sql, 'codes', [$option_id]);
            if ($codes != self::CODE_TYPE_SNOMED_CT . ":" . $code_id) {
                return true;
            }
        }
        // no upgrade needed
        return false;
    }

    private function updateSNOMEDCTMappings($logger = null)
    {
        // update our list options
        try {
            \sqlBeginTrans();
            foreach (self::SNOMED_ENCOUNTER_TYPE_MAPPINGS as $option_id => $code_id) {
                $sql = "UPDATE list_options SET codes=CONCAT('SNOMED-CT:', ?) WHERE list_id='encounter-types' AND option_id=?";
                $values = [$code_id, $option_id];
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

    private function updateCPT4Mappings($logger = null)
    {
        // update our list options
        try {
            \sqlBeginTrans();
            foreach (self::CPT4_ENCOUNTER_TYPE_MAPPINGS as $option_id => $code_text) {
                $code_id = QueryUtils::fetchSingleValue("SELECT `code` FROM codes_cpt WHERE code_text =?", 'code', [$code_text]);
                if (empty($code_id)) {
                    (new SystemLogger())->debug(
                        "Failed to find code in codes_cpt with code_text. Skipping option_id",
                        ['code_text' => $code_text, 'option_id' => $option_id]
                    );
                }
                $sql = "UPDATE list_options SET codes=CONCAT('CPT4:', ?) WHERE list_id='encounter-types' AND option_id=?";
                $values = [$code_id, $option_id];
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
