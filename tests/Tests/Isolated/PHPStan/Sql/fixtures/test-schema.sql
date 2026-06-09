-- Minimal schema fixture for SqlReservedWordRuleTest.
-- Provides identifiers that collide with MySQL 8+ reserved words so the
-- rule's (reserved ∩ schema-identifier) gate can fire deterministically
-- without depending on OpenEMR's full sql/database.sql.

DROP TABLE IF EXISTS `contact_telecom`;
CREATE TABLE `contact_telecom` (
  `id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `system` varchar(32) DEFAULT NULL,
  `use` varchar(32) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT NULL,
  `status` char(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
