#IfNotTable weno_pharmacy
CREATE TABLE `weno_pharmacy` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `App` varchar(8) DEFAULT NULL,
  `NCPDP` varchar(8) DEFAULT NULL,
  `NCPDP_safe` varchar(7) DEFAULT NULL,
  `Mutually_Defined_ID` varchar(10) DEFAULT NULL,
  `Mutually_Defined_ID_safe` varchar(10) DEFAULT NULL,
  `NPI` varchar(10) DEFAULT NULL,
  `NPI_safe` varchar(10) DEFAULT NULL,
  `Business_Name` varchar(255) DEFAULT NULL,
  `Address_Line_1` varchar(255) DEFAULT NULL,
  `Address_Line_2` varchar(255) DEFAULT NULL,
  `City` varchar(20) DEFAULT NULL,
  `State` varchar(20) DEFAULT NULL,
  `ZipCode` varchar(5) DEFAULT NULL,
  `ZipCode_safe` varchar(10) DEFAULT NULL,
  `Country_Code` varchar(255) DEFAULT NULL,
  `International` tinyint(1) DEFAULT NULL,
  `Latitude` varchar(255) DEFAULT NULL,
  `Longitude` varchar(255) DEFAULT NULL,
  `Pharmacy_Phone` varchar(255) DEFAULT NULL,
  `Pharmacy_Phone_safe` varchar(255) DEFAULT NULL,
  `Pharmacy_Fax` varchar(255) DEFAULT NULL,
  `Types` varchar(255) DEFAULT NULL,
  `Script_Msg_Accepted` varchar(255) DEFAULT NULL,
  `Specialized_Msg_Accepted` varchar(255) DEFAULT NULL,
  `Connectivity_Status` varchar(255) DEFAULT NULL,
  `Accept_TSO` varchar(255) DEFAULT NULL,
  `DEA_Audit_Exp` varchar(255) DEFAULT NULL,
  `Test_Pharmacy` varchar(5) DEFAULT NULL,
  `State_Wide_Mail_Order` varchar(6) NOT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  `Deleted` datetime DEFAULT NULL,
  `24HR` varchar(3) DEFAULT NULL,
  `on_weno` tinytext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ncpdp` (`NCPDP`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable weno_assigned_pharmacy
CREATE TABLE `weno_assigned_pharmacy` (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `pid` BIGINT(20) NOT NULL,
    `primary_ncpdp` VARCHAR(8) NOT NULL,
    `alternate_ncpdp` VARCHAR(8) NOT NULL,
    KEY (`pid`),
    PRIMARY KEY(`id`)
) ENGINE=InnoDB;
#EndIf

#IfNotTable weno_download_log
CREATE TABLE `weno_download_log` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `value` VARCHAR(12) NOT NULL,
    `status` VARCHAR(10) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `value` (`value`)
) ENGINE=InnoDB;
#EndIf

-- For early adopters of weno, in case they need to upgrade let's delete and add below.
#IfRow background_services name WenoExchange
DELETE FROM `background_services` WHERE `name` = 'WenoExchange';
#EndIf

#IfNotRow background_services name WenoExchangePharmacies
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) 
VALUES ('WenoExchangePharmacies', 'Weno Exchange Pharmacy', '0', '0', current_timestamp(), '1440', 'downloadWenoPharmacy', '/interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php', '100');
#EndIf

#IfNotRow background_services name WenoExchange
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) 
VALUES ('WenoExchange', 'Weno Log Sync', '0', '0', current_timestamp(), '30', 'downloadWenoPrescriptionLog', '/interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php', '100');
#EndIf

#IfRow globals gl_name weno_provider_password
UPDATE `globals` SET gl_name='weno_admin_password' WHERE gl_name='weno_provider_password';
#EndIf

#IfRow globals gl_name weno_provider_username
UPDATE `globals` SET gl_name='weno_admin_username' WHERE gl_name='weno_provider_username';
#EndIf

