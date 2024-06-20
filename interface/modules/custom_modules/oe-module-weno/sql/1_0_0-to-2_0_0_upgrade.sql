
#IfColumn weno_pharmacy App
DROP TABLE IF EXISTS `weno_pharmacy`;
#EndIf

#IfNotTable weno_pharmacy
CREATE TABLE `weno_pharmacy` (
     `id` bigint(20) NOT NULL AUTO_INCREMENT,
     `Created` datetime DEFAULT NULL,
     `Modified` datetime DEFAULT NULL,
     `Deleted` datetime DEFAULT NULL,
     `NCPDP_safe` varchar(20) DEFAULT NULL,
     `Mutually_Defined_ID_safe` varchar(10) DEFAULT NULL,
     `NPI_safe` varchar(12) DEFAULT NULL,
     `Business_Name` varchar(255) DEFAULT NULL,
     `Address_Line_1` varchar(255) DEFAULT NULL,
     `Address_Line_2` varchar(255) DEFAULT NULL,
     `City` varchar(255) DEFAULT NULL,
     `State` varchar(20) DEFAULT NULL,
     `ZipCode_safe` varchar(11) DEFAULT NULL,
     `Country_Code` varchar(64) DEFAULT NULL,
     `International` varchar(5) DEFAULT NULL,
     `Latitude` varchar(255) DEFAULT NULL,
     `Longitude` varchar(255) DEFAULT NULL,
     `Pharmacy_Phone_safe` varchar(24) DEFAULT NULL,
     `Test_Pharmacy` varchar(15) DEFAULT NULL,
     `State_Wide_Mail_Order` varchar(15) NOT NULL,
     `Mail_Order_US_State_Serviced` varchar(255) DEFAULT NULL,
     `Mail_Order_ US_Territories_Serviced` varchar(255) DEFAULT NULL,
     `On_WENO` varchar(10) DEFAULT NULL,
     `24HR` varchar(3) DEFAULT NULL,
     PRIMARY KEY (`id`),
     UNIQUE KEY `ncpdp` (`NCPDP_safe`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable weno_assigned_pharmacy
CREATE TABLE `weno_assigned_pharmacy` (
  `id`              INT(10)    NOT NULL AUTO_INCREMENT,
  `pid`             BIGINT(20) NOT NULL,
  `primary_ncpdp`   VARCHAR(8) NOT NULL,
  `alternate_ncpdp` VARCHAR(8) NOT NULL,
  `is_history`      TINYINT(1) DEFAULT 0,
  `search_persist`  TINYTEXT,
  PRIMARY KEY (`id`),
  KEY (`pid`)
) ENGINE = InnoDB;
#EndIf

#IfNotTable weno_download_log
CREATE TABLE `weno_download_log` (
 `id`         BIGINT(20)   NOT NULL AUTO_INCREMENT,
 `value`      VARCHAR(63)  NOT NULL,
 `status`     VARCHAR(255) NOT NULL,
 `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 KEY `value` (`value`)
) ENGINE = InnoDB;
#EndIf

-- For early adopters of weno, in case they need to upgrade let's delete and add below.
#IfRow background_services name WenoExchange
DELETE FROM `background_services` WHERE `name` = 'WenoExchange';
#EndIf

#IfNotRow background_services name WenoExchangePharmacies
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES ('WenoExchangePharmacies', 'Weno Exchange Pharmacy', '0', '0', current_timestamp(), '1440', 'downloadWenoPharmacy', '/interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php', '100');
#EndIf

#IfNotRow background_services name WenoExchange
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES ('WenoExchange', 'Weno Log Sync', '0', '0', current_timestamp(), '30', 'downloadWenoPrescriptionLog', '/interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php', '100');
#EndIf

#IfRow globals gl_name weno_provider_password
UPDATE `globals` SET gl_name='weno_admin_password' WHERE gl_name = 'weno_provider_password';
#EndIf

#IfRow globals gl_name weno_provider_username
UPDATE `globals` SET gl_name='weno_admin_username' WHERE gl_name = 'weno_provider_username';
#EndIf

#IfNotColumnType weno_download_log status varchar(255)
ALTER TABLE `weno_download_log` CHANGE `value` `value` VARCHAR(63) NOT NULL, CHANGE `status` `status` VARCHAR(255) NOT NULL;
#EndIf

#IfMissingColumn weno_assigned_pharmacy is_history
ALTER TABLE `weno_assigned_pharmacy` ADD `is_history` TINYINT(1) NOT NULL DEFAULT '0', ADD `search_persist` TINYTEXT;
#EndIf

#IfMissingColumn weno_assigned_pharmacy search_persist
ALTER TABLE `weno_assigned_pharmacy` ADD `search_persist` TINYTEXT;
#EndIf
