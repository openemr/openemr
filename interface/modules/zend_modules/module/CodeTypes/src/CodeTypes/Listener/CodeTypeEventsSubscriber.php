<?php

/**
 * CodeTypeEventsSubscriber  Handles the mapping of code systems to our list options.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ZendModules\CodeTypes\Listener;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Events\Codes\CodeTypeInstalledEvent;
use OpenEMR\Events\Core\SQLUpgradeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CodeTypeEventsSubscriber implements EventSubscriberInterface
{
    private const CODE_TYPE_SNOMED = "SNOMED";
    private const CODE_TYPE_SNOMED_CT = "SNOMED-CT";
    private const CODE_TYPE_SNOMED_PR = "SNOMED-PR";
    private const CODE_TYPE_CPT4 = "CPT4";

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
        $logger = function ($message) use ($event): void {
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
            } elseif ($this->isSnomedCodeType($record['ct_key'])) {
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
        } elseif ($event->getCodeType() == "CPT4" && $this->shouldUpdateCPT4Mappings()) {
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
            ServiceContainer::getLogger()->debug("code_type is not active in system", ['codeType' => $codeType]);
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
                ServiceContainer::getLogger()->debug(
                    "Failed to find cpt4 code in codes with code_text. Skipping option_id",
                    ['code_text' => $code_text, 'option_id' => $option_id]
                );
                continue;
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
        $sql = null;
        $values = null;
        try {
            QueryUtils::inTransaction(function () use ($mappings, $list_id, $logger, &$sql, &$values): void {
                foreach ($mappings as $option_id => $code_id) {
                    $sql = <<<'SQL'
                    UPDATE list_options SET codes = CONCAT('SNOMED-CT:', ?) WHERE list_id = ? AND option_id = ?
                    SQL;
                    $values = [$code_id, $list_id, $option_id];
                    QueryUtils::sqlStatementThrowException($sql, $values);
                    if (is_callable($logger)) {
                        $logger(xl('Success') . ' - (sql=`' . $sql . '`, values=`' . var_export($values, true) . '`)');
                    }
                }
            });
        } catch (\Throwable $exception) {
            ServiceContainer::getLogger()->error($exception->getMessage(), ['exception' => $exception]);
            if (is_callable($logger)) {
                $logger(xl('Failed') . ' - (sql=`' . ($sql ?? 'N/A') . '`, values=`' . var_export($values ?? [], true) . '`)');
            }
        }
    }

    private function updateCPT4Mappings($logger = null)
    {
        // update our list options
        try {
            QueryUtils::inTransaction(function () use ($logger): void {
                foreach (self::CPT4_ENCOUNTER_TYPE_MAPPINGS as $option_id => $code_text) {
                    $code_id = QueryUtils::fetchSingleValue(
                        <<<'SQL'
                        SELECT `code` FROM codes
                        WHERE code_text = ?
                          AND code_type IN (SELECT ct_id FROM code_types WHERE ct_key = 'CPT4')
                        SQL,
                        'code',
                        [$code_text]
                    );
                    if ($code_id === null || $code_id === '') {
                        ServiceContainer::getLogger()->debug(
                            'Failed to find cpt4 code in codes with code_text. Skipping option_id',
                            ['code_text' => $code_text, 'option_id' => $option_id]
                        );
                        continue;
                    }
                    $sql = <<<'SQL'
                    UPDATE list_options SET codes = CONCAT('CPT4:', ?) WHERE list_id = ? AND option_id = ?
                    SQL;
                    $values = [$code_id, self::LIST_ID_ENCOUNTER_TYPES, $option_id];
                    if (is_callable($logger)) {
                        $logger('(sql=`' . $sql . '`, values=`' . var_export($values, true) . '`)');
                    }
                    QueryUtils::sqlStatementThrowException($sql, $values);
                }
            });
        } catch (\Throwable $exception) {
            ServiceContainer::getLogger()->error($exception->getMessage(), ['exception' => $exception]);
        }
    }
}
