CREATE TABLE IF NOT EXISTS `mod_dorn_routes`(
  `lab_guid` varchar(100)
  ,`route_guid` varchar(100)
  ,`ppid` bigint(20) default NULL
  ,`uid` bigint(20) default NULL
  ,`lab_name` varchar(100) default NULL
  ,`lab_account_number` varchar(100) default NULL
  ,`text_line_break_character` varchar(100) default NULL
  ,PRIMARY KEY (`lab_guid`, `route_guid`)
);

CREATE TABLE IF NOT EXISTS `mod_dorn_compendium` (
  `compendium_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `lab_guid` VARCHAR(100) NULL,
  `version` VARCHAR(50) NULL,
  `lab_type` VARCHAR(30) NULL,
  PRIMARY KEY (`compendium_id`));

  CREATE TABLE IF NOT EXISTS `mod_dorn_orderable_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `compendium_id` BIGINT(20) NULL,
  `code` VARCHAR(50) NULL,
  `loinc` VARCHAR(10) NULL,
  `name` VARCHAR(255) NULL,
  `unique_name` VARCHAR(305) NULL,
  `testing_frequency` VARCHAR(255) NULL,
  `is_active` BIT NULL,
  `specimen_limit` INT NULL,
  `specimen_type_notes` VARCHAR(500) NULL,
  `orderable_item_type` VARCHAR(45) NULL,
  PRIMARY KEY (`id`));
