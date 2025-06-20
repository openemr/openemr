CREATE TABLE IF NOT EXISTS `form_hand` (
  `id`                  bigint(20)   NOT NULL auto_increment,
  `date`                datetime     DEFAULT NULL,
  `pid`                 bigint(20)   NOT NULL DEFAULT 0,
  `user`                varchar(255) DEFAULT NULL,
  `groupname`           varchar(255) DEFAULT NULL,
  `authorized`          tinyint(4)   NOT NULL DEFAULT 0,
  `activity`            tinyint(4)   NOT NULL DEFAULT 0,
  `left_1`           text,
  `left_2`           text,
  `left_3`           text,
  `right_1`           text,
  `right_2`           text,
  `right_3`           text,
  `handedness`           text,
  `notes`           text, 
  PRIMARY KEY (id)
) ENGINE=InnoDB;
