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

#IfNotTable report_results
CREATE TABLE `report_results` (
  `report_id` bigint(20) NOT NULL,
  `field_id` varchar(31) NOT NULL default '',
  `field_value` text,
  PRIMARY KEY (`report_id`,`field_id`)
) ENGINE=MyISAM;
#EndIf

#IfMissingColumn documents_legal_detail dld_moved
ALTER TABLE `documents_legal_detail` ADD COLUMN `dld_moved` tinyint(4) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn documents_legal_detail dld_patient_comments
ALTER TABLE `documents_legal_detail` ADD COLUMN `dld_patient_comments` text COMMENT 'Patient comments stored here';
#EndIf

#IfMissingColumn documents_legal_master dlm_upload_type
ALTER TABLE `documents_legal_master` ADD COLUMN `dlm_upload_type` tinyint(4) DEFAULT '0' COMMENT '0-Provider Uploaded,1-Patient Uploaded';
#EndIf

