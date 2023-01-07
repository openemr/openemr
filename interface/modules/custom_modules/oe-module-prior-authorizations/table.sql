
#IfNotTable module_prior_authorizations
CREATE TABLE IF NOT EXISTS `module_prior_authorizations`
(
    `id`  INT NOT NULL PRIMARY KEY auto_increment,
    `pid` bigint(20) NULL,
    `auth_num` VARCHAR(20) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `cpt` TEXT NULL,
    `init_units` INT(5) NULL,
    `remaining_units` INT(5) NULL
) ENGINE = InnoDB COMMENT = 'Store authorizations';
#EndIf
