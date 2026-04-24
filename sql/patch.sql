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

-- OIDC external identity mapping (core — links local users to external IdP identities)
-- Uniqueness uses SHA-256 hashes of the full issuer/external_id values: a
-- prefix-based unique key on the raw VARCHARs would let two pairs sharing the
-- first 255 chars collide and the upsert would rewrite the wrong row.
#IfNotTable oidc_external_identity
CREATE TABLE `oidc_external_identity` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT NOT NULL COMMENT 'FK to users.id',
    `issuer` VARCHAR(512) NOT NULL COMMENT 'OIDC iss claim',
    `external_id` VARCHAR(512) NOT NULL COMMENT 'OIDC sub claim',
    `issuer_sha256` BINARY(32) GENERATED ALWAYS AS (UNHEX(SHA2(`issuer`, 256))) STORED,
    `external_id_sha256` BINARY(32) GENERATED ALWAYS AS (UNHEX(SHA2(`external_id`, 256))) STORED,
    `email` VARCHAR(255) DEFAULT NULL COMMENT 'email at time of linking',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_issuer_external_id_hash` (`issuer_sha256`, `external_id_sha256`),
    UNIQUE KEY `uq_user_id` (`user_id`),
    KEY `idx_issuer_external_id` (`issuer`(255), `external_id`(255)),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
#EndIf

-- Migrate older oidc_external_identity tables (created by an earlier version of
-- this branch) to the hash-based unique key.
#IfMissingColumn oidc_external_identity issuer_sha256
ALTER TABLE `oidc_external_identity`
    DROP INDEX `uq_issuer_external_id`,
    ADD COLUMN `issuer_sha256` BINARY(32) GENERATED ALWAYS AS (UNHEX(SHA2(`issuer`, 256))) STORED AFTER `external_id`,
    ADD COLUMN `external_id_sha256` BINARY(32) GENERATED ALWAYS AS (UNHEX(SHA2(`external_id`, 256))) STORED AFTER `issuer_sha256`,
    ADD UNIQUE KEY `uq_issuer_external_id_hash` (`issuer_sha256`, `external_id_sha256`),
    ADD KEY `idx_issuer_external_id` (`issuer`(255), `external_id`(255));
#EndIf

-- OIDC token revocation list (immediate-lockout entries for valid tokens)
-- Uniqueness uses a SHA-256 hash of the full jti (see note on
-- oidc_external_identity above). The hash lives on a UNIQUE KEY rather than
-- the PRIMARY KEY because MariaDB does not allow generated columns in primary
-- keys (MDEV-12862); a surrogate `id` provides the primary key.
#IfNotTable oidc_token_revocation
CREATE TABLE `oidc_token_revocation` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `jti` VARCHAR(512) NOT NULL COMMENT 'JWT ID claim',
    `jti_sha256` BINARY(32) GENERATED ALWAYS AS (UNHEX(SHA2(`jti`, 256))) STORED,
    `revoked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `token_expiry` DATETIME NOT NULL COMMENT 'When token would naturally expire',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_jti_hash` (`jti_sha256`),
    KEY `idx_jti` (`jti`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
#EndIf

-- Migrate older oidc_token_revocation tables to the hash-based unique key.
#IfMissingColumn oidc_token_revocation jti_sha256
ALTER TABLE `oidc_token_revocation`
    DROP PRIMARY KEY,
    ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD COLUMN `jti_sha256` BINARY(32) GENERATED ALWAYS AS (UNHEX(SHA2(`jti`, 256))) STORED AFTER `jti`,
    ADD UNIQUE KEY `uq_jti_hash` (`jti_sha256`),
    ADD KEY `idx_jti` (`jti`(255));
#EndIf
