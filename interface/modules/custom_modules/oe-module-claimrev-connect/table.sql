-- This table definition is loaded and then executed when the OpenEMR interface's install button is clicked.
CREATE TABLE IF NOT EXISTS `mod_claimrev_eligibility`(
    `id` INT(11)  PRIMARY KEY AUTO_INCREMENT NOT NULL
    ,`pid` bigint(20)
    ,`payer_responsibility` varchar(2)
    ,`request_json` TEXT
    ,`response_json` TEXT
	,`eligibility_json` TEXT
	,`individual_json` TEXT
    ,`response_message` TEXT
    ,`status` varchar(25)
    ,`last_checked` datetime default NULL
    ,`create_date` datetime default NULL
    ,`raw271` MEDIUMTEXT
);

-- Add the background service for sending claims
#IfNotRow background_services name ClaimRev_Send
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Send', 'Send Claims To ClaimRev', 1, 0, '2017-05-09 17:39:10', 1, 'start_X12_Claimrev_send_files', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php', 100);
#Endif

#IfNotRow background_services name ClaimRev_Receive
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Receive', 'Get Reports from ClaimRev', 1, 0, '2017-05-09 17:39:10', 240, 'start_X12_Claimrev_get_reports', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Billing_Claimrev_Service.php', 100);
#Endif

#IfNotRow background_services name ClaimRev_Elig_Send_Receive
INSERT INTO `background_services` (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('ClaimRev_Elig_Send_Receive', 'Send and Receive Eligibility from ClaimRev', 1, 0, '2017-05-09 17:39:10', 1, 'start_send_eligibility', '/interface/modules/custom_modules/oe-module-claimrev-connect/src/Eligibility_ClaimRev_Service.php', 100);
#Endif

