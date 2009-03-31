CREATE TABLE IF NOT EXISTS `form_patient_instruction` (
  `id`                  bigint(20)   NOT NULL auto_increment,
  `date`                datetime     DEFAULT NULL,
  `pid`                 bigint(20)   NOT NULL DEFAULT 0,
  `user`                varchar(255) DEFAULT NULL,
  `groupname`           varchar(255) DEFAULT NULL,
  `authorized`          tinyint(4)   NOT NULL DEFAULT 0,
  `activity`            tinyint(4)   NOT NULL DEFAULT 0,
  `instruction`         varchar(30000) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) TYPE=InnoDB;
