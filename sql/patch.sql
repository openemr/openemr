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


#IfNotTable track_events
CREATE TABLE `track_events` (
    `id`                  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_type`     TEXT,
    `event_label`    VARCHAR(255) DEFAULT NULL,
    `event_url`       TEXT,
    `event_target`  TEXT,
    `first_event`     DATETIME NULL,
    `last_event`     DATETIME NULL,
    `label_count`    INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_event_label` (`event_label`)
) ENGINE = InnoDB COMMENT = 'Telemetry Event Data';
#EndIf

#IfMissingColumn product_registration auth_by_id
ALTER TABLE `product_registration` ADD `auth_by_id` INT(11) NULL;
#EndIf

#IfMissingColumn product_registration telemetry_disabled
ALTER TABLE `product_registration` ADD `telemetry_disabled` TINYINT(1) NULL COMMENT '1 disabled. NULL ask. 0 use option scopes';
#EndIf

#IfMissingColumn product_registration last_ask_date
ALTER TABLE `product_registration` ADD `last_ask_date` DATETIME NULL;
#EndIf

#IfMissingColumn product_registration last_ask_version
ALTER TABLE `product_registration` ADD `last_ask_version` TINYTEXT;
#EndIf

#IfMissingColumn product_registration options
ALTER TABLE `product_registration` ADD `options` TEXT COMMENT 'JSON array of scope options';
#EndIf

