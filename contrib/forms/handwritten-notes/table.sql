/**
 * Handwritten notes database
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 **/

/* The database is only used for first-time setup */
CREATE TABLE IF NOT EXISTS `form_handwritten` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(20) NOT NULL,
    `value` bigint(20) NOT NULL,    
    PRIMARY KEY (id)
) ENGINE=InnoDB;


INSERT INTO `form_handwritten` (`name`, `value`) VALUES ('doc_category', '0');