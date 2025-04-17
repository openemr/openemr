-- This table definition is loaded and then executed when the OpenEMR interface's install button is clicked.
CREATE TABLE IF NOT EXISTS `mod_custom_skeleton_records`(
    `id` INT(11)  PRIMARY KEY AUTO_INCREMENT NOT NULL
    ,`name` VARCHAR(255) NOT NULL
);