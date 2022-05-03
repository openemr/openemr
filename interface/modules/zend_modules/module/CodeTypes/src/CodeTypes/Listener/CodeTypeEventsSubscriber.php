<?php


namespace OpenEMR\ZendModules\CodeTypes\Listener;


use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\Core\CodeTypeInstalledEvent;
use OpenEMR\Events\Core\SQLUpgradeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CodeTypeEventsSubscriber  implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            SQLUpgradeEvent::EVENT_UPGRADE_POST => 'onSqlUpgradeEvent'
            ,CodeTypeInstalledEvent::EVENT_INSTALLED_POST
        ];
    }

    public function onSqlUpgradeEvent(SQLUpgradeEvent $event) {
        // grab our currently installed code types, check for SNOMED-CT AND CPT4 and then update accordingly.
        // SELECT * FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key
    }

    public function onCodeTypeInstalledEvent(CodeTypeInstalledEvent $event) {
        if ($event->getCodeType() == "SNOMED") {
            // check if we have SNOMED codes installed and update our list options
            $this->updateSNOMEDCTMappings();
        }
        else if ($event->getCodeType() == "CPT4") {
            // check if we have CPT4 codes installed and update our list options
            $this->updateCPT4Mappings();
        }
    }
    private function updateSNOMEDCTMappings() {
        // update our list options
        $mappings = [
            'visit-after-hours' => '185463005',
            'visit-after-hours-not-night' => '185464004',
            'weekend-visit' => '185465003',
            'office-visit' => '30346009',
            'established-patient' => '3391000175108',
            'new-patient' => '37894004',
            'postoperative-follow-up' => '439740005'
        ];

        try {
            \sqlBeginTrans();
            foreach ($mappings as $option_id => $code_id) {
                $sql = "UPDATE list_options SET codes=CONCAT('SNOMED-CT:', ?) WHERE list_id='encounter-types' AND option_id=?";
                QueryUtils::sqlStatementThrowException($sql, [$option_id, $code_id]);
            }
            \sqlCommitTrans();
        }
        catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            \sqlRollbackTrans();
        }
    }

    private function updateCPT4Mappings() {
        // update our list options
        $mappings = [
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

        try {
            \sqlBeginTrans();
            foreach ($mappings as $option_id => $code_text) {
                $code_id = QueryUtils::fetchSingleValue("SELECT `code` FROM codes_cpt WHERE code_text =?", 'code', [$code_text]);
                if (empty($code_id)) {
                    (new SystemLogger())->debug("Failed to find code in codes_cpt with code_text. Skipping option_id"
                        , ['code_text' => $code_text, 'option_id' => $option_id]);
                }
                $sql = "UPDATE list_options SET codes=CONCAT('CPT4:', ?) WHERE list_id='encounter-types' AND option_id=?";
                QueryUtils::sqlStatementThrowException($sql, [$option_id, $code_id]);
            }
            \sqlCommitTrans();
        }
        catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            \sqlRollbackTrans();
        }
    }
}