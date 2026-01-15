
CREATE TABLE IF NOT EXISTS `module_faxsms_credentials` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`auth_user` int(11) UNSIGNED DEFAULT 0,
`vendor` varchar(63) DEFAULT NULL,
`credentials` mediumblob NOT NULL,
`updated` datetime DEFAULT current_timestamp(),
`setup_persist` tinytext,
PRIMARY KEY (`id`),
UNIQUE KEY `vendor` (`auth_user`,`vendor`)
) ENGINE=InnoDB COMMENT='Vendor credentials for Fax/SMS';

CREATE TABLE IF NOT EXISTS `oe_faxsms_queue` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`account` tinytext,
`uid` int(11) DEFAULT NULL,
`job_id` text COMMENT 'Guid of fax',
`date` datetime DEFAULT current_timestamp(),
`receive_date` datetime DEFAULT NULL,
`deleted` int(1) NOT NULL DEFAULT 0,
`calling_number` tinytext,
`called_number` tinytext,
`mime` tinytext,
`details_json` longtext,
`status` varchar(50) DEFAULT NULL COMMENT 'Fax status (queued, sent, delivered, received, failed, etc)',
`direction` varchar(20) DEFAULT 'inbound' COMMENT 'inbound or outbound',
`site_id` varchar(63) DEFAULT 'default' COMMENT 'Site identifier for multi-site support',
`patient_id` int(11) DEFAULT NULL COMMENT 'Patient ID if assigned',
`document_id` int(11) DEFAULT NULL COMMENT 'OpenEMR document ID if stored',
`media_path` longtext COMMENT 'Path to stored fax media file',
PRIMARY KEY (`id`),
KEY `uid` (`uid`,`receive_date`),
KEY `job_id` (`job_id`(255)),
KEY `site_id` (`site_id`),
KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB COMMENT='Fax queue';

#IfNotRow categories name FAX
SET @max_rght = (SELECT MAX(rght) FROM categories);
INSERT INTO categories(`id`,`name`, `value`, `parent`, `lft`, `rght`, `aco_spec`) select (select MAX(id) from categories) + 1, 'FAX', '', 1, @max_rght, @max_rght + 1, 'patients|docs' from categories where name = 'Categories';
UPDATE categories SET rght = rght + 2 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#Endif

#IfMissingColumn module_faxsms_credentials updated
ALTER TABLE `module_faxsms_credentials` ADD `updated` DATETIME DEFAULT CURRENT_TIMESTAMP;
#Endif

#IfMissingColumn oe_faxsms_queue status
ALTER TABLE `oe_faxsms_queue` ADD `status` varchar(50) DEFAULT NULL COMMENT 'Fax status (queued, sent, delivered, received, failed, etc)';
#Endif

#IfMissingColumn oe_faxsms_queue direction
ALTER TABLE `oe_faxsms_queue` ADD `direction` varchar(20) DEFAULT 'inbound' COMMENT 'inbound or outbound';
#Endif

#IfMissingColumn oe_faxsms_queue site_id
ALTER TABLE `oe_faxsms_queue` ADD `site_id` varchar(63) DEFAULT 'default' COMMENT 'Site identifier for multi-site support';
ALTER TABLE `oe_faxsms_queue` ADD KEY `site_id` (`site_id`);
#Endif

#IfMissingColumn oe_faxsms_queue patient_id
ALTER TABLE `oe_faxsms_queue` ADD `patient_id` int(11) DEFAULT NULL COMMENT 'Patient ID if assigned';
ALTER TABLE `oe_faxsms_queue` ADD KEY `patient_id` (`patient_id`);
#Endif

#IfMissingColumn oe_faxsms_queue document_id
ALTER TABLE `oe_faxsms_queue` ADD `document_id` int(11) DEFAULT NULL COMMENT 'OpenEMR document ID if stored';
#Endif

#IfMissingColumn oe_faxsms_queue media_path
ALTER TABLE `oe_faxsms_queue` ADD `media_path` longtext COMMENT 'Path to stored fax media file';
#Endif

#IfMissingColumn oe_faxsms_queue job_id
ALTER TABLE `oe_faxsms_queue` ADD KEY `job_id` (`job_id`(255));
#Endif
