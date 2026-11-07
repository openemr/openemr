-- Grapheus by Exetazo. Run by OpenEMR's Module Manager on install.
#IfNotTable grapheus_keys
CREATE TABLE IF NOT EXISTS `grapheus_keys` (
  `user_id` BIGINT NOT NULL PRIMARY KEY COMMENT 'users.id',
  `key_enc` TEXT NOT NULL COMMENT 'Grapheus key, encrypted with OpenEMR CryptoGen',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `connected_at` DATETIME NOT NULL
) ENGINE = InnoDB COMMENT = 'Grapheus: each clinician connects their own Grapheus account';
#EndIf

#IfNotTable grapheus_links
CREATE TABLE IF NOT EXISTS `grapheus_links` (
  `visit_id` VARCHAR(40) NOT NULL PRIMARY KEY COMMENT 'Grapheus visit id',
  `pid` BIGINT NOT NULL,
  `encounter` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `applied_at` DATETIME DEFAULT NULL,
  `applied_by` BIGINT DEFAULT NULL,
  `summary` TEXT,
  KEY `grapheus_links_enc` (`pid`, `encounter`)
) ENGINE = InnoDB COMMENT = 'Grapheus: which recording belongs to which encounter, and what was applied';
#EndIf

#IfNotTable grapheus_settings
CREATE TABLE IF NOT EXISTS `grapheus_settings` (
  `name` VARCHAR(64) NOT NULL PRIMARY KEY,
  `value` TEXT
) ENGINE = InnoDB COMMENT = 'Grapheus module settings';
INSERT IGNORE INTO `grapheus_settings` (`name`, `value`) VALUES ('server', 'https://scribe.exetazohealth.com');
#EndIf

#IfNotTable grapheus_setup_log
CREATE TABLE IF NOT EXISTS `grapheus_setup_log` (
  `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `at` DATETIME NOT NULL,
  `user_id` BIGINT NOT NULL,
  `op` VARCHAR(64) NOT NULL,
  `args` TEXT,
  `before_json` TEXT COMMENT 'values before the change, for undo',
  `undo_json` TEXT,
  `undone_at` DATETIME DEFAULT NULL
) ENGINE = InnoDB COMMENT = 'Grapheus Assistant: every approved change, and how to undo it';
#EndIf
