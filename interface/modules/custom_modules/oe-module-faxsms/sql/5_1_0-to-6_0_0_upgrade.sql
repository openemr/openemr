
#IfMissingColumn module_faxsms_credentials updated
ALTER TABLE `module_faxsms_credentials` ADD `updated` DATETIME DEFAULT CURRENT_TIMESTAMP;
#EndIf

#IfIndex oe_faxsms_queue uniq_account_job_id
ALTER TABLE `oe_faxsms_queue` DROP INDEX `uniq_account_job_id`;
#EndIf

#IfNotColumnType oe_faxsms_queue account varchar(63)
ALTER TABLE `oe_faxsms_queue` MODIFY `account` VARCHAR(63) DEFAULT NULL;
#EndIf

#IfNotColumnType oe_faxsms_queue job_id varchar(128)
ALTER TABLE `oe_faxsms_queue` MODIFY `job_id` VARCHAR(128) DEFAULT NULL COMMENT 'Guid of fax';
#EndIf

#IfNotIndex oe_faxsms_queue uniq_account_job_id_v2
DELETE FROM `oe_faxsms_queue` WHERE `job_id` IS NULL OR TRIM(`job_id`) = '' OR TRIM(`job_id`) = 'NULL';
DELETE FROM oe_faxsms_queue WHERE id NOT IN ( SELECT id FROM ( SELECT MIN(id) AS id FROM oe_faxsms_queue GROUP BY account, job_id) keep);
ALTER TABLE `oe_faxsms_queue` ADD UNIQUE KEY `uniq_account_job_id_v2` (`account`, `job_id`);
#EndIf

#IfMissingColumn oe_faxsms_queue status
ALTER TABLE `oe_faxsms_queue` ADD `status` varchar(50) DEFAULT NULL COMMENT 'Fax status (queued, sent, delivered, received, failed, etc)';
#EndIf

#IfMissingColumn oe_faxsms_queue direction
ALTER TABLE `oe_faxsms_queue` ADD `direction` varchar(20) DEFAULT 'inbound' COMMENT 'inbound or outbound';
#EndIf

#IfMissingColumn oe_faxsms_queue site_id
ALTER TABLE `oe_faxsms_queue` ADD `site_id` varchar(63) DEFAULT 'default' COMMENT 'Site identifier for multi-site support';
#EndIf

#IfMissingColumn oe_faxsms_queue patient_id
ALTER TABLE `oe_faxsms_queue` ADD `patient_id` int(11) DEFAULT NULL COMMENT 'Patient ID if assigned';
ALTER TABLE `oe_faxsms_queue` ADD KEY `patient_id` (`patient_id`);
#EndIf

#IfMissingColumn oe_faxsms_queue document_id
ALTER TABLE `oe_faxsms_queue` ADD `document_id` int(11) DEFAULT NULL COMMENT 'OpenEMR document ID if stored';
#EndIf

#IfMissingColumn oe_faxsms_queue media_path
ALTER TABLE `oe_faxsms_queue` ADD `media_path` longtext COMMENT 'Path to stored fax media file';
#EndIf
