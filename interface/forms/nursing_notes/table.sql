CREATE TABLE IF NOT EXISTS `form_nursing_notes` (
  `id`                  bigint(20)   NOT NULL auto_increment,
  `date`                datetime     DEFAULT NULL,
  `pid`                 bigint(20)   NOT NULL DEFAULT 0,
  `user`                varchar(255) DEFAULT NULL,
  `groupname`           varchar(255) DEFAULT NULL,
  `authorized`          tinyint(4)   NOT NULL DEFAULT 0,
  `activity`            tinyint(4)   NOT NULL DEFAULT 0,
  `assessment`          varchar(255) NOT NULL DEFAULT '',
  `procedures`          varchar(255) NOT NULL DEFAULT '',
  `discharge`           varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=InnoDB;
