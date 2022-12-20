SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

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
