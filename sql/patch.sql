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

#IfNotTable api_write_audit
CREATE TABLE `api_write_audit` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `client_id` varchar(80) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `patient_id` bigint(20) DEFAULT NULL,
  `resource_type` varchar(80) NOT NULL,
  `resource_uuid` varchar(80) NOT NULL,
  `action` enum('create','update','delete') NOT NULL,
  `scope` text NOT NULL,
  `token_id` varchar(80) NOT NULL,
  `before_snapshot` longtext DEFAULT NULL,
  `after_snapshot` longtext DEFAULT NULL,
  `api_log_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_api_write_audit_client_time` (`client_id`, `created_time`),
  KEY `idx_api_write_audit_user_time` (`user_id`, `created_time`),
  KEY `idx_api_write_audit_patient_time` (`patient_id`, `created_time`),
  KEY `idx_api_write_audit_resource` (`resource_type`, `resource_uuid`),
  KEY `idx_api_write_audit_token` (`token_id`),
  KEY `idx_api_write_audit_api_log` (`api_log_id`)
) ENGINE = InnoDB;
#EndIf
