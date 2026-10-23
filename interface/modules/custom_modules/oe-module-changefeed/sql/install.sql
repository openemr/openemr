--
-- Change Feed module - install SQL
--
-- Creates the changefeed_log table. The per-table change-capture triggers are
-- installed separately from ModuleManagerListener (on module enable) because
-- CREATE TRIGGER cannot be expressed through this #If* install parser.
--
-- @package   OpenEMR
-- @link      https://www.open-emr.org
-- @author    Ahmed Armaan <arman.ahmaed@carer.ai>
-- @copyright Copyright (c) 2026 Ahmed Armaan
-- @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
--

#IfNotTable changefeed_log
CREATE TABLE IF NOT EXISTS `changefeed_log` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'Monotonic change cursor',
    `resource_table` VARCHAR(64) NOT NULL COMMENT 'Source table that changed',
    `resource_type` VARCHAR(64) NOT NULL COMMENT 'FHIR resourceType the row maps to',
    `row_pk` VARCHAR(64) NOT NULL COMMENT 'Primary/business key value of the changed row',
    `row_uuid` VARCHAR(32) DEFAULT NULL COMMENT 'Hex uuid of the row when available (delete-safe id)',
    `op` ENUM('insert','update','delete') NOT NULL COMMENT 'Change operation',
    `changed_at` DATETIME NOT NULL COMMENT 'Transaction time the change was recorded',
    PRIMARY KEY (`id`),
    KEY `idx_changed_at` (`changed_at`)
) ENGINE=InnoDB COMMENT='Change Feed - one row per observed clinical-core change';
#EndIf
