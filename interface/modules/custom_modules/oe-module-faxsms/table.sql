
CREATE TABLE IF NOT EXISTS `module_faxsms_credentials` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`auth_user` int(11) UNSIGNED DEFAULT 0,
`vendor` varchar(63) DEFAULT NULL,
`credentials` mediumblob NOT NULL,
`updated` datetime NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`id`),
UNIQUE KEY `vendor` (`auth_user`,`vendor`)
) ENGINE=InnoDB COMMENT='Vendor credentials for Fax/SMS';

