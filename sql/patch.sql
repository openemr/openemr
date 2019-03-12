--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the colname in the table_name table does not exist,  the block will be executed

--  #IfNotColumnType
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed

--  #IfNotRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a row where colname = value, the block will be executed.

--  #IfNotRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfNotRow3D
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfNotRow4D
--    arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3 AND colname4 = value4, the block will be executed.

--  #IfNotRow2Dx2
--    desc:      This is a very specialized function to allow adding items to the list_options table to avoid both redundant option_id and title in each element.
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  The block will be executed if both statements below are true:
--               1) The table table_name does not have a row where colname = value AND colname2 = value2.
--               2) The table table_name does not have a row where colname = value AND colname3 = value3.

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #EndIf
--    all blocks are terminated with and #EndIf statement.

#IfNotColumnType onsite_messages sender_id VARCHAR(64)
ALTER TABLE `onsite_messages` CHANGE `sender_id` `sender_id` VARCHAR(64) NULL COMMENT 'who sent id';
#EndIf

#IfNotColumnType onsite_documents full_document MEDIUMBLOB
ALTER TABLE `onsite_documents` CHANGE `full_document` `full_document` MEDIUMBLOB;
#EndIf

#IfNotColumnType eligibility_verification response_id varchar(32)
ALTER TABLE `eligibility_verification` CHANGE `response_id` `response_id` VARCHAR(32) DEFAULT NULL;
#EndIf

#IfNotTable benefit_eligibility
CREATE TABLE `benefit_eligibility` (
    `response_id` bigint(20) NOT NULL,
    `verification_id` bigint(20) NOT NULL,
    `type` varchar(4) DEFAULT NULL,
    `benefit_type` varchar(255) DEFAULT NULL,
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `coverage_level` varchar(255) DEFAULT NULL,
    `coverage_type` varchar(512) DEFAULT NULL,
    `plan_type` varchar(255) DEFAULT NULL,
    `plan_description` varchar(255) DEFAULT NULL,
    `coverage_period` varchar(255) DEFAULT NULL,
    `amount` decimal(5,2) DEFAULT NULL,
    `percent` decimal(3,2) DEFAULT NULL,
    `network_ind` varchar(2) DEFAULT NULL,
    `message` varchar(512) DEFAULT NULL,
    `response_status` enum('A','D') DEFAULT 'A',
    `response_create_date` date DEFAULT NULL,
    `response_modify_date` date DEFAULT NULL
) ENGINE=InnoDB;
#Endif

#IfTable eligibility_response
DROP TABLE `eligibility_response`;
#Endif

