
CREATE TABLE IF NOT EXISTS `module_faxsms_credentials` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`auth_user` int(11) UNSIGNED DEFAULT 0,
`vendor` varchar(63) DEFAULT NULL,
`credentials` mediumblob NOT NULL,
`updated` datetime NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`id`),
UNIQUE KEY `vendor` (`auth_user`,`vendor`)
) ENGINE=InnoDB COMMENT='Vendor credentials for Fax/SMS';

#IfNotRow categories name FAX
SET @max_rght = (SELECT MAX(rght) FROM categories);
INSERT INTO categories(`id`,`name`, `value`, `parent`, `lft`, `rght`, `aco_spec`) select (select MAX(id) from categories) + 1, 'FAX', '', 1, @max_rght, @max_rght + 1, 'patients|docs' from categories where name = 'Categories';
UPDATE categories SET rght = rght + 2 WHERE name = 'Categories';
UPDATE categories_seq SET id = (select MAX(id) from categories);
#Endif
