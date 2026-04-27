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

-- OIDC external identity mapping (core — used by any OIDC auth module)
#IfNotTable oidc_external_identity
CREATE TABLE `oidc_external_identity` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT NOT NULL COMMENT 'FK to users.id',
    `issuer` VARCHAR(512) NOT NULL COMMENT 'OIDC iss claim',
    `external_id` VARCHAR(512) NOT NULL COMMENT 'OIDC sub claim',
    `email` VARCHAR(255) DEFAULT NULL COMMENT 'email at time of linking',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_issuer_external_id` (`issuer`(255), `external_id`(255)),
    UNIQUE KEY `uq_user_id` (`user_id`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
#EndIf

-- OIDC cache table (core — used when oidc_cache_backend = 'database')
#IfNotTable oidc_cache
CREATE TABLE `oidc_cache` (
    `cache_key` VARCHAR(255) NOT NULL COMMENT 'PSR-16 cache key',
    `cache_value` LONGBLOB NOT NULL COMMENT 'Serialized CacheEntry',
    `expires_at` DATETIME DEFAULT NULL COMMENT 'NULL = no expiration',
    PRIMARY KEY (`cache_key`),
    KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
#EndIf

-- OIDC token revocation list (core — used by any OIDC auth module)
#IfNotTable oidc_token_revocation
CREATE TABLE `oidc_token_revocation` (
    `jti` VARCHAR(512) NOT NULL COMMENT 'JWT ID claim',
    `revoked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `token_expiry` DATETIME NOT NULL COMMENT 'When token would naturally expire',
    PRIMARY KEY (`jti`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
#EndIf
