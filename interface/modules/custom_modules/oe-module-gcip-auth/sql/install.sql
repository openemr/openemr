-- GCIP Auth Module Configuration Table

#IfNotTable module_gcip_config
CREATE TABLE `module_gcip_config` (
    `config_key` VARCHAR(100) NOT NULL,
    `config_value` TEXT,
    PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
#EndIf
