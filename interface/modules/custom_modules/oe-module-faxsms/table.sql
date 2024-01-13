
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
PRIMARY KEY (`id`),
KEY `uid` (`uid`,`receive_date`)
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
