<?php

/**
 * Handles the saving, retrieving, and creating of telehealth settings for patients and users.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Repository;

use Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthPersonSettings;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;

class TeleHealthPersonSettingsRepository
{
    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct(SystemLogger $logger)
    {
        $this->logger = $logger;
    }

    public function saveSettingsForPerson(TeleHealthPersonSettings $settings): TeleHealthPersonSettings
    {
        $columns = [
            'user_id' => $settings->getIsPatient() ? null : $settings->getDbRecordId()
            ,'patient_id' => $settings->getIsPatient() ? $settings->getDbRecordId() : null
            ,'enabled' => $settings->getIsEnabled()
        ];
        $id = $settings->getId();

        $columnKeys = array_keys($columns);
        $bind = array_values($columns);
        if (!empty($settings->getId())) {
            $updateColumns = implode(",", array_map(function ($val) {
                return "`$val` = ?";
            }, $columnKeys));

            // do an update
            $sql = "UPDATE comlink_telehealth_person_settings SET ";
            $sql .= $updateColumns . " WHERE id = ?";
            $bind[] = $id;
            QueryUtils::sqlStatementThrowException($sql, $bind);
        } else {
            $sql = 'INSERT INTO comlink_telehealth_person_settings( ' . implode(",", $columnKeys)
                    . ') VALUES (' . implode(",", array_map(function ($val) {
                        return "?";
                    }, $columnKeys)) . ')';
            $id = QueryUtils::sqlInsert($sql, $bind);
        }
        // get the most up to date db record
        return $this->getSettingsForId($id);
    }

    public function getSettingsForId($id): ?TeleHealthPersonSettings
    {
        $records = QueryUtils::fetchRecords("Select id,user_id, patient_id,date_created,date_updated,enabled  "
            . " from comlink_telehealth_person_settings WHERE id = ?", [$id]);
        if (!empty($records[0])) {
            return $this->createResultRecordFromDatabaseResult($records[0]);
        }
        return null;
    }

    public function getSettingsForPatient($pid): ?TeleHealthPersonSettings
    {
        $records = QueryUtils::fetchRecords("Select id,user_id, patient_id,date_created,date_updated,enabled  "
        . " from comlink_telehealth_person_settings WHERE patient_id = ?", [$pid]);
        if (!empty($records[0])) {
            return $this->createResultRecordFromDatabaseResult($records[0]);
        }
        return null;
    }

    public function getSettingsForUser($userId): ?TeleHealthPersonSettings
    {
        $records = QueryUtils::fetchRecords("Select id,user_id, patient_id,date_created,date_updated,enabled "
        . " from comlink_telehealth_person_settings WHERE user_id = ?", [$userId]);
        if (!empty($records[0])) {
            return $this->createResultRecordFromDatabaseResult($records[0]);
        }
        return null;
    }

    public function getEnabledUsers(): array
    {
        $records = QueryUtils::fetchRecords("Select id,user_id, patient_id,date_created,date_updated,enabled "
            . " from comlink_telehealth_person_settings WHERE enabled = 1 ORDER BY user_id ");
        if (!empty($records)) {
            return array_map(function ($record) {
                return $this->createResultRecordFromDatabaseResult($record);
            }, $records);
        }
        return [];
    }

    private function createResultRecordFromDatabaseResult($row): TeleHealthPersonSettings
    {
        $dateFormat = "Y-m-d H:i:s";

        $settings = new TeleHealthPersonSettings();
        $settings->setId($row['id'] ?? null);
        $settings->setIsPatient(!empty($row['patient_id']));
        $settings->setIsEnabled($row['enabled'] == '1');

        if ($settings->getIsPatient()) {
            $settings->setDbRecordId($row['patient_id']);
        } else {
            $settings->setDbRecordId($row['user_id']);
        }

        if (isset($row['date_created'])) {
            $date = \DateTime::createFromFormat($dateFormat, $row['date_created']);
            if ($date !== false) {
                $settings->setDateCreated($date);
            } else {
                $this->logger->errorLogCaller('failed to create date_created', ['value' => $row['date_created']]);
            }
        }
        if (isset($row['date_updated'])) {
            $date = \DateTime::createFromFormat($dateFormat, $row['date_updated']);
            if ($date !== false) {
                $settings->setDateUpdated($date);
            } else {
                $this->logger->errorLogCaller('failed to create date_updated', ['value' => $row['date_updated']]);
            }
        }
        return $settings;
    }
}
