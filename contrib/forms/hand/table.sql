CREATE TABLE IF NOT EXISTS `form_hand` (
  `id`                  bigint(20)   NOT NULL auto_increment,
  `date`                datetime     DEFAULT NULL,
  `pid`                 bigint(20)   NOT NULL DEFAULT 0,
  `user`                varchar(255) DEFAULT NULL,
  `groupname`           varchar(255) DEFAULT NULL,
  `authorized`          tinyint(4)   NOT NULL DEFAULT 0,
  `activity`            tinyint(4)   NOT NULL DEFAULT 0,
  `left_1`           text NOT NULL DEFAULT '',
  `left_2`           text NOT NULL DEFAULT '',
  `left_3`           text NOT NULL DEFAULT '',
  `right_1`           text NOT NULL DEFAULT '',
  `right_2`           text NOT NULL DEFAULT '',
  `right_3`           text NOT NULL DEFAULT '',
  `handedness`           text NOT NULL DEFAULT '',
  `notes`           text NOT NULL DEFAULT '', 
  PRIMARY KEY (id)
) TYPE=InnoDB;
