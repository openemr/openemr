
#IfNotTable module_prior_authorizations
CREATE TABLE IF NOT EXISTS `module_prior_authorizations`
(
    `id`  INT NOT NULL PRIMARY KEY auto_increment,
    `pid` bigint(20) DEFAULT NULL,
    `auth_num` VARCHAR(20) NOT NULL,
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `cpt` TEXT,
    `init_units` INT(5) NULL,
    `remaining_units` INT(5) NULL
) ENGINE = InnoDB COMMENT = 'Store authorizations';
#EndIf
